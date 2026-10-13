<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchaseOrders')->orderBy('name')->get();

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'onOrder' => $suppliers->sum(fn ($s) => $s->onOrderValue()),
        ]);
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders' => fn ($q) => $q->withCount('items')->latest('ordered_on')->latest('id'),
        ]);

        /* How reliable is this supplier? Measured against their own quoted lead
           time, using orders that have actually been received in full. */
        $completed = $supplier->purchaseOrders->where('status', PurchaseOrder::RECEIVED);

        return view('suppliers.show', [
            'supplier' => $supplier,
            'onOrder' => $supplier->onOrderValue(),
            'receivedCount' => $completed->count(),
            'receivedValue' => (float) $completed->sum('total'),
        ]);
    }
}
