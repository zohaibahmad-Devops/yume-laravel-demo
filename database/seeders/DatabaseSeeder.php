<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Demo data for a Rawalpindi pipes-and-fittings distributor. Prices are in
 * PKR and sized so the margin column and the low-stock flags both have
 * something real to show.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['PVC-6-20', 'PVC Pipe 6" x 20ft', 'Pipes', 'length', 3850, 4620, 64, 40],
            ['PVC-4-20', 'PVC Pipe 4" x 20ft', 'Pipes', 'length', 2400, 2960, 118, 40],
            ['PVC-2-20', 'PVC Pipe 2" x 20ft', 'Pipes', 'length', 1180, 1490, 22, 30],
            ['CPL-6', 'Coupler 6"', 'Fittings', 'pc', 410, 560, 62, 80],
            ['CPL-4', 'Coupler 4"', 'Fittings', 'pc', 240, 330, 310, 80],
            ['ELB-4-90', 'Elbow 4" 90°', 'Fittings', 'pc', 265, 370, 196, 60],
            ['TEE-4', 'Tee 4"', 'Fittings', 'pc', 290, 405, 54, 60],
            ['GV-4', 'Gate Valve 4"', 'Valves', 'pc', 4100, 5350, 28, 20],
            ['GV-2', 'Gate Valve 2"', 'Valves', 'pc', 1850, 2480, 9, 15],
            ['CBL-4C-16', 'Cable 4-core 16mm', 'Cables', 'metre', 520, 690, 1240, 400],
            ['CBL-2C-10', 'Cable 2-core 10mm', 'Cables', 'metre', 275, 365, 86, 300],
            ['TNK-1000', 'Water Tank 1000L', 'Tanks', 'pc', 18500, 23400, 11, 8],
        ];

        foreach ($products as [$sku, $name, $category, $unit, $cost, $price, $stock, $reorder]) {
            Product::create([
                'sku' => $sku,
                'name' => $name,
                'category' => $category,
                'unit' => $unit,
                'cost' => $cost,
                'price' => $price,
                'stock' => $stock,
                'reorder_level' => $reorder,
            ]);
        }

        $dealers = [
            ['Mehran Traders', 'Rawalpindi', '+92 300 5551234', 400000],
            ['Sadiq Pipe House', 'Islamabad', '+92 321 5559876', 250000],
            ['Al-Noor Sanitary', 'Rawalpindi', '+92 333 5554321', 180000],
            ['Gujar Khan Hardware', 'Gujar Khan', '+92 345 5557788', 120000],
            ['Taxila Builders Supply', 'Taxila', '+92 312 5552200', 300000],
        ];

        foreach ($dealers as [$name, $city, $phone, $limit]) {
            Dealer::create([
                'name' => $name,
                'city' => $city,
                'phone' => $phone,
                'credit_limit' => $limit,
            ]);
        }

        /* Orders spread across every status so the pipeline is not all one colour. */
        $orders = [
            ['SO-2451', 1, 'Delivered',  '-12 days', [[1, 12], [4, 40]]],
            ['SO-2452', 2, 'Delivered',  '-10 days', [[10, 300]]],
            ['SO-2453', 3, 'Dispatched', '-6 days',  [[2, 24], [6, 50]]],
            ['SO-2454', 1, 'Packed',     '-4 days',  [[8, 6], [7, 30]]],
            ['SO-2455', 5, 'Confirmed',  '-2 days',  [[12, 4], [11, 60]]],
            ['SO-2456', 4, 'New',        '-1 days',  [[3, 18], [5, 120]]],
            ['SO-2457', 2, 'New',        'today',    [[9, 5], [4, 25]]],
        ];

        foreach ($orders as [$ref, $dealerId, $status, $when, $lines]) {
            $order = Order::create([
                'reference' => $ref,
                'dealer_id' => $dealerId,
                'status' => $status,
                'total' => 0,
                'placed_on' => $when === 'today' ? now() : now()->modify($when),
            ]);

            $total = 0;

            foreach ($lines as [$productId, $qty]) {
                $product = Product::find($productId);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                ]);

                $total += (float) $product->price * $qty;
            }

            $order->update(['total' => $total]);
        }
    }
}
