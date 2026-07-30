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
        Schema::create('search_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->string('actor_key', 80);
            $table->string('display_name', 120);
            $table->string('role', 50)->default('volunteer');
            $table->json('capabilities')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->string('privacy_level', 30)->default('team-only');
            $table->timestamp('available_until')->nullable();
            $table->timestamp('joined_at')->index();
            $table->timestamp('last_check_in_at')->nullable();
            $table->text('temporary_location')->nullable();
            $table->timestamp('location_expires_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['search_case_id', 'actor_key'],
                'search_volunteers_case_actor_unique',
            );
            $table->index(
                ['search_case_id', 'status', 'joined_at'],
                'search_volunteers_case_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_volunteers');
    }
};
