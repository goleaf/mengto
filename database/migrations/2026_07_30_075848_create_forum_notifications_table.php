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
        Schema::create('forum_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained('forum_topics')->cascadeOnDelete();
            $table->string('user_key', 80);
            $table->string('type', 60);
            $table->string('title', 180);
            $table->text('body')->nullable();
            $table->string('deduplication_key', 160)->unique();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_key', 'read_at', 'created_at', 'id'], 'forum_notifications_user_read_created_idx');
            $table->index(['topic_id', 'type'], 'forum_notifications_topic_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_notifications');
    }
};
