<?php

namespace App\Services;

final class PetFriendCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function records(): array
    {
        return [
            'pet-scout' => [
                'id' => 'pet-scout',
                'slug' => 'scout',
                'name' => 'Scout',
                'handle' => '@mia-carter/scout',
                'owner' => 'Mia Carter',
                'owner_handle' => '@mia-carter',
                'owner_conversation' => '',
                'species' => 'Dog',
                'breed' => 'Border Collie mix',
                'age' => '4 years',
                'size' => 'Medium',
                'location' => 'Richmond, Portland',
                'activity' => 'Active',
                'play_style' => 'Parallel walks and fetch',
                'description' => 'Focused trail walks, calm introductions, and structured play.',
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Scout, a black and white Border Collie, resting on grass',
                'route_name' => 'pet-social.pets.scout',
                'route_parameters' => [],
                'intents' => ['walk', 'play', 'training', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-nori' => [
                'id' => 'pet-nori',
                'slug' => 'nori',
                'name' => 'Nori',
                'handle' => '@mia-carter/nori',
                'owner' => 'Mia Carter',
                'owner_handle' => '@mia-carter',
                'owner_conversation' => '',
                'species' => 'Cat',
                'breed' => 'Tabby',
                'age' => '2 years',
                'size' => 'Small',
                'location' => 'Richmond, Portland',
                'activity' => 'Calm',
                'play_style' => 'Quiet company at a distance',
                'description' => 'Indoor routines, window watching, and slow introductions.',
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Nori, a tabby cat, looking toward the camera',
                'route_name' => 'pet-social.pets.nori',
                'route_parameters' => [],
                'intents' => ['play', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-mochi' => [
                'id' => 'pet-mochi',
                'slug' => 'mochi',
                'name' => 'Mochi',
                'handle' => '@ari-jensen/mochi',
                'owner' => 'Ari Jensen',
                'owner_handle' => '@ari-jensen',
                'owner_conversation' => 'ari',
                'species' => 'Dog',
                'breed' => 'Shiba Inu',
                'age' => '3 years',
                'size' => 'Medium',
                'location' => 'Pearl District, Portland',
                'activity' => 'Moderate',
                'play_style' => 'Parallel walks and calm greetings',
                'description' => 'A city dog who prefers steady routes and low-pressure hellos.',
                'image' => 'https://images.unsplash.com/photo-1769635325695-dead509dc5b3?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Mochi, a Shiba Inu, looking toward the camera',
                'route_name' => 'pet-social.neighbors.ari',
                'route_parameters' => [],
                'intents' => ['walk', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-juniper' => [
                'id' => 'pet-juniper',
                'slug' => 'juniper',
                'name' => 'Juniper',
                'handle' => '@noah-and-juniper/juniper',
                'owner' => 'Noah Kim',
                'owner_handle' => '@noah-and-juniper',
                'owner_conversation' => 'noah',
                'species' => 'Dog',
                'breed' => 'Australian Shepherd',
                'age' => '5 years',
                'size' => 'Medium',
                'location' => 'Sellwood, Portland',
                'activity' => 'Active',
                'play_style' => 'Trail walks with careful introductions',
                'description' => 'Thoughtful on first meetings and confident once a routine is familiar.',
                'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Juniper, an Australian Shepherd, sitting outdoors',
                'route_name' => 'pet-social.pets.index',
                'route_parameters' => ['q' => 'Juniper'],
                'intents' => ['walk', 'training'],
                'private' => true,
                'verified' => false,
            ],
            'pet-luna-labrador' => [
                'id' => 'pet-luna-labrador',
                'slug' => 'luna',
                'name' => 'Luna',
                'handle' => '@zoe-and-luna/luna',
                'owner' => 'Zoe Patel',
                'owner_handle' => '@zoe-and-luna',
                'owner_conversation' => '',
                'species' => 'Dog',
                'breed' => 'Labrador Retriever',
                'age' => '2 years',
                'size' => 'Large',
                'location' => 'Northwest Portland',
                'activity' => 'Very active',
                'play_style' => 'Chase, fetch, and open-space walks',
                'description' => 'A young, social Labrador looking for active outdoor companions.',
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Luna, a yellow Labrador, sitting outdoors',
                'route_name' => 'pet-social.pets.index',
                'route_parameters' => ['q' => 'Luna'],
                'intents' => ['walk', 'play', 'training'],
                'private' => false,
                'verified' => false,
            ],
            'pet-pip' => [
                'id' => 'pet-pip',
                'slug' => 'pip',
                'name' => 'Pip',
                'handle' => '@lena-brooks/pip',
                'owner' => 'Lena Brooks',
                'owner_handle' => '@lena-brooks',
                'owner_conversation' => 'lena',
                'species' => 'Cat',
                'breed' => 'Domestic Shorthair',
                'age' => '4 years',
                'size' => 'Small',
                'location' => 'Kerns, Portland',
                'activity' => 'Calm',
                'play_style' => 'Quiet company and window visits',
                'description' => 'A relaxed indoor cat who enjoys familiar voices and patient company.',
                'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Pip, a cat, looking up in soft light',
                'route_name' => 'pet-social.pets.index',
                'route_parameters' => ['q' => 'Pip'],
                'intents' => ['play', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-olive-rabbit' => [
                'id' => 'pet-olive-rabbit',
                'slug' => 'olive',
                'name' => 'Olive',
                'handle' => '@priya-fosters/olive',
                'owner' => 'Priya Shah',
                'owner_handle' => '@priya-fosters',
                'owner_conversation' => 'priya',
                'species' => 'Rabbit',
                'breed' => 'Mini Rex',
                'age' => '3 years',
                'size' => 'Small',
                'location' => 'Sellwood, Portland',
                'activity' => 'Calm',
                'play_style' => 'Separate-space enrichment',
                'description' => 'A foster rabbit whose social time always uses protected, separate spaces.',
                'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Olive, a small rabbit, sitting in grass',
                'route_name' => 'pet-social.pets.index',
                'route_parameters' => ['q' => 'Olive'],
                'intents' => ['play', 'neighbor'],
                'private' => true,
                'verified' => true,
            ],
            'pet-coco-spaniel' => [
                'id' => 'pet-coco-spaniel',
                'slug' => 'coco',
                'name' => 'Coco',
                'handle' => '@maya-and-coco/coco',
                'owner' => 'Maya Chen',
                'owner_handle' => '@maya-and-coco',
                'owner_conversation' => '',
                'species' => 'Dog',
                'breed' => 'English Cocker Spaniel',
                'age' => '4 years',
                'size' => 'Medium',
                'location' => 'Richmond, Portland',
                'activity' => 'Active',
                'play_style' => 'Sniff walks and gentle chase',
                'description' => 'A local spaniel who likes structured greetings and woodland routes.',
                'image' => 'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => 'Coco, a brown spaniel, sitting outdoors',
                'route_name' => 'pet-social.pets.index',
                'route_parameters' => ['q' => 'Coco'],
                'intents' => ['walk', 'play', 'neighbor'],
                'private' => false,
                'verified' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return $this->records()[$id] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function owned(): array
    {
        return array_intersect_key($this->records(), array_flip(['pet-scout', 'pet-nori']));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function candidates(string $source): array
    {
        $records = $this->records();

        return array_values(array_filter(
            $records,
            static fn (array $record): bool => $record['id'] !== $source
                && ! in_array($record['id'], ['pet-scout', 'pet-nori'], true),
        ));
    }

    /**
     * @return array{reason: string, shared: array<int, string>, cautions: array<int, string>, score: int}
     */
    public function compatibility(string $source, string $target): array
    {
        $sourcePet = $this->find($source);
        $targetPet = $this->find($target);

        if ($sourcePet === null || $targetPet === null) {
            return [
                'reason' => 'Compatibility details are unavailable.',
                'shared' => [],
                'cautions' => ['Owners should review both profiles before arranging contact.'],
                'score' => 0,
            ];
        }

        $sameSpecies = $sourcePet['species'] === $targetPet['species'];
        $sameLocation = str_contains($sourcePet['location'], 'Portland')
            && str_contains($targetPet['location'], 'Portland');
        $sharedIntents = array_values(array_intersect($sourcePet['intents'], $targetPet['intents']));
        $shared = [];

        if ($sameSpecies) {
            $shared[] = 'Both are '.$sourcePet['species'].' profiles';
        }

        if ($sameLocation) {
            $shared[] = 'Both live in the Portland area';
        }

        foreach (array_slice($sharedIntents, 0, 2) as $intent) {
            $shared[] = match ($intent) {
                'walk' => 'Both are open to shared walks',
                'play' => 'Both are open to social play',
                'training' => 'Both enjoy structured training',
                default => 'Both are open to nearby friends',
            };
        }

        $cautions = [];

        if (! $sameSpecies) {
            $cautions[] = 'Different species need protected spaces and owner-led introductions.';
        }

        if ($sourcePet['activity'] !== $targetPet['activity']) {
            $cautions[] = 'Their activity levels differ, so start with a short, calm meeting.';
        }

        if ($target === 'pet-juniper') {
            $cautions[] = 'Juniper prefers extra distance during first introductions.';
        }

        if ($target === 'pet-olive-rabbit') {
            $cautions[] = 'Olive should never share an unrestricted play space with another species.';
        }

        $score = min(98, 52 + (count($shared) * 12) - (count($cautions) * 6));

        return [
            'reason' => $sameSpecies
                ? 'Their profiles share routines that may support an owner-led introduction.'
                : 'Their owners share local interests, but any contact needs species-appropriate boundaries.',
            'shared' => $shared,
            'cautions' => $cautions,
            'score' => max(20, $score),
        ];
    }
}
