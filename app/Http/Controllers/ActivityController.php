<?php

namespace App\Http\Controllers;

use App\Models\OrderEvent;
use App\Models\PurchaseOrderEvent;
use App\Models\StockMovement;
use Illuminate\Http\Request;

/**
 * One timeline for the whole system.
 *
 * Nothing here is computed from a status column — every row is an event or a
 * movement that was written at the moment something happened, which is the
 * point of keeping those tables append-only. Delete this screen and the data is
 * still correct; delete the append-only tables and this screen is a guess.
 */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $kind = $request->query('kind', 'all');

        $rows = collect();

        if ($kind === 'all' || $kind === 'orders') {
            $rows = $rows->concat(
                OrderEvent::with('order.dealer')->latest('created_at')->latest('id')->limit(120)->get()
                    ->map(fn ($e) => [
                        'at' => $e->created_at,
                        'kind' => 'order',
                        'title' => $e->order?->reference ?? 'Order',
                        'detail' => $e->from_status
                            ? "{$e->from_status} → {$e->to_status}"
                            : "Placed as {$e->to_status}",
                        'who' => $e->order?->dealer?->name,
                        'actor' => $e->actor,
                        'url' => $e->order ? route('orders.show', $e->order) : null,
                    ])
            );
        }

        if ($kind === 'all' || $kind === 'purchasing') {
            $rows = $rows->concat(
                PurchaseOrderEvent::with('purchaseOrder.supplier')->latest('created_at')->latest('id')->limit(120)->get()
                    ->map(fn ($e) => [
                        'at' => $e->created_at,
                        'kind' => 'purchasing',
                        'title' => $e->purchaseOrder?->reference ?? 'Purchase order',
                        'detail' => trim(($e->from_status ? "{$e->from_status} → {$e->to_status}" : "Raised as {$e->to_status}")
                            .($e->detail ? " · {$e->detail}" : '')),
                        'who' => $e->purchaseOrder?->supplier?->name,
                        'actor' => $e->actor,
                        'url' => $e->purchaseOrder ? route('purchasing.show', $e->purchaseOrder) : null,
                    ])
            );
        }

        if ($kind === 'all' || $kind === 'stock') {
            $rows = $rows->concat(
                StockMovement::with('product')->latest('created_at')->latest('id')->limit(120)->get()
                    ->map(fn ($m) => [
                        'at' => $m->created_at,
                        'kind' => 'stock',
                        'title' => $m->product?->name ?? 'Product',
                        'detail' => sprintf('%s%d → balance %d · %s',
                            $m->delta > 0 ? '+' : '', $m->delta, $m->balance_after, $m->reason),
                        'who' => null,
                        'actor' => null,
                        'url' => $m->product ? route('stock.show', $m->product) : null,
                    ])
            );
        }

        return view('activity.index', [
            'rows' => $rows->sortByDesc('at')->values()->take(80),
            'kind' => $kind,
        ]);
    }
}
