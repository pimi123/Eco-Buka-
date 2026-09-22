<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->unsignedSmallInteger('installment_count')->nullable()->after('payment_method')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['installment_count']);
            $table->dropColumn('installment_count');
        });
    }
};
