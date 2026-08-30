<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceQuestionStatus;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceQuestionEvent> */
final class PlaceQuestionEventFactory extends ApplicationFactory
{
    protected $model = PlaceQuestionEvent::class;

    public function definition(): array
    {
        return [
            'place_question_id' => PlaceQuestion::factory(),
            'actor_user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'event_type' => 'submitted',
            'from_status' => null,
            'to_status' => PlaceQuestionStatus::Open->value,
            'public_summary_key' => 'places.questions.events.submitted',
            'private_note' => null,
            'created_at' => now(),
        ];
    }
}
