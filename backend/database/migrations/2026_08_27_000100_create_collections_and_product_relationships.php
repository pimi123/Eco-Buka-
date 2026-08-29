<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('merchandising')->index();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('category_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['category_id', 'product_id']);
        });

        Schema::create('collection_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['collection_id', 'product_id']);
        });

        DB::table('products')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->each(function ($product): void {
                DB::table('category_product')->updateOrInsert(
                    ['category_id' => $product->category_id, 'product_id' => $product->id],
                    [
                        'sort_order' => (int) ($product->sort_order ?? 0),
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('collections');
    }
};
