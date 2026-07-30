<?php

namespace App\Services;

use App\Enums\ForumSubscriptionLevel;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;

class ForumTaxonomy
{
    /**
     * @return array<string, array{label: string, icon: string, subcategories: array<string, string>}>
     */
    public function categories(): array
    {
        return [
            'health' => ['label' => 'Health', 'icon' => 'heart-pulse', 'subcategories' => [
                'symptoms' => 'Symptoms and next steps',
                'recovery' => 'Recovery and aftercare',
                'senior-care' => 'Senior care',
                'preventive-care' => 'Preventive care',
            ]],
            'nutrition' => ['label' => 'Nutrition', 'icon' => 'utensils', 'subcategories' => [
                'daily-feeding' => 'Daily feeding',
                'diet-transition' => 'Diet transitions',
                'allergies' => 'Allergies and sensitivities',
            ]],
            'behavior' => ['label' => 'Behavior', 'icon' => 'brain', 'subcategories' => [
                'fear' => 'Fear and confidence',
                'introductions' => 'Safe introductions',
                'home-alone' => 'Staying home',
                'reactivity' => 'Reactivity',
            ]],
            'training' => ['label' => 'Training', 'icon' => 'graduation-cap', 'subcategories' => [
                'foundations' => 'Foundations',
                'leash-skills' => 'Leash skills',
                'sport' => 'Sport and agility',
            ]],
            'care' => ['label' => 'Everyday care', 'icon' => 'sparkles', 'subcategories' => [
                'grooming' => 'Grooming',
                'enrichment' => 'Enrichment',
                'routines' => 'Home routines',
            ]],
            'walks' => ['label' => 'Walks and places', 'icon' => 'map-pinned', 'subcategories' => [
                'routes' => 'Routes',
                'parks' => 'Parks',
                'meetups' => 'Meetups',
            ]],
            'travel' => ['label' => 'Travel and documents', 'icon' => 'luggage', 'subcategories' => [
                'documents' => 'Documents',
                'transport' => 'Transport',
                'accommodation' => 'Accommodation',
            ]],
            'adoption' => ['label' => 'Adoption and shelters', 'icon' => 'house-heart', 'subcategories' => [
                'first-days' => 'First days',
                'fostering' => 'Fostering',
                'shelter-support' => 'Shelter support',
            ]],
            'lost-found' => ['label' => 'Lost and found', 'icon' => 'scan-search', 'subcategories' => [
                'lost-pet' => 'Lost pet',
                'found-pet' => 'Found pet',
                'search-coordination' => 'Search coordination',
            ]],
            'services' => ['label' => 'Services and gear', 'icon' => 'briefcase-medical', 'subcategories' => [
                'clinics' => 'Clinics',
                'grooming' => 'Grooming',
                'boarding' => 'Boarding and sitters',
                'technology' => 'Technology and trackers',
            ]],
            'support' => ['label' => 'Support', 'icon' => 'heart-handshake', 'subcategories' => [
                'loss' => 'Pet loss',
                'caregiver' => 'Caregiver support',
                'volunteers' => 'Volunteer support',
            ]],
        ];
    }

    /** @return array<string, string> */
    public function categoryOptions(): array
    {
        return collect($this->categories())
            ->mapWithKeys(fn (array $category, string $key): array => [$key => $category['label']])
            ->all();
    }

    /** @return array<string, string> */
    public function typeOptions(): array
    {
        return collect(ForumTopicType::cases())
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
            '' => 'No pet attached',
            'scout' => 'Scout / dog / 4 years',
            'nori' => 'Nori / cat / 2 years',
        ];
    }

    /** @return array<string, array{name: string, species: string, age: string}> */
    public function pets(): array
    {
        return [
            'scout' => ['name' => 'Scout', 'species' => 'Dog', 'age' => '4 years'],
            'nori' => ['name' => 'Nori', 'species' => 'Cat', 'age' => '2 years'],
        ];
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
