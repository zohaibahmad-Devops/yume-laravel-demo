<?php

namespace App\Http\Controllers;

use App\Actions\AdjustStock;
use App\Models\Product;
use App\Models\PurchaseOrder;
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
        $product->load([
            'movements' => fn ($q) => $q->with('order', 'purchaseOrder')->latest('created_at')->latest('id'),
        ]);

        /* "On order" is the number that stops a buyer ordering the same thing
           twice while the first delivery is still on the road. */
        $onOrder = (int) $product->purchaseOrderItems()
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', [PurchaseOrder::ORDERED, PurchaseOrder::PART_RECEIVED]))
            ->get()
            ->sum(fn ($i) => $i->outstanding());

        return view('stock.show', [
            'product' => $product,
            'onOrder' => $onOrder,
            'reasons' => AdjustStock::REASONS,
        ]);
    }

    public function adjust(Request $request, Product $product, AdjustStock $adjust)
    {
        $outcome = $adjust(
            $product,
            (int) $request->input('counted', 0),
            (string) $request->input('reason', ''),
            $request->input('note')
        );

        return redirect()
            ->route('stock.show', $product)
            ->with($outcome->ok ? 'note' : 'warn', $outcome->message);
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
