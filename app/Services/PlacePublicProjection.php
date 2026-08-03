<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlaceVisibility;
use App\Models\Place;

final class PlacePublicProjection
{
    /** @return array<string, mixed> */
    public function for(Place $place): array
    {
        $isPublic = $place->visibility === PlaceVisibility::Public;

        return [
            'stable_key' => $place->stable_key,
            'slug' => $place->slug,
            'name' => $place->name,
            'summary' => $isPublic ? $place->summary : null,
            'type' => $place->type->value,
            'type_label' => $place->type->label(),
            'visibility' => $place->visibility->value,
            'status' => $place->status->value,
            'public_region' => $place->public_region,
            'public_address' => $isPublic ? $place->public_address : null,
            'public_latitude' => $isPublic ? $place->public_latitude : null,
            'public_longitude' => $isPublic ? $place->public_longitude : null,
            'verification_status' => $place->verification_status->value,
            'verification_label' => $place->verification_status->label(),
            'verification_expires_at' => $place->information_expires_at?->toAtomString(),
            'accessibility_status' => $place->accessibility_status->value,
            'accessibility_label' => $place->accessibility_status->label(),
            'accessibility_facts' => $isPublic ? ($place->accessibility_facts ?? []) : [],
            'transport_information' => $isPublic ? $place->transport_information : null,
            'parking_information' => $isPublic ? $place->parking_information : null,
            'pet_rules' => $isPublic ? $place->pet_rules : null,
            'species_rules' => $isPublic ? ($place->species_rules ?? []) : [],
            'is_indoor' => $place->is_indoor,
        ];
    }
}
