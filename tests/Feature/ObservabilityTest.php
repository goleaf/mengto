<?php

declare(strict_types=1);

use App\Http\Middleware\AttachRequestContext;
use App\Http\Middleware\ReportSlowRequest;
use App\Support\MonotonicClock;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Log\Context\Repository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

test('application responses expose a server generated request identifier', function () {
    $suppliedRequestId = 'untrusted-client-value';
    auth()->logout();

    $response = $this
        ->withHeader('X-Request-ID', $suppliedRequestId)
        ->get(route('login'))
        ->assertOk()
        ->assertHeader('X-Request-ID');

    $requestId = $response->headers->get('X-Request-ID');

    expect($requestId)
        ->toBeString()
        ->not->toBe($suppliedRequestId)
        ->and(Str::isUuid($requestId))
        ->toBeTrue();
});

test('correlation identifiers cover redirects health binding failures and server errors', function (): void {
    Route::get('/_tests/observability-failure', static function (): never {
        throw new RuntimeException('Expected observability test failure.');
    })->name('tests.observability-failure');

    $responses = [
        $this->get('/up')->assertOk(),
        $this->get('/devices/not-a-real-device')->assertNotFound(),
        $this->get('/_tests/observability-failure')->assertServerError(),
    ];

    auth()->logout();
    $responses[] = $this->get(route('devices.index'))->assertRedirect(route('login'));

    $requestIds = collect($responses)->map(
        fn ($response): ?string => $response->headers->get('X-Request-ID'),
    );

    expect($requestIds)->toHaveCount(4)
        ->and($requestIds->unique())->toHaveCount(4);

    foreach ($requestIds as $requestId) {
        expect($requestId)
            ->toBeString()
            ->and(Str::isUuid($requestId))
            ->toBeTrue();
    }
});

test('request logging context contains only safe routing and actor identifiers', function () {
    $this->get(route('devices.index'))->assertOk();

    $context = app(Repository::class)->all();

    expect($context)
        ->toMatchArray([
            'request_method' => 'GET',
            'route_name' => 'devices.index',
            'user_id' => $this->authenticatedUser->getKey(),
            'actor_key' => $this->authenticatedUser->actor_key,
        ])
        ->toHaveKey('request_id')
        ->not->toHaveKeys([
            'password',
            'remember_token',
            'session_id',
            'authorization',
            'request_body',
        ])
        ->and(Str::isUuid($context[AttachRequestContext::REQUEST_ATTRIBUTE]))
        ->toBeTrue();
});

test('route and actor context is available before application logging', function (): void {
    Route::get('/_tests/request-context', static fn (Repository $context) => $context->all())
        ->middleware('web')
        ->name('tests.request-context');

    $this->get('/_tests/request-context')
        ->assertOk()
        ->assertJsonPath('route_name', 'tests.request-context')
        ->assertJsonPath('user_id', $this->authenticatedUser->getKey())
        ->assertJsonPath('actor_key', $this->authenticatedUser->actor_key);
});

test('observability owners and retention are explicit and bounded', function () {
    $signals = config('platform.observability');

    expect($signals)->toBeArray()->not->toBeEmpty();

    foreach ($signals as $signal => $policy) {
        expect($policy, $signal)
            ->toHaveKeys(['owner', 'retention_days'])
            ->and($policy['owner'], $signal)
            ->toBeString()
            ->not->toBeEmpty()
            ->and($policy['retention_days'], $signal)
            ->toBeInt()
            ->toBeGreaterThan(0);
    }

    expect(config('logging.channels.daily.days'))
        ->toBe(14)
        ->and(config('platform.observability.slow_requests.enabled'))->toBeBool()
        ->and(config('platform.observability.slow_requests.threshold_ms'))->toBeInt()
        ->toBeGreaterThan(0)
        ->and(config('platform.observability.slow_requests.max_per_minute'))->toBe(60);

    $environmentExample = file_get_contents(base_path('.env.example'));

    expect($environmentExample)
        ->toBeString()
        ->toContain('LOG_STACK=daily')
        ->toContain('LOG_DAILY_DAYS=14');
});

test('slow requests emit bounded secret safe correlation context', function (): void {
    Route::get('/_tests/slow-request', static fn (): string => 'measured')
        ->name('tests.slow-request');
    $clock = $this->mock(MonotonicClock::class);
    $clock->shouldReceive('nowNanoseconds')->twice()->andReturn(0, 5_000_000);
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', 1);
    Log::spy();

    $response = $this
        ->withHeader('Authorization', 'Bearer must-not-appear')
        ->get('/_tests/slow-request')
        ->assertOk();

    $requestId = $response->headers->get('X-Request-ID');

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function (string $message, array $context) use ($requestId): bool {
            return $message === 'Slow request completed.'
                && $context['request_id'] === $requestId
                && $context['request_method'] === 'GET'
                && $context['route_name'] === 'tests.slow-request'
                && $context['response_status'] === 200
                && is_int($context['duration_ms'])
                && $context['duration_ms'] >= 0
                && is_int($context['response_bytes'])
                && $context['response_bytes'] > 0
                && $context['owner'] === 'platform-operations'
                && $context['outcome'] === 'completed'
                && array_keys($context) === [
                    'request_id',
                    'request_method',
                    'route_name',
                    'response_status',
                    'duration_ms',
                    'response_bytes',
                    'owner',
                    'outcome',
                ]
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'must-not-appear');
        });
});

