<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class ReportController extends Controller
{
    public function index()
    {
        $orders = Order::with('dealer')->get();

        /* Group in PHP rather than SQL: the date functions differ between
           SQLite and MySQL, and this demo is small enough that the clarity is
           worth more than the query. */
        $byMonth = $orders
            ->groupBy(fn ($o) => $o->placed_on->format('Y-m'))
            ->map(fn ($group, $month) => [
                'month' => $month,
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'orders' => $group->count(),
                'value' => (float) $group->sum('total'),
            ])
            ->sortKeys()
            ->values();

        $items = OrderItem::with('product', 'order')->get();

        $byCategory = $items
            ->groupBy(fn ($i) => $i->product->category)
            ->map(fn ($group, $category) => [
                'category' => $category,
                'units' => $group->sum('quantity'),
                'revenue' => $group->sum(fn ($i) => $i->lineTotal()),
                'cost' => $group->sum(fn ($i) => (float) $i->product->cost * $i->quantity),
            ])
            ->map(fn ($row) => $row + [
                'margin' => $row['revenue'] > 0
                    ? round((($row['revenue'] - $row['cost']) / $row['revenue']) * 100, 1)
                    : 0.0,
            ])
            ->sortByDesc('revenue')
            ->values();

        $byDealer = $orders
            ->groupBy('dealer_id')
            ->map(fn ($group) => [
                'dealer' => $group->first()->dealer,
                'orders' => $group->count(),
                'value' => (float) $group->sum('total'),
                'open' => (float) $group->whereNotIn('status', ['Delivered'])->sum('total'),
            ])
            ->sortByDesc('value')
            ->values();

        return view('reports.index', [
            'byMonth' => $byMonth,
            'byCategory' => $byCategory,
            'byDealer' => $byDealer,
            'totalValue' => (float) $orders->sum('total'),
            'stockValue' => Product::all()->sum(fn ($p) => (float) $p->cost * $p->stock),
            'dealerCount' => Dealer::count(),
        ]);
    }
}
