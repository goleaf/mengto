<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

test('group directory preserves localized long copy and every membership action state', function (
    string $locale,
    string $searchLabel,
) {
    $this->authenticatedUser->forceFill(['locale' => $locale])->save();

    $response = $this->get(route('groups.index'));

    $response
        ->assertSuccessful()
        ->assertSee('lang="'.$locale.'"', false)
        ->assertSee($searchLabel);

    expect($response->getContent())
        ->not->toMatch('/\b(?:messages|presentation|ui)\.[a-z0-9_]+/');

    $xpath = responseXPath($response);
    $descriptions = $xpath->query('//article[@data-group-card]//*[@data-card-description]');
    $descriptionLengths = [];

    foreach ($descriptions as $description) {
        $descriptionLengths[] = Str::length(trim((string) $description->textContent));
    }

    expect($xpath->query('//article[@data-group-card]')->length)->toBe(6)
        ->and($descriptions->length)->toBe(6)
        ->and(max($descriptionLengths))->toBeGreaterThanOrEqual(100)
        ->and($xpath->query('//article[@data-group-card]//form[input[@name="action" and @value="leave-group"]]/button[@aria-pressed="true"]')->length)->toBe(2)
        ->and($xpath->query('//article[@data-group-card]//form[input[@name="action" and @value="cancel-group-request"]]/button[@aria-pressed="false"]')->length)->toBe(1)
        ->and($xpath->query('//article[@data-group-card]//form[input[@name="action" and @value="join-group"]]/button[@aria-pressed="false"]')->length)->toBe(3)
        ->and($xpath->query('//article[@data-group-card]//form[input[@name="action" and @value="dismiss-group-recommendation"]]/button[@aria-label]')->length)->toBe(6);
})->with([
    'English' => ['en', 'Search'],
    'Lithuanian' => ['lt', 'Ieškoti'],
    'Russian' => ['ru', 'Найти'],
]);

test('shared card primitives normalize unsupported presentation input and escape copy', function () {
    $title = '<script>alert("title")</script>';
    $copy = '<img src=x onerror=alert("copy")>';

    $heading = Blade::render(
        '<x-card-heading :title="$title" :level="7" spacing="unsupported" />',
        ['title' => $title],
    );
    $description = Blade::render(
        '<x-card-description>{{ $copy }}</x-card-description>',
        ['copy' => $copy],
    );
    $media = Blade::render(
        '<x-card-media src="/images/card.jpg" alt="Card image" sizes="100vw" ratio="unsupported" />',
    );

    expect($heading)
        ->toContain('<h3')
        ->toContain('&lt;script&gt;alert(&quot;title&quot;)&lt;/script&gt;')
        ->not->toContain('<script>')
        ->and($description)
        ->toContain('&lt;img src=x onerror=alert(&quot;copy&quot;)&gt;')
        ->not->toContain('<img src=x')
        ->and($media)
        ->toContain('aspect-[3/2]');
});

test('compatible public card families compose shared typography leaves without adopting the directory shell', function () {
    $discovery = file_get_contents(resource_path('views/components/discovery-result-card.blade.php'));
    $expert = file_get_contents(resource_path('views/components/expert-card.blade.php'));

    expect($discovery)
        ->not->toBeFalse()
        ->toContain('<x-card-heading')
        ->toContain('<x-card-description')
        ->not->toContain('<h3 class="discovery-result-card__title">')
        ->not->toContain('<p class="discovery-result-card__description">')
        ->not->toContain('<x-directory-card')
        ->and($expert)
        ->not->toBeFalse()
        ->toContain('<x-card-heading')
        ->not->toContain('<h2 class="text-lg font-bold leading-tight">')
        ->not->toContain('<x-directory-card');
});
