<?php

declare(strict_types=1);

use App\Models\ForumReport;
use App\Models\Place;
use App\Models\PlaceCompatibilityBackfill;
use App\Models\PlaceCorrection;
use App\Models\PlaceQuestion;
use App\Models\PlaceReview;
use App\Models\PlaceWarning;
use App\Models\User;
use App\Models\UserDomainState;
use App\Services\PlaceCompatibilityBackfillService;
use Database\Seeders\ForumModerationDefinitionSeeder;

test('places state compatibility backfill is repeatable conservative and non destructive', function (): void {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $user = User::factory()->create();
    $place = Place::factory()->public()->create(['stable_key' => 'legacy-community-place']);
    $payload = [
        'visited' => [
            $place->stable_key => ['pet' => 'scout', 'visited_at' => '2026-08-01T10:00:00+00:00'],
        ],
        'corrections' => [
            $place->stable_key => [[
                'id' => 'legacy-correction-1',
                'field' => 'pet-rules',
                'current_value' => 'Leashes required.',
                'proposed_value' => 'Leashes required near both gates.',
                'evidence' => 'A dated public notice was observed at the entrance.',
                'created_at' => '2026-08-01T10:10:00+00:00',
            ]],
        ],
        'warnings' => [
            $place->stable_key => [[
                'key' => 'legacy-warning-1',
                'title' => 'Damaged west gate',
                'category' => 'damaged-fence',
                'detail' => 'Use the east gate until the damaged latch is repaired.',
                'zone' => 'West gate',
                'evidence' => 'Dated photograph reference.',
                'expires_at' => '2026-09-01T10:00:00+00:00',
            ]],
        ],
        'reviews' => [
            $place->stable_key => [[
                'key' => 'legacy-review-1',
                'rating' => 4,
                'criterion' => 'safety',
                'body' => 'The marked route was clear during the recorded visit.',
                'anonymous' => true,
                'created_at' => '2026-08-01T10:20:00+00:00',
            ]],
        ],
        'questions' => [
            $place->stable_key => [[
                'key' => 'legacy-question-1',
                'body' => 'Is the repaired west gate now available after 18:00?',
                'created_at' => '2026-08-01T10:30:00+00:00',
            ]],
        ],
        'reports' => [[
            'id' => 'legacy-report-1',
            'place' => $place->stable_key,
            'category' => 'dangerous-location-information',
            'body' => 'The access warning may no longer match current conditions.',
            'evidence' => 'Public maintenance notice reference.',
            'created_at' => '2026-08-01T10:40:00+00:00',
        ]],
    ];
    $legacy = UserDomainState::factory()->for($user)->create([
        'namespace' => 'places.state.v1',
        'version' => 1,
        'payload' => $payload,
    ]);

    $first = app(PlaceCompatibilityBackfillService::class)->handle();
    $second = app(PlaceCompatibilityBackfillService::class)->handle();

    dump($first, $second, PlaceCompatibilityBackfill::query()->get(['contribution_type', 'status', 'error_code'])->toArray());

    expect($first['imported'])->toBe(5)
        ->and($second['imported'])->toBe(0)
        ->and($second['already_processed'])->toBe(5)
        ->and(PlaceCorrection::query()->count())->toBe(1)
        ->and(PlaceWarning::query()->count())->toBe(1)
        ->and(PlaceReview::query()->count())->toBe(1)
        ->and(PlaceQuestion::query()->count())->toBe(1)
        ->and(ForumReport::query()->count())->toBe(1)
        ->and(PlaceCompatibilityBackfill::query()->count())->toBe(5)
        ->and($legacy->fresh()->payload)->toBe($payload);

    expect(PlaceCorrection::query()->sole()->moderation_status->value)->toBe('pending')
        ->and(PlaceWarning::query()->sole()->status->value)->toBe('needs_review')
        ->and(PlaceReview::query()->sole()->moderation_status->value)->toBe('pending')
        ->and(PlaceQuestion::query()->sole()->moderation_status)->toBe('pending')
        ->and(ForumReport::query()->sole()->status)->toBe('received');
});

test('places compatibility backfill command supports a mutation free dry run', function (): void {
    $legacy = UserDomainState::factory()->create([
        'namespace' => 'places.state.v1',
        'payload' => ['reviews' => ['missing-place' => [['key' => 'review-1']]]],
    ]);

    $this->artisan('places:backfill-contributions', ['--dry-run' => true])
        ->expectsOutputToContain('scanned')
        ->assertSuccessful();

    expect(PlaceCompatibilityBackfill::query()->count())->toBe(0)
        ->and($legacy->fresh()->payload)->toBe(['reviews' => ['missing-place' => [['key' => 'review-1']]]]);
});
