<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table): void {
                $table->id();
                $table->string('purpose')->default('offer')->index();
                $table->string('status')->default('new')->index();
                $table->string('name');
                $table->string('phone', 80);
                $table->string('email')->nullable();
                $table->string('subject')->nullable();
                $table->text('message');
                $table->string('source_path')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index('created_at');
                $table->index(['purpose', 'status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
