<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumSubscriptionLevel;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Models\ForumCategory;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;

final readonly class ForumTaxonomy
{
    public const LEGACY_CATEGORY_SLUGS = [
        'health' => 'health',
        'nutrition' => 'nutrition',
        'behavior' => 'behavior',
        'training' => 'training-education',
        'care' => 'everyday-care',
        'walks' => 'walks-exercise-places',
        'travel' => 'travel-documents',
        'adoption' => 'adoption-rescue-shelters',
        'lost-found' => 'lost-found',
        'services' => 'services-professionals',
        'support' => 'owner-support-wellbeing',
    ];

    public function __construct(
        private ForumCategoryTree $categoryTree,
        private ForumTopicTypeSchemaRegistry $topicTypeSchemas,
        private PetProfileCatalog $pets,
        private Factory $auth,
    ) {}

    /**
     * @return array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>
     */
    public function categories(): array
    {
        return $this->categoryTree->forLocale(app()->getLocale());
    }

    /** @return array<string, string> */
    public function categoryOptions(): array
    {
        return collect($this->categories())
            ->mapWithKeys(fn (array $category, string $key): array => [$key => $category['label']])
            ->all();
    }

    /** @return list<string> */
    public function acceptedCategoryKeys(): array
    {
        return array_values(array_unique([
            ...array_keys($this->categoryOptions()),
            ...array_keys(self::LEGACY_CATEGORY_SLUGS),
        ]));
    }

    /** @return list<string> */
    public function acceptedBrowseCategoryKeys(): array
    {
        $categories = $this->categories();
        $subcategoryKeys = [];

        foreach ($categories as $category) {
            $subcategoryKeys = [
                ...$subcategoryKeys,
                ...array_keys($category['subcategories']),
            ];
        }

        return array_values(array_unique([
            ...array_keys($categories),
            ...array_keys(self::LEGACY_CATEGORY_SLUGS),
            ...$subcategoryKeys,
        ]));
    }

    /**
     * @param  array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>  $categories
     * @return array{root: string, subcategory: string|null}
     */
    public function browseSelection(string $category, array $categories): array
    {
        if ($category === 'all') {
            return ['root' => 'all', 'subcategory' => null];
        }

        $resolvedCategory = $this->resolveCategoryKey($category);

        if (isset($categories[$resolvedCategory])) {
            return ['root' => $resolvedCategory, 'subcategory' => null];
        }

        foreach ($categories as $root => $definition) {
            if (isset($definition['subcategories'][$category])) {
                return ['root' => $root, 'subcategory' => $category];
            }
        }

        return ['root' => 'all', 'subcategory' => null];
    }

    public function resolveCategoryKey(string $category): string
    {
        return self::LEGACY_CATEGORY_SLUGS[$category] ?? $category;
    }

    public function categoryLabel(string $category): string
    {
        $canonical = $this->resolveCategoryKey($category);

        return $this->categoryOptions()[$canonical] ?? $canonical;
    }

    public function categoryId(string $category): ?int
    {
        return ForumCategory::query()
            ->active()
            ->where('slug', $this->resolveCategoryKey($category))
            ->value('id');
    }

    public function topicTypeId(string $type): ?int
    {
        return $this->topicTypeSchemas->definition($type)?->databaseId;
    }

    /** @return array<string, string> */
    public function typeOptions(): array
    {
        $activeTypes = $this->topicTypeSchemas->definitions();

        return collect(ForumTopicType::cases())
            ->filter(static fn (ForumTopicType $type): bool => isset(
                $activeTypes[$type->value],
            ))
            ->mapWithKeys(fn (ForumTopicType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function visibilityOptions(): array
    {
        return collect(ForumVisibility::cases())
            ->mapWithKeys(fn (ForumVisibility $visibility): array => [$visibility->value => $visibility->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function subscriptionOptions(): array
    {
        return collect(ForumSubscriptionLevel::cases())
            ->mapWithKeys(fn (ForumSubscriptionLevel $level): array => [$level->value => $level->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function filterOptions(): array
    {
        return [
            'all' => 'For you',
            'unanswered' => 'No answers',
            'resolved' => 'Resolved',
            'expert' => 'Expert replies',
            'local' => 'Local',
            'medical' => 'Health',
        ];
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return [
            'active' => 'Recently active',
            'new' => 'Newest',
            'helpful' => 'Most helpful',
            'unanswered' => 'Needs an answer',
        ];
    }

    /** @return array<string, string> */
    public function petOptions(): array
    {
        return [
            '' => __('messages.no_pet_attached'),
            ...collect($this->pets())->mapWithKeys(
                static fn (array $pet, string $key): array => [
                    $key => implode(' / ', array_filter([$pet['name'], $pet['species']])),
                ],
            )->all(),
        ];
    }

    /** @return array<string, array{name: string, species: string, age: string}> */
    public function pets(): array
    {
        $user = $this->currentUser();

        if (! $user instanceof User) {
            return [];
        }

        return collect($this->pets->managedBy($user))
            ->mapWithKeys(static fn (array $pet): array => [
                $pet['profile_key'] => [
                    'name' => $pet['name'],
                    'species' => $pet['species'],
                    'age' => $pet['age'],
                ],
            ])
            ->all();
    }

    private function currentUser(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }

    /** @return array<string, string> */
    public function desiredAnswerOptions(): array
    {
        return [
            'personal-experience' => 'Personal experience',
            'professional-opinion' => 'Professional opinion',
            'local-recommendation' => 'Local recommendation',
            'step-by-step' => 'Step-by-step plan',
            'comparison' => 'Comparison',
            'sources' => 'Sources',
            'support' => 'Emotional support',
        ];
    }

    /** @return array<string, string> */
    public function commentPolicyOptions(): array
    {
        return [
            'registered' => 'Registered members',
            'experts' => 'Verified specialists only',
            'group' => 'Group members',
            'review' => 'Replies require review',
            'closed' => 'Read only',
        ];
    }

    /** @return array<int, string> */
    public function suggestedTags(): array
    {
        return [
            'puppy',
            'senior pet',
            'adaptation',
            'fear',
            'leash',
            'travel',
            'clinic',
            'adoption',
            'boarding',
            'nutrition',
            'GPS',
            'lost pet',
            'support',
        ];
    }
}
