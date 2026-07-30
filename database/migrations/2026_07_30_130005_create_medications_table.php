<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('active_ingredient', 160)->nullable();
            $table->string('form', 60);
            $table->string('concentration', 80)->nullable();
            $table->string('dose', 120);
            $table->string('route', 80);
            $table->string('schedule_type', 40)->default('fixed');
            $table->string('schedule_text', 180);
            $table->date('starts_on')->index();
            $table->date('ends_on')->nullable()->index();
            $table->timestamp('next_dose_at')->nullable()->index();
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('status', 40)->index();
            $table->string('reason', 180)->nullable();
            $table->string('prescribed_by_name', 160)->nullable();
            $table->string('clinic_name', 160)->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_high_risk')->default(false)->index();
            $table->decimal('remaining_quantity', 10, 2)->nullable();
            $table->string('remaining_unit', 40)->nullable();
            $table->date('expires_on')->nullable()->index();
            $table->string('verification_status', 40)->index();
            $table->string('created_by_key', 80);
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'status', 'next_dose_at'],
                'medications_record_status_dose_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
