<?php

declare(strict_types=1);

use App\Actions\RegisterUser;
use App\Models\ForumEventRegistration;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use Database\Seeders\AdoptionDemoSeeder;
use Database\Seeders\CanonicalIdentityBrowserSeeder;
use Database\Seeders\CollaborativeGuideDemoSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SocialIdentitySeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\seed;

test('every explicitly named demo seeder retains a fail closed environment guard', function (): void {
    $seeders = glob(database_path('seeders/*DemoSeeder.php')) ?: [];

    expect($seeders)->not->toBeEmpty();

    foreach ($seeders as $seeder) {
        $source = file_get_contents($seeder);

        expect($source)
            ->not->toBeFalse()
            ->toContain("config('platform.demo_seed_environments')")
            ->toContain('app()->environment($allowedEnvironments)')
            ->toContain('throw new LogicException');
    }
});

test('adoption demo seeding refuses an environment not explicitly allowed', function (): void {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(AdoptionDemoSeeder::class))
        ->toThrow(LogicException::class);
});

test('collaborative guide demo seeding refuses an environment not explicitly allowed', function (): void {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(CollaborativeGuideDemoSeeder::class))
        ->toThrow(LogicException::class);
});

test('social identity demo seeding refuses an environment not explicitly allowed', function (): void {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(SocialIdentitySeeder::class))
        ->toThrow(LogicException::class);
});

test('canonical identity browser seeding creates one isolated zero pet testing account', function (): void {
    seed(CanonicalIdentityBrowserSeeder::class);
    seed(CanonicalIdentityBrowserSeeder::class);

    $user = User::query()->where('email', 'andrej-browser@example.test')->sole();

    expect($user->name)->toBe('Andrej Browser')
        ->and($user->actor_key)->toBe('andrej-browser')
        ->and($user->socialActor()->count())->toBe(1)
        ->and($user->socialActor()->firstOrFail()->actor_key)->toBe('00000000-0000-4000-8000-000000000001')
        ->and($user->socialActor()->firstOrFail()->settings()->count())->toBe(1)
        ->and($user->onboarding()->firstOrFail()->isComplete())->toBeTrue()
        ->and($user->petProfiles()->count())->toBe(0)
        ->and($user->managedPetProfiles()->count())->toBe(0)
        ->and($user->petProfileAccessRequests()->count())->toBe(0);
});

test('repeatable demo seeding never attaches demo pets or access to an existing registered account', function (): void {
    $registered = app(RegisterUser::class)->handle([
        'name' => 'Andrej Prus',
        'email' => 'andrej-before-seeding@example.test',
        'password' => 'fresh-verifier-password',
    ])->user;

    seed(DatabaseSeeder::class);
    seed(DatabaseSeeder::class);

    $demoUsers = User::query()
        ->where('actor_key', 'mia-carter')
        ->orWhere('actor_key', 'like', 'demo-%')
        ->with('onboarding')
        ->get();

    expect($registered->petProfiles()->count())->toBe(0)
        ->and($registered->managedPetProfiles()->count())->toBe(0)
        ->and($registered->petProfileAccessRequests()->count())->toBe(0)
        ->and(PetProfile::query()->whereBelongsTo($registered)->count())->toBe(0)
        ->and(PetProfileManager::query()->whereBelongsTo($registered)->count())->toBe(0)
        ->and(PetProfileAccessRequest::query()->whereBelongsTo($registered, 'requester')->count())->toBe(0)
        ->and($demoUsers)->not->toBeEmpty()
        ->and($demoUsers->every(
            static fn (User $user): bool => $user->onboarding?->isComplete() === true,
        ))->toBeTrue();
});

test('representative seeding never enriches a real administrator or event registration', function (): void {
    $registered = app(RegisterUser::class)->handle([
        'name' => 'Real Administrator',
        'email' => 'real-administrator-before-seeding@example.test',
        'password' => 'fresh-verifier-password',
    ])->user;
    $registered->forceFill(['is_admin' => true])->saveOrFail();
    $registration = ForumEventRegistration::factory()
        ->for($registered, 'user')
        ->create();

    seed(DatabaseSeeder::class);

    expect($registered->petProfiles()->count())->toBe(0)
        ->and(DB::table('forum_event_registration_pets')
            ->where('forum_event_registration_id', $registration->id)
            ->count())->toBe(0)
        ->and(DB::table('forum_mentorships')
            ->where('validated_by_user_id', $registered->id)
            ->count())->toBe(0)
        ->and(DB::table('forum_user_trust_levels')
            ->where('granted_by_user_id', $registered->id)
            ->count())->toBe(0)
        ->and(DB::table('forum_moderation_case_reports')
            ->where('linked_by_user_id', $registered->id)
            ->count())->toBe(0);
});
