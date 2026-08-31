<?php

declare(strict_types=1);

use App\Actions\RegisterUser;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\SocialActorSetting;
use App\Models\UserOnboarding;
use App\Services\AuthenticatedUserPresenter;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
putenv('EMAIL_VERIFICATION_ENABLED=true');
$_ENV['EMAIL_VERIFICATION_ENABLED'] = 'true';
$_SERVER['EMAIL_VERIFICATION_ENABLED'] = 'true';

$reportUncaughtFailure = static function (Throwable $throwable): never {
    fwrite(STDERR, 'Fresh database verification failed: '.$throwable->getMessage().PHP_EOL);
    exit(1);
};
set_exception_handler($reportUncaughtFailure);

require dirname(__DIR__).'/vendor/autoload.php';

$application = require dirname(__DIR__).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();
set_exception_handler($reportUncaughtFailure);

$temporaryDirectory = realpath(sys_get_temp_dir());

if ($temporaryDirectory === false) {
    throw new RuntimeException('The system temporary directory is unavailable.');
}

$database = $temporaryDirectory.'/mengto-fresh-verification-'.bin2hex(random_bytes(8)).'.sqlite';

if (dirname($database) !== $temporaryDirectory) {
    throw new RuntimeException('Refusing destructive verification outside the system temporary directory.');
}

if (! touch($database)) {
    throw new RuntimeException('Unable to create the temporary verification database.');
}

config()->set('database.default', 'sqlite');
config()->set('database.connections.sqlite.url', null);
config()->set('database.connections.sqlite.database', $database);
DB::purge('sqlite');

if (config('database.connections.sqlite.database') !== $database) {
    throw new RuntimeException('Temporary database configuration assertion failed.');
}

