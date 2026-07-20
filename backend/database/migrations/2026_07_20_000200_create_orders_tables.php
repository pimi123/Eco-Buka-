<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->string('order_number')->unique();
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_email')->nullable();
                $table->string('delivery_address');
                $table->string('city');
                $table->text('delivery_details')->nullable();
                $table->text('customer_note')->nullable();
                $table->string('status')->default('pending')->index();
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('delivery_fee', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->string('currency', 3)->default('EUR');
                $table->text('admin_note')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('processing_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index('created_at');
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('product_name');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('line_total', 10, 2)->default(0);
                $table->json('selected_options')->nullable();
                $table->json('product_snapshot')->nullable();
                $table->timestamps();

                $table->index('product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
