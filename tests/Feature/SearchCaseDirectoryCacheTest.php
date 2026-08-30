<?php

declare(strict_types=1);

use App\Models\Listing;
use App\Models\SearchCase;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::forget(Listing::DIRECTORY_STATS_CACHE_KEY);
    Cache::forget(SearchCase::DIRECTORY_STATS_CACHE_KEY);
});

test('search case directory statistics exclude activity belonging to private cases', function (): void {
    $publicCase = SearchCase::factory()->create(['visibility' => 'public']);
    $privateCase = SearchCase::factory()->create(['visibility' => 'team']);

    Sighting::factory()->confirmed()->for($publicCase)->create();
    Sighting::factory()->confirmed()->for($privateCase)->create();
    SearchVolunteer::factory()->for($publicCase)->create();
    SearchVolunteer::factory()->for($privateCase)->create();

    expect(SearchCase::directoryStats())
        ->sightings->toBe(1)
        ->volunteers->toBe(1);
});

test('public sighting and volunteer mutations invalidate warmed directory statistics', function (): void {
    $searchCase = SearchCase::factory()->create(['visibility' => 'public']);

    expect(SearchCase::directoryStats())
        ->sightings->toBe(0)
        ->volunteers->toBe(0);

    Sighting::factory()->confirmed()->for($searchCase)->create();
    SearchVolunteer::factory()->for($searchCase)->create();

    expect(SearchCase::directoryStats())
        ->sightings->toBe(1)
        ->volunteers->toBe(1);
});

test('public listing statistics fail open to the source query when cache is unavailable', function (): void {
    Listing::factory()->create();
    Cache::shouldReceive('get')
        ->once()
        ->andThrow(new RuntimeException('cache unavailable'));

    expect(Listing::directoryStats())
        ->available->toBe(1)
        ->cities->toBe(1);
});

test('public listing statistics have a zero query warm path and invalidate on mutation', function (): void {
    Listing::factory()->create();
    $listingQueries = 0;
    DB::listen(static function (QueryExecuted $query) use (&$listingQueries): void {
        if (
            str_starts_with(strtolower(ltrim($query->sql)), 'select')
            && str_contains($query->sql, 'listings')
        ) {
            $listingQueries++;
        }
    });

    expect(Listing::directoryStats())->available->toBe(1)
        ->and($listingQueries)->toBe(6)
        ->and(Listing::directoryStats())->available->toBe(1)
        ->and($listingQueries)->toBe(6);

    Listing::factory()->create();

    expect(Listing::directoryStats())->available->toBe(2)
        ->and($listingQueries)->toBe(12);
});
