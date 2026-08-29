<?php

declare(strict_types=1);

use App\Enums\MedicalKnowledgeStatus;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\PetEvidenceStatus;
use App\Enums\PetProfileVisibility;
use App\Models\ExpertProfile;
use App\Models\DeviceEvent;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\ForumJournalMeasurement;
use App\Models\KnowledgeCorrection;
use App\Models\Listing;
use App\Models\MedicalRecord;
use App\Models\PetProfileFact;
use App\Models\SearchCase;
use App\Models\Sighting;

test('factory decimals use exact strings and fixture urls are local or reserved', function () {
    $listing = Listing::factory()->create();
    $measurement = ForumJournalMeasurement::factory()->create();
    $searchCase = SearchCase::factory()->create();
    $sighting = Sighting::factory()->create();
    $expert = ExpertProfile::factory()->create();
    $correction = KnowledgeCorrection::factory()->create();

    expect($listing->price)->toBeString()
        ->and($measurement->numeric_value)->toBeString()
        ->and($searchCase->public_latitude)->toBeString()
        ->and($searchCase->public_longitude)->toBeString()
        ->and($sighting->public_latitude)->toBeString()
        ->and($sighting->public_longitude)->toBeString()
        ->and($listing->cover_url)->toStartWith('/images/')
        ->and($searchCase->cover_url)->toStartWith('/images/')
        ->and($expert->avatar_url)->toStartWith('/images/')
        ->and($correction->source_url)->toStartWith('https://knowledge.example.test/');
});

test('factory enum columns use their declared enum casts', function () {
    $event = ForumEvent::factory()->create();
    $medicalRecord = MedicalRecord::factory()->create();
    $fact = PetProfileFact::factory()->create();

    expect($event->accessibility_status)->toBe(ForumEventAccessibilityStatus::NotAssessed)
        ->and($medicalRecord->allergy_knowledge_status)->toBe(MedicalKnowledgeStatus::Known)
        ->and($medicalRecord->medication_knowledge_status)->toBe(MedicalKnowledgeStatus::NoneKnown)
        ->and($fact->verification_status)->toBe(PetEvidenceStatus::Unverified)
        ->and($fact->visibility)->toBe(PetProfileVisibility::Private)
        ->and($fact->author_user_id)->toBe($fact->profile->user_id)
        ->and($fact->normalized_value_hash)->toBe(hash('sha256', json_encode($fact->value, JSON_THROW_ON_ERROR)))
        ->and($fact->current_key)->toBe("pet:{$fact->pet_profile_id}:fact:{$fact->fact_key}");
});

test('factory lifecycle timestamps agree with their status', function () {
    $deviceEvent = DeviceEvent::factory()->create();
    $pending = ForumEventRegistration::factory()->pending()->create();
    $waitlisted = ForumEventRegistration::factory()->waitlisted()->create();
    $confirmed = ForumEventRegistration::factory()->confirmed()->create();

    expect($deviceEvent->first_occurred_at->lessThanOrEqualTo($deviceEvent->last_occurred_at))->toBeTrue()
        ->and($deviceEvent->occurred_at->equalTo($deviceEvent->last_occurred_at))->toBeTrue()
        ->and($pending->confirmed_at)->toBeNull()
        ->and($waitlisted->confirmed_at)->toBeNull()
        ->and($confirmed->confirmed_at)->not->toBeNull();
});
