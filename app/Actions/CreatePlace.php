<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreatePlaceData;
use App\Enums\PlaceVisibility;
use App\Models\Organization;
use App\Models\Place;
use App\Models\User;
use App\Services\PlaceIdentityNormalizer;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class CreatePlace
{
    public function __construct(
        private Gate $gate,
        private PlaceIdentityNormalizer $normalizer,
    ) {}

    public function handle(User $actor, CreatePlaceData $data): Place
    {
        $this->gate->forUser($actor)->authorize('create', Place::class);
        $this->validate($data);

        $existing = Place::query()
            ->where('creation_idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing !== null) {
            if (! $existing->isManagedBy($actor)) {
                throw ValidationException::withMessages([
                    'place.name' => __('places.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $organization = $data->organizationId === null
            ? null
            : Organization::query()->findOrFail($data->organizationId);

        if ($organization !== null) {
            $this->gate->forUser($actor)->authorize('organizeEvents', $organization);
        }

        $duplicate = Place::query()
            ->select(['id', 'name'])
            ->accessibleTo($actor)
            ->where(function ($query) use ($data): void {
                $query->where('name', trim($data->name));

                if (filled($data->publicAddress)) {
                    $query->orWhere('public_address', trim((string) $data->publicAddress));
                }
            })
            ->first();

        if ($duplicate !== null) {
            throw ValidationException::withMessages([
                'place.name' => __('places.validation.possible_duplicate', ['name' => $duplicate->name]),
            ]);
        }

        return DB::transaction(function () use ($actor, $data, $organization): Place {
            $this->gate->forUser($actor)->authorize('create', Place::class);

            if ($organization !== null) {
                $lockedOrganization = Organization::query()
                    ->lockForUpdate()
                    ->findOrFail($organization->id);
                $this->gate->forUser($actor)->authorize('organizeEvents', $lockedOrganization);
            }

            $baseSlug = Str::slug($data->name);
            $suffix = Str::lower((string) Str::ulid());

            return Place::query()->create([
                'owner_user_id' => $organization === null ? $actor->id : null,
                'organization_id' => $organization?->id,
                'created_by_user_id' => $actor->id,
                'last_edited_by_user_id' => $actor->id,
                'stable_key' => $baseSlug.'-'.$suffix,
                'slug' => $baseSlug.'-'.$suffix,
                'creation_idempotency_key' => $data->idempotencyKey,
                'name' => trim($data->name),
                'normalized_name' => $this->normalizer->name($data->name),
                'summary' => filled($data->summary) ? trim((string) $data->summary) : null,
                'type' => $data->type,
                'catalog_category' => $data->catalogCategory,
                'visibility' => $data->visibility,
                'locale' => $data->locale,
                'public_region' => trim($data->publicRegion),
                'public_address' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->nullableTrim($data->publicAddress)
                    : null,
                'normalized_address' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->normalizer->address($data->publicAddress)
                    : null,
                'public_phone' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->nullableTrim($data->publicPhone)
                    : null,
                'normalized_phone' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->normalizer->phone($data->publicPhone)
                    : null,
                'public_website' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->nullableTrim($data->publicWebsite)
                    : null,
                'normalized_website' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->normalizer->website($data->publicWebsite)
                    : null,
                'public_email' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->nullableTrim($data->publicEmail)
                    : null,
                'normalized_email' => in_array($data->visibility, [PlaceVisibility::Public, PlaceVisibility::Unlisted], true)
                    ? $this->normalizer->email($data->publicEmail)
                    : null,
                'public_latitude' => $data->publicLatitude,
                'public_longitude' => $data->publicLongitude,
                'exact_address' => $this->nullableTrim($data->exactAddress),
                'exact_latitude' => $data->exactLatitude,
                'exact_longitude' => $data->exactLongitude,
                'private_instructions' => $this->nullableTrim($data->privateInstructions),
                'is_indoor' => $data->isIndoor,
                'verification_status' => $data->verificationStatus,
                'accessibility_status' => $data->accessibilityStatus,
                'transport_information' => $this->nullableTrim($data->transportInformation),
                'parking_information' => $this->nullableTrim($data->parkingInformation),
                'pet_rules' => $this->nullableTrim($data->petRules),
                'species_rules' => array_values(array_unique($data->speciesRules)),
            ]);
        }, 3);
    }

    private function validate(CreatePlaceData $data): void
    {
        Validator::make([
            'name' => $data->name,
            'type' => $data->type->value,
            'visibility' => $data->visibility->value,
            'public_region' => $data->publicRegion,
            'public_address' => $data->publicAddress,
            'exact_address' => $data->exactAddress,
            'public_latitude' => $data->publicLatitude,
            'public_longitude' => $data->publicLongitude,
            'exact_latitude' => $data->exactLatitude,
            'exact_longitude' => $data->exactLongitude,
            'locale' => $data->locale,
            'idempotency_key' => $data->idempotencyKey,
            'summary' => $data->summary,
            'private_instructions' => $data->privateInstructions,
            'transport_information' => $data->transportInformation,
            'parking_information' => $data->parkingInformation,
            'pet_rules' => $data->petRules,
            'species_rules' => $data->speciesRules,
            'organization_id' => $data->organizationId,
            'catalog_category' => $data->catalogCategory,
            'public_phone' => $data->publicPhone,
            'public_website' => $data->publicWebsite,
            'public_email' => $data->publicEmail,
        ], [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'type' => ['required', Rule::enum($data->type::class)],
            'visibility' => ['required', Rule::enum($data->visibility::class)],
            'public_region' => ['required', 'string', 'max:160'],
            'public_address' => ['nullable', 'string', 'max:500'],
            'exact_address' => ['nullable', 'string', 'max:2000'],
            'public_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:public_longitude'],
            'public_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:public_latitude'],
            'exact_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:exact_longitude'],
            'exact_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:exact_latitude'],
            'locale' => ['required', Rule::in(config('platform.supported_locales', ['en']))],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'private_instructions' => ['nullable', 'string', 'max:3000'],
            'transport_information' => ['nullable', 'string', 'max:3000'],
            'parking_information' => ['nullable', 'string', 'max:3000'],
            'pet_rules' => ['nullable', 'string', 'max:5000'],
            'species_rules' => ['array', 'max:20'],
            'species_rules.*' => ['string', 'max:80', 'distinct'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'catalog_category' => [
                'nullable',
                Rule::in([
                    'park',
                    'dog-park',
                    'route',
                    'vet',
                    'emergency-vet',
                    'pet-store',
                    'grooming',
                    'shelter',
                    'pet-cafe',
                ]),
            ],
            'public_phone' => ['nullable', 'string', 'max:40'],
            'public_website' => ['nullable', 'url:http,https', 'max:2048'],
            'public_email' => ['nullable', 'email:rfc', 'max:255'],
        ])->validate();
    }

    private function nullableTrim(?string $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
