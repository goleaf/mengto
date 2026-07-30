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
        Schema::create('sightings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_key', 80)->index();
            $table->string('reporter_name', 120);
            $table->uuid('idempotency_key')->unique();
            $table->string('status', 30)->default('submitted')->index();
            $table->timestamp('observed_at')->index();
            $table->timestamp('submitted_at')->index();
            $table->string('time_accuracy', 30)->default('exact');
            $table->string('public_area', 160);
            $table->decimal('public_latitude', 9, 6)->nullable();
            $table->decimal('public_longitude', 9, 6)->nullable();
            $table->text('exact_location')->nullable();
            $table->string('direction', 100)->nullable();
            $table->string('distance', 60)->nullable();
            $table->string('confidence', 30)->default('possible')->index();
            $table->string('contact_status', 40)->default('seen-only');
            $table->string('animal_condition', 80)->nullable();
            $table->string('danger', 100)->nullable()->index();
            $table->text('notes')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('exact_location_public')->default(false);
            $table->json('risk_flags')->nullable();
            $table->string('verified_by_key', 80)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(
                ['search_case_id', 'status', 'observed_at', 'id'],
                'sightings_case_status_observed_idx',
            );
            $table->index(
                ['search_case_id', 'confidence', 'observed_at'],
                'sightings_case_confidence_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sightings');
    }
};
