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
            $table->index(
                ['species', 'is_discoverable', 'visibility', 'status', 'id'],
                'pet_profiles_duplicate_review_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropIndex('pet_profiles_duplicate_review_idx');
        });
    }
};
