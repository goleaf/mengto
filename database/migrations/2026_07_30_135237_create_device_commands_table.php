<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->uuid('idempotency_key')->unique();
            $table->string('command_type', 64);
            $table->text('parameters')->nullable();
            $table->string('status', 32)->default('created');
            $table->string('safety_level', 24)->default('normal');
            $table->boolean('requires_confirmation')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('result')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['smart_device_id', 'status', 'issued_at']);
            $table->index(['smart_device_id', 'command_type', 'issued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
