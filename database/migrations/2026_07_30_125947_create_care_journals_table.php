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
        Schema::create('care_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_key', 80);
            $table->string('slug', 120)->unique();
            $table->string('pet_profile_key', 80);
            $table->string('pet_name', 120);
            $table->string('species', 80);
            $table->string('breed', 160)->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->string('privacy', 32)->default('private');
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('current_caregiver_key', 80)->nullable();
            $table->string('current_caregiver_name', 120)->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamp('last_feeding_at')->nullable();
            $table->timestamp('last_water_at')->nullable();
            $table->timestamp('last_walk_at')->nullable();
            $table->timestamp('last_toilet_at')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->unique(['owner_key', 'pet_profile_key']);
            $table->index(['owner_key', 'status', 'updated_at']);
            $table->index(['status', 'updated_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_journals');
    }
};
