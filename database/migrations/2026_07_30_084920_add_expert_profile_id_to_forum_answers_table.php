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
        Schema::table('forum_answers', function (Blueprint $table) {
            $table->foreignId('expert_profile_id')
                ->nullable()
                ->after('author_id')
                ->constrained()
                ->nullOnDelete();
            $table->index(
                ['expert_profile_id', 'status', 'created_at'],
                'forum_answers_profile_status_created_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_answers', function (Blueprint $table) {
            $table->dropIndex('forum_answers_profile_status_created_idx');
            $table->dropConstrainedForeignId('expert_profile_id');
        });
    }
};
