<?php

declare(strict_types=1);

use App\Livewire\Forms\ForumEventForm;
use App\Livewire\Forms\ForumEventInvitationForm;
use App\Livewire\Forms\ForumEventRescheduleForm;
use App\Livewire\Forms\ForumGroupActivityForm;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumGroupActivity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Form;

final class ForumTimezoneValidationHarness extends Component {}

/** @return array{form: Form, result: callable(): mixed, field: string} */
function configuredForumTimezoneForm(
    string $formType,
    string $timezone,
    string $startsAt,
    string $endsAt,
): array {
    $component = new ForumTimezoneValidationHarness;

    return match ($formType) {
        'event' => (static function () use ($component, $timezone, $startsAt, $endsAt): array {
            $form = new ForumEventForm($component, 'form');
            $form->title = 'Local event';
            $form->summary = 'A sufficiently detailed local event summary.';
            $form->format = 'online';
            $form->onlineUrl = 'https://events.example.test/room';
            $form->startsAt = $startsAt;
            $form->endsAt = $endsAt;
            $form->timezone = $timezone;
            $form->animalWelfareRules = 'Follow the published animal welfare rules.';
            $form->emergencyContactPlan = 'Contact the event safety coordinator immediately.';
            $form->idempotencyKey = 'event-timezone-test-0001';

            return [
                'form' => $form,
                'result' => static fn (): mixed => $form->data(),
                'field' => 'startsAt',
            ];
        })(),
        'group activity' => (static function () use (
            $component,
            $timezone,
            $startsAt,
            $endsAt,
        ): array {
            $form = new ForumGroupActivityForm($component, 'form');
            $form->title = 'Local walk';
            $form->summary = 'A sufficiently detailed local group activity summary.';
            $form->startsAt = $startsAt;
            $form->endsAt = $endsAt;
            $form->timezone = $timezone;

            return [
                'form' => $form,
                'result' => static fn (): mixed => $form->toData('group-timezone-test-0001'),
                'field' => 'startsAt',
            ];
        })(),
        'reschedule' => (static function () use (
            $component,
            $timezone,
            $startsAt,
            $endsAt,
        ): array {
            $form = new ForumEventRescheduleForm($component, 'form');
            $form->startsAt = $startsAt;
            $form->endsAt = $endsAt;
            $form->timezone = $timezone;
            $form->explanation = 'The venue requested a later event schedule.';
            $form->idempotencyKey = 'reschedule-timezone-0001';

            return [
                'form' => $form,
                'result' => static fn (): mixed => $form->data(),
                'field' => 'startsAt',
            ];
        })(),
        'invitation' => (static function () use ($component, $timezone, $startsAt): array {
            $form = new ForumEventInvitationForm($component, 'form');
            $form->recipientEmail = 'invitee@example.test';
            $form->expiresAt = $startsAt;
            $form->idempotencyKey = 'invitation-timezone-0001';

            return [
                'form' => $form,
                'result' => static fn (): mixed => $form->data($timezone),
                'field' => 'expiresAt',
            ];
        })(),
        default => throw new InvalidArgumentException("Unknown forum form type [{$formType}]."),
    };
}

dataset('forum datetime forms', [
    'event creation' => ['event'],
    'group activity creation' => ['group activity'],
    'event rescheduling' => ['reschedule'],
    'event invitation' => ['invitation'],
]);

dataset('forum datetime range forms', [
    'event creation' => ['event'],
    'group activity creation' => ['group activity'],
    'event rescheduling' => ['reschedule'],
]);

