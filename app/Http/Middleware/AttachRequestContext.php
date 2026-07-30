<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final readonly class AttachRequestContext
{
    public const REQUEST_ATTRIBUTE = 'request_id';

    public function __construct(private Repository $context) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = Str::uuid()->toString();
        $request->attributes->set(self::REQUEST_ATTRIBUTE, $requestId);

        $route = $request->route();
        $user = $request->user();
        $context = [
            'request_id' => $requestId,
            'request_method' => $request->getMethod(),
        ];

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
