<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_cards', function (Blueprint $table): void {
            $table->string('category_slug')->nullable()->after('button_link');
        });
    }

    public function down(): void
    {
        Schema::table('promo_cards', function (Blueprint $table): void {
            $table->dropColumn('category_slug');
        });
    }
};
