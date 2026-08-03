<?php

declare(strict_types=1);

use App\Models\Listing;
use Illuminate\Support\Facades\Blade;

test('shared card action rows expose a wrapping fill contract', function () {
    $html = Blade::render(<<<'BLADE'
        <x-card-action-row fill aria-label="Card actions">
            <x-action-control label="Save" icon="bookmark" href="/save" />
            <x-action-control label="View details" icon="arrow-right" href="/details" variant="primary" />
        </x-card-action-row>
    BLADE);

    $xpath = sharedCardActionXPath($html);
    $row = $xpath->query('//*[@data-card-action-row]')->item(0);

    expect($row)->not->toBeNull()
        ->and(' '.$row?->attributes?->getNamedItem('class')?->nodeValue.' ')
        ->toContain(' card-action-row ')
        ->toContain(' card-action-row--fill ')
        ->and($row?->attributes?->getNamedItem('aria-label')?->nodeValue)->toBe('Card actions')
        ->and($xpath->query('//*[@data-card-action-row]/a')->length)->toBe(2);
});

test('group and marketplace cards share action rows while keeping domain shells', function () {
    Listing::factory()->create([
        'title' => 'Reflective walking harness',
        'cover_url' => '/images/places/pet-store-primary-lg.jpg',
    ]);

    $groups = $this->get(route('groups.index'));
    $marketplace = $this->get(route('marketplace.index'));

    $groups->assertSuccessful();
    $marketplace->assertSuccessful();

    $groupXPath = responseXPath($groups);
    $marketplaceXPath = responseXPath($marketplace);
    $listingCard = $marketplaceXPath->query(
        '//article[contains(concat(" ", normalize-space(@class), " "), " market-card ")][.//*[@data-card-heading and normalize-space()="Reflective walking harness"]]',
    )->item(0);
    $description = $marketplaceXPath->query('.//p[@data-card-description]', $listingCard)->item(0);

    expect($groupXPath->query('//article[@data-group-card]//*[@data-card-action-row]')->length)->toBe(6)
        ->and($listingCard)->not->toBeNull()
        ->and($marketplaceXPath->query('.//h3[@data-card-heading]', $listingCard)->length)->toBe(1)
        ->and($marketplaceXPath->query('.//h2', $listingCard)->length)->toBe(0)
        ->and($description)->not->toBeNull()
        ->and(' '.$description?->attributes?->getNamedItem('class')?->nodeValue.' ')->toContain(' mt-0 ')
        ->and($marketplaceXPath->query('.//*[@data-card-action-row]', $listingCard)->length)->toBe(1);

    $mediaLink = $marketplaceXPath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " market-card__media ")]', $listingCard)->item(0);
    $titleLink = $marketplaceXPath->query('.//*[@data-card-heading]/a', $listingCard)->item(0);

    expect($mediaLink)->not->toBeNull()
        ->and($titleLink)->not->toBeNull()
        ->and($mediaLink?->attributes?->getNamedItem('href')?->nodeValue)
        ->toBe($titleLink?->attributes?->getNamedItem('href')?->nodeValue);
});

test('shared card consumers contain no duplicate action or marketplace copy rules', function () {
    $group = file_get_contents(resource_path('views/components/group-card.blade.php'));
    $listing = file_get_contents(resource_path('views/components/listing-card.blade.php'));
    $groupStyles = file_get_contents(resource_path('scss/_groups.scss'));
    $marketplaceStyles = file_get_contents(resource_path('scss/_marketplace.scss'));

    expect($group)
        ->not->toBeFalse()
        ->toContain('<x-card-action-row')
        ->and($listing)
        ->not->toBeFalse()
        ->toContain('<x-card-heading')
        ->toContain('<x-card-description')
        ->toContain('<x-card-action-row')
        ->not->toContain('<h2 class="mt-1 text-lg')
        ->not->toContain('market-card__excerpt')
        ->and($groupStyles)
        ->not->toBeFalse()
        ->not->toContain('&__actions')
        ->and($marketplaceStyles)
        ->not->toBeFalse()
        ->not->toContain('&__excerpt');
});

function sharedCardActionXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}
