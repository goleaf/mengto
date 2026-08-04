<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->string('life_stage_override', 24)
                ->nullable()
                ->after('birthday_celebration_day');
            $table->foreignId('life_stage_override_by_user_id')
                ->nullable()
                ->after('life_stage_override')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('life_stage_override_at')
                ->nullable()
                ->after('life_stage_override_by_user_id');

            $table->index(
                ['life_stage_override_by_user_id', 'id'],
                'pet_profiles_life_stage_actor_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropForeign(['life_stage_override_by_user_id']);
            $table->dropIndex('pet_profiles_life_stage_actor_idx');
            $table->dropColumn([
                'life_stage_override',
                'life_stage_override_by_user_id',
                'life_stage_override_at',
            ]);
        });
    }
};
