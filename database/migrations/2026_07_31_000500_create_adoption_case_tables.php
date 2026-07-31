<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adoption_cases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('listing_id')->unique()->constrained('listings')->cascadeOnDelete();
            $table->foreignId('pet_profile_id')->nullable()->constrained('pet_profiles')->nullOnDelete();
            $table->foreignId('taxon_id')->nullable()->constrained('taxa')->restrictOnDelete();
            $table->foreignId('domestic_classification_id')
                ->nullable()
                ->constrained('domestic_classifications')
                ->restrictOnDelete();
            $table->string('case_number', 40)->unique();
            $table->string('provider_type', 40);
            $table->boolean('provider_verified')->default(false);
            $table->string('status', 40)->default('draft');
            $table->string('animal_name', 120);
            $table->string('age_description', 120)->nullable();
            $table->string('sex', 40)->nullable();
            $table->string('sterilization_status', 40)->default('unknown');
            $table->string('vaccination_status', 40)->default('unknown');
            $table->string('microchip_status', 40)->default('unknown');
            $table->string('public_location', 180);
            $table->text('health_summary')->nullable();
            $table->text('behavior_summary')->nullable();
            $table->text('compatibility_summary')->nullable();
            $table->text('special_requirements')->nullable();
            $table->unsignedBigInteger('adoption_fee_minor')->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->text('fee_explanation')->nullable();
            $table->json('transport_options')->nullable();
            $table->string('privacy_level', 40)->default('approximate-location');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['status', 'provider_type', 'published_at', 'id'],
                'adoption_cases_status_provider_published_idx',
            );
            $table->index(
                ['taxon_id', 'status', 'published_at', 'id'],
                'adoption_cases_taxon_status_published_idx',
            );
            $table->index(
                ['domestic_classification_id', 'status', 'id'],
                'adoption_cases_classification_status_idx',
            );
        });

        Schema::create('adoption_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('adoption_case_id')
                ->constrained('adoption_cases')
                ->cascadeOnDelete();
            $table->foreignId('applicant_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->string('placement_type', 40);
            $table->string('status', 40)->default('submitted');
            $table->string('identity_status', 40)->default('unverified');
            $table->text('message');
            $table->longText('private_profile');
            $table->longText('private_references')->nullable();
            $table->longText('screening_notes')->nullable();
            $table->longText('home_check_notes')->nullable();
            $table->longText('contract_metadata')->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('privacy_accepted')->default(false);
            $table->boolean('reference_contact_consent')->default(false);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('meeting_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('contracted_at')->nullable();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['adoption_case_id', 'applicant_user_id'],
                'adoption_applications_case_applicant_unique',
            );
            $table->index(
                ['adoption_case_id', 'status', 'submitted_at', 'id'],
                'adoption_applications_case_status_submitted_idx',
            );
            $table->index(
                ['applicant_user_id', 'status', 'submitted_at', 'id'],
                'adoption_applications_applicant_status_idx',
            );
        });

        Schema::create('adoption_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('adoption_case_id')
                ->constrained('adoption_cases')
                ->cascadeOnDelete();
            $table->foreignId('adoption_application_id')
                ->nullable()
                ->constrained('adoption_applications')
                ->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('previous_status', 40)->nullable();
            $table->string('current_status', 40)->nullable();
            $table->string('reason_translation_key', 190);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['adoption_case_id', 'created_at', 'id'],
                'adoption_events_case_created_idx',
            );
            $table->index(
                ['adoption_application_id', 'created_at', 'id'],
                'adoption_events_application_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_events');
        Schema::dropIfExists('adoption_applications');
        Schema::dropIfExists('adoption_cases');
    }
};
