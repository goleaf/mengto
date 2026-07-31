<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentPublicationStatus;
use App\Enums\ContentPublicationType;
use App\Models\ContentPublication;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ContentPublication> */
final class ContentPublicationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'publication_key' => (string) Str::ulid(),
            'real_author_user_id' => User::factory(),
            'publishing_actor_id' => SocialActor::factory()->forUser(),
            'representation_role' => 'self',
            'content_type' => ContentPublicationType::Post,
            'status' => ContentPublicationStatus::Draft,
            'language' => 'en',
            'title' => fake()->sentence(6),
            'summary' => fake()->sentence(12),
            'body' => fake()->paragraphs(2, true),
            'lock_version' => 1,
            'creation_fingerprint' => hash('sha256', fake()->uuid()),
            'idempotency_key' => (string) Str::ulid(),
            'published_at' => null,
            'scheduled_at' => null,
            'expires_at' => null,
        ];
    }

    public function by(User $user, SocialActor $actor): self
    {
        return $this->state(fn (): array => [
            'real_author_user_id' => $user->id,
            'publishing_actor_id' => $actor->id,
        ]);
    }

    public function published(): self
    {
        return $this->state(fn (): array => [
            'status' => ContentPublicationStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
    }
}
