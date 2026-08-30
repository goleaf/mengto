<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SubmitPlaceData;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PlaceFactScope;
use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceType;
use App\Models\OrganizationMembership;
use App\Models\PlaceFact;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;
use App\Models\PlaceSubmissionIdentityLock;
use App\Models\PlaceSubmissionRevision;
use App\Models\User;
use App\Notifications\PlaceSubmissionStatusChanged;
use App\Services\PlaceDuplicateDetector;
use App\Services\PlaceIdentityNormalizer;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class SubmitPlaceSubmission
{
    private const OPEN_SUBMISSION_LIMIT = 10;

    private const DETECTION_LOCK_HASH = '992a34320277074bc22580f50a8b5e0dfe227340f878c928967833e456a136aa';

    public function __construct(
        private Gate $gate,
        private PlaceIdentityNormalizer $normalizer,
        private PlaceDuplicateDetector $duplicateDetector,
        private Request $request,
    ) {}

    public function handle(User $actor, SubmitPlaceData $data): PlaceSubmission
    {
        $this->gate->forUser($actor)->authorize('create', PlaceSubmission::class);
        $this->validate($data);

        $fingerprint = $this->payloadFingerprint($data);
        $existing = PlaceSubmission::query()
            ->where('submitter_user_id', $actor->id)
            ->where('idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing !== null) {
            return $this->replay($existing, $fingerprint);
        }

        $this->enforceRateLimits($actor);
        $normalized = $this->normalized($data);
        $identityHash = $this->identityHash($normalized, $data);

        [$submission, $created] = DB::transaction(function () use (
            $actor,
            $data,
            $fingerprint,
            $normalized,
            $identityHash,
        ): array {
            PlaceSubmissionIdentityLock::query()
                ->whereKey(self::DETECTION_LOCK_HASH)
                ->lockForUpdate()
                ->firstOrFail();
            $this->enforceRateLimits($actor);
            $this->gate->forUser($actor)->authorize('create', PlaceSubmission::class);

            $replay = PlaceSubmission::query()
                ->where('submitter_user_id', $actor->id)
                ->where('idempotency_key', $data->idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($replay !== null) {
                return [$this->replay($replay, $fingerprint), false];
            }

            $this->authorizeOrganization($actor, $data->canonicalOrganizationId);

            $openCount = PlaceSubmission::query()
                ->where('submitter_user_id', $actor->id)
                ->whereIn('status', $this->openStatusValues())
                ->lockForUpdate()
                ->count();

            if ($openCount >= self::OPEN_SUBMISSION_LIMIT) {
                throw ValidationException::withMessages([
                    'submission' => __('places.submissions.validation.open_limit'),
                ]);
            }

            $identityLock = PlaceSubmissionIdentityLock::query()->firstOrCreate(
                ['identity_hash' => $identityHash],
                ['first_submission_id' => null, 'lock_version' => 0],
            );
            $identityLock = PlaceSubmissionIdentityLock::query()
                ->whereKey($identityLock->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $now = now();
            $stableKey = 'place-submission-'.Str::lower((string) Str::ulid());
            $submission = PlaceSubmission::query()->create([
                'submitter_user_id' => $actor->id,
                'canonical_organization_id' => $data->canonicalOrganizationId,
                'stable_key' => $stableKey,
                'idempotency_key' => $data->idempotencyKey,
                'payload_fingerprint' => $fingerprint,
                'status' => PlaceSubmissionStatus::Submitted,
                'resolution' => PlaceSubmissionResolution::None,
                'source_kind' => $data->source,
                'source_reference' => $this->trimmed($data->sourceReference),
                'relationship_to_place' => $data->relationshipToPlace,
                'location_precision' => $data->locationPrecision,
                'locale' => $data->locale,
                'name' => trim($data->name),
                'normalized_name' => $normalized['name'],
                'catalog_category' => $data->catalogCategory,
                'place_type' => $data->type,
                'summary' => $this->trimmed($data->summary),
                'public_region' => trim($data->publicRegion),
                'public_address' => $this->trimmed($data->publicAddress),
                'normalized_address' => $normalized['address'],
                'public_latitude' => $data->publicLatitude,
                'public_longitude' => $data->publicLongitude,
                'exact_address' => $this->trimmed($data->exactAddress),
                'exact_latitude' => $data->exactLatitude,
                'exact_longitude' => $data->exactLongitude,
                'public_phone' => $this->trimmed($data->publicPhone),
                'normalized_phone' => $normalized['phone'],
                'public_email' => $this->trimmed($data->publicEmail),
                'normalized_email' => $normalized['email'],
                'public_website' => $this->trimmed($data->publicWebsite),
                'normalized_website' => $normalized['website'],
                'identity_hash' => $identityHash,
                'submitted_facts' => $data->facts,
                'consent_version' => $data->consentVersion,
                'consented_at' => $now,
                'observed_at' => $data->observedAt,
                'audit_context' => $this->auditContext(),
                'continued_as_distinct' => false,
                'lock_version' => 0,
                'submitted_at' => $now,
            ]);

            $revision = PlaceSubmissionRevision::query()->create([
                'place_submission_id' => $submission->id,
                'submitted_by_user_id' => $actor->id,
                'stable_key' => $stableKey.'-revision-1',
                'revision_number' => 1,
                'kind' => 'initial',
                'summary' => $this->trimmed($data->summary),
                'created_at' => $now,
            ]);

            $this->recordFacts($submission, $revision, $actor, $data);
            $candidates = $this->duplicateDetector->detect($submission);
            $status = $candidates === []
                ? PlaceSubmissionStatus::Submitted
                : PlaceSubmissionStatus::DuplicateReview;

            if ($status !== $submission->status) {
                $submission->status = $status;
                $submission->save();
            }

            PlaceSubmissionEvent::query()->create([
                'place_submission_id' => $submission->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => 'submit:'.$actor->id.':'.$data->idempotencyKey,
                'action' => PlaceSubmissionAction::Submitted,
                'from_status' => null,
                'to_status' => $status,
                'reason_code' => 'community-submission',
                'payload_fingerprint' => $fingerprint,
                'audit_context' => $this->auditContext(),
                'created_at' => $now,
            ]);

            if ($identityLock->first_submission_id === null) {
                $identityLock->first_submission_id = $submission->id;
                $identityLock->save();
            }

            RateLimiter::hit($this->shortRateKey($actor), 600);
            RateLimiter::hit($this->dailyRateKey($actor), 86_400);

            return [$submission->refresh(), true];
        }, 3);

        if ($created) {
            DB::afterCommit(static function () use ($actor, $submission): void {
                $actor->notify(new PlaceSubmissionStatusChanged($submission, $submission->status));
            });
        }

        return $submission;
    }

    private function validate(SubmitPlaceData $data): void
    {
        $validator = Validator::make([
            'name' => $data->name,
            'type' => $data->type->value,
            'catalog_category' => $data->catalogCategory,
            'source' => $data->source->value,
            'source_reference' => $data->sourceReference,
            'relationship_to_place' => $data->relationshipToPlace,
            'location_precision' => $data->locationPrecision->value,
            'locale' => $data->locale,
            'public_region' => $data->publicRegion,
            'public_address' => $data->publicAddress,
            'public_latitude' => $data->publicLatitude,
            'public_longitude' => $data->publicLongitude,
            'exact_address' => $data->exactAddress,
            'exact_latitude' => $data->exactLatitude,
            'exact_longitude' => $data->exactLongitude,
            'public_phone' => $data->publicPhone,
            'public_email' => $data->publicEmail,
            'public_website' => $data->publicWebsite,
            'summary' => $data->summary,
            'facts' => $data->facts,
            'canonical_organization_id' => $data->canonicalOrganizationId,
            'observed_at' => $data->observedAt,
            'consent_version' => $data->consentVersion,
            'consent_granted' => $data->consentGranted,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'type' => ['required', Rule::enum(PlaceType::class)],
            'catalog_category' => ['required', Rule::in(array_keys($this->categoryTypes()))],
            'source' => ['required', Rule::enum(PlaceSubmissionSource::class)],
            'source_reference' => ['nullable', 'string', 'max:2048'],
            'relationship_to_place' => ['required', Rule::in(['visitor', 'customer', 'employee', 'owner', 'organization', 'public-observer'])],
            'location_precision' => ['required', Rule::enum(PlaceLocationPrecision::class)],
            'locale' => ['required', Rule::in(config('platform.supported_locales', ['en']))],
            'public_region' => ['required', 'string', 'max:160'],
            'public_address' => ['nullable', 'string', 'max:500'],
            'public_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:public_longitude'],
            'public_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:public_latitude'],
            'exact_address' => ['nullable', 'string', 'max:2000'],
            'exact_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:exact_longitude'],
            'exact_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:exact_latitude'],
            'public_phone' => ['nullable', 'string', 'max:40'],
            'public_email' => ['nullable', 'email:rfc', 'max:255'],
            'public_website' => ['nullable', 'url:http,https', 'max:2048'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'facts' => ['array', 'max:20'],
            'facts.hours' => ['sometimes', 'array', 'max:14'],
            'facts.hours.*' => ['string', 'max:80'],
            'facts.services' => ['sometimes', 'array', 'max:30'],
            'facts.services.*' => ['string', 'max:80', 'distinct'],
            'facts.features' => ['sometimes', 'array', 'max:30'],
            'facts.features.*' => ['string', 'max:80', 'distinct'],
            'facts.rules' => ['sometimes', 'string', 'max:3000'],
            'canonical_organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'observed_at' => ['nullable', 'date', 'before_or_equal:tomorrow'],
            'consent_version' => ['required', Rule::in(['places-submission-v1'])],
            'consent_granted' => ['accepted'],
            'idempotency_key' => ['required', 'uuid', 'max:190'],
        ]);

        $validator->after(function ($validator) use ($data): void {
            $publicCoordinates = filled($data->publicLatitude) && filled($data->publicLongitude);
            $exactCoordinates = filled($data->exactLatitude) && filled($data->exactLongitude);

            if ($data->locationPrecision === PlaceLocationPrecision::PublicPoint && ! $publicCoordinates) {
                $validator->errors()->add('public_latitude', __('places.submissions.validation.public_point_required'));
            }

            if ($data->locationPrecision === PlaceLocationPrecision::PublicRegion
                && (filled($data->publicAddress) || $publicCoordinates)) {
                $validator->errors()->add('public_latitude', __('places.submissions.region_only'));
            }

            if ($data->locationPrecision !== PlaceLocationPrecision::PrivateExact
                && (filled($data->exactAddress) || $exactCoordinates)) {
                $validator->errors()->add('exact_address', __('places.submissions.validation.private_exact_only'));
            }

            if ($data->locationPrecision === PlaceLocationPrecision::PrivateExact
                && ! filled($data->exactAddress)
                && ! $exactCoordinates) {
                $validator->errors()->add('exact_address', __('places.submissions.validation.private_exact_required'));
            }

            $phone = $this->normalizer->phone($data->publicPhone);

            if (filled($data->publicPhone)
                && preg_match('/\A\+?[0-9][0-9\s().-]*\z/', (string) $data->publicPhone) !== 1) {
                $validator->errors()->add('public_phone', __('places.submissions.validation.phone'));
            }

            if ($phone !== null && (strlen($phone) < 8 || strlen($phone) > 15)) {
                $validator->errors()->add('public_phone', __('places.submissions.validation.phone'));
            }

            if (($this->categoryTypes()[$data->catalogCategory] ?? null) !== $data->type) {
                $validator->errors()->add('catalog_category', __('places.submissions.validation.category_type'));
            }

            $hasSpecificLocation = filled($data->publicAddress)
                || $publicCoordinates
                || filled($data->exactAddress)
                || $exactCoordinates;

            if (! in_array($data->catalogCategory, ['park', 'dog-park', 'route'], true)
                && ! $hasSpecificLocation) {
                $validator->errors()->add('public_address', __('places.submissions.validation.category_location'));
            }

            if ($data->catalogCategory === 'emergency-vet') {
                if ($phone === null) {
                    $validator->errors()->add('public_phone', __('places.submissions.validation.emergency_phone'));
                }

                if (blank($data->facts['hours'] ?? null)) {
                    $validator->errors()->add('facts.hours', __('places.submissions.validation.emergency_hours'));
                }
            }
        });

        $validator->validate();
    }

    /** @return array{name: string, address: string|null, phone: string|null, email: string|null, website: string|null} */
    private function normalized(SubmitPlaceData $data): array
    {
        return [
            'name' => $this->normalizer->name($data->name),
            'address' => $this->normalizer->address($data->publicAddress),
            'phone' => $this->normalizer->phone($data->publicPhone),
            'email' => $this->normalizer->email($data->publicEmail),
            'website' => $this->normalizer->website($data->publicWebsite),
        ];
    }

    /** @param array{name: string, address: string|null, phone: string|null, email: string|null, website: string|null} $normalized */
    private function identityHash(array $normalized, SubmitPlaceData $data): string
    {
        return hash_hmac('sha256', implode('|', [
            PlaceDuplicateDetector::ALGORITHM_VERSION,
            $normalized['name'],
            $normalized['address'],
            $normalized['phone'],
            $normalized['email'],
            $normalized['website'],
            $data->publicRegion,
        ]), (string) config('app.key'));
    }

    private function payloadFingerprint(SubmitPlaceData $data): string
    {
        $payload = [
            'name' => trim($data->name),
            'type' => $data->type->value,
            'catalog_category' => $data->catalogCategory,
            'source' => $data->source->value,
            'source_reference' => $this->trimmed($data->sourceReference),
            'relationship_to_place' => $data->relationshipToPlace,
            'location_precision' => $data->locationPrecision->value,
            'locale' => $data->locale,
            'public_region' => trim($data->publicRegion),
            'public_address' => $this->trimmed($data->publicAddress),
            'public_latitude' => $data->publicLatitude,
            'public_longitude' => $data->publicLongitude,
            'exact_address' => $this->trimmed($data->exactAddress),
            'exact_latitude' => $data->exactLatitude,
            'exact_longitude' => $data->exactLongitude,
            'public_phone' => $this->trimmed($data->publicPhone),
            'public_email' => $this->normalizer->email($data->publicEmail),
            'public_website' => $this->normalizer->website($data->publicWebsite),
            'summary' => $this->trimmed($data->summary),
            'facts' => $this->sortRecursively($data->facts),
            'canonical_organization_id' => $data->canonicalOrganizationId,
            'observed_at' => $data->observedAt?->toIso8601String(),
            'consent_version' => $data->consentVersion,
        ];

        return hash_hmac('sha256', (string) json_encode($payload, JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    private function replay(PlaceSubmission $submission, string $fingerprint): PlaceSubmission
    {
        if (! hash_equals($submission->payload_fingerprint, $fingerprint)) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('places.submissions.validation.idempotency_conflict'),
            ]);
        }

        return $submission;
    }

    private function enforceRateLimits(User $actor): void
    {
        if (RateLimiter::tooManyAttempts($this->shortRateKey($actor), 3)
            || RateLimiter::tooManyAttempts($this->dailyRateKey($actor), 10)) {
            throw ValidationException::withMessages([
                'submission' => __('places.submissions.validation.rate_limited'),
            ]);
        }
    }

    private function shortRateKey(User $actor): string
    {
        return 'place-submission:10m:'.hash('sha256', (string) $actor->id);
    }

    private function dailyRateKey(User $actor): string
    {
        return 'place-submission:day:'.hash('sha256', (string) $actor->id);
    }

    private function authorizeOrganization(User $actor, ?int $organizationId): void
    {
        if ($organizationId === null) {
            return;
        }

        $authorized = OrganizationMembership::query()
            ->active()
            ->where('organization_id', $organizationId)
            ->where('user_id', $actor->id)
            ->whereIn('role', OrganizationRole::placeManagerValues())
            ->whereHas('organization', fn ($query) => $query
                ->where('status', OrganizationStatus::Active->value)
                ->whereNull('archived_at'))
            ->exists();

        if (! $authorized) {
            throw ValidationException::withMessages([
                'canonical_organization_id' => __('places.submissions.validation.organization'),
            ]);
        }
    }

    private function recordFacts(
        PlaceSubmission $submission,
        PlaceSubmissionRevision $revision,
        User $actor,
        SubmitPlaceData $data,
    ): void {
        $facts = [
            'name' => trim($data->name),
            'type' => $data->type->value,
            'catalog_category' => $data->catalogCategory,
            'summary' => $this->trimmed($data->summary),
            'public_region' => trim($data->publicRegion),
            'public_address' => $this->trimmed($data->publicAddress),
            'public_latitude' => $data->publicLatitude,
            'public_longitude' => $data->publicLongitude,
            'exact_address' => $this->trimmed($data->exactAddress),
            'exact_latitude' => $data->exactLatitude,
            'exact_longitude' => $data->exactLongitude,
            'public_phone' => $this->trimmed($data->publicPhone),
            'public_email' => $this->normalizer->email($data->publicEmail),
            'public_website' => $this->trimmed($data->publicWebsite),
            ...Arr::dot($data->facts),
        ];

        $sequence = 0;

        foreach ($facts as $field => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $encoded = is_scalar($value)
                ? (string) $value
                : (string) json_encode($value, JSON_THROW_ON_ERROR);
            $sequence++;

            PlaceFact::query()->create([
                'place_submission_id' => $submission->id,
                'place_submission_revision_id' => $revision->id,
                'submitted_by_user_id' => $actor->id,
                'stable_key' => $submission->stable_key.'-fact-'.$sequence,
                'field_key' => (string) $field,
                'field_value' => $encoded,
                'value_hash' => hash_hmac('sha256', $encoded, (string) config('app.key')),
                'source_kind' => $data->source,
                'source_reference' => $this->trimmed($data->sourceReference),
                'provenance_scope' => PlaceFactScope::Submitted,
                'visibility_scope' => Str::startsWith((string) $field, 'exact_') ? 'private' : 'review_only',
                'observed_at' => $data->observedAt,
                'created_at' => now(),
            ]);
        }
    }

    /** @return list<string> */
    private function openStatusValues(): array
    {
        return array_map(
            static fn (PlaceSubmissionStatus $status): string => $status->value,
            [
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::NeedsInformation,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::Approved,
            ],
        );
    }

    /** @return array<string, PlaceType> */
    private function categoryTypes(): array
    {
        return [
            'park' => PlaceType::Park,
            'dog-park' => PlaceType::Park,
            'route' => PlaceType::WalkingRoute,
            'vet' => PlaceType::VeterinaryClinic,
            'emergency-vet' => PlaceType::VeterinaryClinic,
            'pet-store' => PlaceType::PublicSpace,
            'grooming' => PlaceType::PublicSpace,
            'shelter' => PlaceType::Shelter,
            'pet-cafe' => PlaceType::PublicSpace,
        ];
    }

    /** @return array<string, string> */
    private function auditContext(): array
    {
        return array_filter([
            'request_id' => $this->request->attributes->getString('request_id'),
            'channel' => $this->request->attributes->getString('operation_channel', 'application'),
        ], static fn (string $value): bool => $value !== '');
    }

    /** @param array<string, mixed> $values */
    private function sortRecursively(array $values): array
    {
        ksort($values);

        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursively($value);
            }
        }

        return $values;
    }

    private function trimmed(?string $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
