<?php

use App\Http\Controllers\GroupDirectoryPreviewController;
use App\View\Components\GroupCard;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

function groupCardXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}

test('the group directory renders a functional local community catalog', function () {
    $route = Route::getRoutes()->getByName('groups.index');

    expect($route)
        ->not->toBeNull()
        ->and($route?->uri())->toBe('groups')
        ->and($route?->methods())->toBe(['GET', 'HEAD'])
        ->and($route?->getActionName())->toBe(GroupDirectoryPreviewController::class)
        ->and($route?->gatherMiddleware())->toContain('web');

    $response = $this->get(route('groups.index'));

    $response
        ->assertSuccessful()
        ->assertSee('<title>Groups | PawCircle</title>', false)
        ->assertSee('data-section="group-header"', false)
        ->assertSee('data-section="group-summary"', false)
        ->assertSee('data-section="group-filters"', false)
        ->assertSee('data-section="group-directory"', false)
        ->assertSee('Apartment Pets PDX')
        ->assertSee('Trail Tails')
        ->assertSee('Cat People of Portland')
        ->assertSee('Foster Network PDX')
        ->assertSee('Gentle Senior Companions')
        ->assertSee('Portland Labradors')
        ->assertSee('Recommended')
        ->assertSee('alt="Dog and cat resting together in a compact home"', false)
        ->assertSee('alt="Dogs running together beside a wooded trail"', false)
        ->assertSee('alt="Two fluffy cats sitting together indoors"', false)
        ->assertSee('alt="Foster dog resting safely on a blue couch"', false);

    $xpath = responseXPath($response);

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and(trim((string) $xpath->query('//h1')->item(0)?->textContent))->toBe('Find your people and build something useful')
        ->and($xpath->query('//article[@data-group-card]')->length)->toBe(6)
        ->and($xpath->query('//section[@data-section="group-directory"]/h2')->length)->toBe(1)
        ->and($xpath->query('//article[@data-group-card]//h3')->length)->toBe(6)
        ->and($xpath->query('//section[@data-section="group-directory"]/*[@role="list" and contains(concat(" ", normalize-space(@class), " "), " sm:grid-cols-2 ") and contains(concat(" ", normalize-space(@class), " "), " xl:grid-cols-3 ") and not(contains(concat(" ", normalize-space(@class), " "), " xl:grid-cols-4 "))]')->length)->toBe(1)
        ->and($xpath->query('//article[@data-group-card]//img[@loading="eager" and @fetchpriority="high" and @srcset and @sizes]')->length)->toBe(1)
        ->and($xpath->query('//article[@data-group-card]//img[@loading="lazy" and @decoding="async" and @srcset and @sizes]')->length)->toBe(5)
        ->and($xpath->query('//article[@data-group-card]//a')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//input[@id="group-search" and not(@disabled)]')->length)->toBe(1)
        ->and($xpath->query('//main//form')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//header//nav[@aria-label="Preview navigation"]')->length)->toBe(0);
});

test('group cards use shared media body and footer regions without mixing image and copy', function () {
    $response = $this->get(route('groups.index'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $cards = $xpath->query('//article[@data-group-card and @data-ui-card]');

    expect($cards->length)->toBe(6);

    foreach ($cards as $card) {
        $media = $xpath->query('./div[@data-card-region="media"]', $card)->item(0);
        $body = $xpath->query('./div[@data-card-region="body"]', $card)->item(0);

        expect($media)->not->toBeNull()
            ->and($body)->not->toBeNull()
            ->and($xpath->query('.//img', $media)->length)->toBe(1)
            ->and($xpath->query('.//h3[@data-card-heading]', $body)->length)->toBe(1)
            ->and($xpath->query('.//p[@data-card-description]', $body)->length)->toBe(1)
            ->and($xpath->query('.//*[@data-card-region="footer"]', $body)->length)->toBe(1)
            ->and(' '.$media?->attributes?->getNamedItem('class')?->nodeValue.' ')
            ->toContain(' border-b ')
            ->and(' '.$body?->attributes?->getNamedItem('class')?->nodeValue.' ')
            ->toContain(' bg-white ');
    }
});

test('the shared card heading keeps semantic levels and optional destinations explicit', function () {
    $linked = Blade::render(
        '<x-card-heading title="Community walks" href="https://mengto.test/groups/walks" />',
    );
    $unlinked = Blade::render(
        '<x-card-heading title="Private draft" :href="null" :level="2" spacing="none" />',
    );

    $linkedXPath = groupCardXPath($linked);
    $unlinkedXPath = groupCardXPath($unlinked);

    expect($linkedXPath->query('//h3[@data-card-heading]/a[@href="https://mengto.test/groups/walks"]')->length)
        ->toBe(1)
        ->and($unlinkedXPath->query('//h2[@data-card-heading and normalize-space()="Private draft"]')->length)
        ->toBe(1)
        ->and($unlinkedXPath->query('//a')->length)
        ->toBe(0);
});

test('group card fallback actions use the active locale', function (string $locale) {
    Lang::setLocale($locale);

    $source = file_get_contents(app_path('View/Components/GroupCard.php'));

    expect($source)
        ->not->toBeFalse()
        ->toContain("__('ui.joined_69318b0c6a')")
        ->toContain("__('ui.join_fd30fe681b')")
        ->not->toContain("? 'Joined' : 'Join'");

    foreach ([false, true] as $joined) {
        $component = new GroupCard([
            'name' => 'Community walks',
            'members' => '12 members',
            'activity' => 'Weekly',
            'joined' => $joined,
        ]);

        expect($component->primary['label'])->toBe(
            __($joined ? 'ui.joined_69318b0c6a' : 'ui.join_fd30fe681b'),
        );
    }
})->with(['en', 'lt', 'ru']);

test('direct catalogue card consumers stay composed from the shared card regions', function () {
    $contracts = [
        'group-card.blade.php' => true,
        'meetup-card.blade.php' => true,
        'neighbor-card.blade.php' => true,
        'pet-directory-card.blade.php' => false,
    ];

    foreach ($contracts as $view => $usesDescription) {
        $source = file_get_contents(resource_path('views/components/'.$view));

        expect($source)
            ->not->toBeFalse()
            ->toContain('<x-directory-card')
            ->toContain('<x-card-media')
            ->toContain('<x-card-heading')
            ->toContain('<x-slot:footer>');

        if ($usesDescription) {
            expect($source)->toContain('<x-card-description');
        }
    }
});

test('groups navigation is active in the catalog and linked from existing pages', function () {
    $groupsUrl = route('groups.index');

    $groupsResponse = $this->get($groupsUrl);
    $feedResponse = $this->get(route('preview.feed'));
    $petsResponse = $this->get(route('pets.index'));
    $profileResponse = $this->get(route('pets.scout'));
    $meetupsResponse = $this->get(route('meetups.index'));

    foreach ([$groupsResponse, $feedResponse, $petsResponse, $profileResponse, $meetupsResponse] as $response) {
        $response
            ->assertSuccessful()
            ->assertSee('href="'.$groupsUrl.'"', false)
            ->assertSee('data-nav-item="groups"', false);
    }

    $groupsXPath = responseXPath($groupsResponse);
    $feedXPath = responseXPath($feedResponse);
    $petsXPath = responseXPath($petsResponse);
    $meetupsXPath = responseXPath($meetupsResponse);

    expect($groupsXPath->query('//a[@data-nav-item="groups" and @aria-current="page"]')->length)->toBe(2)
        ->and($groupsXPath->query('//a[@data-nav-item="feed" and @aria-current]')->length)->toBe(0)
        ->and($groupsXPath->query('//a[@data-nav-item="pets" and @aria-current]')->length)->toBe(0)
        ->and($groupsXPath->query('//a[@data-nav-item="meetups" and @aria-current]')->length)->toBe(0)
        ->and($feedXPath->query('//a[@data-nav-item="feed" and @aria-current="page"]')->length)->toBe(2)
        ->and($petsXPath->query('//a[@data-nav-item="pets" and @aria-current="page"]')->length)->toBe(2)
        ->and($meetupsXPath->query('//a[@data-nav-item="meetups" and @aria-current="page"]')->length)->toBe(2);
});

test('the group card renders an explicit empty tags state', function () {
    $group = [
        'name' => 'Test Group',
        'category' => 'Local',
        'members' => '12 members',
        'activity' => '3 posts this week',
        'topic' => 'Friendly introductions',
        'description' => 'A test community for nearby pets and their people.',
        'organizer' => 'Test Organizer',
        'organizer_initials' => 'TO',
        'image' => 'https://example.test/group.jpg',
        'image_small' => 'https://example.test/group-small.jpg',
        'image_medium' => 'https://example.test/group-medium.jpg',
        'image_alt' => 'Test pets together',
        'tags' => [],
    ];

    $card = Blade::render(
        '<x-group-card :group="$group" />',
        ['group' => $group],
    );

    expect($card)->toContain('Open to new neighbors.');
});
