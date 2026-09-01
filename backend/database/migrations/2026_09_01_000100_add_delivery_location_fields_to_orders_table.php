<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'country')) {
                $table->string('country', 120)->default('Kosove')->after('customer_email');
            }

            if (! Schema::hasColumn('orders', 'municipality')) {
                $table->string('municipality', 120)->nullable()->after('country');
            }

            if (! Schema::hasColumn('orders', 'postal_code')) {
                $table->string('postal_code', 30)->nullable()->after('city');
            }

            if (! Schema::hasColumn('orders', 'policy_accepted_at')) {
                $table->timestamp('policy_accepted_at')->nullable()->after('customer_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'policy_accepted_at')) {
                $table->dropColumn('policy_accepted_at');
            }

            if (Schema::hasColumn('orders', 'postal_code')) {
                $table->dropColumn('postal_code');
            }

            if (Schema::hasColumn('orders', 'municipality')) {
                $table->dropColumn('municipality');
            }

            if (Schema::hasColumn('orders', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
