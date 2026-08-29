<?php

declare(strict_types=1);

namespace App\Providers;

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
        ForumTopicType::observe(ForumTopicTypeObserver::class);

        Livewire::addPersistentMiddleware(RequirePortalAccess::class);

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
}
