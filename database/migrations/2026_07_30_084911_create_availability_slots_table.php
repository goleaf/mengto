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
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at');
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('format', 60);
            $table->string('location_label', 180)->nullable();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->unsignedSmallInteger('booked_count')->default(0);
            $table->string('status', 40)->default('open')->index();
            $table->timestamps();

            $table->unique(
                ['expert_profile_id', 'starts_at', 'ends_at'],
                'availability_slots_profile_time_unique',
            );
            $table->index(
                ['expert_profile_id', 'status', 'starts_at', 'id'],
                'availability_slots_profile_status_start_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};
