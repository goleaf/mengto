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
        $requestId = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        if (! is_string($requestId) || ! Str::isUuid($requestId)) {
            $requestId = Str::uuid()->toString();
            $request->attributes->set(self::REQUEST_ATTRIBUTE, $requestId);
        }

        $context = $this->safeContext($request, $requestId);
        $this->context->add($context);
        $response = $next($request);
        $this->context->add($this->safeContext($request, $requestId));
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /** @return array<string, int|string> */
    private function safeContext(Request $request, string $requestId): array
    {
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

        return $context;
    }
}
