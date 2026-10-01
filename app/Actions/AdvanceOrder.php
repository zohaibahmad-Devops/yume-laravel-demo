<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Moving an order forward is the only thing in this system that changes more
 * than one table, so it is the only thing worth pulling out of the controller.
 *
 * Dispatching is the step that costs stock. If any line cannot be covered the
 * whole transition is refused — a half-dispatched order with negative stock is
 * worse than an order that stayed put.
 */
class AdvanceOrder
{
    public function __invoke(Order $order): Outcome
    {
        $next = $order->nextStatus();

        if ($next === null) {
            return Outcome::refused("{$order->reference} is already delivered.");
        }

        if ($next === 'Dispatched') {
            if ($short = $this->linesWithoutStock($order)) {
                return Outcome::refused(
                    "{$order->reference} cannot be dispatched: not enough stock of ".
                    implode(', ', $short).'.'
                );
            }
        }

        DB::transaction(function () use ($order, $next) {
            $from = $order->status;

            if ($next === 'Dispatched') {
                $this->releaseStock($order);
            }

            $order->update(['status' => $next]);

            OrderEvent::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $next,
                'actor' => 'demo visitor',
            ]);
        });

        return Outcome::applied("{$order->reference} moved to {$next}.");
    }

    /** @return string[] names of the products that cannot cover their line */
    private function linesWithoutStock(Order $order): array
    {
        return $order->items
            ->filter(fn ($item) => $item->product->stock < $item->quantity)
            ->map(fn ($item) => $item->product->name)
            ->values()
            ->all();
    }

    private function releaseStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            $balance = $product->stock - $item->quantity;

            $product->update(['stock' => $balance]);

            StockMovement::create([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'delta' => -$item->quantity,
                'balance_after' => $balance,
                'reason' => "Dispatched on {$order->reference}",
            ]);
        }
    }
}
