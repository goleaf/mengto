<?php

use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\Listing;
use App\Models\Order;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

function linkedMediaXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}

test('linked media renders a semantic anchor and a passive null state', function () {
    $linked = Blade::render(
        <<<'BLADE'
            <x-linked-media href="https://mengto.test/pets/birch" label="Open Birch profile" variant="card">
                <img src="https://example.test/birch.jpg" alt="Birch resting on grass">
            </x-linked-media>
        BLADE,
    );
    $passive = Blade::render(
        <<<'BLADE'
            <x-linked-media :href="null" :label="null" variant="card">
                <img src="https://example.test/maple.jpg" alt="Maple resting indoors">
            </x-linked-media>
        BLADE,
    );

    $linkedXPath = linkedMediaXPath($linked);
    $passiveXPath = linkedMediaXPath($passive);

    expect($linkedXPath->query('//a[@data-linked-media="linked" and @href="https://mengto.test/pets/birch" and @aria-label="Open Birch profile"]')->length)
        ->toBe(1)
        ->and($linkedXPath->query('//a[@data-linked-media="linked"]//img[@alt="Birch resting on grass"]')->length)
        ->toBe(1)
        ->and($passiveXPath->query('//a')->length)
        ->toBe(0)
        ->and($passiveXPath->query('//div[@data-linked-media="passive"]//img[@alt="Maple resting indoors"]')->length)
        ->toBe(1);
});

test('pet directory media uses the same profile destination as the pet name', function () {
    foreach (['Birch', 'Maple'] as $name) {
        $profile = PetProfile::factory()->for($this->authenticatedUser)->create(['name' => $name]);
        PetProfileManager::factory()
            ->for($profile, 'profile')
            ->for($this->authenticatedUser)
            ->create();
    }

    $response = $this->get(route('pets.index'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);

    foreach (['Birch', 'Maple'] as $name) {
        $card = $xpath->query('//article[@data-pet-workspace-profile][.//h2[normalize-space()="'.$name.'"]]')->item(0);

        expect($card)->not->toBeNull();

        $mediaLink = $xpath->query('.//a[@data-linked-media="linked"]', $card)->item(0);
        $titleLink = $xpath->query('.//h2//a', $card)->item(0);

        expect($mediaLink)->not->toBeNull()
            ->and($titleLink)->not->toBeNull()
            ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
            ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue)
            ->and($mediaLink?->attributes?->getNamedItem('aria-label')?->nodeValue)
            ->toBe(__('pet_workspace.open_workspace', ['name' => $name]));
    }
});

