<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\EmailVerificationMode;
use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as LaravelEnsureEmailIsVerified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EnsureEmailIsVerified extends LaravelEnsureEmailIsVerified
{
    public function __construct(private readonly EmailVerificationMode $emailVerification) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  string|null  $redirectToRoute
     * @return Response|RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (! $this->emailVerification->isEnabled()) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
