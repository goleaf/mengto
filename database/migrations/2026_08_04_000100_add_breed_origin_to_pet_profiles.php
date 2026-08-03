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
            $table->string('breed_origin_type', 32)
                ->nullable()
                ->after('domestic_classification_id');
        });

        Schema::create('pet_profile_breed_origins', function (Blueprint $table): void {
            $table->id();
            $table->char('origin_key', 26)->unique();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->cascadeOnDelete();
            $table->foreignId('domestic_classification_id')
                ->nullable()
                ->constrained('domestic_classifications')
                ->nullOnDelete();
            $table->string('breed_name', 220);
            $table->string('confidence', 32);
            $table->string('source', 32);
            $table->unsignedTinyInteger('approximate_share_percent')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(
                ['pet_profile_id', 'position', 'id'],
                'pet_breed_origins_profile_position_idx',
            );
            $table->index(
                ['domestic_classification_id', 'pet_profile_id'],
                'pet_breed_origins_class_profile_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profile_breed_origins');

        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropColumn('breed_origin_type');
        });
    }
};
