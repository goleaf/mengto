<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Validation\Validator;
use Livewire\Form;

final class ForumEventRescheduleForm extends Form
{
    private const LOCAL_DATE_TIME_FORMAT = 'Y-m-d\TH:i';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public string $explanation = '';

    public string $idempotencyKey = '';

    /** @return array{starts_at: CarbonImmutable, ends_at: CarbonImmutable, timezone: string, explanation: string, idempotency_key: string} */
    public function data(): array
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
                        'attribute' => __('forum_events.fields.starts_at'),
                        'date' => 'now',
                    ]));
                }

                if ($endsAt->lessThanOrEqualTo($startsAt)) {
                    $validator->errors()->add('endsAt', __('validation.after', [
                        'attribute' => __('forum_events.fields.ends_at'),
                        'date' => __('forum_events.fields.starts_at'),
                    ]));
                }
            });
        })->validate([
            'startsAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'endsAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'timezone' => ['required', 'timezone:all'],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);
        $timezone = (string) $validated['timezone'];
        $startsAt = $this->parseLocalDateTime((string) $validated['startsAt'], $timezone);
        $endsAt = $this->parseLocalDateTime((string) $validated['endsAt'], $timezone);

        if ($startsAt === null || $endsAt === null) {
            throw new \LogicException(__('messages.validated_reschedule_date_times_could_not_be_parsed'));
        }

        return [
            'starts_at' => $startsAt->utc(),
            'ends_at' => $endsAt->utc(),
            'timezone' => $timezone,
            'explanation' => trim((string) $validated['explanation']),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'startsAt' => __('forum_events.fields.starts_at'),
            'endsAt' => __('forum_events.fields.ends_at'),
            'timezone' => __('forum_events.fields.timezone'),
            'explanation' => __('forum_events.fields.reschedule_reason'),
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
                'attribute' => __('forum_events.fields.starts_at'),
                'format' => self::LOCAL_DATE_TIME_FORMAT,
            ]));
        }

        if ($endsAt === null) {
            $validator->errors()->add('endsAt', __('validation.date_format', [
                'attribute' => __('forum_events.fields.ends_at'),
                'format' => self::LOCAL_DATE_TIME_FORMAT,
            ]));
        }
    }
}
