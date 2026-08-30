<?php

declare(strict_types=1);

use App\Models\Listing;
use App\Models\SearchCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('directory statistics are invalidated immediately and again after commit', function (): void {
    $afterCommit = [];
    DB::shouldReceive('transactionLevel')->twice()->andReturn(1);
    DB::shouldReceive('afterCommit')
        ->twice()
        ->andReturnUsing(static function (callable $callback) use (&$afterCommit): void {
            $afterCommit[] = $callback;
        });

    Cache::put(Listing::DIRECTORY_STATS_CACHE_KEY, ['stale' => true], 60);
    Cache::put(SearchCase::DIRECTORY_STATS_CACHE_KEY, ['stale' => true], 60);

    Listing::invalidateDirectoryStats();
    SearchCase::invalidateDirectoryStats();

    expect(Cache::get(Listing::DIRECTORY_STATS_CACHE_KEY))->toBeNull()
        ->and(Cache::get(SearchCase::DIRECTORY_STATS_CACHE_KEY))->toBeNull();

    Cache::put(Listing::DIRECTORY_STATS_CACHE_KEY, ['repopulated_before_commit' => true], 60);
    Cache::put(SearchCase::DIRECTORY_STATS_CACHE_KEY, ['repopulated_before_commit' => true], 60);

    foreach ($afterCommit as $callback) {
        $callback();
    }

    expect(Cache::get(Listing::DIRECTORY_STATS_CACHE_KEY))->toBeNull()
        ->and(Cache::get(SearchCase::DIRECTORY_STATS_CACHE_KEY))->toBeNull();
});
