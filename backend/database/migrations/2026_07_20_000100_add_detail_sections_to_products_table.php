<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'included_items')) {
                $table->json('included_items')->nullable()->after('specs');
            }

            if (! Schema::hasColumn('products', 'downloads')) {
                $table->json('downloads')->nullable()->after('included_items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'downloads')) {
                $table->dropColumn('downloads');
            }

            if (Schema::hasColumn('products', 'included_items')) {
                $table->dropColumn('included_items');
            }
        });
    }
};