test('normal requests do not emit slow request noise', function (): void {
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', 60_000);
    Log::spy();

    $this->get(route('devices.index'))->assertOk();

    Log::shouldNotHaveReceived('warning');
});

test('invalid slow request thresholds disable logging instead of logging every request', function (): void {
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', -1);
    Log::spy();

    $this->get(route('devices.index'))->assertOk();

    Log::shouldNotHaveReceived('warning');
});

test('streamed request timing includes the stream callback without buffering its body', function (): void {
    Route::get('/_tests/slow-stream', static fn () => response()->stream(
        static function (): void {
            echo str_repeat('x', 8_192);
        },
    ))->name('tests.slow-stream');
    $clock = $this->mock(MonotonicClock::class);
    $clock->shouldReceive('nowNanoseconds')->twice()->andReturn(0, 5_000_000);
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', 1);
    Log::spy();

    $response = $this->get('/_tests/slow-stream')->assertOk();

    expect($response->streamedContent())->toHaveLength(8_192);
    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Slow request completed.'
            && $context['route_name'] === 'tests.slow-stream'
            && $context['duration_ms'] >= 5
            && $context['response_bytes'] === null
            && $context['outcome'] === 'completed'
            && count($context) === 8);
});

test('slow stream failures propagate and are never logged as completed', function (): void {
    Route::get('/_tests/failing-stream', static fn () => response()->stream(
        static function (): never {
            echo 'partial';

            throw new RuntimeException('Expected streamed failure.');
        },
    ))->name('tests.failing-stream');
    $clock = $this->mock(MonotonicClock::class);
    $clock->shouldReceive('nowNanoseconds')->twice()->andReturn(0, 5_000_000);
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', 1);
    Log::spy();
    $response = $this->get('/_tests/failing-stream')->assertOk();
    $outputLevel = ob_get_level();
    $failure = null;

    try {
        ob_start();
        $response->baseResponse->sendContent();
    } catch (RuntimeException $exception) {
        $failure = $exception;
    } finally {
        while (ob_get_level() > $outputLevel) {
            ob_end_clean();
        }
    }

    expect($failure)
        ->toBeInstanceOf(RuntimeException::class)
        ->and($failure?->getMessage())->toBe('Expected streamed failure.');
    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Slow streamed request failed.'
            && $context['route_name'] === 'tests.failing-stream'
            && $context['outcome'] === 'failed');
});

test('slow ordinary failures propagate and are recorded with a failed outcome', function (): void {
    $clock = $this->mock(MonotonicClock::class);
    $clock->shouldReceive('nowNanoseconds')->twice()->andReturn(0, 5_000_000);
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', 1);
    Log::spy();
    $request = HttpRequest::create('/_tests/failing-slow-request', 'GET');
    $request->attributes->set(AttachRequestContext::REQUEST_ATTRIBUTE, (string) Str::uuid());
    $route = new Illuminate\Routing\Route(
        ['GET'],
        '/_tests/failing-slow-request',
        static fn (): string => 'unused',
    );
    $route->name('tests.failing-slow-request');
    $request->setRouteResolver(static fn (): Illuminate\Routing\Route => $route);

    expect(fn () => app(ReportSlowRequest::class)->handle(
        $request,
        static function (): never {
            throw new RuntimeException('Expected ordinary request failure.');
        },
    ))->toThrow(RuntimeException::class, 'Expected ordinary request failure.');

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Slow request failed.'
            && $context['route_name'] === 'tests.failing-slow-request'
            && $context['response_status'] === 500
            && $context['response_bytes'] === null
            && $context['outcome'] === 'failed');
});

test('slow request logging enforces the atomic per route and status volume budget', function (): void {
    Route::get('/_tests/rate-limited-slow-request', static fn (): string => 'measured')
        ->name('tests.rate-limited-slow-request');
    $clock = $this->mock(MonotonicClock::class);
    $nanoseconds = 0;
    $clock->shouldReceive('nowNanoseconds')
        ->andReturnUsing(static function () use (&$nanoseconds): int {
            $nanoseconds += 2_000_000;

            return $nanoseconds;
        });
    config()->set('platform.observability.slow_requests.enabled', true);
    config()->set('platform.observability.slow_requests.threshold_ms', 1);
    config()->set('platform.observability.slow_requests.max_per_minute', 60);
    Log::spy();

    foreach (range(1, 61) as $_request) {
        $this->get('/_tests/rate-limited-slow-request')->assertOk();
    }

    Log::shouldHaveReceived('warning')->times(60);
});
