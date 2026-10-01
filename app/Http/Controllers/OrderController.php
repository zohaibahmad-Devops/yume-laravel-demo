<?php

namespace App\Http\Controllers;

use App\Actions\AdvanceOrder;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('dealer')->withCount('items')->latest('placed_on')->latest('id');

        if (($status = $request->query('status')) && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('reference', 'like', "%{$q}%")
                ->orWhereHas('dealer', fn ($d) => $d->where('name', 'like', "%{$q}%")));
        }

        return view('orders.index', [
            'orders' => $query->paginate(8)->withQueryString(),
            'status' => $status ?: 'all',
            'statuses' => Order::STATUSES,
            'q' => trim((string) $request->query('q')),
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'dealer',
            'items.product',
            'events' => fn ($q) => $q->oldest('created_at')->oldest('id'),
            'movements.product',
        ]);

        return view('orders.show', ['order' => $order]);
    }

    public function advance(Order $order, AdvanceOrder $advance)
    {
        $order->load('items.product');
        $outcome = $advance($order);

        return redirect()
            ->route('orders.show', $order)
            ->with($outcome->ok ? 'note' : 'warn', $outcome->message);
    }

    public function invoice(Order $order)
    {
        $order->load('dealer', 'items.product');

        return view('orders.invoice', ['order' => $order]);
    }
}
