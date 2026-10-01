<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streamed rather than built in memory. These tables are tiny, but a report
 * export is exactly the thing that is small in the demo and 200,000 rows in
 * production, and the shape of the code should not have to change then.
 */
class ExportController extends Controller
{
    public function stock(): StreamedResponse
    {
        return $this->stream('stock.csv',
            ['SKU', 'Product', 'Category', 'Unit', 'Cost', 'Price', 'Margin %', 'Stock', 'Reorder level', 'Value at cost'],
            function ($out) {
                foreach (Product::orderBy('name')->cursor() as $p) {
                    fputcsv($out, [
                        $p->sku, $p->name, $p->category, $p->unit,
                        $p->cost, $p->price, $p->marginPercent(),
                        $p->stock, $p->reorder_level,
                        round((float) $p->cost * $p->stock, 2),
                    ]);
                }
            });
    }

    public function orders(): StreamedResponse
    {
        return $this->stream('orders.csv',
            ['Reference', 'Dealer', 'City', 'Placed on', 'Status', 'Lines', 'Total'],
            function ($out) {
                foreach (Order::with('dealer')->withCount('items')->orderBy('placed_on')->cursor() as $o) {
                    fputcsv($out, [
                        $o->reference, $o->dealer->name, $o->dealer->city,
                        $o->placed_on->format('Y-m-d'), $o->status,
                        $o->items_count, $o->total,
                    ]);
                }
            });
    }

    private function stream(string $filename, array $header, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            $rows($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
