<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('nestpay')->index();
            $table->string('provider_order_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('currency_code', 3)->default('978');
            $table->string('status')->default('pending')->index();
            $table->string('response')->nullable();
            $table->string('proc_return_code', 20)->nullable();
            $table->string('auth_code', 100)->nullable();
            $table->string('host_ref_num', 100)->nullable();
            $table->string('trans_id', 100)->nullable();
            $table->string('md_status', 20)->nullable();
            $table->string('masked_pan', 32)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('request_metadata')->nullable();
            $table->json('response_metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['provider', 'status']);
            $table->index('paid_at');
            $table->index('processed_at');
            $table->unique(['provider', 'provider_order_id']);
            $table->unique(['provider', 'trans_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
