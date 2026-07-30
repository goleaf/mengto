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
        Schema::create('forum_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('forum_answers')->cascadeOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('forum_comments')->cascadeOnDelete();
            $table->string('reporter_key', 80);
            $table->string('reason', 80);
            $table->text('details')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 40)->default('new');
            $table->timestamps();

            $table->index(['status', 'priority', 'created_at', 'id'], 'forum_reports_status_priority_created_idx');
            $table->index(['topic_id', 'status'], 'forum_reports_topic_status_idx');
            $table->index(['answer_id', 'status'], 'forum_reports_answer_status_idx');
            $table->index(['reporter_key', 'created_at'], 'forum_reports_reporter_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_reports');
    }
};
