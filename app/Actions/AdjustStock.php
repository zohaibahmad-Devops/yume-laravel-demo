<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Every real warehouse needs a way to say "the shelf disagrees with the
 * screen". The temptation is to let someone type the new number straight over
 * the old one, which destroys the only thing that makes a stock figure
 * trustworthy: the explanation of how it got there.
 *
 * So an adjustment is a movement like any other. The operator enters the count
 * they actually made, the system works out the difference, and the reason is
 * required — an unexplained correction is the one thing this refuses.
 */
class AdjustStock
{
    public const REASONS = [
        'Stock count correction',
        'Damaged in warehouse',
        'Damaged in transit',
        'Returned by dealer',
        'Returned to supplier',
        'Sample or site use',
        'Found after recount',
    ];

    public function __invoke(Product $product, int $countedStock, string $reason, ?string $note = null): Outcome
    {
        $reason = trim($reason);

        if ($reason === '') {
            return Outcome::refused('An adjustment needs a reason. A number that changes for no recorded reason cannot be audited.');
        }

        if ($countedStock < 0) {
            return Outcome::refused('A counted quantity cannot be negative.');
        }

        $delta = $countedStock - $product->stock;

        if ($delta === 0) {
            return Outcome::refused("The count matches the system: {$product->name} is already at {$product->stock}.");
        }

        $label = $note !== null && trim($note) !== ''
            ? $reason.' — '.trim($note)
            : $reason;

        DB::transaction(function () use ($product, $countedStock, $delta, $label) {
            $product->update(['stock' => $countedStock]);

            StockMovement::create([
                'product_id' => $product->id,
                'delta' => $delta,
                'balance_after' => $countedStock,
                'reason' => $label,
            ]);
        });

        return Outcome::applied(sprintf(
            '%s adjusted by %s%d to %d. %s',
            $product->name,
            $delta > 0 ? '+' : '',
            $delta,
            $countedStock,
            $label
        ));
    }
}
