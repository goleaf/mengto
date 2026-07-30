<?php

use App\Enums\BookingStatus;
use App\Enums\ExpertProfileStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('urgent warning signs stop planned consultation booking', function () {
    $expert = ExpertProfile::factory()->create();
    $service = Service::factory()->create(['expert_profile_id' => $expert->id]);
    $slot = AvailabilitySlot::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $service->id,
    ]);
    $payload = expertBookingPayload($service, $slot, (string) Str::uuid());

    $this->from(route('experts.bookings.create', $expert))
        ->post(route('experts.bookings.store', $expert), [
            ...$payload,
            'urgent_signs' => 1,
            'main_question' => 'The pet has difficulty breathing, cannot stand, and needs immediate help.',
        ])
        ->assertRedirect(route('experts.bookings.create', $expert))
        ->assertSessionHasErrors('urgent_signs');

    $this->assertDatabaseCount('bookings', 0);
});

test('credential originals stay private while public profile exposes only a check status', function () {
    Storage::fake('local');

    $response = $this->post(route('experts.store'), [
        'public_name' => 'Mia Care Coordinator',
        'legal_name' => 'Mia Carter',
        'primary_type' => 'shelter-specialist',
        'headline' => 'Volunteer care coordinator for adoption support',
        'bio' => str_repeat('Care coordination with explicit task ownership and privacy-aware communication. ', 2),
        'approach' => 'Structured preparation and clear role boundaries.',
        'boundaries' => 'No diagnosis, prescriptions, emergency intake, or medical treatment.',
        'years_experience' => 4,
        'country' => 'Lithuania',
        'city' => 'Vilnius',
        'service_area' => 'Vilnius',
        'specializations' => ['adoption'],
        'species' => ['dog', 'cat'],
        'languages' => ['English'],
        'formats' => ['text', 'video'],
        'methods' => ['Structured planning'],
        'availability_status' => 'limited',
        'response_time' => 'One business day',
        'accepts_new_clients' => 0,
        'offers_emergency_care' => 0,
        'currency' => 'EUR',
        'credential_type' => 'organization-role',
        'credential_title' => 'Volunteer coordinator confirmation',
        'credential_issuer' => 'Vilnius Animal Aid',
        'credential_file' => UploadedFile::fake()->create('private-confirmation.pdf', 120, 'application/pdf'),
    ]);

    $profile = ExpertProfile::query()->where('owner_key', 'mia-carter')->firstOrFail();
    $credential = Credential::query()->where('expert_profile_id', $profile->id)->firstOrFail();

    $response->assertRedirect(route('experts.show', $profile));
    Storage::disk('local')->assertExists($credential->file_path);
    expect($profile->status)->toBe(ExpertProfileStatus::Pending)
        ->and($credential->file_path)->toStartWith('expert-credentials/');

    $this->get(route('experts.show', $profile))
        ->assertOk()
        ->assertSee('not verified')
        ->assertDontSee($credential->file_path)
        ->assertDontSee('private-confirmation.pdf');

    $this->get(route('experts.index'))
        ->assertOk()
        ->assertDontSee('Mia Care Coordinator');
});

test('only a completed service can produce one verified review', function () {
    $expert = ExpertProfile::factory()->create();
    $service = Service::factory()->create(['expert_profile_id' => $expert->id]);
    $booking = Booking::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $service->id,
        'client_key' => 'mia-carter',
        'status' => BookingStatus::Confirmed,
    ]);

    $review = [
        'booking_id' => $booking->id,
        'rating' => 5,
        'communication_rating' => 5,
        'clarity_rating' => 5,
        'organization_rating' => 4,
        'price_transparency_rating' => 5,
        'body' => 'The professional explained the scope clearly and provided a useful next step without guarantees.',
        'is_anonymous' => 1,
    ];

    $this->post(route('experts.reviews.store', $expert), $review)
        ->assertSessionHasErrors('booking_id');

    $booking->update(['status' => BookingStatus::Completed, 'completed_at' => now()]);
    $this->post(route('experts.reviews.store', $expert), $review)->assertRedirect(route('experts.show', $expert));
    $this->post(route('experts.reviews.store', $expert), $review)->assertSessionHasErrors('booking_id');

    expect(Review::query()->count())->toBe(1)
        ->and($expert->refresh()->verified_review_count)->toBe(1)
        ->and($expert->review_average)->toBe('5.00');
});

test('specialist must confirm the written consultation summary before it becomes final', function () {
    $expert = ExpertProfile::factory()->create(['owner_key' => 'mia-carter']);
    $service = Service::factory()->create(['expert_profile_id' => $expert->id]);
    $booking = Booking::factory()->create([
        'expert_profile_id' => $expert->id,
        'service_id' => $service->id,
        'client_key' => 'another-client',
        'status' => BookingStatus::Confirmed,
    ]);
    $consultation = Consultation::factory()->create([
        'booking_id' => $booking->id,
        'expert_profile_id' => $expert->id,
        'client_summary' => null,
        'summary_confirmed_at' => null,
    ]);

    $this->post(route('bookings.actions', $booking), [
        'action' => 'complete-consultation',
        'client_summary' => 'The reviewed summary separates general guidance from diagnosis and gives the client a clear next step.',
        'action_plan' => ['Arrange an in-person assessment if warning signs appear'],
        'referral_summary' => 'Contact a local clinic if the condition worsens.',
        'follow_up_until' => now()->addWeek()->toDateString(),
    ])->assertRedirect();

    expect($consultation->refresh()->summary_confirmed_at)->not->toBeNull()
        ->and($booking->refresh()->status)->toBe(BookingStatus::Completed)
        ->and($consultation->client_summary)->toContain('reviewed summary');
});
