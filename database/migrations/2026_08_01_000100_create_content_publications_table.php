<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_publications', function (Blueprint $table): void {
            $table->id();
            $table->ulid('publication_key')->unique();
            $table->foreignId('real_author_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('publishing_actor_id')
                ->constrained('social_actors')
                ->restrictOnDelete();
            $table->string('representation_role', 80);
            $table->string('content_type', 40);
            $table->string('status', 40)->default('draft');
            $table->string('language', 12);
            $table->string('title', 240)->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->char('creation_fingerprint', 64);
            $table->string('idempotency_key', 190);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['real_author_user_id', 'idempotency_key'],
                'content_publications_author_idempotency_unique',
            );
            $table->index(
                ['status', 'published_at', 'id'],
                'content_publications_chronological_idx',
            );
            $table->index(
                ['publishing_actor_id', 'status', 'published_at', 'id'],
                'content_publications_actor_chronological_idx',
            );
            $table->index(
                ['content_type', 'status', 'published_at', 'id'],
                'content_publications_type_chronological_idx',
            );
            $table->index(
                ['expires_at', 'status', 'id'],
                'content_publications_expiry_idx',
            );
            $table->index(
                ['real_author_user_id', 'status', 'updated_at', 'id'],
                'content_publications_author_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_publications');
    }
};
