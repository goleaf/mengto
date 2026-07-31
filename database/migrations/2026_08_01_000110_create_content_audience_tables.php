<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_audience_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_publication_id')
                ->unique()
                ->constrained('content_publications')
                ->cascadeOnDelete();
            $table->string('audience_type', 40);
            $table->foreignId('context_actor_id')
                ->nullable()
                ->constrained('social_actors')
                ->restrictOnDelete();
            $table->string('context_type', 60)->nullable();
            $table->string('context_key', 190)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->index(
                ['audience_type', 'expires_at', 'content_publication_id'],
                'content_audience_rules_type_expiry_idx',
            );
            $table->index(
                ['context_actor_id', 'audience_type', 'content_publication_id'],
                'content_audience_rules_actor_type_idx',
            );
            $table->index(
                ['context_type', 'context_key', 'content_publication_id'],
                'content_audience_rules_context_idx',
            );
        });

        Schema::create('content_audience_actors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_audience_rule_id')
                ->constrained('content_audience_rules')
                ->cascadeOnDelete();
            $table->foreignId('social_actor_id')
                ->constrained('social_actors')
                ->restrictOnDelete();
            $table->string('effect', 16);
            $table->timestamps();

            $table->unique(
                ['content_audience_rule_id', 'social_actor_id'],
                'content_audience_actors_rule_actor_unique',
            );
            $table->index(
                ['social_actor_id', 'effect', 'content_audience_rule_id'],
                'content_audience_actors_actor_effect_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_audience_actors');
        Schema::dropIfExists('content_audience_rules');
    }
};
