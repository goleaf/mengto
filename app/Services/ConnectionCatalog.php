<?php

namespace App\Services;

final class ConnectionCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function records(): array
    {
        return [
            'owner-mia-carter' => $this->record(
                key: 'owner-mia-carter',
                name: 'Mia Carter',
                handle: '@mia-carter',
                type: 'people',
                typeLabel: 'Pet owner and volunteer',
                description: 'Trail walks, foster setup notes, and quiet Portland routes.',
                location: 'Richmond, Portland',
                image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Mia Carter profile portrait',
                followers: '2.4k followers',
                routeName: 'pet-social.profile.mia',
                tags: ['foster', 'walks', 'local'],
            ),
            'pet-scout' => $this->record(
                key: 'pet-scout',
                name: 'Scout',
                handle: '@mia-carter/scout',
                type: 'pets',
                typeLabel: 'Border Collie mix',
                description: 'Shaded park loops, calm greetings, and positive training.',
                location: 'Richmond, Portland',
                image: 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Scout, a black and white Border Collie mix',
                followers: '1.8k followers',
                routeName: 'pet-social.pets.scout',
                tags: ['dog', 'walks', 'training'],
            ),
            'pet-nori' => $this->record(
                key: 'pet-nori',
                name: 'Nori',
                handle: '@mia-carter/nori',
                type: 'pets',
                typeLabel: 'Tabby cat',
                description: 'Indoor enrichment, window watching, and quiet routines.',
                location: 'Richmond, Portland',
                image: 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Nori, a tabby cat, looking toward the camera',
                followers: '690 followers',
                routeName: 'pet-social.pets.nori',
                tags: ['cat', 'indoor enrichment', 'quiet'],
            ),
            'owner-ari-jensen' => $this->record(
                key: 'owner-ari-jensen',
                name: 'Ari Jensen',
                handle: '@ari-jensen',
                type: 'people',
                typeLabel: 'Pet owner',
                description: 'Quiet city walks with Mochi and practical training notes.',
                location: 'Pearl District, Portland',
                image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Ari Jensen profile portrait',
                followers: '1.3k followers',
                routeName: 'pet-social.neighbors.ari',
                tags: ['local', 'walks', 'training'],
            ),
            'pet-mochi' => $this->record(
                key: 'pet-mochi',
                name: 'Mochi',
                handle: '@ari-jensen/mochi',
                type: 'pets',
                typeLabel: 'Shiba Inu mix',
                description: 'Careful greetings, cafe practice, and calm city routines.',
                location: 'Pearl District, Portland',
                image: 'https://images.unsplash.com/photo-1769635325695-dead509dc5b3?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Mochi, a Shiba Inu, looking toward the camera',
                followers: '860 followers',
                routeName: 'pet-social.neighbors.ari',
                tags: ['dog', 'Shiba mix', 'young adult'],
            ),
            'organization-rose-city' => $this->record(
                key: 'organization-rose-city',
                name: 'Rose City Animal Shelter',
                handle: '@rose-city-shelter',
                type: 'organizations',
                typeLabel: 'Verified shelter',
                description: 'Adoption profiles, foster updates, and local volunteer needs.',
                location: 'North Portland',
                image: 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Brown shelter dog standing outside',
                followers: '4.8k followers',
                routeName: 'pet-social.discover.index',
                routeParameters: ['q' => 'Rose City Animal Shelter'],
                tags: ['adoption', 'foster', 'verified'],
                verified: true,
            ),
            'specialist-elena-ruiz' => $this->record(
                key: 'specialist-elena-ruiz',
                name: 'Dr. Elena Ruiz',
                handle: '@dr-elena-ruiz',
                type: 'specialists',
                typeLabel: 'Verified veterinarian',
                description: 'General practice, preventive care, and practical summer safety.',
                location: 'Portland, Oregon',
                image: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Dr. Elena Ruiz profile portrait',
                followers: '3.2k followers',
                routeName: 'pet-social.discover.index',
                routeParameters: ['q' => 'Dr Elena Ruiz'],
                tags: ['veterinary', 'health', 'verified'],
                verified: true,
            ),
            'group-apartment-pets' => $this->record(
                key: 'group-apartment-pets',
                name: 'Apartment Pets PDX',
                handle: '@apartment-pets-pdx',
                type: 'groups',
                typeLabel: 'Open community',
                description: 'Small-space routines, indoor enrichment, and low-pressure meetups.',
                location: 'Portland',
                image: 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'A dog and cat relaxing together',
                followers: '2.4k members',
                routeName: 'pet-social.groups.apartment_pets',
                tags: ['community', 'indoor pets', 'local'],
            ),
            'topic-positive-training' => $this->record(
                key: 'topic-positive-training',
                name: 'Positive training',
                handle: '#positive-training',
                type: 'topics',
                typeLabel: 'Topic',
                description: 'Reward-based routines, confidence building, and calm introductions.',
                location: 'All regions',
                image: 'https://images.unsplash.com/photo-1554456854-55a089fd4cb2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'A dog looking attentively during a training session',
                followers: '12k followers',
                routeName: 'pet-social.discover.index',
                routeParameters: ['q' => 'positive training'],
                tags: ['training', 'behavior', 'topic'],
            ),
            'owner-lena-brooks' => $this->record(
                key: 'owner-lena-brooks',
                name: 'Lena Brooks',
                handle: '@lena-brooks',
                type: 'people',
                typeLabel: 'Cat owner',
                description: 'Neighborhood cat safety and updates about Willow.',
                location: 'Richmond, Portland',
                image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Lena Brooks profile portrait',
                followers: '420 followers',
                routeName: 'pet-social.neighbors.index',
                routeParameters: ['q' => 'Lena Brooks'],
                tags: ['cats', 'local', 'safety'],
            ),
            'pet-willow' => $this->record(
                key: 'pet-willow',
                name: 'Willow',
                handle: '@lena-brooks/willow',
                type: 'pets',
                typeLabel: 'Tabby cat',
                description: 'Indoor routines, window watching, and neighborhood safety.',
                location: 'Richmond, Portland',
                image: 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Willow, a grey tabby cat, looking upward',
                followers: '380 followers',
                routeName: 'pet-social.pets.index',
                routeParameters: ['q' => 'Willow'],
                tags: ['cat', 'tabby', 'local'],
            ),
            'owner-noah-kim' => $this->record(
                key: 'owner-noah-kim',
                name: 'Noah Kim',
                handle: '@noah-and-juniper',
                type: 'people',
                typeLabel: 'Private pet owner',
                description: 'Weekend trail notes and confidence-building walks with Juniper.',
                location: 'Portland',
                image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Noah Kim profile portrait',
                followers: 'Private profile',
                routeName: 'pet-social.neighbors.index',
                routeParameters: ['q' => 'Noah Kim'],
                tags: ['dogs', 'trails', 'private'],
                private: true,
            ),
            'pet-juniper' => $this->record(
                key: 'pet-juniper',
                name: 'Juniper',
                handle: '@noah-and-juniper/juniper',
                type: 'pets',
                typeLabel: 'Private dog profile',
                description: 'A thoughtful trail companion who prefers careful introductions.',
                location: 'Portland',
                image: 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Juniper, a dog, sitting outdoors',
                followers: 'Private profile',
                routeName: 'pet-social.pets.index',
                routeParameters: ['q' => 'Juniper'],
                tags: ['dog', 'trails', 'private'],
                private: true,
            ),
            'owner-priya-shah' => $this->record(
                key: 'owner-priya-shah',
                name: 'Priya Shah',
                handle: '@priya-shah',
                type: 'people',
                typeLabel: 'Foster volunteer',
                description: 'Senior foster care, accessible walks, and local volunteer shifts.',
                location: 'Sellwood, Portland',
                image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Priya Shah profile portrait',
                followers: '970 followers',
                routeName: 'pet-social.neighbors.index',
                routeParameters: ['q' => 'Priya Shah'],
                tags: ['foster', 'senior pets', 'local'],
            ),
            'owner-zoe-patel' => $this->record(
                key: 'owner-zoe-patel',
                name: 'Zoe Patel',
                handle: '@zoe-patel',
                type: 'people',
                typeLabel: 'Dog owner',
                description: 'Young-dog training routines and relaxed neighborhood walks with Luna.',
                location: 'Alberta, Portland',
                image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Zoe Patel profile portrait',
                followers: '540 followers',
                routeName: 'pet-social.neighbors.index',
                routeParameters: ['q' => 'Zoe Patel'],
                tags: ['dogs', 'training', 'local'],
            ),
            'pet-luna-labrador' => $this->record(
                key: 'pet-luna-labrador',
                name: 'Luna',
                handle: '@zoe-patel/luna',
                type: 'pets',
                typeLabel: 'Young Labrador',
                description: 'Friendly training walks and gentle play with similar-sized dogs.',
                location: 'Alberta, Portland',
                image: 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Luna, a young golden Labrador, sitting outside',
                followers: '610 followers',
                routeName: 'pet-social.pets.index',
                routeParameters: ['q' => 'Luna Labrador'],
                tags: ['dog', 'Labrador', 'young'],
            ),
            'specialist-cam-lee' => $this->record(
                key: 'specialist-cam-lee',
                name: 'Cam Lee',
                handle: '@cam-positive-dogs',
                type: 'specialists',
                typeLabel: 'Verified dog trainer',
                description: 'Low-pressure introductions and reward-based city training.',
                location: 'Southeast Portland',
                image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: 'Cam Lee profile portrait',
                followers: '1.9k followers',
                routeName: 'pet-social.discover.index',
                routeParameters: ['q' => 'Cam Lee dog trainer'],
                tags: ['training', 'dogs', 'verified'],
                verified: true,
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $target): ?array
    {
        return $this->records()[$target] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function followerTargets(): array
    {
        return ['owner-ari-jensen', 'owner-lena-brooks', 'owner-priya-shah'];
    }

    /**
     * @return array<int, string>
     */
    public function incomingRequestTargets(): array
    {
        return ['owner-noah-kim', 'owner-zoe-patel'];
    }

    /**
     * @return array<int, array{target: string, group: string, reason: string, signals: array<int, string>}>
     */
    public function recommendations(): array
    {
        return [
            [
                'target' => 'pet-luna-labrador',
                'group' => 'Pet matches',
                'reason' => 'Young dog in your city with a similar activity level.',
                'signals' => ['Portland', 'young dog', 'group walks'],
            ],
            [
                'target' => 'pet-juniper',
                'group' => 'Pet matches',
                'reason' => 'Scout and Juniper both prefer careful introductions.',
                'signals' => ['calm greetings', 'trail walks'],
            ],
            [
                'target' => 'owner-priya-shah',
                'group' => 'Nearby people',
                'reason' => 'You share foster-care interests and two local connections.',
                'signals' => ['2 mutuals', 'foster care', 'Portland'],
            ],
            [
                'target' => 'specialist-elena-ruiz',
                'group' => 'Trusted help',
                'reason' => 'Verified veterinarian near Portland who publishes about preventive care.',
                'signals' => ['verified', 'nearby', 'health'],
            ],
            [
                'target' => 'specialist-cam-lee',
                'group' => 'Trusted help',
                'reason' => 'You save positive-training posts and follow local dog routines.',
                'signals' => ['verified', 'training', 'dogs'],
            ],
            [
                'target' => 'organization-rose-city',
                'group' => 'Local organizations',
                'reason' => 'Verified shelter near you with active foster and adoption updates.',
                'signals' => ['verified', 'adoption', 'local'],
            ],
            [
                'target' => 'group-apartment-pets',
                'group' => 'Communities',
                'reason' => 'Your cat-enrichment interests overlap with this local group.',
                'signals' => ['local group', 'indoor pets'],
            ],
            [
                'target' => 'topic-positive-training',
                'group' => 'Topics',
                'reason' => 'You recently saved several calm-training publications.',
                'signals' => ['training', 'behavior'],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $tags
     * @param  array<string, string>  $routeParameters
     * @return array<string, mixed>
     */
    private function record(
        string $key,
        string $name,
        string $handle,
        string $type,
        string $typeLabel,
        string $description,
        string $location,
        string $image,
        string $imageAlt,
        string $followers,
        string $routeName,
        array $tags,
        array $routeParameters = [],
        bool $verified = false,
        bool $private = false,
    ): array {
        return compact(
            'key',
            'name',
            'handle',
            'type',
            'typeLabel',
            'description',
            'location',
            'image',
            'imageAlt',
            'followers',
            'routeName',
            'routeParameters',
            'tags',
            'verified',
            'private',
        );
    }
}
