<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchContactRelay;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SearchContactRelay> */
final class SearchContactRelayFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory()->for(User::factory(), 'owner'),
            'sender_user_id' => User::factory(),
            'recipient_user_id' => static fn (array $attributes): int => (int) SearchCase::query()
                ->whereKey((int) $attributes['search_case_id'])
                ->valueOrFail('owner_id'),
            'idempotency_key' => (string) Str::uuid(),
            'purpose' => 'sighting',
            'message' => 'I have additional private context that may help the search coordinator.',
            'status' => 'submitted',
            'read_at' => null,
        ];
    }
}
