<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_cards', function (Blueprint $table): void {
            $table->string('background_video')->nullable()->after('mobile_background_image');
        });

        DB::table('showcase_sections')
            ->where('section_type', 'promo_cards')
            ->whereNull('layout_variant')
            ->update(['layout_variant' => 'two_cards']);
    }

    public function down(): void
    {
        Schema::table('promo_cards', function (Blueprint $table): void {
            $table->dropColumn('background_video');
        });
    }
};
