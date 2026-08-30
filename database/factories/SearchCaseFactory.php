<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ModerationStatus;
use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Models\SearchCase;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<SearchCase>
 */
class SearchCaseFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->unique()->firstName();
        $petProfileKey = Str::slug($name).'-'.Str::lower((string) Str::ulid());

        return [
            'owner_id' => static fn (array $attributes): mixed => User::query()
                ->where('actor_key', (string) $attributes['owner_key'])
                ->value('id') ?? User::factory(),
            'owner_key' => fake()->unique()->userName(),
            'owner_name' => fake()->name(),
            'owner_initials' => fake()->lexify('??'),
            'coordinator_key' => fake()->unique()->userName(),
            'coordinator_name' => fake()->name(),
            'slug' => Str::slug($name.' missing '.Str::random(7)),
            'public_code' => Str::upper(Str::random(8)),
            'active_key' => fake()->unique()->userName().':'.Str::lower($name),
            'type' => SearchCaseType::Lost,
            'status' => SearchStatus::Active,
            'moderation_status' => ModerationStatus::Approved,
            'pet_profile_key' => $petProfileKey,
            'pet_name' => $name,
            'species' => 'dog',
            'breed' => 'Mixed breed',
            'sex' => 'male',
            'age_label' => 'Adult',
            'size' => 'medium',
            'primary_color' => 'Black with a white chest',
            'coat' => 'short',
            'distinctive_marks' => 'White chest patch and blue collar.',
            'hidden_marks' => 'Small scar under the left shoulder.',
            'description' => 'Frightened by a loud noise and may keep distance from strangers.',
            'health_notice' => null,
            'approach_instructions' => 'Stay sideways, speak softly, and report the location.',
            'avoid_instructions' => 'Do not chase, surround, or call loudly.',
            'accessories' => ['blue collar'],
            'temperament' => 'Usually social, but may hide when frightened.',
            'microchip_status' => 'present',
            'last_seen_area' => 'Vingis Park near the river path',
            'city' => 'Vilnius',
            'country' => 'LT',
            'public_latitude' => '54.683000',
            'public_longitude' => '25.238000',
            'exact_location' => [
                'latitude' => '54.682941',
                'longitude' => '25.237611',
                'note' => 'Near the southern park gate',
            ],
            'direction' => 'East toward the river path',
            'last_seen_at' => now()->subHours(2),
            'reported_at' => now()->subHours(2),
            'notification_radius_km' => 5,
            'visibility' => 'public',
            'alerts_active' => true,
            'volunteer_join_open' => true,
            'animal_secured' => false,
            'contact_protected' => true,
            'contact_details' => ['channel' => 'platform', 'value' => fake()->unique()->userName()],
            'contact_token' => hash('sha256', (string) Str::uuid()),
            'cover_url' => asset('images/places/park-primary-lg.jpg'),
            'photos' => [],
            'risk_flags' => [],
            'animal_snapshot' => [
                'name' => $name,
                'species' => 'dog',
                'breed' => 'Mixed breed',
                'captured_at' => now()->toIso8601String(),
            ],
            'requires_taxonomy_review' => false,
            'reward_offered' => false,
            'reward_summary' => null,
            'latest_update' => 'Search teams are checking the river path quietly.',
            'lock_version' => 1,
            'view_count' => fake()->numberBetween(20, 500),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (SearchCase $case): void {
            if ($case->owner_id === null) {
                return;
            }

            $owner = User::query()->where('actor_key', $case->owner_key)->first()
                ?? User::query()->findOrFail($case->owner_id);

            $case->owner_id = $owner->id;
            $case->owner_key = $owner->actor_key;
            $case->owner_name = $owner->name;
            $case->owner_initials = self::initials($owner->name);
            $case->coordinator_key = $owner->actor_key;
            $case->coordinator_name = $owner->name;
            $case->active_key = ! $case->status->isClosed()
                && in_array($case->type, [SearchCaseType::Lost, SearchCaseType::Stolen], true)
                && filled($case->pet_profile_key)
                    ? $owner->actor_key.':'.$case->pet_profile_key
                    : null;
            $case->contact_details = ['channel' => 'platform', 'value' => $owner->actor_key];
        });
    }

    public function found(): static
    {
        return $this->state(fn (): array => [
            'active_key' => null,
            'type' => SearchCaseType::Found,
            'status' => SearchStatus::Safe,
            'pet_profile_key' => null,
            'pet_name' => 'Unknown cat',
            'species' => 'cat',
            'animal_secured' => true,
            'alerts_active' => true,
            'last_seen_area' => 'Naujamiestis',
            'description' => 'Found indoors and kept separately from household pets.',
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (): array => [
            'active_key' => null,
            'status' => SearchStatus::Returned,
            'alerts_active' => false,
            'volunteer_join_open' => false,
            'returned_at' => now(),
            'closed_at' => now(),
            'closure_reason' => 'returned',
        ]);
    }

    public function sighted(): static
    {
        return $this->state(fn (): array => [
            'active_key' => null,
            'type' => SearchCaseType::Sighted,
            'status' => SearchStatus::PossibleSighting,
            'pet_profile_key' => null,
            'pet_name' => 'Unidentified animal',
            'requires_taxonomy_review' => true,
        ]);
    }

    public function stolen(): static
    {
        return $this->state(fn (): array => [
            'type' => SearchCaseType::Stolen,
            'status' => SearchStatus::Active,
            'alerts_active' => true,
            'volunteer_join_open' => true,
        ]);
    }

    public function reunited(): static
    {
        return $this->state(fn (): array => [
            'active_key' => null,
            'status' => SearchStatus::Reunited,
            'alerts_active' => false,
            'volunteer_join_open' => false,
            'animal_secured' => true,
            'found_at' => now()->subHour(),
            'returned_at' => now(),
            'reunited_at' => now(),
            'closed_at' => now(),
            'closure_reason' => SearchStatus::Reunited->value,
        ]);
    }

    private static function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
