<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceQuestionAnswer;
use App\Models\PlaceQuestionAnswerVersion;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceQuestionAnswerVersion> */
final class PlaceQuestionAnswerVersionFactory extends ApplicationFactory
{
    protected $model = PlaceQuestionAnswerVersion::class;

    public function definition(): array
    {
        return [
            'place_question_answer_id' => PlaceQuestionAnswer::factory(),
            'editor_user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'version' => 1,
            'body' => $this->faker->paragraph(),
            'reason' => null,
            'created_at' => now(),
        ];
    }
}
