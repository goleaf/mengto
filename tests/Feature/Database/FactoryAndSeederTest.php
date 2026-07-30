<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\Listing;
use App\Models\PetProfile;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\SmartDevice;
use App\Models\User;
use Database\Factories\ApplicationFactory;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PerformanceSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\seed;

dataset('model factories', static function (): array {
    $models = [];
    $modelDirectory = dirname(__DIR__, 3).'/app/Models';

    foreach (glob($modelDirectory.'/*.php') ?: [] as $path) {
        $class = 'App\\Models\\'.pathinfo($path, PATHINFO_FILENAME);

        if (is_subclass_of($class, Model::class)) {
            $models[pathinfo($path, PATHINFO_FILENAME)] = [$class];
        }
    }

    return $models;
});

dataset('factory states', [
    'booking completed' => [Booking::class, 'completed'],
    'expert unverified' => [ExpertProfile::class, 'unverified'],
    'forum answer expert' => [ForumAnswer::class, 'expert'],
    'forum topic draft' => [ForumTopic::class, 'draft'],
    'forum topic medical' => [ForumTopic::class, 'medical'],
    'forum topic resolved' => [ForumTopic::class, 'resolved'],
    'listing adoption' => [Listing::class, 'adoption'],
    'listing draft' => [Listing::class, 'draft'],
    'listing rental' => [Listing::class, 'rental'],
    'listing shelter need' => [Listing::class, 'shelterNeed'],
    'search case found' => [SearchCase::class, 'found'],
    'search case returned' => [SearchCase::class, 'returned'],
    'sighting confirmed' => [Sighting::class, 'confirmed'],
    'user unverified' => [User::class, 'unverified'],
    'user blocked' => [User::class, 'blocked'],
    'user suspended' => [User::class, 'suspended'],
    'user administrator' => [User::class, 'administrator'],
    'user Lithuanian' => [User::class, 'lithuanian'],
    'user Russian' => [User::class, 'russian'],
]);

dataset('enum factory states', static function (): array {
    $states = [];
    $modelDirectory = dirname(__DIR__, 3).'/app/Models';

    foreach (glob($modelDirectory.'/*.php') ?: [] as $path) {
        $modelClass = 'App\\Models\\'.pathinfo($path, PATHINFO_FILENAME);

        if (! is_subclass_of($modelClass, Model::class)) {
            continue;
        }

        $model = new $modelClass;

        foreach ($model->getCasts() as $attribute => $cast) {
            if (! is_string($cast) || ! enum_exists($cast)) {
                continue;
            }

            foreach ((new ReflectionEnum($cast))->getCases() as $case) {
                $value = $case->getValue();
                $states[
                    pathinfo($path, PATHINFO_FILENAME)." {$attribute} {$case->getName()}"
                ] = [$modelClass, $attribute, $value];
            }
        }
    }

    return $states;
});

test('every first party model factory creates a persisted valid record', function (string $modelClass) {
    $model = $modelClass::factory()->create();

    expect($model)
        ->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeTrue()
        ->and($model->getKey())->not->toBeNull();
})->with('model factories');

test('every documented factory state creates a persisted valid record', function (
    string $modelClass,
    string $state,
) {
    $model = $modelClass::factory()->{$state}()->create();

    expect($model)
        ->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeTrue();
})->with('factory states');

test('every enum-backed factory state creates a persisted valid record', function (
    string $modelClass,
    string $attribute,
    BackedEnum $value,
) {
    $factory = $modelClass::factory();

    if (! $factory instanceof ApplicationFactory) {
        throw new LogicException("{$modelClass} must use ApplicationFactory.");
    }

    $model = $factory->withEnum($attribute, $value)->create();

    expect($model)
        ->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeTrue()
        ->and($model->getAttribute($attribute))->toBe($value);
})->with('enum factory states');

test('database seeding is repeatable without changing stable entity counts', function () {
    seed(DatabaseSeeder::class);

    $firstCounts = [
        'users' => User::query()->count(),
        'topics' => ForumTopic::query()->count(),
        'listings' => Listing::query()->count(),
        'devices' => SmartDevice::query()->count(),
    ];

    seed(DatabaseSeeder::class);

    expect([
        'users' => User::query()->count(),
        'topics' => ForumTopic::query()->count(),
        'listings' => Listing::query()->count(),
        'devices' => SmartDevice::query()->count(),
    ])->toBe($firstCounts)
        ->and(User::query()->where('actor_key', 'mia-carter')->count())->toBe(1)
        ->and(User::query()->where('email', 'mia@example.test')->count())->toBe(1)
        ->and(User::query()->where('is_admin', true)->count())->toBe(1)
        ->and(User::query()->where('locale', 'lt')->count())->toBe(1)
        ->and(User::query()->where('status', 'blocked')->count())->toBe(1)
        ->and(User::query()->whereNull('email_verified_at')->count())->toBe(1);
});

test('demo seeding refuses an environment not explicitly allowed', function () {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(DatabaseSeeder::class))
        ->toThrow(LogicException::class);
});

test('performance seeding is opt in deterministic and repeatable', function () {
    seed(PerformanceSeeder::class);
    seed(PerformanceSeeder::class);

    expect(PetProfile::query()
        ->where('profile_key', 'like', 'performance-pet-%')
        ->count())->toBe(250)
        ->and(User::query()->where('actor_key', 'performance-owner')->count())->toBe(1);
});

test('performance seeding refuses an environment not explicitly allowed', function () {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(PerformanceSeeder::class))
        ->toThrow(LogicException::class);
});
