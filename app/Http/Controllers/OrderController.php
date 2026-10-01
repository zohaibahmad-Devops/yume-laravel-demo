<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Order::with('dealer')->withCount('items')->latest('placed_on');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return view('orders.index', [
            'orders' => $query->get(),
            'status' => $status ?: 'all',
            'statuses' => Order::STATUSES,
        ]);
    }

    public function show(Order $order)
    {
        $order->load('dealer', 'items.product');

        return view('orders.show', ['order' => $order]);
    }

    /** Advance one step. The demo re-seeds on every boot, so this is safe to click. */
    public function advance(Order $order)
    {
        if ($next = $order->nextStatus()) {
            $order->update(['status' => $next]);
        }

        return redirect()->route('orders.show', $order)
            ->with('note', "Order {$order->reference} moved to {$order->status}.");
    }
}
