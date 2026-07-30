<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $supportedLocales = config('platform.supported_locales');
        $candidate = $user instanceof User
            ? $user->locale
            : $request->session()->get('locale', config('app.locale'));

        if (! is_string($candidate) || ! in_array($candidate, $supportedLocales, true)) {
            $candidate = config('app.fallback_locale');
        }

        App::setLocale($candidate);

        return $next($request);
    }
}
