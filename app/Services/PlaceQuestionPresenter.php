<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

final readonly class PlaceQuestionPresenter
{
    public function __construct(private LocaleFormatter $formatter) {}

    /**
     * @return array<int, array{
     *     key: string,
     *     question: string,
     *     author: string,
     *     answer: string|null,
     *     answer_author: string|null,
     *     answered_at: string|null,
     *     answerable: bool,
     *     answer_idempotency_key: string
     * }>
     */
    public function forPlace(string $placeKey): array
    {
        return PlaceQuestion::query()
            ->select([
                'id',
                'place_id',
                'author_user_id',
                'stable_key',
                'body',
                'status',
                'created_at',
            ])
            ->visible()
            ->whereHas('place', static function (Builder $places) use ($placeKey): void {
                $places->where('stable_key', $placeKey);
            })
            ->with([
                'author' => static function (Relation $authors): void {
                    $authors->select(['id', 'name']);
                },
                'answer' => static function (Relation $answers): void {
                    $answers->select([
                        'id',
                        'place_question_id',
                        'author_user_id',
                        'body',
                        'answered_at',
                    ]);
                },
                'answer.author' => static function (Relation $authors): void {
                    $authors->select(['id', 'name']);
                },
            ])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn (PlaceQuestion $question): array => $this->present($question))
            ->all();
    }

    /**
     * @return array{
     *     key: string,
     *     question: string,
     *     author: string,
     *     answer: string|null,
     *     answer_author: string|null,
     *     answered_at: string|null,
     *     answerable: bool,
     *     answer_idempotency_key: string
     * }
     */
    private function present(PlaceQuestion $question): array
    {
        $answer = $question->answer;

        return [
            'key' => $question->stable_key,
            'question' => $question->body,
            'author' => $question->author->name,
            'answer' => $answer?->body,
            'answer_author' => $answer instanceof PlaceQuestionAnswer
                ? __('places.questions.official_answer_by', [
                    'name' => $answer->author->name,
                ])
                : null,
            'answered_at' => $this->formatter->dateTime($answer?->answered_at),
            'answerable' => $answer === null,
            'answer_idempotency_key' => (string) Str::uuid(),
        ];
    }
}
