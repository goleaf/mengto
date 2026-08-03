<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use App\Models\DiscoveryPreference;
use App\Models\User;

/** @extends ApplicationFactory<DiscoveryPreference> */
final class DiscoveryPreferenceFactory extends ApplicationFactory
{
    protected $model = DiscoveryPreference::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'scope' => DiscoveryPreferenceScope::Item,
            'category' => fake()->randomElement(DiscoveryCategory::recommendationCategories()),
            'target_key' => fake()->unique()->slug(3),
            'reason_code' => 'not_relevant',
        ];
    }

    public function category(DiscoveryCategory $category): self
    {
        return $this->state(fn (): array => [
            'scope' => DiscoveryPreferenceScope::Category,
            'category' => $category,
            'target_key' => '*',
        ]);
    }
}
