<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_onboardings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->unique('user_onboardings_user_unique')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('current_step', 40)->default('introduction');
            $table->string('pet_relationship_choice', 32)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('introduction_completed_at')->nullable();
            $table->timestamp('preferences_completed_at')->nullable();
            $table->timestamp('pet_relationship_completed_at')->nullable();
            $table->timestamp('privacy_discovery_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_onboardings');
    }
};
