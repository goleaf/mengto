<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_event_registration_pets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_registration_id')
                ->constrained('forum_event_registrations')
                ->restrictOnDelete();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->string('eligibility_status', 40)->default('not_assessed');
            $table->string('verification_source', 40)->default('unknown');
            $table->text('conditions')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['forum_event_registration_id', 'pet_profile_id'],
                'forum_event_registration_pets_registration_pet_unique',
            );
            $table->index(
                ['pet_profile_id', 'eligibility_status', 'id'],
                'forum_event_registration_pets_pet_eligibility_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_event_registration_pets');
    }
};