try {
    $freshExit = Artisan::call('migrate:fresh', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);

    if ($freshExit !== 0) {
        throw new RuntimeException('Fresh migration failed: '.Artisan::output());
    }

    $tables = count(Schema::getTables());
    $migrations = DB::table('migrations')->count();

    $registration = app(RegisterUser::class)->handle([
        'name' => '  Andrej Prus  ',
        'email' => 'ANDREJ-FRESH@example.test ',
        'password' => 'fresh-verifier-password',
    ]);
    $registeredUser = $registration->user->refresh();
    Auth::login($registeredUser);

    if ($registeredUser->name !== 'Andrej Prus' || $registeredUser->email !== 'andrej-fresh@example.test') {
        throw new RuntimeException('Fresh registration did not preserve the normalized canonical identity.');
    }

    if (Auth::id() !== $registeredUser->id) {
        throw new RuntimeException('Fresh registration verifier did not authenticate the exact created user.');
    }

    $onboarding = UserOnboarding::query()->whereBelongsTo($registeredUser)->firstOrFail();
    $registeredUser->markEmailAsVerified();
    $completedAt = now();
    $onboarding->forceFill([
        'current_step' => OnboardingStep::Complete,
        'pet_relationship_choice' => OnboardingPetChoice::AddLater,
        'introduction_completed_at' => $completedAt,
        'preferences_completed_at' => $completedAt,
        'pet_relationship_completed_at' => $completedAt,
        'privacy_discovery_completed_at' => $completedAt,
        'completed_at' => $completedAt,
        'lock_version' => OnboardingStep::Complete->position(),
    ])->saveOrFail();

    $onboarding = $onboarding->refresh();

    if (! $onboarding->isComplete()) {
        throw new RuntimeException('Fresh registration verifier did not complete the canonical add-later onboarding journey.');
    }

    $actor = $registeredUser->socialActor()->firstOrFail();
    $presented = app(AuthenticatedUserPresenter::class)->present($registeredUser->refresh());

    if ($presented['name'] !== 'Andrej Prus' || $presented['profile_route_parameters']['socialActor'] !== $actor->actor_key) {
        throw new RuntimeException('Fresh authenticated shell identity did not resolve from the registered user actor.');
    }

    if (
        $registeredUser->socialActor()->count() !== 1
        || SocialActorSetting::query()->where('social_actor_id', $actor->id)->count() !== 1
        || UserOnboarding::query()->whereBelongsTo($registeredUser)->count() !== 1
        || PetProfile::query()->where('user_id', $registeredUser->id)->exists()
        || PetProfileManager::query()->where('user_id', $registeredUser->id)->exists()
        || PetProfileAccessRequest::query()->where('requester_user_id', $registeredUser->id)->exists()
    ) {
        throw new RuntimeException('Fresh registration domain invariants are incomplete.');
    }

    $httpKernel = app(HttpKernel::class);
    $portalRequest = Request::create(route('content.index', [], false), 'GET');
    $portalResponse = $httpKernel->handle($portalRequest);
    $httpKernel->terminate($portalRequest, $portalResponse);

    if ($portalResponse->getStatusCode() !== 200
        || ! str_contains((string) $portalResponse->getContent(), 'Andrej Prus')
        || str_contains((string) $portalResponse->getContent(), 'Mia Carter')) {
        throw new RuntimeException(sprintf(
            'Fresh unseeded portal did not render the exact authenticated identity (status %d, location %s).',
            $portalResponse->getStatusCode(),
            (string) $portalResponse->headers->get('Location', 'none'),
        ));
    }

    $profileRequest = Request::create(route('members.show', $actor, false), 'GET');
    $profileResponse = $httpKernel->handle($profileRequest);
    $httpKernel->terminate($profileRequest, $profileResponse);

    if ($profileResponse->getStatusCode() !== 200
        || ! str_contains((string) $profileResponse->getContent(), 'Andrej Prus')
        || str_contains((string) $profileResponse->getContent(), 'Mia Carter')) {
        throw new RuntimeException(sprintf(
            'Fresh unseeded self profile did not render the exact authenticated identity (status %d, location %s).',
            $profileResponse->getStatusCode(),
            (string) $profileResponse->headers->get('Location', 'none'),
        ));
    }

    $seedExit = Artisan::call('db:seed', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);

    if ($seedExit !== 0) {
        throw new RuntimeException('Fresh demo seeding failed: '.Artisan::output());
    }

    $usersBeforeRepeat = DB::table('users')->count();

    $repeatExit = Artisan::call('db:seed', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $usersAfterRepeat = DB::table('users')->count();

    if ($repeatExit !== 0) {
        throw new RuntimeException('Repeated seeding failed: '.Artisan::output());
    }

    if ($usersBeforeRepeat !== $usersAfterRepeat) {
        throw new RuntimeException('Repeated seeding changed the stable user count.');
    }

    if (DB::table('pet_profiles')->where('user_id', $registeredUser->id)->exists()) {
        throw new RuntimeException('Demo seeding attached a pet to the independently registered user.');
    }

    echo json_encode([
        'database' => $database,
        'tables' => $tables,
        'migrations' => $migrations,
        'users_before_repeat' => $usersBeforeRepeat,
        'users_after_repeat' => $usersAfterRepeat,
        'fresh_exit' => $freshExit,
        'first_seed_exit' => $seedExit,
        'repeat_seed_exit' => $repeatExit,
        'registered_user_id' => $registeredUser->id,
        'registered_user_name' => $registeredUser->name,
        'authenticated_user_id' => Auth::id(),
        'personal_actor_count' => $registeredUser->socialActor()->count(),
        'social_actor_setting_count' => SocialActorSetting::query()->where('social_actor_id', $actor->id)->count(),
        'onboarding_count' => UserOnboarding::query()->whereBelongsTo($registeredUser)->count(),
        'onboarding_complete' => $onboarding->isComplete(),
        'onboarding_pet_choice' => $onboarding->pet_relationship_choice->value,
        'registered_user_pet_count' => PetProfile::query()->where('user_id', $registeredUser->id)->count(),
        'registered_user_manager_count' => PetProfileManager::query()->where('user_id', $registeredUser->id)->count(),
        'registered_user_pet_access_request_count' => PetProfileAccessRequest::query()->where('requester_user_id', $registeredUser->id)->count(),
        'profile_actor_key' => $presented['profile_route_parameters']['socialActor'],
        'portal_http_status' => $portalResponse->getStatusCode(),
        'self_profile_http_status' => $profileResponse->getStatusCode(),
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
} finally {
    DB::disconnect('sqlite');

    if (is_file($database) && ! unlink($database)) {
        fwrite(STDERR, "Unable to remove temporary verification database: {$database}\n");
    }
}