test('discover result media uses the same destination as its title', function () {
    ForumEvent::factory()->create(['title' => 'Linked discovery event']);

    $response = $this->get(route('discover.index'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $cards = $xpath->query('//article[@data-discover-result]');

    expect($cards->length)->toBe(1);

    foreach ($cards as $card) {
        $mediaLink = $xpath->query('.//a[@data-linked-media="linked"]', $card)->item(0);
        $titleLink = $xpath->query('.//h3//a', $card)->item(0);

        expect($mediaLink)->not->toBeNull()
            ->and($titleLink)->not->toBeNull()
            ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
            ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue);
    }
});

test('expert avatar and placeholder targets match the expert name', function (?string $avatarUrl) {
    $expert = ExpertProfile::factory()->create([
        'public_name' => $avatarUrl === null ? 'Placeholder Specialist' : 'Photo Specialist',
        'slug' => $avatarUrl === null ? 'placeholder-specialist' : 'photo-specialist',
        'avatar_url' => $avatarUrl,
    ]);

    $response = $this->get(route('experts.index'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $card = $xpath->query('//article[.//h2//a[@href="'.route('experts.show', $expert).'"]]')->item(0);

    expect($card)->not->toBeNull();

    $mediaLink = $xpath->query('.//a[@data-linked-media="linked"]', $card)->item(0);
    $titleLink = $xpath->query('.//h2//a', $card)->item(0);

    expect($mediaLink)->not->toBeNull()
        ->and($titleLink)->not->toBeNull()
        ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->and($mediaLink?->attributes?->getNamedItem('aria-label')?->nodeValue)
        ->toBe(__('presentation.open_profile', ['name' => $expert->public_name]));
})->with([
    'uploaded avatar' => 'https://example.test/specialist.jpg',
    'initials placeholder' => null,
]);

test('marketplace order snapshot media matches the listing title destination', function () {
    $user = User::factory()->create(['actor_key' => 'linked-media-buyer']);
    $listing = Listing::factory()->create(['title' => 'Reflective walking harness']);
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => $user->actor_key,
        'requester_name' => $user->name,
    ]);
    $order = Order::factory()->create([
        'listing_id' => $listing->id,
        'reservation_id' => $reservation->id,
        'buyer_key' => $user->actor_key,
        'item_snapshot' => [
            'title' => $listing->title,
            'description' => $listing->description,
            'condition' => 'good',
            'quantity' => 1,
            'cover_url' => $listing->cover_url,
        ],
    ]);

    $response = $this->actingAs($user)->get(route('marketplace.orders.show', [$listing, $order]));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $mediaLink = $xpath->query('//section[@aria-labelledby="item-heading"]//a[@data-linked-media="linked"]')->item(0);
    $titleLink = $xpath->query('//section[@aria-labelledby="item-heading"]//h3/a')->item(0);

    expect($mediaLink)->not->toBeNull()
        ->and($titleLink)->not->toBeNull()
        ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->and($mediaLink?->attributes?->getNamedItem('aria-label')?->nodeValue)
        ->toBe(__('presentation.view_listing', ['title' => $listing->title]));
});

test('linked media never contains nested interactive controls on migrated routes', function (string $routeName) {
    $response = $this->get(route($routeName));

    $response->assertSuccessful();

    $xpath = responseXPath($response);

    expect($xpath->query('//a[@data-linked-media]//*[self::a or self::button or self::input or self::select or self::textarea or self::summary or self::audio or self::video]')->length)
        ->toBe(0);
})->with([
    'pets' => 'pets.index',
    'neighbors' => 'neighbors.index',
    'groups' => 'groups.index',
    'discover' => 'discover.index',
    'messages' => 'messages.index',
]);

test('every media-bearing Blade template has an explicit navigation classification', function () {
    $inventory = require base_path('tests/Fixtures/linked-media-navigation.php');
    $templates = collect(File::allFiles(resource_path('views')))
        ->filter(static function (SplFileInfo $file): bool {
            if ($file->getExtension() !== 'php') {
                return false;
            }

            $source = File::get($file->getPathname());

            return preg_match('/<img\b|<x-responsive-image\b|<x-card-media\b|<x-avatar\b|<x-initials-avatar\b/', $source) === 1;
        })
        ->map(static fn (SplFileInfo $file): string => 'resources/views/'.str_replace('\\', '/', $file->getRelativePathname()))
        ->sort()
        ->values()
        ->all();
    $classifiedTemplates = collect(array_keys($inventory))->sort()->values()->all();
    $allowedClassifications = [
        'action',
        'composite',
        'conditional',
        'current-page',
        'decorative',
        'linked',
        'passive',
        'primitive',
        'protected-download',
        'viewer',
    ];

    expect($classifiedTemplates)->toBe($templates)
        ->and($templates)->toHaveCount(57);

    foreach ($inventory as $classifications) {
        expect($classifications)->not->toBeEmpty();

        foreach ($classifications as $classification) {
            expect($allowedClassifications)->toContain($classification);
        }
    }
});

test('card media overlay slots contain no interactive descendants', function () {
    $componentFiles = collect(File::allFiles(resource_path('views')))
        ->filter(static fn (SplFileInfo $file): bool => str_contains(
            File::get($file->getPathname()),
            '<x-card-media',
        ));

    foreach ($componentFiles as $file) {
        $source = File::get($file->getPathname());
        preg_match_all('/<x-card-media\b[^>]*>(.*?)<\/x-card-media>/s', $source, $matches);

        foreach ($matches[1] as $slot) {
            expect($slot)->not->toMatch('/<(?:a|button|input|select|textarea|summary|audio|video)\b|<x-(?:action-control|text-link|optional-link)\b/i');
        }
    }
});
