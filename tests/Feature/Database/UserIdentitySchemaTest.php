<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\QueryException;

test('actor keys are required by the database', function () {
    expect(fn () => User::factory()->create(['actor_key' => null]))
        ->toThrow(QueryException::class);
});

test('actor keys are unique at the database boundary', function () {
    User::factory()->create(['actor_key' => 'unique-actor']);

    expect(fn () => User::factory()->create(['actor_key' => 'unique-actor']))
        ->toThrow(QueryException::class);
});
