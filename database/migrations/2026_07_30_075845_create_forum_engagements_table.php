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
        Schema::create('forum_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->string('user_key', 80);
            $table->boolean('is_bookmarked')->default(false);
            $table->string('subscription_level', 40)->default('none');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('remind_at')->nullable();
            $table->timestamps();

            $table->unique(['topic_id', 'user_key'], 'forum_engagements_topic_user_unique');
            $table->index(['user_key', 'is_bookmarked', 'updated_at'], 'forum_engagements_user_bookmarked_idx');
            $table->index(['user_key', 'subscription_level'], 'forum_engagements_user_subscription_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_engagements');
    }
};
