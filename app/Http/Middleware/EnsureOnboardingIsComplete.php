<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\OnboardingState;
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
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'logout',
        'default-livewire.update',
    ];

    /** @var list<string> */
    private const array PET_RELATIONSHIP_ROUTE_NAMES = [
        'pets.manage.create',
        'livewire.upload-file',
        'livewire.preview-file',
    ];

    public function __construct(private readonly OnboardingState $onboardingState) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $state = $user->onboarding()->first();

        if (! $state instanceof UserOnboarding || $this->onboardingState->isComplete($state)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if (
            is_string($routeName)
            && in_array($routeName, self::PET_RELATIONSHIP_ROUTE_NAMES, true)
            && $this->onboardingState->currentStep($state) === OnboardingStep::PetRelationship
        ) {
            return $next($request);
        }

        if (
            $request->hasHeader('X-Livewire')
            || (is_string($routeName) && str_ends_with($routeName, 'livewire.update'))
        ) {
            abort(Response::HTTP_CONFLICT, __('onboarding.middleware.incomplete_detail'));
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
            && is_string($routeName)
            && ! in_array($routeName, [
                ...self::ALLOWED_ROUTE_NAMES,
                ...self::PET_RELATIONSHIP_ROUTE_NAMES,
            ], true)
        ) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('onboarding.show');
    }
}
