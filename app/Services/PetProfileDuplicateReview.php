<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Collection;
use JsonException;

final readonly class PetProfileDuplicateReview
{
    private const CANDIDATE_LIMIT = 6;

    private const TOKEN_LIFETIME_SECONDS = 1800;

    public function __construct(
        private Encrypter $encrypter,
        private PetProfileAgeLabel $ageLabels,
        private PetSpeciesLabel $speciesLabels,
    ) {}

    /** @return array{candidates: list<array<string, string|null>>, token: string} */
    public function review(User $viewer, string $name, string $species): array
    {
        $candidates = $this->candidateModels($viewer, $name, $species, true);

        return [
            'candidates' => $this->cards($candidates),
            'token' => $this->issueToken($viewer, $name, $species, $candidates),
        ];
    }

    /** @return list<array<string, string|null>> */
    public function candidatesFromToken(
        User $viewer,
        string $name,
        string $species,
        string $token,
    ): array {
        $candidates = $this->candidateModels($viewer, $name, $species, true);

        return $this->tokenMatches($viewer, $name, $species, $token, $candidates)
            ? $this->cards($candidates)
            : [];
    }

    public function hasCompletedReview(
        User $viewer,
        string $name,
        string $species,
        string $reviewToken,
        string $decisionToken = '',
    ): bool {
        $candidates = $this->candidateModels($viewer, $name, $species);

        return $candidates->isEmpty()
            || (
                $this->tokenMatches(
                    $viewer,
                    $name,
                    $species,
                    $reviewToken,
                    $candidates,
                    'review',
                )
                && $this->tokenMatches(
                    $viewer,
                    $name,
                    $species,
                    $decisionToken,
                    $candidates,
                    'different-animal',
                )
            );
    }

    public function confirmDifferentAnimal(
        User $viewer,
        string $name,
        string $species,
        string $reviewToken,
    ): string {
        $candidates = $this->candidateModels($viewer, $name, $species);

        if ($candidates->isEmpty() || ! $this->tokenMatches(
            $viewer,
            $name,
            $species,
            $reviewToken,
            $candidates,
            'review',
        )) {
            return '';
        }

        return $this->issueToken($viewer, $name, $species, $candidates, 'different-animal');
    }

    public function candidateProfile(
        User $viewer,
        string $name,
        string $species,
        string $token,
        string $profileKey,
    ): ?PetProfile {
        $candidates = $this->candidateModels($viewer, $name, $species);

        if (! $this->tokenMatches($viewer, $name, $species, $token, $candidates)) {
            return null;
        }

        return $candidates->first(
            static fn (PetProfile $profile): bool => hash_equals($profile->profile_key, $profileKey),
        );
    }

    /** @return Collection<int, PetProfile> */
    private function candidateModels(
        User $viewer,
        string $name,
        string $species,
        bool $withMedia = false,
    ): Collection {
        $normalizedName = PetProfileDuplicateIdentity::normalizeName($name);

        if ($normalizedName === '' || ! in_array(
            $species,
            config('pet_profiles.species_options', []),
            true,
        )) {
            return new Collection;
        }

        $candidates = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'name',
                'species',
                'species_confidence',
                'birth_date',
                'birth_date_precision',
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
                'visibility',
                'status',
                'is_discoverable',
                'lock_version',
            ])
            ->where('species', $species)
            ->where('duplicate_name_hash', PetProfileDuplicateIdentity::nameHash($name))
            ->whereNull('canonical_profile_id')
            ->visibleTo($viewer)
            ->orderByDesc('id')
            ->limit(self::CANDIDATE_LIMIT)
            ->get()
            ->filter(
                fn (PetProfile $profile): bool => PetProfileDuplicateIdentity::normalizeName(
                    $profile->name,
                ) === $normalizedName,
            )
            ->take(self::CANDIDATE_LIMIT)
            ->values();

        if ($withMedia && $candidates->isNotEmpty()) {
            $candidates->load([
                'primaryMedia' => fn ($query) => $query->select([
                    'id',
                    'pet_profile_id',
                    'content_media_asset_id',
                    'media_key',
                    'role',
                    'status',
                    'current_key',
                ]),
                'primaryMedia.asset:id,alt_text',
            ]);
        }

        return $candidates;
    }

    /**
     * @param  Collection<int, PetProfile>  $candidates
     * @return list<array<string, string|null>>
     */
    private function cards(Collection $candidates): array
    {
        return $candidates
            ->map(function (PetProfile $profile): array {
                $media = $profile->primaryMedia;

                return [
                    'profile_key' => $profile->profile_key,
                    'name' => $profile->name,
                    'species' => $this->speciesLabels->for(
                        $profile->species,
                        $profile->species_confidence,
                    ),
                    'age' => $this->ageLabels->for($profile),
                    'photo' => $media instanceof PetProfileMedia
                        ? route('pets.media.show', [
                            'petProfile' => $profile->profile_key,
                            'petProfileMedia' => $media->media_key,
                        ])
                        : null,
                    'photo_alt' => $media instanceof PetProfileMedia
                        ? ($media->asset->alt_text
                            ?? __('pet_profiles.public.avatar_alt', ['name' => $profile->name]))
                        : __('pet_profiles.public.avatar_alt', ['name' => $profile->name]),
                ];
            })
            ->all();
    }

    /** @param Collection<int, PetProfile> $candidates */
    private function issueToken(
        User $viewer,
        string $name,
        string $species,
        Collection $candidates,
        string $purpose = 'review',
    ): string {
        try {
            $payload = json_encode([
                'viewer_id' => $viewer->id,
                'name' => PetProfileDuplicateIdentity::normalizeName($name),
                'species' => $species,
                'purpose' => $purpose,
                'candidate_keys' => $this->candidateKeys($candidates),
                'expires_at' => now()->addSeconds(self::TOKEN_LIFETIME_SECONDS)->getTimestamp(),
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '';
        }

        return $this->encrypter->encryptString($payload);
    }

    /** @param Collection<int, PetProfile> $candidates */
    private function tokenMatches(
        User $viewer,
        string $name,
        string $species,
        string $token,
        Collection $candidates,
        string $purpose = 'review',
    ): bool {
        if ($token === '') {
            return false;
        }

        try {
            $payload = json_decode(
                $this->encrypter->decryptString($token),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (DecryptException|JsonException) {
            return false;
        }

        return is_array($payload)
            && ($payload['viewer_id'] ?? null) === $viewer->id
            && ($payload['name'] ?? null) === PetProfileDuplicateIdentity::normalizeName($name)
            && ($payload['species'] ?? null) === $species
            && ($payload['purpose'] ?? null) === $purpose
            && ($payload['candidate_keys'] ?? null) === $this->candidateKeys($candidates)
            && is_int($payload['expires_at'] ?? null)
            && $payload['expires_at'] >= now()->getTimestamp();
    }

    /**
     * @param  Collection<int, PetProfile>  $candidates
     * @return list<string>
     */
    private function candidateKeys(Collection $candidates): array
    {
        return $candidates
            ->pluck('profile_key')
            ->filter(static fn (mixed $key): bool => is_string($key))
            ->values()
            ->all();
    }
}
