<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('showcase_sections', function (Blueprint $table): void {
            $table->string('section_type')->default('product_showcase')->after('subtitle')->index();
            $table->string('source_type')->default('manual_products')->after('section_type')->index();
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            $table->string('source_slug')->nullable()->after('source_id')->index();
            $table->unsignedInteger('display_limit')->default(4)->after('source_slug');
            $table->string('layout_variant')->nullable()->after('display_limit');
            $table->string('banner_image')->nullable()->after('layout_variant');
            $table->string('mobile_banner_image')->nullable()->after('banner_image');
            $table->string('button_text')->nullable()->after('mobile_banner_image');
            $table->string('button_link')->nullable()->after('button_text');
        });
    }

    public function down(): void
    {
        Schema::table('showcase_sections', function (Blueprint $table): void {
            $table->dropColumn([
                'section_type',
                'source_type',
                'source_id',
                'source_slug',
                'display_limit',
                'layout_variant',
                'banner_image',
                'mobile_banner_image',
                'button_text',
                'button_link',
            ]);
        });
    }
};
