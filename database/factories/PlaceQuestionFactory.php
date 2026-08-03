<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceQuestionStatus;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceQuestion> */
final class PlaceQuestionFactory extends ApplicationFactory
{
    protected $model = PlaceQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'author_user_id' => User::factory(),
            'stable_key' => 'place-question-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'body' => $this->faker->sentence(12),
            'status' => PlaceQuestionStatus::Open,
            'answered_at' => null,
        ];
    }
}
