<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\CareJournal;
use App\Models\ExpertProfile;
use App\Models\Listing;
use App\Models\SearchCase;
use App\Models\Service;
use App\Models\SmartDevice;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

test('owner-backed factories keep foreign keys and identity snapshots coherent', function () {
    $expert = ExpertProfile::factory()->create();
    $journal = CareJournal::factory()->create();
    $listing = Listing::factory()->create();
    $searchCase = SearchCase::factory()->create();
    $device = SmartDevice::factory()->create();

    $initials = static fn (string $name): string => collect(preg_split('/\s+/', trim($name)) ?: [])
        ->filter()
        ->take(2)
        ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');

    foreach ([$expert, $journal, $listing, $searchCase, $device] as $ownedModel) {
        $owner = $ownedModel->owner()->firstOrFail();

        expect($ownedModel->owner_id)->toBe($owner->id)
            ->and($ownedModel->owner_key)->toBe($owner->actor_key);
    }

    $journalOwner = $journal->owner()->firstOrFail();
    $listingOwner = $listing->owner()->firstOrFail();
    $searchOwner = $searchCase->owner()->firstOrFail();

    expect($journal->current_caregiver_key)->toBe($journalOwner->actor_key)
        ->and($journal->current_caregiver_name)->toBe($journalOwner->name)
        ->and($listing->owner_name)->toBe($listingOwner->name)
        ->and($listing->owner_initials)->toBe($initials($listingOwner->name))
        ->and($searchCase->owner_name)->toBe($searchOwner->name)
        ->and($searchCase->owner_initials)->toBe($initials($searchOwner->name))
        ->and($searchCase->coordinator_key)->toBe($searchOwner->actor_key)
        ->and($searchCase->coordinator_name)->toBe($searchOwner->name)
        ->and($searchCase->active_key)->toBe($searchOwner->actor_key.':'.$searchCase->pet_profile_key)
        ->and($searchCase->contact_details)->toBe([
            'channel' => 'platform',
            'value' => $searchOwner->actor_key,
        ]);
});

test('search case factory creates unique active identities for a repeated owner', function () {
    $owner = User::factory()->create();
    $userCount = User::query()->count();
    $cases = SearchCase::factory()
        ->count(20)
        ->for($owner, 'owner')
        ->create();

    expect($cases->pluck('pet_profile_key')->unique())->toHaveCount(20)
        ->and($cases->pluck('active_key')->unique())->toHaveCount(20)
        ->and($cases->every(
            static fn (SearchCase $case): bool => str_starts_with(
                (string) $case->active_key,
                $owner->actor_key.':',
            ),
        ))->toBeTrue()
        ->and(User::query()->count())->toBe($userCount);
});

test('owner-backed factories preserve an explicit existing actor identity', function () {
    $owner = User::factory()->create();
    $userCount = User::query()->count();

    $models = [
        ExpertProfile::factory()->create(['owner_key' => $owner->actor_key]),
        CareJournal::factory()->create(['owner_key' => $owner->actor_key]),
        Listing::factory()->create(['owner_key' => $owner->actor_key]),
        SearchCase::factory()->create(['owner_key' => $owner->actor_key]),
        SmartDevice::factory()->create(['owner_key' => $owner->actor_key]),
    ];

    foreach ($models as $model) {
        expect($model->owner_id)->toBe($owner->id)
            ->and($model->owner_key)->toBe($owner->actor_key);
    }

    expect(User::query()->count())->toBe($userCount);

    $service = Service::factory()->create();
    $userCount = User::query()->count();
    $booking = Booking::factory()->create([
        'service_id' => $service->id,
        'client_key' => $owner->actor_key,
    ]);

    expect($booking->client_id)->toBe($owner->id)
        ->and($booking->client_key)->toBe($owner->actor_key)
        ->and(User::query()->count())->toBe($userCount);
});

test('factory media URLs are absolute first-party URLs backed by local fixtures', function () {
    $urls = [
        ExpertProfile::factory()->create()->avatar_url,
        Listing::factory()->create()->cover_url,
        SearchCase::factory()->create()->cover_url,
    ];
    $firstPartyHost = parse_url(asset('/'), PHP_URL_HOST);

    foreach ($urls as $url) {
        $path = parse_url($url, PHP_URL_PATH);

        expect(Validator::make(
            ['url' => $url],
            ['url' => ['required', 'url:http,https']],
        )->passes())->toBeTrue()
            ->and(parse_url($url, PHP_URL_HOST))->toBe($firstPartyHost)
            ->and($path)->toBeString()->toStartWith('/images/places/')
            ->and(is_file(public_path(ltrim($path, '/'))))->toBeTrue();
    }
});

test('domain seeders use committed first-party media instead of Unsplash', function () {
    $seederFiles = [
        'CareJournalSeeder.php',
        'ExpertSeeder.php',
        'ForumSeeder.php',
        'ListingSeeder.php',
        'MarketplaceExpansionSeeder.php',
        'MedicalRecordSeeder.php',
        'RepresentativeFieldCoverageSeeder.php',
        'SearchSeeder.php',
    ];

    foreach ($seederFiles as $seederFile) {
        $source = file_get_contents(database_path('seeders/'.$seederFile));

        expect($source)->not->toBeFalse()->not->toContain('images.unsplash.com');

        preg_match_all("/asset\\('([^']+)'\\)/", $source, $matches);

        expect($matches[1])->not->toBeEmpty();

        foreach ($matches[1] as $fixture) {
            expect($fixture)->toStartWith('images/places/')
                ->and(is_file(public_path($fixture)))->toBeTrue();
        }
    }
});
