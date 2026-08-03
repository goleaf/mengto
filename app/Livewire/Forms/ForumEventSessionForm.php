<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\SaveForumEventSessionData;
use App\Enums\ForumEventSessionReservationPolicy;
use App\Enums\ForumEventSessionRole;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventSessionType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventSessionForm extends Form
{
    public ?int $occurrenceId = null;

    public ?int $trackId = null;

    public ?int $roomId = null;

    public string $title = '';

    public string $summary = '';

    public string $type = 'session';

    public string $status = 'scheduled';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public ?int $capacity = null;

    public string $reservationPolicy = 'optional';

    public bool $isRequired = false;

    public int $position = 0;

    public ?int $staffUserId = null;

    public string $staffRole = 'speaker';

    public bool $staffIsPublic = true;

    public string $conflictOverrideReason = '';

    public string $idempotencyKey = '';

    public function data(): SaveForumEventSessionData
    {
        $validated = $this->validate([
            'occurrenceId' => ['required', 'integer'],
            'trackId' => ['nullable', 'integer'],
            'roomId' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(ForumEventSessionType::class)],
            'status' => ['required', Rule::enum(ForumEventSessionStatus::class)],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['required', 'date', 'after:startsAt'],
            'timezone' => ['required', 'timezone:all'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'reservationPolicy' => [
                'required',
                Rule::enum(ForumEventSessionReservationPolicy::class),
            ],
            'isRequired' => ['boolean'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'staffUserId' => ['nullable', 'integer'],
            'staffRole' => ['required_with:staffUserId', Rule::enum(ForumEventSessionRole::class)],
            'staffIsPublic' => ['boolean'],
            'conflictOverrideReason' => ['nullable', 'string', 'min:20', 'max:2000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);
        $timezone = (string) $validated['timezone'];
        $staffUserId = isset($validated['staffUserId'])
            ? (int) $validated['staffUserId']
            : null;

        return new SaveForumEventSessionData(
            occurrenceId: (int) $validated['occurrenceId'],
            trackId: isset($validated['trackId']) ? (int) $validated['trackId'] : null,
            roomId: isset($validated['roomId']) ? (int) $validated['roomId'] : null,
            title: trim((string) $validated['title']),
            summary: $this->optionalString($validated, 'summary'),
            type: ForumEventSessionType::from((string) $validated['type']),
            status: ForumEventSessionStatus::from((string) $validated['status']),
            startsAt: CarbonImmutable::parse((string) $validated['startsAt'], $timezone),
            endsAt: CarbonImmutable::parse((string) $validated['endsAt'], $timezone),
            timezone: $timezone,
            capacity: isset($validated['capacity']) ? (int) $validated['capacity'] : null,
            reservationPolicy: ForumEventSessionReservationPolicy::from(
                (string) $validated['reservationPolicy'],
            ),
            isRequired: (bool) $validated['isRequired'],
            position: (int) $validated['position'],
            staff: $staffUserId === null ? [] : [[
                'user_id' => $staffUserId,
                'role' => ForumEventSessionRole::from((string) $validated['staffRole']),
                'is_public' => (bool) $validated['staffIsPublic'],
            ]],
            conflictOverrideReason: $this->optionalString($validated, 'conflictOverrideReason'),
            idempotencyKey: (string) $validated['idempotencyKey'],
        );
    }

    /** @param array<string, mixed> $validated */
    private function optionalString(array $validated, string $key): ?string
    {
        $value = trim((string) ($validated[$key] ?? ''));

        return $value === '' ? null : $value;
    }
}
