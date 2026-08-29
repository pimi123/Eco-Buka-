<?php

use App\Models\PromoCard;
use App\Models\ShowcaseSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_cards', function (Blueprint $table): void {
            $table->foreignId('homepage_section_id')
                ->nullable()
                ->after('id')
                ->constrained('showcase_sections')
                ->nullOnDelete();
        });

        PromoCard::query()
            ->whereNull('homepage_section_id')
            ->get()
            ->each(function (PromoCard $card): void {
                $section = ShowcaseSection::query()
                    ->where('section_key', $card->section_key)
                    ->first();

                if ($section) {
                    $card->forceFill(['homepage_section_id' => $section->id])->save();
                }
            });
    }

    public function down(): void
    {
        Schema::table('promo_cards', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('homepage_section_id');
        });
    }
};
