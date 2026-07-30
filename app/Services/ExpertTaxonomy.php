<?php

namespace App\Services;

class ExpertTaxonomy
{
    /** @return array<string, string> */
    public function types(): array
    {
        return [
            'veterinarian' => 'General veterinarian',
            'avian-veterinarian' => 'Avian veterinarian',
            'exotic-veterinarian' => 'Exotic animal veterinarian',
            'veterinary-surgeon' => 'Veterinary surgeon',
            'veterinary-dermatologist' => 'Veterinary dermatologist',
            'veterinary-dentist' => 'Veterinary dentist',
            'veterinary-cardiologist' => 'Veterinary cardiologist',
            'veterinary-neurologist' => 'Veterinary neurologist',
            'veterinary-ophthalmologist' => 'Veterinary ophthalmologist',
            'veterinary-oncologist' => 'Veterinary oncologist',
            'rehabilitation-veterinarian' => 'Veterinary rehabilitation specialist',
            'veterinary-nutritionist' => 'Veterinary nutritionist',
            'behavior-veterinarian' => 'Veterinary behaviorist',
            'dog-trainer' => 'Dog trainer',
            'behavior-consultant' => 'Animal behavior consultant',
            'cat-behavior-consultant' => 'Cat behavior consultant',
            'feline-specialist' => 'Feline specialist',
            'groomer' => 'Groomer',
            'cat-groomer' => 'Cat groomer',
            'physiotherapist' => 'Animal physiotherapist',
            'shelter-specialist' => 'Shelter specialist',
        ];
    }

    /** @return array<string, string> */
    public function species(): array
    {
        return [
            'dog' => 'Dogs',
            'cat' => 'Cats',
            'bird' => 'Birds',
            'parrot' => 'Parrots',
            'rabbit' => 'Rabbits',
            'rodent' => 'Rodents',
            'reptile' => 'Reptiles',
            'amphibian' => 'Amphibians',
            'fish' => 'Fish',
            'horse' => 'Horses',
            'exotic-mammal' => 'Exotic mammals',
            'farm-animal' => 'Farm animals',
        ];
    }

    /** @return array<string, string> */
    public function specializations(): array
    {
        return [
            'general-practice' => 'General practice',
            'avian-medicine' => 'Avian medicine',
            'exotic-medicine' => 'Exotic animal medicine',
            'surgery' => 'Surgery',
            'dermatology' => 'Dermatology',
            'dentistry' => 'Dentistry',
            'cardiology' => 'Cardiology',
            'neurology' => 'Neurology',
            'ophthalmology' => 'Ophthalmology',
            'oncology' => 'Oncology',
            'rehabilitation' => 'Rehabilitation',
            'nutrition' => 'Nutrition',
            'behavior' => 'Behavior',
            'training' => 'Training',
            'feline-care' => 'Feline care',
            'grooming' => 'Grooming',
            'senior-care' => 'Senior pet care',
            'adoption' => 'Adoption support',
        ];
    }

    /** @return array<string, string> */
    public function formats(): array
    {
        return [
            'in-person' => 'In person',
            'video' => 'Video consultation',
            'audio' => 'Audio consultation',
            'text' => 'Text consultation',
            'asynchronous' => 'Asynchronous review',
            'home-visit' => 'Home visit',
            'group' => 'Group session',
        ];
    }

    /** @return array<string, string> */
    public function languages(): array
    {
        return [
            'Lithuanian' => 'Lithuanian',
            'English' => 'English',
            'Russian' => 'Russian',
            'Polish' => 'Polish',
            'German' => 'German',
        ];
    }

    /** @return array<string, string> */
    public function availability(): array
    {
        return [
            'available' => 'Accepting clients',
            'today' => 'Available today',
            'week' => 'Available this week',
            'waitlist' => 'Waitlist available',
        ];
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return [
            'relevance' => 'Best match',
            'availability' => 'Soonest available',
            'rating' => 'Client rating',
            'experience' => 'Experience',
            'newest' => 'Recently joined',
        ];
    }

    /** @return array<string, string> */
    public function pets(): array
    {
        return [
            'scout' => 'Scout · Dog · 4 years',
            'nori' => 'Nori · Cat · 2 years',
            'kesha' => 'Kesha · Bird · 2 years',
        ];
    }

    /** @return array<string, array{name: string, species: string, age: string}> */
    public function petData(): array
    {
        return [
            'scout' => ['name' => __('messages.scout_8a1db462be'), 'species' => 'dog', 'age' => __('messages.4_years_cfd73a0bc4')],
            'nori' => ['name' => __('messages.nori_a64203ba20'), 'species' => 'cat', 'age' => __('messages.2_years_7dab2372ff')],
            'kesha' => ['name' => __('messages.kesha_b8b1fc4ca7'), 'species' => 'bird', 'age' => __('messages.2_years_7dab2372ff')],
        ];
    }
}
