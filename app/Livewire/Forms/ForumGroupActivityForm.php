<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumGroupActivityData;
use App\Enums\ForumGroupActivityFormat;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Form;

final class ForumGroupActivityForm extends Form
{
    private const LOCAL_DATE_TIME_FORMAT = 'Y-m-d\TH:i';

    public string $title = '';

    public string $summary = '';

    public string $format = 'physical';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public string $locationScope = '';

    public string $onlineUrl = '';

    public ?int $capacity = null;

    public string $participationNotes = '';

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'summary' => ['required', 'string', 'min:10', 'max:3000'],
            'format' => ['required', Rule::enum(ForumGroupActivityFormat::class)],
            'startsAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'endsAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'timezone' => ['required', 'timezone:all'],
            'locationScope' => ['nullable', 'string', 'max:160'],
            'onlineUrl' => [
                Rule::requiredIf(
                    in_array($this->format, [
                        ForumGroupActivityFormat::Online->value,
                        ForumGroupActivityFormat::Hybrid->value,
                    ], true),
                ),
                'nullable',
                'url:http,https',
                'max:2000',
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'participationNotes' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function toData(string $idempotencyKey): CreateForumGroupActivityData
    {
        $validated = $this->withValidator(function (Validator $validator): void {
            $validator->after(function (Validator $validator): void {
                if ($validator->errors()->hasAny(['startsAt', 'endsAt', 'timezone'])) {
                    return;
                }

                $startsAt = $this->parseLocalDateTime($this->startsAt, $this->timezone);
                $endsAt = $this->parseLocalDateTime($this->endsAt, $this->timezone);

                if ($startsAt === null || $endsAt === null) {
                    $this->addLocalDateTimeShapeErrors($validator, $startsAt, $endsAt);

                    return;
                }

                if ($startsAt->lessThanOrEqualTo(CarbonImmutable::now($this->timezone))) {
                    $validator->errors()->add('startsAt', __('validation.after', [
                        'attribute' => __('forum_polls.fields.activity_starts_at'),
                        'date' => 'now',
                    ]));
                }

                if ($endsAt->lessThanOrEqualTo($startsAt)) {
                    $validator->errors()->add('endsAt', __('validation.after', [
                        'attribute' => __('forum_polls.fields.activity_ends_at'),
                        'date' => __('forum_polls.fields.activity_starts_at'),
                    ]));
                }
            });
        })->validate();
        $timezone = (string) $validated['timezone'];
        $startsAt = $this->parseLocalDateTime((string) $validated['startsAt'], $timezone);
        $endsAt = $this->parseLocalDateTime((string) $validated['endsAt'], $timezone);

        if ($startsAt === null || $endsAt === null) {
            throw new \LogicException(__('messages.validated_group_activity_date_times_could_not_be_parsed'));
        }

        return new CreateForumGroupActivityData(
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            format: ForumGroupActivityFormat::from((string) $validated['format']),
            startsAt: $startsAt->utc(),
            endsAt: $endsAt->utc(),
            timezone: $timezone,
            locationScope: filled($validated['locationScope'] ?? null)
                ? trim((string) $validated['locationScope'])
                : null,
            onlineUrl: filled($validated['onlineUrl'] ?? null)
                ? trim((string) $validated['onlineUrl'])
                : null,
            capacity: isset($validated['capacity']) ? (int) $validated['capacity'] : null,
            participationNotes: filled($validated['participationNotes'] ?? null)
                ? trim((string) $validated['participationNotes'])
                : null,
            idempotencyKey: $idempotencyKey,
        );
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'startsAt' => __('forum_polls.fields.activity_starts_at'),
            'endsAt' => __('forum_polls.fields.activity_ends_at'),
            'timezone' => __('forum_polls.fields.activity_timezone'),
        ];
    }

    private function parseLocalDateTime(string $value, string $timezone): ?CarbonImmutable
    {
        try {
            $dateTime = CarbonImmutable::createFromFormat(
                '!'.self::LOCAL_DATE_TIME_FORMAT,
                $value,
                $timezone,
            );
        } catch (InvalidFormatException) {
            return null;
        }

        return $dateTime instanceof CarbonImmutable
            && $dateTime->format(self::LOCAL_DATE_TIME_FORMAT) === $value
            ? $dateTime
            : null;
    }

    private function addLocalDateTimeShapeErrors(
        Validator $validator,
        ?CarbonImmutable $startsAt,
        ?CarbonImmutable $endsAt,
    ): void {
        if ($startsAt === null) {
            $validator->errors()->add('startsAt', __('validation.date_format', [
                'attribute' => __('forum_polls.fields.activity_starts_at'),
                'format' => self::LOCAL_DATE_TIME_FORMAT,
            ]));
        }

        if ($endsAt === null) {
            $validator->errors()->add('endsAt', __('validation.date_format', [
                'attribute' => __('forum_polls.fields.activity_ends_at'),
                'format' => self::LOCAL_DATE_TIME_FORMAT,
            ]));
        }
    }
}
