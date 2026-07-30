<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_key', 80)->index();
            $table->string('slug', 160)->unique();
            $table->string('pet_profile_key', 80);
            $table->string('pet_name', 100);
            $table->string('species', 60)->index();
            $table->string('breed', 120)->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('birth_date_estimated')->default(false);
            $table->string('sex', 30)->nullable();
            $table->string('reproductive_status', 40)->default('unknown');
            $table->unsignedInteger('current_weight_grams')->nullable();
            $table->string('image_url')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->string('privacy', 30)->default('private')->index();
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('microchip_status', 30)->default('unknown');
            $table->text('microchip_number')->nullable();
            $table->date('microchip_checked_on')->nullable();
            $table->string('blood_group', 60)->nullable();
            $table->text('critical_allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('emergency_notes')->nullable();
            $table->string('primary_clinic_name', 160)->nullable();
            $table->text('primary_clinic_contact')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->timestamp('last_visit_at')->nullable()->index();
            $table->timestamp('next_appointment_at')->nullable()->index();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->unique(
                ['owner_key', 'pet_profile_key'],
                'medical_records_owner_pet_unique',
            );
            $table->index(
                ['owner_key', 'status', 'updated_at', 'id'],
                'medical_records_owner_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
