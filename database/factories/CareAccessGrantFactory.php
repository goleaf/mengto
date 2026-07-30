<?php

namespace Database\Factories;

use App\Models\CareAccessGrant;
use App\Models\CareJournal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareAccessGrant>
 */
class CareAccessGrantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'care_journal_id' => CareJournal::factory(),
            'granted_by_key' => 'mia-carter',
            'recipient_name' => fake()->name(),
            'recipient_role' => 'sitter',
            'label' => 'Weekend care',
            'token_hash' => hash('sha256', (string) Str::uuid()),
            'sections' => ['feeding', 'water', 'walks', 'toilet', 'observations'],
            'permissions' => ['view'],
            'allow_add' => false,
            'allow_location' => false,
            'allow_media' => false,
            'max_views' => 20,
            'views_used' => 0,
            'expires_at' => now()->addDays(3),
        ];
    }
}
