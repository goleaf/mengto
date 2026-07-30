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
        Schema::create('device_safe_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('shape', 24)->default('circle');
            $table->string('public_area_label', 160);
            $table->text('exact_geometry');
            $table->text('schedule')->nullable();
            $table->unsignedInteger('exit_delay_seconds')->default(45);
            $table->decimal('accuracy_threshold_meters', 10, 2)->default(35);
            $table->string('status', 24)->default('active');
            $table->boolean('is_home')->default(false);
            $table->timestamps();

            $table->index(['smart_device_id', 'status']);
            $table->index(['smart_device_id', 'is_home']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_safe_zones');
    }
};