test('forum datetime forms accept a Los Angeles local value whose instant is future', function (
    string $formType,
) {
    $this->travelTo(CarbonImmutable::parse('2026-01-15 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        $formType,
        'America/Los_Angeles',
        '2026-01-15T05:00',
        '2026-01-15T06:00',
    );

    $result = $configured['result']();
    $instant = match ($formType) {
        'event', 'group activity' => $result->startsAt,
        'reschedule' => $result['starts_at'],
        'invitation' => $result['expires_at'],
    };

    expect($instant->utc()->toIso8601String())->toBe('2026-01-15T13:00:00+00:00');
})->with('forum datetime forms');

test('forum datetime forms persist the submitted instant in UTC without wall-time drift', function (
    string $formType,
) {
    $this->travelTo(CarbonImmutable::parse('2026-01-15 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        $formType,
        'America/Los_Angeles',
        '2026-01-15T05:00',
        '2026-01-15T06:00',
    );
    $result = $configured['result']();

    [$table, $id, $column] = match ($formType) {
        'event' => (static function () use ($result): array {
            $event = ForumEvent::factory()->create([
                'starts_at' => $result->startsAt,
                'ends_at' => $result->endsAt,
                'timezone' => $result->timezone,
            ]);

            return [$event->getTable(), $event->getKey(), 'starts_at'];
        })(),
        'group activity' => (static function () use ($result): array {
            $activity = ForumGroupActivity::factory()->create([
                'starts_at' => $result->startsAt,
                'ends_at' => $result->endsAt,
                'timezone' => $result->timezone,
            ]);

            return [$activity->getTable(), $activity->getKey(), 'starts_at'];
        })(),
        'reschedule' => (static function () use ($result): array {
            $event = ForumEvent::factory()->create();
            $event->update([
                'starts_at' => $result['starts_at'],
                'ends_at' => $result['ends_at'],
                'timezone' => $result['timezone'],
            ]);

            return [$event->getTable(), $event->getKey(), 'starts_at'];
        })(),
        'invitation' => (static function () use ($result): array {
            $invitation = ForumEventInvitation::factory()->create([
                'expires_at' => $result['expires_at'],
            ]);

            return [$invitation->getTable(), $invitation->getKey(), 'expires_at'];
        })(),
    };

    expect(DB::table($table)->where('id', $id)->value($column))
        ->toBe('2026-01-15 13:00:00');
})->with('forum datetime forms');

test('forum datetime forms reject a Kiritimati local value whose instant is past', function (
    string $formType,
) {
    $this->travelTo(CarbonImmutable::parse('2026-01-15 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        $formType,
        'Pacific/Kiritimati',
        '2026-01-16T01:00',
        '2026-01-16T02:00',
    );

    try {
        $configured['result']();
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('form.'.$configured['field']);

        return;
    }

    $this->fail('The past local date-time was accepted.');
})->with('forum datetime forms');

test('forum datetime forms reject values outside the browser local datetime shape', function (
    string $formType,
) {
    $this->travelTo(CarbonImmutable::parse('2026-01-15 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        $formType,
        'America/Los_Angeles',
        '2026-01-15 05:00',
        '2026-01-15T06:00',
    );

    try {
        $configured['result']();
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('form.'.$configured['field']);

        return;
    }

    $this->fail('The malformed local date-time was accepted.');
})->with('forum datetime forms');

test('forum datetime forms reject a local time skipped by a daylight saving transition', function (
    string $formType,
) {
    $this->travelTo(CarbonImmutable::parse('2026-03-07 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        $formType,
        'America/Los_Angeles',
        '2026-03-08T02:30',
        '2026-03-08T04:00',
    );

    try {
        $configured['result']();
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('form.'.$configured['field']);

        return;
    }

    $this->fail('The nonexistent local date-time was accepted.');
})->with('forum datetime forms');

test('forum datetime ranges reject an end before the start after timezone parsing', function (
    string $formType,
) {
    $this->travelTo(CarbonImmutable::parse('2026-01-15 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        $formType,
        'America/Los_Angeles',
        '2026-01-15T06:00',
        '2026-01-15T05:30',
    );

    try {
        $configured['result']();
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('form.endsAt');

        return;
    }

    $this->fail('The reversed local date-time range was accepted.');
})->with('forum datetime range forms');

test('reschedule shape errors use the localized event field label', function () {
    $this->travelTo(CarbonImmutable::parse('2026-01-15 12:00:00 UTC'));
    $configured = configuredForumTimezoneForm(
        'reschedule',
        'America/Los_Angeles',
        '2026-01-15 05:00',
        '2026-01-15T06:00',
    );

    try {
        $configured['result']();
    } catch (ValidationException $exception) {
        expect($exception->errors()['form.startsAt'][0])->toBe(__('validation.date_format', [
            'attribute' => __('forum_events.fields.starts_at'),
            'format' => 'Y-m-d\TH:i',
        ]));

        return;
    }

    $this->fail('The malformed reschedule start was accepted.');
});
