<?php

declare(strict_types=1);

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
        Schema::create('place_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->string('stable_key', 100)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->text('body');
            $table->string('status', 24)->default('open');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(
                ['place_id', 'status', 'created_at'],
                'place_questions_place_status_created_idx',
            );
            $table->index(
                ['author_user_id', 'created_at'],
                'place_questions_author_created_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_questions');
    }
};
