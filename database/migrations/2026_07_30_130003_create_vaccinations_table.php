<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('manufacturer', 160)->nullable();
            $table->string('lot_number', 120)->nullable();
            $table->date('product_expires_on')->nullable();
            $table->date('administered_on')->nullable()->index();
            $table->date('next_due_on')->nullable()->index();
            $table->string('status', 40)->index();
            $table->string('dose', 80)->nullable();
            $table->string('route', 80)->nullable();
            $table->string('clinic_name', 160)->nullable();
            $table->string('veterinarian_name', 160)->nullable();
            $table->text('reaction')->nullable();
            $table->string('verification_status', 40)->index();
            $table->string('created_by_key', 80);
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'status', 'next_due_on'],
                'vaccinations_record_status_due_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
