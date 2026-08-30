<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Validation\Validator;
use Livewire\Form;

final class ForumEventInvitationForm extends Form
{
    private const LOCAL_DATE_TIME_FORMAT = 'Y-m-d\TH:i';

    public string $recipientEmail = '';

    public string $expiresAt = '';

    public string $idempotencyKey = '';

    /** @return array{recipient_email: string, expires_at: CarbonImmutable, idempotency_key: string} */
    public function data(string $timezone): array
    {
        $validated = $this->withValidator(function (Validator $validator) use ($timezone): void {
            $validator->after(function (Validator $validator) use ($timezone): void {
                if ($validator->errors()->has('expiresAt')) {
                    return;
                }

                $expiresAt = $this->parseLocalDateTime($this->expiresAt, $timezone);

                if ($expiresAt === null) {
                    $validator->errors()->add('expiresAt', __('validation.date_format', [
                        'attribute' => __('forum_events.fields.invitation_expires_at'),
                        'format' => self::LOCAL_DATE_TIME_FORMAT,
                    ]));

                    return;
                }

                if ($expiresAt->lessThanOrEqualTo(CarbonImmutable::now($timezone))) {
                    $validator->errors()->add('expiresAt', __('validation.after', [
                        'attribute' => __('forum_events.fields.invitation_expires_at'),
                        'date' => 'now',
                    ]));
                }
            });
        })->validate([
            'recipientEmail' => ['required', 'email:rfc', 'max:255'],
            'expiresAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);

        $expiresAt = $this->parseLocalDateTime((string) $validated['expiresAt'], $timezone);

        if ($expiresAt === null) {
            throw new \LogicException(__('messages.validated_invitation_expiry_could_not_be_parsed_a6b2d4c9ff'));
        }

        return [
            'recipient_email' => mb_strtolower(trim((string) $validated['recipientEmail'])),
            'expires_at' => $expiresAt->utc(),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'recipientEmail' => __('forum_events.fields.recipient_email'),
            'expiresAt' => __('forum_events.fields.invitation_expires_at'),
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
}
