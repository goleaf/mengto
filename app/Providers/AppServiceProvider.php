<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Http\Middleware\RequirePortalAccess;
use App\Models\ForumTopicType;
use App\Models\User;
use App\Observers\ForumTopicTypeObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->assertProductionMailTransportIsSafe();

        ForumTopicType::observe(ForumTopicTypeObserver::class);

        Livewire::addPersistentMiddleware(RequirePortalAccess::class);
        Livewire::addPersistentMiddleware(EnsureOnboardingIsComplete::class);

        Route::matched(static function (RouteMatched $event): void {
            if ($event->route->getName() === 'boost.browser-logs') {
                $event->route->middleware(['web', 'auth', 'active', 'verified']);
            }
        });

        Model::shouldBeStrict(! $this->app->isProduction());

        Password::defaults(
            static fn (): Password => Password::min(12)->mixedCase()->numbers(),
        );

        ResetPassword::createUrlUsing(
            static fn (User $user, string $token): string => route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]),
        );
    }

    private function assertProductionMailTransportIsSafe(): void
    {
        if (! $this->app->isProduction()) {
            return;
        }

        $mailers = config('mail.mailers');
        $pending = [(string) config('mail.default')];
        $visited = [];

        if (! is_array($mailers)) {
            throw new \LogicException('Production mail configuration is unavailable.');
        }

        while ($pending !== []) {
            $mailer = array_shift($pending);

            if (! is_string($mailer) || $mailer === '' || isset($visited[$mailer])) {
                continue;
            }

            $visited[$mailer] = true;
            $configuration = $mailers[$mailer] ?? null;
            $transport = is_array($configuration) ? ($configuration['transport'] ?? null) : null;

            if (! is_string($transport)) {
                throw new \LogicException("Production mailer [{$mailer}] is not configured.");
            }

            if (in_array($transport, ['array', 'log'], true)) {
                throw new \LogicException(
                    "Production authentication mail must not use the [{$transport}] transport.",
                );
            }

            if (in_array($transport, ['failover', 'roundrobin'], true)) {
                $configuredMailers = $configuration['mailers'] ?? [];

                if (! is_array($configuredMailers) || $configuredMailers === []) {
                    throw new \LogicException("Production mailer [{$mailer}] has no delivery transport.");
                }

                array_push($pending, ...$configuredMailers);
            }
        }
    }
}
