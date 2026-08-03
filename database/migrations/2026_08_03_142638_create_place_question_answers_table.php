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
        Schema::create('place_question_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_question_id')
                ->unique()
                ->constrained('place_questions')
                ->cascadeOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->string('stable_key', 100)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->text('body');
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->index(
                ['author_user_id', 'answered_at'],
                'place_question_answers_author_answered_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_question_answers');
    }
};
