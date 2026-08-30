<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\EmailVerificationMode;
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

    /** @var list<string> */
    private const array NON_PRODUCT_ROUTE_NAMES = [
        'login',
        'register',
        'password.request',
        'password.reset',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'onboarding.show',
        'logout',
        'locale.update',
        'default-livewire.update',
        'livewire.upload-file',
        'livewire.preview-file',
    ];

    public function __construct(
        private UnavailableAccountResponse $unavailableAccount,
        private EmailVerificationMode $emailVerification,
    ) {}

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

            $this->storeFirstProductDestination($request, $routeName);

            return redirect()->route('login');
        }

        if (! $user->isActive()) {
            return $this->unavailableAccount->redirect($request);
        }

        if (
            $this->emailVerification->isEnabled()
            && ! $user->hasVerifiedEmail()
            && ! $this->isUnverifiedRoute($routeName)
            && $routeName !== self::LIVEWIRE_UPDATE_ROUTE
        ) {
            if ($request->expectsJson() || ! $request->isMethodSafe()) {
                return response()->json([
                    'code' => 'email_verification_required',
                    'message' => __('auth.verification.required'),
                    'verification_url' => route('verification.notice'),
                ], Response::HTTP_CONFLICT);
            }

            $this->storeFirstProductDestination($request, $routeName);

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

    private function storeFirstProductDestination(Request $request, ?string $routeName): void
    {
        if (
            ! $request->isMethodSafe()
            || $request->expectsJson()
            || $request->session()->has('url.intended')
            || ! is_string($routeName)
            || in_array($routeName, self::NON_PRODUCT_ROUTE_NAMES, true)
        ) {
            return;
        }

        $request->session()->put('url.intended', $request->fullUrl());
    }
}
