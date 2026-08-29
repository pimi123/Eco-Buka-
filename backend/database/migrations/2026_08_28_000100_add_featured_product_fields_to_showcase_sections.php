<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('showcase_sections', function (Blueprint $table): void {
            $table->string('eyebrow')->nullable()->after('subtitle');
            $table->string('banner_title')->nullable()->after('eyebrow');
            $table->text('banner_subtitle')->nullable()->after('banner_title');
            $table->string('background_video')->nullable()->after('button_link');
        });
    }

    public function down(): void
    {
        Schema::table('showcase_sections', function (Blueprint $table): void {
            $table->dropColumn([
                'eyebrow',
                'banner_title',
                'banner_subtitle',
                'background_video',
            ]);
        });
    }
};
