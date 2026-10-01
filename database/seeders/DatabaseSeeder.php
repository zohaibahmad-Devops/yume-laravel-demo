<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data for a Rawalpindi pipes-and-fittings distributor. Prices are in
 * PKR and sized so the margin column and the low-stock flags both have
 * something real to show.
 *
 * Orders are replayed through the pipeline rather than inserted at their final
 * status, so every order carries a believable history and the stock ledger
 * actually explains the quantities on hand.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['PVC-6-20', 'PVC Pipe 6" x 20ft', 'Pipes', 'length', 3850, 4620, 76, 40],
            ['PVC-4-20', 'PVC Pipe 4" x 20ft', 'Pipes', 'length', 2400, 2960, 142, 40],
            ['PVC-2-20', 'PVC Pipe 2" x 20ft', 'Pipes', 'length', 1180, 1490, 22, 30],
            ['CPL-6', 'Coupler 6"', 'Fittings', 'pc', 410, 560, 102, 80],
            ['CPL-4', 'Coupler 4"', 'Fittings', 'pc', 240, 330, 335, 80],
            ['ELB-4-90', 'Elbow 4" 90°', 'Fittings', 'pc', 265, 370, 246, 60],
            ['TEE-4', 'Tee 4"', 'Fittings', 'pc', 290, 405, 54, 60],
            ['GV-4', 'Gate Valve 4"', 'Valves', 'pc', 4100, 5350, 28, 20],
            ['GV-2', 'Gate Valve 2"', 'Valves', 'pc', 1850, 2480, 9, 15],
            ['CBL-4C-16', 'Cable 4-core 16mm', 'Cables', 'metre', 520, 690, 1540, 400],
            ['CBL-2C-10', 'Cable 2-core 10mm', 'Cables', 'metre', 275, 365, 86, 300],
            ['TNK-1000', 'Water Tank 1000L', 'Tanks', 'pc', 18500, 23400, 11, 8],
        ];

        $opened = now()->subDays(30);

        foreach ($products as [$sku, $name, $category, $unit, $cost, $price, $stock, $reorder]) {
            $product = Product::create([
                'sku' => $sku,
                'name' => $name,
                'category' => $category,
                'unit' => $unit,
                'cost' => $cost,
                'price' => $price,
                'stock' => $stock,
                'reorder_level' => $reorder,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'delta' => $stock,
                'balance_after' => $stock,
                'reason' => 'Opening balance',
                'created_at' => $opened,
                'updated_at' => $opened,
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

        /* [reference, dealer, final status, days ago, [[product id, qty], ...]] */
        $orders = [
            ['SO-2451', 1, 'Delivered',  12, [[1, 12], [4, 40]]],
            ['SO-2452', 2, 'Delivered',  10, [[10, 300]]],
            ['SO-2453', 3, 'Dispatched',  6, [[2, 24], [6, 50]]],
            ['SO-2454', 1, 'Packed',      4, [[8, 6], [7, 30]]],
            ['SO-2455', 5, 'Confirmed',   2, [[12, 4], [11, 60]]],
            ['SO-2456', 4, 'New',         1, [[3, 18], [5, 120]]],
            ['SO-2457', 2, 'New',         0, [[9, 5], [4, 25]]],
        ];

        foreach ($orders as [$ref, $dealerId, $finalStatus, $daysAgo, $lines]) {
            $placed = now()->subDays($daysAgo)->startOfDay();

            $order = Order::create([
                'reference' => $ref,
                'dealer_id' => $dealerId,
                'status' => 'New',
                'total' => 0,
                'placed_on' => $placed,
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

            $this->replay($order, $finalStatus, $placed, $lines);
        }
    }

    /**
     * Walk the order from New to its final status, writing one event per step
     * and the stock movements that dispatching causes.
     */
    private function replay(Order $order, string $finalStatus, Carbon $placed, array $lines): void
    {
        $target = array_search($finalStatus, Order::STATUSES, true);
        $at = $placed->copy()->addHours(2);

        OrderEvent::create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => 'New',
            'actor' => 'sales desk',
            'created_at' => $at,
            'updated_at' => $at,
        ]);

        for ($i = 1; $i <= $target; $i++) {
            $from = Order::STATUSES[$i - 1];
            $to = Order::STATUSES[$i];
            $at = $at->copy()->addHours(7 + $i * 3);

            if ($to === 'Dispatched') {
                foreach ($lines as [$productId, $qty]) {
                    $product = Product::find($productId);
                    $balance = $product->stock - $qty;

                    $product->update(['stock' => $balance]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'order_id' => $order->id,
                        'delta' => -$qty,
                        'balance_after' => $balance,
                        'reason' => "Dispatched on {$order->reference}",
                        'created_at' => $at,
                        'updated_at' => $at,
                    ]);
                }
            }

            OrderEvent::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $to,
                'actor' => $to === 'Dispatched' ? 'warehouse' : 'sales desk',
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }

        $order->update(['status' => $finalStatus]);
    }
}
