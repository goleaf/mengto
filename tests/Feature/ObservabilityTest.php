<?php

declare(strict_types=1);

use App\Http\Middleware\AttachRequestContext;
use Illuminate\Log\Context\Repository;
use Illuminate\Support\Str;

test('application responses expose a server generated request identifier', function () {
    $suppliedRequestId = 'untrusted-client-value';
    auth()->logout();

    $response = $this
        ->withHeader('X-Request-ID', $suppliedRequestId)
        ->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Request-ID');

    $requestId = $response->headers->get('X-Request-ID');

    expect($requestId)
        ->toBeString()
        ->not->toBe($suppliedRequestId)
        ->and(Str::isUuid($requestId))
        ->toBeTrue();
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
        ->toBeGreaterThan(0);

    $environmentExample = file_get_contents(base_path('.env.example'));

    expect($environmentExample)
        ->toBeString()
        ->toContain('LOG_STACK=daily')
        ->toContain('LOG_DAILY_DAYS=14');
});
