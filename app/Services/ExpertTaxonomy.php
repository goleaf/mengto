<?php

declare(strict_types=1);

namespace App\Services;

class ExpertTaxonomy
{
    /** @return array<string, string> */
    public function types(): array
    {
        return $this->labels('types', [
            'veterinarian',
            'avian-veterinarian',
            'exotic-veterinarian',
            'veterinary-surgeon',
            'veterinary-dermatologist',
            'veterinary-dentist',
            'veterinary-cardiologist',
            'veterinary-neurologist',
            'veterinary-ophthalmologist',
            'veterinary-oncologist',
            'rehabilitation-veterinarian',
            'veterinary-nutritionist',
            'behavior-veterinarian',
            'dog-trainer',
            'behavior-consultant',
            'cat-behavior-consultant',
            'feline-specialist',
            'groomer',
            'cat-groomer',
            'physiotherapist',
            'shelter-specialist',
        ]);
    }

    /** @return array<string, string> */
    public function species(): array
    {
        return $this->labels('species', [
            'dog',
            'cat',
            'bird',
            'parrot',
            'rabbit',
            'rodent',
            'reptile',
            'amphibian',
            'fish',
            'horse',
            'exotic-mammal',
            'farm-animal',
        ]);
    }

    /** @return array<string, string> */
    public function specializations(): array
    {
        return $this->labels('specializations', [
            'general-practice',
            'avian-medicine',
            'exotic-medicine',
            'surgery',
            'dermatology',
            'dentistry',
            'cardiology',
            'neurology',
            'ophthalmology',
            'oncology',
            'rehabilitation',
            'nutrition',
            'behavior',
            'training',
            'feline-care',
            'grooming',
            'senior-care',
            'adoption',
        ]);
    }

    /** @return array<string, string> */
    public function formats(): array
    {
        return $this->labels('formats', [
            'in-person',
            'video',
            'audio',
            'text',
            'asynchronous',
            'home-visit',
            'group',
        ]);
    }

    /** @return array<string, string> */
    public function languages(): array
    {
        return $this->labels('languages', [
            'Lithuanian',
            'English',
            'Russian',
            'Polish',
            'German',
        ]);
    }

    /** @return array<string, string> */
    public function availability(): array
    {
        return $this->labels('availability', [
            'available',
            'today',
            'week',
            'waitlist',
        ]);
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return $this->labels('sort_options', [
            'relevance',
            'availability',
            'rating',
            'experience',
            'newest',
        ]);
    }

    /** @return array<string, string> */
    public function pets(): array
    {
        return $this->labels('pets', [
            'scout',
            'nori',
            'kesha',
        ]);
    }

    /** @return array<string, array{name: string, species: string, age: string}> */
    public function petData(): array
    {
        return [
            'scout' => ['name' => __('messages.scout'), 'species' => 'dog', 'age' => __('messages.4_years')],
            'nori' => ['name' => __('messages.nori'), 'species' => 'cat', 'age' => __('messages.2_years')],
            'kesha' => ['name' => __('messages.kesha'), 'species' => 'bird', 'age' => __('messages.2_years')],
        ];
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    private function labels(string $group, array $keys): array
    {
        return collect($keys)
            ->mapWithKeys(fn (string $key): array => [
                $key => (string) __("experts.{$group}.{$key}"),
            ])
            ->all();
    }
}
