<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AddSecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self), geolocation=(self), microphone=(self)',
        );

        $requestId = $request->attributes->get(AttachRequestContext::REQUEST_ATTRIBUTE);

        if (is_string($requestId)) {
            $response->headers->set('X-Request-ID', $requestId);
        }

        return $response;
    }
}
