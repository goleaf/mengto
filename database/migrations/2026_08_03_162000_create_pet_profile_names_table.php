<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_profile_names', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('normalized_name', 120);
            $table->string('type', 32);
            $table->string('visibility', 24)->default('private');
            $table->string('locale', 16)->nullable();
            $table->boolean('is_searchable')->default(true);
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['pet_profile_id', 'normalized_name'],
                'pet_profile_names_profile_normalized_unique',
            );
            $table->index(
                ['normalized_name', 'is_searchable', 'pet_profile_id'],
                'pet_profile_names_search_idx',
            );
            $table->index(
                ['pet_profile_id', 'visibility', 'id'],
                'pet_profile_names_projection_idx',
            );
            $table->index(
                ['recorded_by_user_id', 'pet_profile_id'],
                'pet_profile_names_recorder_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profile_names');
    }
};
