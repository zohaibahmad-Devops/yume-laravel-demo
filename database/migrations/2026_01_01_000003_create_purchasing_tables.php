<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purchasing closes the loop. Without it stock only ever leaves the building,
 * which is why the first version of this demo could show a warehouse emptying
 * and never filling.
 *
 * A purchase order is received a line at a time, so the received quantity lives
 * on the line rather than the header, and the header's status is derived from
 * the lines instead of being set by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('phone')->nullable();
            $table->integer('lead_time_days')->default(7);
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('Draft');
            $table->decimal('total', 12, 2)->default(0);
            $table->date('ordered_on');
            $table->date('expected_on')->nullable();
            $table->timestamps();

            $table->index(['status', 'expected_on']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->timestamps();
        });

        Schema::create('purchase_order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('detail')->nullable();
            $table->string('actor')->default('system');
            $table->timestamps();

            $table->index(['purchase_order_id', 'created_at']);
        });

        // A movement can now be caused by a receipt as well as a dispatch.
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('order_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_id');
        });

        Schema::dropIfExists('purchase_order_events');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
