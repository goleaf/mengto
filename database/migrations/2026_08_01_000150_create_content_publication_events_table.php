<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_publication_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_publication_id')
                ->constrained('content_publications')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('represented_actor_id')
                ->nullable()
                ->constrained('social_actors')
                ->nullOnDelete();
            $table->string('actor_key_snapshot', 120);
            $table->string('representation_role', 80);
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('idempotency_key', 190)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(
                ['content_publication_id', 'occurred_at', 'id'],
                'content_publication_events_history_idx',
            );
            $table->index(
                ['event_type', 'occurred_at', 'id'],
                'content_publication_events_type_idx',
            );
            $table->index(
                ['actor_user_id', 'occurred_at', 'id'],
                'content_publication_events_actor_idx',
            );
            $table->index(
                ['represented_actor_id', 'occurred_at', 'id'],
                'content_publication_events_represented_actor_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_publication_events');
    }
};
