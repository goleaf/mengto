<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ForumGroupStatus;
use App\Enums\ForumGroupVisibility;
use App\Models\ForumGroup;
use Illuminate\Database\Seeder;

final class ForumGroupDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $stableKey => $definition) {
            $group = ForumGroup::query()->firstOrNew(['stable_key' => $stableKey]);
            $isNew = ! $group->exists;

            if ($group->exists && ! $group->is_system_managed) {
                continue;
            }

            $attributes = [
                ...$definition,
                'stable_key' => $stableKey,
                'creation_idempotency_key' => "system-group:{$stableKey}",
                'is_system_managed' => true,
                'default_locale' => 'en',
            ];

            if ($isNew) {
                $attributes['status'] = ForumGroupStatus::Active;
                $attributes['active_member_count'] = 0;
                $attributes['lock_version'] = 0;
            }

            $group->forceFill($attributes)->save();
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function definitions(): array
    {
        return [
            'apartment-pets' => $this->definition(
                'Apartment Pets PDX',
                'apartment-pets',
                ForumGroupVisibility::Public,
                'us-oregon-portland',
            ),
            'trail-tails' => $this->definition(
                'Trail Tails Portland',
                'trail-tails',
                ForumGroupVisibility::Public,
                'us-oregon-portland-metro',
            ),
            'cat-people' => $this->definition(
                'Cat People of Portland',
                'cat-people',
                ForumGroupVisibility::RequestToJoin,
                'us-oregon-portland',
                ['forum_groups.system.cat-people.question'],
            ),
            'foster-network' => $this->definition(
                'Foster Network PDX',
                'foster-network',
                ForumGroupVisibility::Private,
                'us-oregon-portland-metro',
            ),
            'portland-labradors' => $this->definition(
                'Portland Labradors',
                'portland-labradors',
                ForumGroupVisibility::Public,
                'us-oregon-portland',
            ),
            'senior-companions' => $this->definition(
                'Gentle Senior Companions',
                'senior-companions',
                ForumGroupVisibility::Unlisted,
                'us-pacific-northwest',
            ),
        ];
    }

    /**
     * @param  list<string>  $membershipQuestions
     * @return array<string, mixed>
     */
    private function definition(
        string $fallbackName,
        string $translationKey,
        ForumGroupVisibility $visibility,
        string $locationScope,
        array $membershipQuestions = [],
    ): array {
        return [
            'name' => $fallbackName,
            'name_translation_key' => "forum_groups.system.{$translationKey}.name",
            'description' => $fallbackName,
            'description_translation_key' => "forum_groups.system.{$translationKey}.description",
            'rules' => [
                'forum_groups.system.shared_rules.privacy',
                'forum_groups.system.shared_rules.respect',
            ],
            'visibility' => $visibility,
            'location_scope' => $locationScope,
            'membership_questions' => $membershipQuestions,
        ];
    }
}
