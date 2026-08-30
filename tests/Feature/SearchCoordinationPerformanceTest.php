<?php

declare(strict_types=1);

use App\Models\SearchCase;
use App\Models\Sighting;
use App\Services\SearchPresenter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

test('private search coordination keeps expanded activity bounded', function (): void {
    fake()->seed(830);
    $this->travelTo('2026-08-30 10:00:00');
    $owner = $this->authenticatedUser;
    $searchCase = SearchCase::factory()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'coordinator_key' => $owner->actor_key,
    ]);
    Sighting::factory()
        ->count(125)
        ->confirmed()
        ->for($searchCase)
        ->create();
    $queryCount = 0;
    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        $queryCount++;
    });
    memory_reset_peak_usage();
    $memoryBefore = memory_get_usage(true);
    $startedAt = hrtime(true);

    $data = app(SearchPresenter::class)->coordination($searchCase);

    $elapsedMs = round((hrtime(true) - $startedAt) / 1_000_000, 2);
    $memoryDelta = max(0, memory_get_peak_usage(true) - $memoryBefore);
    $responseBytes = strlen(json_encode($data, JSON_THROW_ON_ERROR));

    if (getenv('PERFORMANCE_REPORT') === '1') {
        fwrite(STDERR, json_encode([
            'path' => 'lost-found.coordination',
            'fixture_sightings' => 125,
            'queries' => $queryCount,
            'response_bytes' => $responseBytes,
            'peak_memory_delta_bytes' => $memoryDelta,
            'elapsed_ms' => $elapsedMs,
        ], JSON_THROW_ON_ERROR).PHP_EOL);
    }

    expect($data['sightings'])->toHaveCount(100)
        ->and($queryCount)->toBeLessThanOrEqual(16)
        ->and($responseBytes)->toBeLessThanOrEqual(524_288);
});
