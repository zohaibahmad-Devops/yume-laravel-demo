<?php

namespace App\Http\Controllers;

use App\Actions\RaisePurchaseOrder;
use App\Actions\ReceivePurchaseOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier')->withCount('items')
            ->latest('ordered_on')->latest('id');

        if (($status = $request->query('status')) && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('reference', 'like', "%{$q}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$q}%")));
        }

        $open = PurchaseOrder::whereIn('status', [PurchaseOrder::ORDERED, PurchaseOrder::PART_RECEIVED])->get();

        return view('purchasing.index', [
            'orders' => $query->paginate(8)->withQueryString(),
            'status' => $status ?: 'all',
            'statuses' => [
                PurchaseOrder::ORDERED,
                PurchaseOrder::PART_RECEIVED,
                PurchaseOrder::RECEIVED,
                PurchaseOrder::CANCELLED,
            ],
            'q' => trim((string) $request->query('q')),
            'openValue' => (float) $open->sum('total'),
            'openCount' => $open->count(),
            'lateCount' => $open->filter(fn ($po) => ($po->daysLate() ?? 0) > 0)->count(),
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'items.product',
            'events' => fn ($q) => $q->oldest('created_at')->oldest('id'),
            'movements.product',
        ]);

        return view('purchasing.show', ['po' => $purchaseOrder]);
    }

    /** The buying screen opens on what is actually short, not on a blank form. */
    public function create(Request $request)
    {
        $supplierId = (int) $request->query('supplier');

        return view('purchasing.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'supplierId' => $supplierId,
            'products' => Product::orderBy('category')->orderBy('name')->get(),
            'lowProducts' => Product::whereColumn('stock', '<=', 'reorder_level')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, RaisePurchaseOrder $raise)
    {
        $supplier = Supplier::findOrFail((int) $request->input('supplier_id'));

        $outcome = $raise($supplier, (array) $request->input('lines', []));

        if (! $outcome->ok) {
            return redirect()->back()->withInput()->with('warn', $outcome->message);
        }

        return redirect()->route('purchasing.index')->with('note', $outcome->message);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, ReceivePurchaseOrder $receive)
    {
        $outcome = $receive($purchaseOrder, (array) $request->input('receive', []));

        return redirect()
            ->route('purchasing.show', $purchaseOrder)
            ->with($outcome->ok ? 'note' : 'warn', $outcome->message);
    }
}
