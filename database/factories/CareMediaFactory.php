<?php

namespace Database\Factories;

use App\Models\CareEntry;
use App\Models\CareMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareMedia>
 */
class CareMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'care_entry_id' => CareEntry::factory(),
            'care_journal_id' => fn (array $attributes): int => CareEntry::query()
                ->select(['id', 'care_journal_id'])
                ->findOrFail($attributes['care_entry_id'])
                ->care_journal_id,
            'disk' => 'local',
            'path' => 'care-journals/testing/example.jpg',
            'mime_type' => 'image/jpeg',
            'original_name' => 'care-photo.jpg',
            'size_bytes' => 1024,
            'alt_text' => 'Private care journal photograph',
            'sensitivity' => 'private',
            'created_by_key' => 'mia-carter',
        ];
    }
}
