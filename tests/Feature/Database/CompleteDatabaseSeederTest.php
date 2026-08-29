<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

/** @return array<class-string<Model>, string> */
function persistentApplicationModels(): array
{
    $models = [];

    foreach (glob(app_path('Models/*.php')) ?: [] as $path) {
        $class = 'App\\Models\\'.pathinfo($path, PATHINFO_FILENAME);

        if (is_subclass_of($class, Model::class)) {
            $models[$class] = (new $class)->getTable();
        }
    }

    ksort($models);

    return $models;
}

test('root seeding creates the complete bounded representative domain and is repeatable', function () {
    Storage::fake('local');

    seed(DatabaseSeeder::class);

    $target = User::query()->where('email', 'user@example.com')->firstOrFail();
    $modelCounts = [];

    foreach (persistentApplicationModels() as $modelClass => $table) {
        $modelCounts[$modelClass] = $modelClass::query()->count();
    }

    $underfilled = array_filter($modelCounts, static fn (int $count): bool => $count < 10);
    $pivotTables = [
        'community_animal_group_taxon',
        'content_publication_media',
        'forum_category_relations',
        'forum_event_registration_pets',
        'forum_event_taxon',
        'forum_group_taxon',
        'forum_moderation_case_reports',
        'forum_topic_taxon',
    ];
    $pivotCounts = collect($pivotTables)
        ->mapWithKeys(static fn (string $table): array => [$table => DB::table($table)->count()])
        ->all();

    expect($underfilled)->toBe([])
        ->and(array_filter($pivotCounts, static fn (int $count): bool => $count < 10))->toBe([])
        ->and(User::query()->count())->toBe(10)
        ->and($target->actor_key)->toBe('mia-carter')
        ->and($target->email_verified_at)->not->toBeNull()
        ->and($target->last_login_at)->not->toBeNull()
        ->and($target->password)->not->toBe('password')
        ->and(Hash::check('password', $target->password))->toBeTrue()
        ->and($target->petProfiles()->exists())->toBeTrue()
        ->and($target->socialActor()->exists())->toBeTrue();

    seed(DatabaseSeeder::class);

    foreach ($modelCounts as $modelClass => $count) {
        expect($modelClass::query()->count(), "{$modelClass} changed after repeated seeding")
            ->toBe($count);
    }

    foreach ($pivotCounts as $table => $count) {
        expect(DB::table($table)->count(), "{$table} changed after repeated seeding")
            ->toBe($count);
    }
});
