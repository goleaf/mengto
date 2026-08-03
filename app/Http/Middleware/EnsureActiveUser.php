<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UnavailableAccountResponse;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureActiveUser
{
    public function __construct(private UnavailableAccountResponse $unavailableAccount) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->isActive()) {
            return $next($request);
        }

        return $this->unavailableAccount->redirect($request);
    }
}
