<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventVersion;

final class ForumEventLifecycleSnapshot
{
    /** @return array<string, mixed> */
    public function event(ForumEvent $event): array
    {
        return [
            'event_key' => $event->stable_key,
            'title' => $event->title,
            'summary' => $event->summary,
            'type' => $event->type->value,
            'visibility' => $event->visibility->value,
            'format' => $event->format->value,
            'status' => $event->status->value,
            'locale' => $event->locale,
            'starts_at' => $event->starts_at->toAtomString(),
            'ends_at' => $event->ends_at->toAtomString(),
            'timezone' => $event->timezone,
            'registration_policy' => $event->registration_policy->value,
            'place_id' => $event->place_id,
            'venue_id' => $event->venue_id,
            'location_scope' => $event->location_scope,
            'exact_location' => $event->exact_location,
            'online_url' => $event->online_url,
            'attendance_requirements' => $event->attendance_requirements,
            'vaccination_requirements' => $event->vaccination_requirements,
            'vaccination_jurisdiction' => $event->vaccination_jurisdiction,
            'minimum_animal_age_months' => $event->minimum_animal_age_months,
            'maximum_animal_age_months' => $event->maximum_animal_age_months,
            'pet_participation_mode' => $event->pet_participation_mode->value,
            'accessibility_status' => $event->accessibility_status->value,
            'accessibility_information' => $event->accessibility_information,
            'cost_minor' => $event->cost_minor,
            'currency' => $event->currency,
            'refund_policy' => $event->refund_policy,
            'photo_consent_mode' => $event->photo_consent_mode->value,
            'animal_welfare_rules' => $event->animal_welfare_rules,
            'emergency_contact_plan' => $event->emergency_contact_plan,
        ];
    }

    /** @param list<int> $petProfileIds
     * @return array<string, mixed>
     */
    public function registration(
        ForumEventRegistration $registration,
        ForumEventVersion $version,
        ForumEventOccurrence $occurrence,
        array $petProfileIds,
    ): array {
        return [
            'event_version' => $version->version_number,
            'event_version_checksum' => $version->snapshot_checksum,
            'occurrence_key' => $occurrence->stable_key,
            'starts_at' => $occurrence->starts_at->toAtomString(),
            'ends_at' => $occurrence->ends_at->toAtomString(),
            'timezone' => $occurrence->timezone,
            'attendance_format' => $registration->attendance_format->value,
            'guest_count' => $registration->guest_count,
            'pet_profile_ids' => $petProfileIds,
            'photo_consent' => $registration->photo_consent->value,
            'requirements_accepted' => $registration->requirements_accepted,
            'price_minor' => $registration->event->cost_minor,
            'currency' => $registration->event->currency,
            'accepted_at' => ($registration->submitted_at ?? now())->toAtomString(),
        ];
    }

    /** @param array<string, mixed> $snapshot */
    public function checksum(array $snapshot): string
    {
        return hash('sha256', json_encode(
            $snapshot,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }
}
