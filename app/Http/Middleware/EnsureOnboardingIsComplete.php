<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserOnboarding;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureOnboardingIsComplete
{
    /** @var list<string> */
    private const array ALLOWED_ROUTE_NAMES = [
        'onboarding.show',
        'pets.manage.create',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'logout',
        'default-livewire.update',
        'livewire.upload-file',
        'livewire.preview-file',
    ];

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $state = $user->onboarding()->first();

        if (! $state instanceof UserOnboarding || $state->isComplete()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'code' => 'onboarding_required',
                'message' => __('onboarding.middleware.incomplete_detail'),
                'onboarding_url' => route('onboarding.show'),
            ], Response::HTTP_CONFLICT);
        }

        if (! $request->isMethodSafe()) {
            return response()->json([
                'code' => 'onboarding_required',
                'message' => __('onboarding.middleware.incomplete_detail'),
                'onboarding_url' => route('onboarding.show'),
            ], Response::HTTP_CONFLICT);
        }

        if (
            ! $request->session()->has('url.intended')
            && (! is_string($routeName) || ! str_starts_with($routeName, 'pets.manage.'))
        ) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('onboarding.show');
    }
}
