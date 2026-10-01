<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();

        return view('dashboard', [
            'skuCount' => $products->count(),
            'stockValue' => $products->sum(fn ($p) => (float) $p->cost * $p->stock),
            'lowStock' => $products->filter(fn ($p) => $p->isLow())->values(),
            'openOrders' => Order::whereIn('status', ['New', 'Confirmed', 'Packed', 'Dispatched'])->count(),
            'outstanding' => (float) Order::whereIn('status', ['New', 'Confirmed', 'Packed', 'Dispatched'])->sum('total'),
            'dealerCount' => Dealer::count(),
            'recent' => Order::with('dealer')->latest('placed_on')->take(6)->get(),
            'byStatus' => collect(Order::STATUSES)->mapWithKeys(
                fn ($s) => [$s => Order::where('status', $s)->count()]
            ),
        ]);
    }
}
