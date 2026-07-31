<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Models\ForumGroup;
use App\Models\Taxon;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEvent>
 */
final class ForumEventFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $startsAt = now()->addDays(fake()->numberBetween(2, 60))->startOfHour();

        return [
            'organizer_user_id' => User::factory(),
            'organizer_key' => fn (array $attributes): string => User::query()
                ->findOrFail($attributes['organizer_user_id'])
                ->actor_key,
            'organizer_name' => fn (array $attributes): string => User::query()
                ->findOrFail($attributes['organizer_user_id'])
                ->name,
            'forum_group_id' => null,
            'stable_key' => 'event-'.Str::lower((string) Str::ulid()),
            'creation_idempotency_key' => (string) Str::uuid(),
            'is_system_managed' => false,
            'legacy_source_key' => null,
            'title' => fake()->sentence(5),
            'summary' => fake()->paragraph(),
            'type' => ForumEventType::Walk,
            'visibility' => ForumEventVisibility::Public,
            'format' => ForumEventFormat::Physical,
            'status' => ForumEventStatus::Scheduled,
            'locale' => 'en',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addHours(2),
            'timezone' => 'Europe/Vilnius',
            'capacity' => 20,
            'registration_policy' => ForumEventRegistrationPolicy::Open,
            'waitlist_enabled' => true,
            'location_scope' => fake()->city(),
            'exact_location' => fake()->streetAddress(),
            'online_url' => null,
            'attendance_requirements' => fake()->sentence(),
            'vaccination_requirements' => null,
            'vaccination_jurisdiction' => null,
            'minimum_animal_age_months' => null,
            'maximum_animal_age_months' => null,
            'accessibility_information' => fake()->sentence(),
            'cost_minor' => 0,
            'currency' => 'EUR',
            'refund_policy' => null,
            'photo_consent_mode' => ForumEventPhotoConsent::AskFirst,
            'animal_welfare_rules' => fake()->paragraph(),
            'emergency_contact_plan' => fake()->paragraph(),
            'lock_version' => 0,
            'cancelled_by_user_id' => null,
            'cancelled_at' => null,
            'cancellation_reason_code' => null,
            'archived_at' => null,
            'metadata' => null,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (): array => [
            'format' => ForumEventFormat::Online,
            'location_scope' => __('forum_events.defaults.online_location'),
            'exact_location' => null,
            'online_url' => 'https://events.example.test/'.Str::lower((string) Str::ulid()),
        ]);
    }

    public function hybrid(): static
    {
        return $this->state(fn (): array => [
            'format' => ForumEventFormat::Hybrid,
            'online_url' => 'https://events.example.test/'.Str::lower((string) Str::ulid()),
        ]);
    }

    public function approvalRequired(): static
    {
        return $this->state(fn (): array => [
            'registration_policy' => ForumEventRegistrationPolicy::Approval,
        ]);
    }

    public function invitationOnly(): static
    {
        return $this->state(fn (): array => [
            'registration_policy' => ForumEventRegistrationPolicy::Invitation,
            'visibility' => ForumEventVisibility::Private,
        ]);
    }

    public function paid(int $minor = 2500): static
    {
        return $this->state(fn (): array => [
            'cost_minor' => $minor,
            'refund_policy' => fake()->sentence(),
        ]);
    }

    public function forGroup(?ForumGroup $group = null): static
    {
        return $this
            ->for($group ?? ForumGroup::factory(), 'group')
            ->state(fn (): array => [
                'visibility' => ForumEventVisibility::Group,
                'type' => ForumEventType::ClubMeetup,
            ]);
    }

    public function completed(): static
    {
        $startsAt = now()->subDays(2)->startOfHour();

        return $this->state(fn (): array => [
            'status' => ForumEventStatus::Completed,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addHours(2),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason_code' => 'organizer-cancelled',
        ]);
    }

    public function withTaxon(?Taxon $taxon = null): static
    {
        return $this->afterCreating(function (ForumEvent $event) use ($taxon): void {
            $selected = $taxon ?? Taxon::factory()->create();
            $event->taxa()->sync([
                $selected->id => ['is_primary' => true],
            ]);
        });
    }
}
