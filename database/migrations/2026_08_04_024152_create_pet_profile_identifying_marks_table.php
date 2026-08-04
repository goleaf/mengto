<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_profile_identifying_marks', function (Blueprint $table): void {
            $table->id();
            $table->char('mark_key', 26)->unique();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->cascadeOnDelete();
            $table->string('type', 40);
            $table->text('description');
            $table->string('visibility', 32)->default('verification');
            $table->unsignedSmallInteger('position')->default(0);
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();

            $table->index(
                ['pet_profile_id', 'retired_at', 'position', 'id'],
                'pet_marks_profile_active_position_idx',
            );
            $table->index(
                ['pet_profile_id', 'visibility', 'retired_at', 'position', 'id'],
                'pet_marks_profile_visibility_active_idx',
            );
            $table->index('created_by_user_id', 'pet_marks_creator_idx');
            $table->index('updated_by_user_id', 'pet_marks_updater_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profile_identifying_marks');
    }
};
