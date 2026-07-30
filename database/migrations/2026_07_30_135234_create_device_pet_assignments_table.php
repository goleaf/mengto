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
        Schema::create('device_pet_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->string('pet_profile_key', 80);
            $table->string('pet_name', 120);
            $table->string('relationship_type', 40)->default('assigned');
            $table->string('identification_method', 40)->default('manual');
            $table->string('confidence', 24)->default('high');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['smart_device_id', 'pet_profile_key']);
            $table->index(['pet_profile_key', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_pet_assignments');
    }
};
