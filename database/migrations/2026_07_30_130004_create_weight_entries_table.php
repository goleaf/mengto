<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->timestamp('measured_at')->index();
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->unsignedInteger('weight_grams');
            $table->unsignedInteger('tare_grams')->nullable();
            $table->string('source_type', 40)->index();
            $table->string('source_name', 160);
            $table->string('measurement_context', 120)->nullable();
            $table->text('notes')->nullable();
            $table->string('verification_status', 40)->index();
            $table->string('created_by_key', 80);
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'measured_at', 'id'],
                'weight_entries_record_measured_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_entries');
    }
};
