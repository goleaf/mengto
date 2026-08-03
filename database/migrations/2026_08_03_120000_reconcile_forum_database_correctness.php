<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_votes', function (Blueprint $table): void {
            $table->enum('value', [
                'helpful',
                'not-helpful',
                'needs-source',
                'outdated',
                'dangerous',
                'off-topic',
            ])->change();
        });

        Schema::table('photo_reactions', function (Blueprint $table): void {
            $table->enum('reaction', [
                'like',
                'love',
                'funny',
                'support',
                'useful',
            ])->change();
        });

        Schema::table('forum_moderation_cases', function (Blueprint $table): void {
            $table->unsignedInteger('lock_version')->default(0)->after('status');
            $table->string('closure_idempotency_key', 190)
                ->nullable()
                ->after('closed_at');
            $table->unique(
                'closure_idempotency_key',
                'forum_moderation_cases_closure_idempotency_key_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_moderation_cases', function (Blueprint $table): void {
            $table->dropUnique(
                'forum_moderation_cases_closure_idempotency_key_unique',
            );
            $table->dropColumn(['lock_version', 'closure_idempotency_key']);
        });

        Schema::table('photo_reactions', function (Blueprint $table): void {
            $table->string('reaction', 24)->change();
        });

        Schema::table('forum_votes', function (Blueprint $table): void {
            $table->string('value', 40)->change();
        });
    }
};
