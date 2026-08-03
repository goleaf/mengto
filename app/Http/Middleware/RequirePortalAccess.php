<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UnavailableAccountResponse;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequirePortalAccess
{
    /**
     * Routes required to enter or recover an account.
     *
     * @var list<string>
     */
    private const array GUEST_ROUTE_NAMES = [
        'login',
        'register',
        'password.request',
        'password.reset',
        'locale.update',
    ];

    /**
     * Routes an authenticated account may use before email verification.
     *
     * @var list<string>
     */
    private const array UNVERIFIED_ROUTE_NAMES = [
        'verification.notice',
        'verification.verify',
        'password.confirm',
        'logout',
    ];

    private const string LIVEWIRE_UPDATE_ROUTE = 'default-livewire.update';

    public function __construct(private UnavailableAccountResponse $unavailableAccount) {}

    /**
     * @param  Closure(Request): Response  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $routeName = $request->route()?->getName();
        $user = $request->user();

        if (! $user instanceof User) {
            if ($this->isGuestRoute($routeName) || $routeName === self::LIVEWIRE_UPDATE_ROUTE) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                throw new AuthenticationException(
                    message: __('auth.unauthenticated'),
                    guards: ['web'],
                    redirectTo: route('login'),
                );
            }

            return redirect()->guest(route('login'));
        }

        if (! $user->isActive()) {
            return $this->unavailableAccount->redirect($request);
        }

        if (
            ! $user->hasVerifiedEmail()
            && ! $this->isUnverifiedRoute($routeName)
            && $routeName !== self::LIVEWIRE_UPDATE_ROUTE
        ) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }

    private function isGuestRoute(?string $routeName): bool
    {
        return is_string($routeName) && in_array($routeName, self::GUEST_ROUTE_NAMES, true);
    }

    private function isUnverifiedRoute(?string $routeName): bool
    {
        return is_string($routeName) && in_array($routeName, self::UNVERIFIED_ROUTE_NAMES, true);
    }
}
