<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnrichRequestContext
{
    public function __construct(private Repository $context) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->attributes->get(AttachRequestContext::REQUEST_ATTRIBUTE);
        $route = $request->route();
        $user = $request->user();
        $context = [];

        if (is_string($requestId)) {
            $context['request_id'] = $requestId;
        }

        if ($route instanceof Route && is_string($route->getName())) {
            $context['route_name'] = $route->getName();
        }

        if ($user instanceof User) {
            $context['user_id'] = $user->getKey();
            $context['actor_key'] = $user->actor_key;
        }

        $this->context->add($context);

        return $next($request);
    }
}
