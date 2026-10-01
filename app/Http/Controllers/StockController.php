<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $query = Product::query()->orderBy('name');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($request->boolean('low')) {
            $query->whereColumn('stock', '<=', 'reorder_level');
        }

        return view('stock.index', [
            'products' => $query->get(),
            'categories' => Product::query()->distinct()->orderBy('category')->pluck('category'),
            'category' => $category ?: 'all',
            'lowOnly' => $request->boolean('low'),
        ]);
    }
}
