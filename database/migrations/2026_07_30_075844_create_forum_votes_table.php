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
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('forum_answers')->cascadeOnDelete();
            $table->string('user_key', 80);
            $table->string('value', 40);
            $table->string('reason', 80)->nullable();
            $table->timestamps();

            $table->unique(['answer_id', 'user_key'], 'forum_votes_answer_user_unique');
            $table->index(['answer_id', 'value'], 'forum_votes_answer_value_idx');
            $table->index(['user_key', 'created_at'], 'forum_votes_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
    }
};
