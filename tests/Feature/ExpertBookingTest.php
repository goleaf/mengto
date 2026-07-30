<?php

use App\Enums\BookingStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\DocumentGrant;
use App\Models\ExpertProfile;
use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('booking form renders structured pet choices and compatible openings', function () {
    $expert = ExpertProfile::factory()->create([
        'public_name' => 'Dr. Emilia Vaitke',
        'slug' => 'dr-emilia-vaitke',
        'species' => ['bird', 'parrot'],
    ]);
    $service = Service::factory()->create([
        'expert_profile_id' => $expert->id,
        'name' => 'Avian clinic visit',
        'format' => 'in-person',
    ]);
    $slot = AvailabilitySlot::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $service->id,
        'format' => 'in-person',
        'location_label' => 'Old Town clinic',
    ]);

    $this->get(route('experts.bookings.create', [
        'expertProfile' => $expert,
        'service' => $service->id,
    ]))
        ->assertOk()
        ->assertSee('Request a consultation')
        ->assertSee('Scout')
        ->assertSee('Dog')
        ->assertSee('Avian clinic visit')
        ->assertSee('Old Town clinic')
        ->assertSee((string) $slot->id, false);
});

test('booking uses server price reserves one slot and ignores an idempotent retry', function () {
    $expert = ExpertProfile::factory()->create();
    $service = Service::factory()->create([
        'expert_profile_id' => $expert->id,
        'price' => 73,
        'currency' => 'EUR',
        'format' => 'video',
    ]);
    $slot = AvailabilitySlot::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $service->id,
        'format' => 'video',
        'location_label' => 'Secure video room',
        'capacity' => 1,
        'booked_count' => 0,
    ]);
    $key = (string) Str::uuid();
    $payload = expertBookingPayload($service, $slot, $key);

    $first = $this->post(route('experts.bookings.store', $expert), [
        ...$payload,
        'amount' => 1,
        'currency' => 'USD',
    ]);
    $first->assertRedirect();

    $this->post(route('experts.bookings.store', $expert), $payload)->assertRedirect();

    expect(Booking::query()->count())->toBe(1);
    $booking = Booking::query()->firstOrFail();
    expect($booking->amount)->toBe('73.00')
        ->and($booking->currency)->toBe('EUR')
        ->and($booking->format)->toBe('video')
        ->and($slot->refresh()->booked_count)->toBe(1)
        ->and($slot->status)->toBe('booked')
        ->and($booking->consultation()->exists())->toBeTrue();

    $this->get(route('bookings.show', $booking))
        ->assertOk()
        ->assertSee('Appointment '.$booking->reference)
        ->assertSee('Professional summary');

    $this->get(route('consultations.show', $booking->consultation()->firstOrFail()))
        ->assertOk()
        ->assertSee('Secure consultation room')
        ->assertSee('Access is limited to this appointment');
});

test('booking rejects a slot from another service', function () {
    $expert = ExpertProfile::factory()->create();
    $selectedService = Service::factory()->create(['expert_profile_id' => $expert->id]);
    $otherService = Service::factory()->create(['expert_profile_id' => $expert->id]);
    $slot = AvailabilitySlot::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $otherService->id,
    ]);

    $this->from(route('experts.bookings.create', $expert))
        ->post(route('experts.bookings.store', $expert), expertBookingPayload(
            $selectedService,
            $slot,
            (string) Str::uuid(),
        ))
        ->assertRedirect(route('experts.bookings.create', $expert))
        ->assertSessionHasErrors('availability_slot_id');

    $this->assertDatabaseCount('bookings', 0);
});

test('client can revoke a private document and cancellation releases capacity', function () {
    Storage::fake('local');
    $expert = ExpertProfile::factory()->create();
    $service = Service::factory()->create([
        'expert_profile_id' => $expert->id,
        'requires_payment' => false,
        'requires_approval' => false,
    ]);
    $slot = AvailabilitySlot::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $service->id,
        'capacity' => 1,
    ]);
    $payload = [
        ...expertBookingPayload($service, $slot, (string) Str::uuid()),
        'document_label' => 'Selected lab result',
        'document_type' => 'lab-result',
        'document' => UploadedFile::fake()->create('result.pdf', 100, 'application/pdf'),
    ];

    $this->post(route('experts.bookings.store', $expert), $payload)->assertRedirect();
    $booking = Booking::query()->firstOrFail();
    $grant = DocumentGrant::query()->firstOrFail();

    Storage::disk('local')->assertExists($grant->file_path);
    expect($grant->permissions)->toBe(['view'])
        ->and($grant->expires_at)->not->toBeNull();

    $this->post(route('bookings.actions', $booking), [
        'action' => 'revoke-document',
        'document_grant_id' => $grant->id,
    ])->assertRedirect();
    expect($grant->refresh()->revoked_at)->not->toBeNull();

    $this->post(route('bookings.actions', $booking), [
        'action' => 'cancel',
        'reason' => 'Plans changed.',
    ])->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($slot->refresh()->booked_count)->toBe(0)
        ->and($slot->status)->toBe('open');
});
