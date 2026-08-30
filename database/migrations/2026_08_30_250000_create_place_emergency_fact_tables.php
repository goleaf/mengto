<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_operating_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->unique()->constrained('places')->restrictOnDelete();
            $table->string('timezone', 64);
            $table->string('coverage_status', 24)->default('partial');
            $table->string('verification_status', 40)->default('not_assessed');
            $table->string('verification_source', 190)->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fresh_until')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(
                ['verification_status', 'fresh_until', 'place_id'],
                'place_schedules_verification_freshness_idx',
            );
        });

        Schema::create('place_weekly_opening_intervals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_operating_schedule_id')
                ->constrained('place_operating_schedules')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('iso_weekday');
            $table->unsignedSmallInteger('starts_at_minute');
            $table->unsignedSmallInteger('ends_at_minute');
            $table->boolean('is_appointment_only')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(
                [
                    'place_operating_schedule_id',
                    'iso_weekday',
                    'starts_at_minute',
                    'ends_at_minute',
                ],
                'place_weekly_intervals_exact_unique',
            );
            $table->index(
                ['place_operating_schedule_id', 'iso_weekday', 'starts_at_minute', 'id'],
                'place_weekly_intervals_schedule_day_idx',
            );
        });

        Schema::create('place_schedule_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_operating_schedule_id')
                ->constrained('place_operating_schedules')
                ->cascadeOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->date('local_date');
            $table->string('kind', 32);
            $table->string('reason_code', 120)->nullable();
            $table->string('verification_status', 40)->default('not_assessed');
            $table->string('verification_source', 190)->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fresh_until')->nullable();
            $table->timestamps();

            $table->unique(
                ['place_operating_schedule_id', 'local_date'],
                'place_schedule_exceptions_date_unique',
            );
        });

        Schema::create('place_schedule_exception_intervals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_schedule_exception_id')
                ->constrained('place_schedule_exceptions')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('starts_at_minute');
            $table->unsignedSmallInteger('ends_at_minute');
            $table->boolean('is_appointment_only')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(
                ['place_schedule_exception_id', 'starts_at_minute', 'ends_at_minute'],
                'place_exception_intervals_exact_unique',
            );
        });

        Schema::create('place_service_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 160)->unique();
            $table->string('name_translation_key', 190)->unique();
            $table->string('description_translation_key', 190)->nullable();
            $table->string('service_domain', 64);
            $table->boolean('is_emergency_capability')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(
                ['service_domain', 'is_emergency_capability', 'is_active', 'position', 'id'],
                'place_service_definitions_capability_idx',
            );
        });

        Schema::create('place_service_offerings', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 190)->unique();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('place_service_definition_id')
                ->constrained('place_service_definitions')
                ->restrictOnDelete();
            $table->string('availability', 40)->default('unknown');
            $table->string('access_mode', 40)->default('unknown');
            $table->string('verification_status', 40)->default('not_assessed');
            $table->string('verification_source', 190)->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fresh_until')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(
                ['place_id', 'place_service_definition_id'],
                'place_service_offerings_place_definition_unique',
            );
            $table->index(
                ['place_service_definition_id', 'availability', 'place_id', 'id'],
                'place_service_offerings_capability_idx',
            );
        });

        Schema::create('place_service_offering_taxa', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_service_offering_id')
                ->constrained('place_service_offerings')
                ->cascadeOnDelete();
            $table->foreignId('taxon_id')->constrained('taxa')->restrictOnDelete();
            $table->string('eligibility', 32)->default('unknown');
            $table->boolean('includes_descendants')->default(false);
            $table->timestamps();

            $table->unique(
                ['place_service_offering_id', 'taxon_id'],
                'place_service_offering_taxa_unique',
            );
            $table->index(
                ['taxon_id', 'eligibility', 'place_service_offering_id'],
                'place_service_offering_taxa_lookup_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_service_offering_taxa');
        Schema::dropIfExists('place_service_offerings');
        Schema::dropIfExists('place_service_definitions');
        Schema::dropIfExists('place_schedule_exception_intervals');
        Schema::dropIfExists('place_schedule_exceptions');
        Schema::dropIfExists('place_weekly_opening_intervals');
        Schema::dropIfExists('place_operating_schedules');
    }
};
