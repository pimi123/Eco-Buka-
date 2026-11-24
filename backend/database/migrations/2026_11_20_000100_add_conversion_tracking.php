<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('tracking_context')->nullable();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->string('action_source')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('nestpay_checkout_sessions', fn (Blueprint $table) => $table->uuid('checkout_key')->nullable()->unique());
        Schema::create('checkout_attempts', function (Blueprint $table) {
            $table->uuid('key')->primary();
            $table->string('payload_hash', 64);
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('meta_conversion_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_name', 40);
            $table->string('event_id')->unique();
            $table->string('dataset_id', 40);
            $table->timestamp('occurred_at');
            $table->decimal('value', 12, 2);
            $table->string('currency', 3);
            $table->string('status', 24)->default('pending')->index();
            $table->longText('payload')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('duplicate_attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('last_error', 80)->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'event_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_conversion_events');
        Schema::dropIfExists('checkout_attempts');
        Schema::table('nestpay_checkout_sessions', function (Blueprint $table) { $table->dropUnique(['checkout_key']); $table->dropColumn('checkout_key'); });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn('action_source');
        });
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('tracking_context'));
    }
};
