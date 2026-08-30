<?php

declare(strict_types=1);

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\AttachRequestContext;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\RequirePortalAccess;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AddSecurityHeaders::class);
        $middleware->web(append: [
            SetLocale::class,
            AttachRequestContext::class,
            RequirePortalAccess::class,
        ]);
        $middleware->appendToPriorityList(
            StartSession::class,
            RequirePortalAccess::class,
        );
        $middleware->alias([
            'active' => EnsureActiveUser::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (
            Response $response,
            Throwable $_exception,
            Request $request,
        ): Response {
            $requestId = $request->attributes->get(AttachRequestContext::REQUEST_ATTRIBUTE);

            if (is_string($requestId)) {
                $response->headers->set('X-Request-ID', $requestId);
            }

            return $response;
        });
    })->create();
