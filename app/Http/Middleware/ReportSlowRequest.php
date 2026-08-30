<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\MonotonicClock;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final readonly class ReportSlowRequest
{
    public function __construct(
        private LogManager $logger,
        private RateLimiter $limiter,
        private MonotonicClock $clock,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = $this->clock->nowNanoseconds();

        try {
            $response = $next($request);
        } catch (Throwable $throwable) {
            $this->report(
                $request,
                new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR),
                $startedAt,
                null,
                'failed',
            );

            throw $throwable;
        }

        if ($response instanceof StreamedResponse && $response->getCallback() !== null) {
            $callback = $response->getCallback();
            $response->setCallback(function () use ($callback, $request, $response, $startedAt): void {
                try {
                    $callback();
                } catch (Throwable $throwable) {
                    $this->report($request, $response, $startedAt, null, 'failed', true);

                    throw $throwable;
                }

                $this->report($request, $response, $startedAt, null, 'completed');
            });

            return $response;
        }

        $content = $response->getContent();
        $this->report(
            $request,
            $response,
            $startedAt,
            is_string($content) ? strlen($content) : null,
            'completed',
        );

        return $response;
    }

    private function report(
        Request $request,
        Response $response,
        int $startedAt,
        ?int $responseBytes,
        string $outcome,
        bool $streamed = false,
    ): void {
        $thresholdMs = $this->thresholdMs();

        if ($thresholdMs === null) {
            return;
        }

        $durationMs = (int) round(
            ($this->clock->nowNanoseconds() - $startedAt) / 1_000_000,
        );

        if ($durationMs < $thresholdMs) {
            return;
        }

        $requestId = $request->attributes->get(AttachRequestContext::REQUEST_ATTRIBUTE);
        $route = $request->route();
        $routeName = $route instanceof Route && is_string($route->getName())
            ? $route->getName()
            : 'unnamed';
        $owner = config('platform.observability.slow_requests.owner');

        if (! is_string($requestId) || ! is_string($owner) || $owner === '') {
            return;
        }

        try {
            $limit = config('platform.observability.slow_requests.max_per_minute', 60);

            if (! is_int($limit) || $limit < 1) {
                return;
            }

            $limitKey = 'slow-request:'.hash(
                'sha256',
                $routeName.'|'.$response->getStatusCode(),
            );

            if ($this->limiter->hit($limitKey, 60) > $limit) {
                return;
            }

            $message = $outcome === 'failed' && $streamed
                ? 'Slow streamed request failed.'
                : ($outcome === 'failed' ? 'Slow request failed.' : 'Slow request completed.');
            $this->logger->warning($message, [
                'request_id' => $requestId,
                'request_method' => $request->getMethod(),
                'route_name' => $routeName,
                'response_status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'response_bytes' => $responseBytes,
                'owner' => $owner,
                'outcome' => $outcome,
            ]);
        } catch (Throwable) {
            // Observability failure must not replace the application response.
        }
    }

    private function thresholdMs(): ?int
    {
        if (config('platform.observability.slow_requests.enabled') !== true) {
            return null;
        }

        $threshold = config('platform.observability.slow_requests.threshold_ms');

        if (
            ! is_int($threshold)
            && (! is_string($threshold) || ! ctype_digit($threshold))
        ) {
            return null;
        }

        $threshold = (int) $threshold;

        return $threshold > 0 ? $threshold : null;
    }
}
