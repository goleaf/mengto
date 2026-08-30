<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceQuestionAnswer> */
final class PlaceQuestionAnswerFactory extends ApplicationFactory
{
    protected $model = PlaceQuestionAnswer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'place_question_id' => PlaceQuestion::factory(),
            'author_user_id' => User::factory(),
            'stable_key' => 'place-answer-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'body' => $this->faker->paragraph(),
            'current_version' => 1,
            'correction_reason' => null,
            'answered_at' => now(),
        ];
    }
}
