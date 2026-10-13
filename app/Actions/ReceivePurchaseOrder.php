<?php

namespace App\Actions;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderEvent;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Receiving is where stock enters the building, and it is the mirror of
 * dispatch: dispatch writes negative movements, this writes positive ones.
 *
 * It accepts a partial delivery, because suppliers send partial deliveries.
 * Refusing anything short of the full quantity would push the operator into
 * lying to the system, which is how stock records stop being believed.
 *
 * What it refuses is receiving MORE than was ordered. That is almost always a
 * typo, and a silent over-receipt is a stock figure nobody can explain later.
 */
class ReceivePurchaseOrder
{
    /**
     * @param  array<int, int|string>  $quantities  line id => quantity arriving now
     */
    public function __invoke(PurchaseOrder $po, array $quantities): Outcome
    {
        if (! $po->isOpen()) {
            return Outcome::refused("{$po->reference} is {$po->status} and cannot receive stock.");
        }

        $po->loadMissing('items.product');

        $toReceive = [];
        foreach ($po->items as $item) {
            $qty = (int) ($quantities[$item->id] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            if ($qty > $item->outstanding()) {
                return Outcome::refused(sprintf(
                    '%s: trying to receive %d but only %d is outstanding on that line.',
                    $item->product->name,
                    $qty,
                    $item->outstanding()
                ));
            }

            $toReceive[$item->id] = $qty;
        }

        if ($toReceive === []) {
            return Outcome::refused('Nothing was entered to receive.');
        }

        DB::transaction(function () use ($po, $toReceive) {
            $from = $po->status;
            $units = 0;

            foreach ($po->items as $item) {
                if (! isset($toReceive[$item->id])) {
                    continue;
                }

                $qty = $toReceive[$item->id];
                $units += $qty;

                $product = $item->product;
                $product->increment('stock', $qty);

                StockMovement::create([
                    'product_id' => $product->id,
                    'purchase_order_id' => $po->id,
                    'delta' => $qty,
                    'balance_after' => $product->stock,
                    'reason' => "Received on {$po->reference}",
                ]);

                $item->increment('quantity_received', $qty);
            }

            $po->load('items');
            $to = $po->statusFromLines();
            $po->update(['status' => $to]);

            PurchaseOrderEvent::create([
                'purchase_order_id' => $po->id,
                'from_status' => $from,
                'to_status' => $to,
                'detail' => $units.' units received',
                'actor' => 'demo visitor',
            ]);
        });

        $po->refresh();

        return Outcome::applied("{$po->reference} received. It is now {$po->status}.");
    }
}
