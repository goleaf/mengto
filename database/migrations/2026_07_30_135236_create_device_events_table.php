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
        Schema::create('device_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_pet_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pet_profile_key', 80)->nullable();
            $table->string('pet_name', 120)->nullable();
            $table->string('external_event_id', 160)->nullable();
            $table->string('type', 64);
            $table->string('severity', 24)->default('routine');
            $table->string('status', 32)->default('open');
            $table->string('title', 180);
            $table->text('summary')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('occurred_at');
            $table->string('timezone', 64);
            $table->string('confidence', 24)->default('unknown');
            $table->string('source', 64)->default('device');
            $table->boolean('requires_attention')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('acknowledged_by_key', 80)->nullable();
            $table->foreignId('care_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('search_case_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['smart_device_id', 'external_event_id']);
            $table->index(['smart_device_id', 'type', 'occurred_at']);
            $table->index(['severity', 'status', 'occurred_at']);
            $table->index(['pet_profile_key', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_events');
    }
};
