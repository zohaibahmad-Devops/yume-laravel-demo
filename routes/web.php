<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/* Stock. The literal segments are declared before {product} so that
   /stock/export.csv is never read as a product called "export.csv". */
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::get('/stock/export.csv', [ExportController::class, 'stock'])->name('stock.export');
Route::get('/stock/{product}', [StockController::class, 'show'])->name('stock.show');
Route::post('/stock/{product}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

/* Selling. */
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/new', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/export.csv', [ExportController::class, 'orders'])->name('orders.export');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
Route::post('/orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

/* Buying — the half that puts stock back on the shelf. */
Route::get('/purchasing', [PurchaseOrderController::class, 'index'])->name('purchasing.index');
Route::get('/purchasing/new', [PurchaseOrderController::class, 'create'])->name('purchasing.create');
Route::post('/purchasing', [PurchaseOrderController::class, 'store'])->name('purchasing.store');
Route::get('/purchasing/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchasing.show');
Route::post('/purchasing/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchasing.receive');

Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');

Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
Route::get('/dealers/{dealer}', [DealerController::class, 'show'])->name('dealers.show');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

/* One timeline across orders, purchasing and stock. */
Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
