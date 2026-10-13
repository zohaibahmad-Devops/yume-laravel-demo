<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderEvent;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Raising a purchase order is the buying half of the loop. It writes nothing to
 * stock — ordering something does not make it arrive, and a system that adds
 * stock at order time will tell a dealer a pipe is available weeks before it is.
 *
 * The expected date is derived from the supplier's lead time rather than typed,
 * so the "late" column on the purchasing screen means something consistent.
 */
class RaisePurchaseOrder
{
    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $lines
     */
    public function __invoke(Supplier $supplier, array $lines): Outcome
    {
        $lines = $this->clean($lines);

        if ($lines === []) {
            return Outcome::refused('A purchase order needs at least one line with a quantity above zero.');
        }

        $products = Product::findMany(array_column($lines, 'product_id'))->keyBy('id');

        $total = 0.0;
        foreach ($lines as $line) {
            $product = $products->get($line['product_id']);

            if (! $product) {
                return Outcome::refused('One of the lines refers to a product that no longer exists.');
            }

            $total += $line['quantity'] * (float) $product->cost;
        }

        $po = DB::transaction(function () use ($supplier, $lines, $products, $total) {
            $po = PurchaseOrder::create([
                'reference' => $this->nextReference(),
                'supplier_id' => $supplier->id,
                'status' => PurchaseOrder::ORDERED,
                'total' => $total,
                'ordered_on' => Carbon::today(),
                'expected_on' => Carbon::today()->addDays(max(1, (int) $supplier->lead_time_days)),
            ]);

            foreach ($lines as $line) {
                $po->items()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $products->get($line['product_id'])->cost,
                ]);
            }

            PurchaseOrderEvent::create([
                'purchase_order_id' => $po->id,
                'from_status' => null,
                'to_status' => PurchaseOrder::ORDERED,
                'detail' => count($lines).' lines, PKR '.number_format($total),
                'actor' => 'demo visitor',
            ]);

            return $po;
        });

        return Outcome::applied(
            "{$po->reference} raised with {$supplier->name}, PKR ".number_format($total).
            '. Expected '.$po->expected_on->format('j M').'.'
        );
    }

    /**
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
                $clean[$id] = ['product_id' => $id, 'quantity' => ($clean[$id]['quantity'] ?? 0) + $qty];
            }
        }

        return array_values($clean);
    }

    private function nextReference(): string
    {
        $n = PurchaseOrder::max('id') + 1;

        return 'PO-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
