<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Support\Facades\DB;

/**
 * An order can be cancelled right up to the moment it costs something.
 *
 * Once it is Dispatched the pipes are on a truck, so cancelling is no longer a
 * change of mind — it is a return, which is a different process with a
 * different paper trail. Pretending otherwise would put stock back on the shelf
 * that is physically somewhere else.
 */
class CancelOrder
{
    public function __invoke(Order $order, string $reason): Outcome
    {
        if ($order->status === 'Cancelled') {
            return Outcome::refused("{$order->reference} is already cancelled.");
        }

        if (in_array($order->status, ['Dispatched', 'Delivered'], true)) {
            return Outcome::refused(
                "{$order->reference} is {$order->status}: the stock has already left the warehouse. ".
                'That is a return, not a cancellation.'
            );
        }

        if (trim($reason) === '') {
            return Outcome::refused('A cancellation needs a reason.');
        }

        DB::transaction(function () use ($order, $reason) {
            $from = $order->status;

            $order->update(['status' => 'Cancelled']);

            OrderEvent::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => 'Cancelled — '.trim($reason),
                'actor' => 'demo visitor',
            ]);
        });

        return Outcome::applied("{$order->reference} cancelled. The dealer's credit is freed immediately.");
    }
}
