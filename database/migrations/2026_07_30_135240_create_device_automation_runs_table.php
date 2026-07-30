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
        Schema::create('device_automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_automation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('smart_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_event_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->text('trigger_snapshot');
            $table->text('action_snapshot');
            $table->string('status', 32)->default('simulated');
            $table->boolean('is_simulation')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['device_automation_id', 'started_at']);
            $table->index(['smart_device_id', 'status', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_automation_runs');
    }
};
