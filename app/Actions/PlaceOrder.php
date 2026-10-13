<?php

namespace App\Actions;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Placing an order is the second operation in this system that touches more
 * than one table, and the only one that has to say no for a commercial reason
 * rather than a physical one.
 *
 * Stock is NOT taken here. An order being placed does not move anything out of
 * the warehouse — that happens at dispatch, in AdvanceOrder. Taking stock at
 * both points is the classic way to end up counting the same pipe twice.
 *
 * What is checked here is credit. A dealer who already owes more than their
 * limit does not get another order on the same account, because the cost of
 * that decision lands weeks later and on somebody else.
 */
class PlaceOrder
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $lines
     */
    public function __invoke(Dealer $dealer, array $lines): Outcome
    {
        $lines = $this->clean($lines);

        if ($lines === []) {
            return Outcome::refused('An order needs at least one line with a quantity above zero.');
        }

        $products = Product::findMany(array_column($lines, 'product_id'))->keyBy('id');

        $total = 0.0;
        foreach ($lines as $line) {
            $product = $products->get($line['product_id']);

            if (! $product) {
                return Outcome::refused('One of the lines refers to a product that no longer exists.');
            }

            $total += $line['quantity'] * (float) $product->price;
        }

        $headroom = (float) $dealer->credit_limit - $dealer->outstanding();

        if ($total > $headroom) {
            return Outcome::refused(sprintf(
                '%s has PKR %s of credit left and this order is PKR %s. Raise the limit or settle an open order first.',
                $dealer->name,
                number_format(max(0, $headroom)),
                number_format($total)
            ));
        }

        $order = DB::transaction(function () use ($dealer, $lines, $products, $total) {
            $order = Order::create([
                'reference' => $this->nextReference(),
                'dealer_id' => $dealer->id,
                'status' => 'New',
                'total' => $total,
                'placed_on' => Carbon::today(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $products->get($line['product_id'])->price,
                ]);
            }

            OrderEvent::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'New',
                'actor' => 'demo visitor',
            ]);

            return $order;
        });

        return Outcome::applied("{$order->reference} placed for {$dealer->name}, PKR ".number_format($total).'.');
    }

    /**
     * Quantities arrive from a form, so they arrive as strings and some of them
     * are blank. Anything that is not a positive whole number is not a line.
     *
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $lines
     * @return array<int, array{product_id: int, quantity: int}>
     */
    private function clean(array $lines): array
    {
        $clean = [];

        foreach ($lines as $line) {
            $qty = (int) ($line['quantity'] ?? 0);
            $id = (int) ($line['product_id'] ?? 0);

            if ($qty > 0 && $id > 0) {
                // The same product picked twice is one line, not two.
                $clean[$id] = ['product_id' => $id, 'quantity' => ($clean[$id]['quantity'] ?? 0) + $qty];
            }
        }

        return array_values($clean);
    }

    private function nextReference(): string
    {
        $n = Order::max('id') + 1;

        return 'SO-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
