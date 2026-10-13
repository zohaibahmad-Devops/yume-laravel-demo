<?php

namespace App\Http\Controllers;

use App\Actions\AdvanceOrder;
use App\Actions\CancelOrder;
use App\Actions\PlaceOrder;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
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

    /**
     * The order form shows each dealer's remaining credit up front, because the
     * question it answers most often is not "what do they want" but "can they
     * have it".
     */
    public function create(Request $request)
    {
        $dealers = Dealer::orderBy('name')->get();

        return view('orders.create', [
            'dealers' => $dealers->map(fn ($d) => [
                'model' => $d,
                'outstanding' => $d->outstanding(),
                'headroom' => (float) $d->credit_limit - $d->outstanding(),
            ]),
            'dealerId' => (int) $request->query('dealer'),
            'products' => Product::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, PlaceOrder $place)
    {
        $dealer = Dealer::findOrFail((int) $request->input('dealer_id'));

        $outcome = $place($dealer, (array) $request->input('lines', []));

        if (! $outcome->ok) {
            return redirect()->back()->withInput()->with('warn', $outcome->message);
        }

        return redirect()->route('orders.index')->with('note', $outcome->message);
    }

    public function cancel(Request $request, Order $order, CancelOrder $cancel)
    {
        $outcome = $cancel($order, (string) $request->input('reason', ''));

        return redirect()
            ->route('orders.show', $order)
            ->with($outcome->ok ? 'note' : 'warn', $outcome->message);
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
