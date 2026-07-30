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
        Schema::create('device_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('owner_key', 80);
            $table->string('name', 140);
            $table->string('trigger_type', 64);
            $table->text('trigger_config')->nullable();
            $table->text('condition_config')->nullable();
            $table->string('action_type', 64);
            $table->text('action_config')->nullable();
            $table->string('status', 24)->default('draft');
            $table->string('priority', 24)->default('normal');
            $table->string('safety_level', 24)->default('normal');
            $table->unsignedSmallInteger('max_runs_per_hour')->default(3);
            $table->unsignedInteger('cooldown_seconds')->default(300);
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->index(['owner_key', 'status', 'updated_at']);
            $table->index(['smart_device_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_automations');
    }
};
