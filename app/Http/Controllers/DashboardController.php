<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        $open = ['New', 'Confirmed', 'Packed', 'Dispatched'];

        return view('dashboard', [
            'skuCount' => $products->count(),
            'stockValue' => $products->sum(fn ($p) => (float) $p->cost * $p->stock),
            'lowStock' => $products->filter(fn ($p) => $p->isLow())->values(),
            'openOrders' => Order::whereIn('status', $open)->count(),
            'outstanding' => (float) Order::whereIn('status', $open)->sum('total'),
            'dealerCount' => Dealer::count(),
            'recent' => Order::with('dealer')->latest('placed_on')->latest('id')->take(6)->get(),
            'activity' => OrderEvent::with('order')->latest('created_at')->latest('id')->take(7)->get(),
            'byStatus' => collect(Order::STATUSES)->mapWithKeys(
                fn ($s) => [$s => Order::where('status', $s)->count()]
            ),
        ]);
    }
}
