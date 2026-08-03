<?php

use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\User;
use App\Services\PreviewService;
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
            <x-linked-media href="https://mengto.test/pets/scout" label="Open Scout profile" variant="card">
                <img src="https://example.test/scout.jpg" alt="Scout resting on grass">
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

    expect($linkedXPath->query('//a[@data-linked-media="linked" and @href="https://mengto.test/pets/scout" and @aria-label="Open Scout profile"]')->length)
        ->toBe(1)
        ->and($linkedXPath->query('//a[@data-linked-media="linked"]//img[@alt="Scout resting on grass"]')->length)
        ->toBe(1)
        ->and($passiveXPath->query('//a')->length)
        ->toBe(0)
        ->and($passiveXPath->query('//div[@data-linked-media="passive"]//img[@alt="Maple resting indoors"]')->length)
        ->toBe(1);
});

test('pet directory media uses the same profile destination as the pet name', function () {
    $response = $this->get(route('pets.index'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);

    foreach (['Scout', 'Nori'] as $name) {
        $card = $xpath->query('//article[@data-directory-pet][.//h2[normalize-space()="'.$name.'"]]')->item(0);

        expect($card)->not->toBeNull();

        $mediaLink = $xpath->query('.//a[@data-linked-media="linked"]', $card)->item(0);
        $titleLink = $xpath->query('.//h2//a', $card)->item(0);

        expect($mediaLink)->not->toBeNull()
            ->and($titleLink)->not->toBeNull()
            ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
            ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue)
            ->and($mediaLink?->attributes?->getNamedItem('aria-label')?->nodeValue)
            ->toBe(__('presentation.open_profile', ['name' => $name]));
    }

    $passiveCard = $xpath->query('//article[@data-directory-pet][.//h2[normalize-space()="Maple"]]')->item(0);

    expect($passiveCard)->not->toBeNull()
        ->and($xpath->query('.//a[@data-linked-media]', $passiveCard)->length)
        ->toBe(0)
        ->and($xpath->query('.//*[@data-linked-media="passive"]', $passiveCard)->length)
        ->toBe(1);
});

test('directory cards keep media and title destinations synchronized', function (string $routeName, string $cardSelector) {
    $response = $this->get(route($routeName));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $cards = $xpath->query($cardSelector);

    expect($cards->length)->toBeGreaterThan(0);

    foreach ($cards as $card) {
        $titleLink = $xpath->query('.//*[self::h2 or self::h3]//a', $card)->item(0);
        $mediaLink = $xpath->query('.//a[@data-linked-media="linked"]', $card)->item(0);

        if ($titleLink === null) {
            expect($mediaLink)->toBeNull()
                ->and($xpath->query('.//*[@data-linked-media="passive"]', $card)->length)
                ->toBeGreaterThan(0);

            continue;
        }

        expect($mediaLink)->not->toBeNull()
            ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
            ->toBe($titleLink->attributes?->getNamedItem('href')?->nodeValue);
    }
})->with([
    'neighbor directory' => ['neighbors.index', '//article[@data-neighbor-card]'],
    'group directory' => ['groups.index', '//article[@data-group-card]'],
]);

test('meetup card media uses the same destination as its title', function () {
    $meetups = app(PreviewService::class)->meetupDirectoryData()['directoryMeetups'];
    $meetup = collect($meetups)->first(
        static fn (array $candidate): bool => ($candidate['media_target'] ?? null) !== null,
    );

    expect($meetup)->toBeArray();

    $card = Blade::render('<x-meetup-card :meetup="$meetup" />', ['meetup' => $meetup]);
    $xpath = linkedMediaXPath($card);
    $mediaLink = $xpath->query('//a[@data-linked-media="linked"]')->item(0);
    $titleLink = $xpath->query('//h3//a')->item(0);

    expect($mediaLink)->not->toBeNull()
        ->and($titleLink)->not->toBeNull()
        ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue);
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

test('profile pet thumbnails use the same destination as their names', function () {
    $response = $this->get(route('profile.mia'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $cards = $xpath->query('//article[@data-profile-pet]');

    expect($cards->length)->toBeGreaterThan(0);

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

test('message identity media uses the conversation details destination', function () {
    $response = $this->get(route('messages.index', ['conversation' => 'ari']));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $headerMedia = $xpath->query('//header[contains(concat(" ", normalize-space(@class), " "), " messaging-thread-header ")]//a[@data-linked-media="linked"]')->item(0);
    $detailsLink = $xpath->query('//header[contains(concat(" ", normalize-space(@class), " "), " messaging-thread-header ")]//div[contains(concat(" ", normalize-space(@class), " "), " messaging-thread-header__actions ")]/a')->item(0);
    $contextMedia = $xpath->query('//aside[contains(concat(" ", normalize-space(@class), " "), " messaging-context ")]//section[contains(concat(" ", normalize-space(@class), " "), " messaging-context__identity ")]//a[@data-linked-media="linked"]')->item(0);

    expect($headerMedia)->not->toBeNull()
        ->and($detailsLink)->not->toBeNull()
        ->and($contextMedia)->not->toBeNull()
        ->and($headerMedia?->attributes?->getNamedItem('href')?->nodeValue)
        ->toBe($detailsLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->and($contextMedia?->attributes?->getNamedItem('href')?->nodeValue)
        ->toBe($detailsLink?->attributes?->getNamedItem('href')?->nodeValue);
});

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
    'profile' => 'profile.mia',
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
        ->and($templates)->toHaveCount(74);

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
