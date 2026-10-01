<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::get('/stock/export.csv', [ExportController::class, 'stock'])->name('stock.export');
Route::get('/stock/{product}', [StockController::class, 'show'])->name('stock.show');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/export.csv', [ExportController::class, 'orders'])->name('orders.export');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
Route::post('/orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance');

Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
Route::get('/dealers/{dealer}', [DealerController::class, 'show'])->name('dealers.show');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
