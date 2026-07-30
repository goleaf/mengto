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
        Schema::create('forum_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('user_key', 80);
            $table->string('blocked_author_key', 80);
            $table->string('reason', 120)->nullable();
            $table->timestamps();

            $table->unique(['user_key', 'blocked_author_key'], 'forum_blocks_user_author_unique');
            $table->index(['blocked_author_key', 'created_at'], 'forum_blocks_author_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_blocks');
    }
};
