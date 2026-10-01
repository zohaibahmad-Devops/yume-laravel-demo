<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $products = $this->filtered($request)->paginate(8)->withQueryString();

        /* Totals describe the whole filtered set, not the page you are looking
           at — a footer that only adds up eight rows is worse than no footer. */
        $all = $this->filtered($request)->get();

        return view('stock.index', [
            'products' => $products,
            'totalValue' => $all->sum(fn ($p) => (float) $p->cost * $p->stock),
            'matchCount' => $all->count(),
            'categories' => Product::query()->distinct()->orderBy('category')->pluck('category'),
            'category' => $request->query('category') ?: 'all',
            'lowOnly' => $request->boolean('low'),
            'q' => trim((string) $request->query('q')),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['movements' => fn ($q) => $q->with('order')->latest('created_at')->latest('id')]);

        return view('stock.show', ['product' => $product]);
    }

    private function filtered(Request $request)
    {
        $query = Product::query()->orderBy('name');

        if (($c = $request->query('category')) && $c !== 'all') {
            $query->where('category', $c);
        }

        if ($request->boolean('low')) {
            $query->whereColumn('stock', '<=', 'reorder_level');
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
        }

        return $query;
    }
}
