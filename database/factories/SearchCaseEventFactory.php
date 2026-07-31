<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchCaseEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SearchCaseEvent> */
final class SearchCaseEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'actor_user_id' => User::factory(),
            'event_type' => 'status-changed',
            'previous_status' => 'active',
            'current_status' => 'possible-sighting',
            'reason_translation_key' => 'lost_found.events.status_changed',
            'idempotency_key' => (string) Str::uuid(),
            'metadata' => [],
        ];
    }
}
