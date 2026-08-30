<?php

declare(strict_types=1);

use App\Enums\PlaceScheduleCoverage;
use App\Enums\PlaceScheduleExceptionKind;
use App\Enums\PlaceServiceAccessMode;
use App\Enums\PlaceServiceAvailability;
use App\Enums\PlaceSpeciesEligibility;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Models\Place;
use App\Models\PlaceOperatingSchedule;
use App\Models\PlaceScheduleException;
use App\Models\PlaceServiceDefinition;
use App\Models\PlaceServiceOffering;
use App\Models\PlaceServiceOfferingTaxon;
use App\Models\PlaceWeeklyOpeningInterval;
use App\Models\Taxon;
use Illuminate\Database\QueryException;

test('canonical emergency schedule and service facts expose typed relationships', function (): void {
    $place = Place::factory()->create(['type' => PlaceType::VeterinaryClinic]);
    $schedule = PlaceOperatingSchedule::factory()
        ->for($place)
        ->create([
            'coverage_status' => PlaceScheduleCoverage::Complete,
            'verification_status' => PlaceVerificationStatus::Verified,
        ]);
    $interval = PlaceWeeklyOpeningInterval::factory()
        ->for($schedule, 'schedule')
        ->overnight()
        ->appointmentOnly()
        ->create();
    $exception = PlaceScheduleException::factory()
        ->for($schedule, 'schedule')
        ->fullClosure()
        ->create();
    $definition = PlaceServiceDefinition::factory()->emergencyVeterinary()->create();
    $offering = PlaceServiceOffering::factory()
        ->for($place)
        ->for($definition, 'definition')
        ->create([
            'availability' => PlaceServiceAvailability::Available,
            'access_mode' => PlaceServiceAccessMode::WalkIn,
            'verification_status' => PlaceVerificationStatus::Verified,
        ]);
    $taxon = Taxon::factory()->create();
    $eligibility = PlaceServiceOfferingTaxon::factory()
        ->for($offering, 'offering')
        ->for($taxon)
        ->create(['eligibility' => PlaceSpeciesEligibility::Supported]);

    expect($place->refresh()->operatingSchedule->is($schedule))->toBeTrue()
        ->and($place->serviceOfferings->sole()->is($offering))->toBeTrue()
        ->and($schedule->coverage_status)->toBe(PlaceScheduleCoverage::Complete)
        ->and($schedule->verification_status)->toBe(PlaceVerificationStatus::Verified)
        ->and($schedule->weeklyIntervals->sole()->is($interval))->toBeTrue()
        ->and($schedule->exceptions->sole()->is($exception))->toBeTrue()
        ->and($interval->spans_next_day)->toBeTrue()
        ->and($interval->is_appointment_only)->toBeTrue()
        ->and($exception->kind)->toBe(PlaceScheduleExceptionKind::FullClosure)
        ->and($definition->is_emergency_capability)->toBeTrue()
        ->and($offering->availability)->toBe(PlaceServiceAvailability::Available)
        ->and($offering->access_mode)->toBe(PlaceServiceAccessMode::WalkIn)
        ->and($offering->taxonEligibilities->sole()->is($eligibility))->toBeTrue()
        ->and($eligibility->eligibility)->toBe(PlaceSpeciesEligibility::Supported);
});

test('canonical emergency fact uniqueness is protected by the database', function (
    string $constraint,
): void {
    $place = Place::factory()->create(['type' => PlaceType::VeterinaryClinic]);
    $schedule = PlaceOperatingSchedule::factory()->for($place)->create();

    expect(function () use ($constraint, $place, $schedule): void {
        match ($constraint) {
            'schedule' => PlaceOperatingSchedule::factory()->for($place)->create(),
            'weekly-interval' => (function () use ($schedule): void {
                $attributes = [
                    'place_operating_schedule_id' => $schedule->id,
                    'iso_weekday' => 1,
                    'starts_at_minute' => 540,
                    'ends_at_minute' => 1020,
                ];
                PlaceWeeklyOpeningInterval::factory()->create($attributes);
                PlaceWeeklyOpeningInterval::factory()->create($attributes);
            })(),
            'exception-date' => (function () use ($schedule): void {
                $attributes = [
                    'place_operating_schedule_id' => $schedule->id,
                    'local_date' => '2026-12-25',
                ];
                PlaceScheduleException::factory()->create($attributes);
                PlaceScheduleException::factory()->create($attributes);
            })(),
            'offering' => (function () use ($place): void {
                $definition = PlaceServiceDefinition::factory()->create();
                PlaceServiceOffering::factory()->for($place)->for($definition, 'definition')->create();
                PlaceServiceOffering::factory()->for($place)->for($definition, 'definition')->create();
            })(),
        };
    })->toThrow(QueryException::class);
})->with(['schedule', 'weekly-interval', 'exception-date', 'offering']);

test('canonical fact factories default to unknown rather than invented emergency truth', function (): void {
    $schedule = PlaceOperatingSchedule::factory()->create();
    $offering = PlaceServiceOffering::factory()->create();

    expect($schedule->coverage_status)->toBe(PlaceScheduleCoverage::Partial)
        ->and($schedule->verification_status)->toBe(PlaceVerificationStatus::NotAssessed)
        ->and($schedule->weeklyIntervals)->toBeEmpty()
        ->and($offering->availability)->toBe(PlaceServiceAvailability::Unknown)
        ->and($offering->access_mode)->toBe(PlaceServiceAccessMode::Unknown)
        ->and($offering->taxonEligibilities)->toBeEmpty();
});

