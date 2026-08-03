<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfileNameVisibility;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Enums\PetWorkspaceFilter;
use App\Enums\PetWorkspaceSort;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfileMedia;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;

final readonly class PetProfileWorkspace
{
    public function __construct(
        private Gate $gate,
        private LocaleFormatter $formatter,
        private PetProfileAgeLabel $ageLabels,
        private PetSpeciesLabel $speciesLabels,
        private PetProfileNameNormalizer $nameNormalizer,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function browse(User $user, array $parameters): array
    {
        $query = trim((string) ($parameters['q'] ?? ''));
        $filter = PetWorkspaceFilter::from((string) ($parameters['filter'] ?? PetWorkspaceFilter::All->value));
        $sort = PetWorkspaceSort::from((string) ($parameters['sort'] ?? PetWorkspaceSort::Recent->value));
        $at = now();

        $profiles = PetProfile::query()
            ->managedBy($user)
            ->select([
                'id',
                'user_id',
                'profile_key',
                'name',
                'species',
                'species_confidence',
                'breed',
                'birth_date',
                'birth_date_precision',
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
                'visibility',
                'status',
                'is_discoverable',
                'updated_at',
            ])
            ->with([
                'managers' => fn ($managers) => $managers
                    ->select([
                        'id',
                        'pet_profile_id',
                        'user_id',
                        'role',
                        'status',
                        'permission_overrides',
                        'starts_at',
                        'ends_at',
                        'revoked_at',
                    ])
                    ->where('user_id', $user->id)
                    ->activeAt($at),
                'primaryMedia' => fn ($media) => $media
                    ->select([
                        'id',
                        'media_key',
                        'pet_profile_id',
                        'content_media_asset_id',
                    ])
                    ->with('asset:id,alt_text'),
            ]);

        $this->applyFilter($profiles, $filter, $user);
        $this->applySearch($profiles, $query, $user);
        $this->applySort($profiles, $sort);

        $pets = $profiles
            ->paginate(12)
            ->withQueryString()
            ->through(fn (PetProfile $profile): array => $this->card($profile, $user));
        $invitationCount = PetProfileManager::query()
            ->where('user_id', $user->id)
            ->where('status', PetManagerStatus::Invited)
            ->whereNull('revoked_at')
            ->where(function (Builder $expiry) use ($at): void {
                $expiry->whereNull('ends_at')->orWhere('ends_at', '>', $at);
            })
            ->count();

        return [
            'summary' => [
                'eyebrow' => __('pet_workspace.eyebrow'),
                'title' => __('pet_workspace.title'),
                'description' => __('pet_workspace.description'),
                'count' => trans_choice('pet_workspace.profile_count', $pets->total(), [
                    'count' => $this->formatter->number($pets->total()),
                ]),
            ],
            'pets' => $pets,
            'query' => $query,
            'activeFilter' => $filter->value,
            'activeSort' => $sort->value,
            'filters' => collect(PetWorkspaceFilter::cases())
                ->map(static fn (PetWorkspaceFilter $option): array => [
                    'value' => $option->value,
                    'label' => $option->label(),
                ])
                ->all(),
            'sortOptions' => collect(PetWorkspaceSort::cases())
                ->mapWithKeys(static fn (PetWorkspaceSort $option): array => [
                    $option->value => $option->label(),
                ])
                ->all(),
            'invitationCount' => $invitationCount,
            'invitationTitle' => trans_choice('pet_workspace.invitation_count', $invitationCount, [
                'count' => $this->formatter->number($invitationCount),
            ]),
            'isFiltered' => $query !== '' || $filter !== PetWorkspaceFilter::All,
        ];
    }

    private function applyFilter(
        Builder $profiles,
        PetWorkspaceFilter $filter,
        User $user,
    ): void {
        match ($filter) {
            PetWorkspaceFilter::Owned => $profiles->where('user_id', $user->id),
            PetWorkspaceFilter::Shared => $profiles->where('user_id', '!=', $user->id),
            PetWorkspaceFilter::Drafts => $profiles->where('status', PetProfileStatus::Draft->value),
            PetWorkspaceFilter::Discoverable => $profiles
                ->where('visibility', PetProfileVisibility::Public->value)
                ->where('is_discoverable', true)
                ->whereIn('status', collect(PetProfileStatus::cases())
                    ->filter(static fn (PetProfileStatus $status): bool => $status->isPubliclyEligible())
                    ->map(static fn (PetProfileStatus $status): string => $status->value)
                    ->all()),
            PetWorkspaceFilter::All => null,
        };
    }

    private function applySearch(Builder $profiles, string $query, User $user): void
    {
        if ($query === '') {
            return;
        }

        $profiles->where(function (Builder $search) use ($query, $user): void {
            $like = "%{$query}%";
            $normalized = $this->nameNormalizer->normalize($query);
            $search
                ->where('name', 'like', $like)
                ->orWhere('species', 'like', $like)
                ->orWhere('breed', 'like', $like)
                ->orWhereHas('names', static function (Builder $names) use ($normalized, $user): void {
                    $names
                        ->where('is_searchable', true)
                        ->where('normalized_name', 'like', $normalized.'%')
                        ->where(static function (Builder $visibility) use ($user): void {
                            $visibility
                                ->whereIn('visibility', [
                                    PetProfileNameVisibility::Managers->value,
                                    PetProfileNameVisibility::Public->value,
                                ])
                                ->orWhere('recorded_by_user_id', $user->id);
                        });
                });
        });
    }

    private function applySort(Builder $profiles, PetWorkspaceSort $sort): void
    {
        match ($sort) {
            PetWorkspaceSort::Name => $profiles->orderBy('name')->orderBy('id'),
            PetWorkspaceSort::Status => $profiles->orderBy('status')->orderBy('name')->orderBy('id'),
            PetWorkspaceSort::Recent => $profiles->orderByDesc('updated_at')->orderByDesc('id'),
        };
    }

    /** @return array<string, mixed> */
    private function card(PetProfile $profile, User $user): array
    {
        $manager = $profile->managers->first();
        $role = $manager instanceof PetProfileManager
            ? $manager->role
            : ($profile->user_id === $user->id ? PetManagerRole::PrimaryOwner : PetManagerRole::Other);
        $canManage = $this->gate->forUser($user)->allows('update', $profile);
        $managementUrl = $canManage
            ? route('pets.manage.show', ['petProfile' => $profile->profile_key])
            : null;
        $profileUrl = route('pets.profile', ['petProfile' => $profile->profile_key]);
        $primaryUrl = $managementUrl ?? $profileUrl;
        $media = $profile->primaryMedia;
        $details = array_values(array_filter([
            $this->speciesLabels->for($profile->species, $profile->species_confidence),
            $profile->breed,
            $this->ageLabels->for($profile),
        ]));

        return [
            'context' => 'workspace',
            'key' => $profile->profile_key,
            'name' => $profile->name,
            'species' => $this->speciesLabels->for(
                $profile->species,
                $profile->species_confidence,
            ),
            'breed' => $profile->breed,
            'age' => $this->ageLabels->for($profile),
            'status' => $profile->status->label(),
            'status_tone' => $this->statusTone($profile->status),
            'status_icon' => $this->statusIcon($profile->status),
            'relationship' => $role->label(),
            'visibility' => PetProfileVisibility::fromStored($profile->visibility)->label(),
            'details' => $details === []
                ? __('pet_workspace.details_unavailable')
                : implode(' · ', $details),
            'discoverability' => $profile->is_discoverable
                ? __('pet_workspace.discoverable')
                : __('pet_workspace.not_discoverable'),
            'updated' => __('pet_workspace.updated', [
                'time' => $this->formatter->relative($profile->updated_at) ?? __('pet_workspace.updated_unknown'),
            ]),
            'image' => $media instanceof PetProfileMedia
                ? route('pets.media.show', [
                    'petProfile' => $profile->profile_key,
                    'petProfileMedia' => $media->media_key,
                ])
                : null,
            'image_alt' => $media instanceof PetProfileMedia
                ? ($media->asset->alt_text ?? __('pet_profiles.public.avatar_alt', ['name' => $profile->name]))
                : __('pet_profiles.public.avatar_alt', ['name' => $profile->name]),
            'media_target' => [
                'url' => $primaryUrl,
                'label' => __('pet_workspace.open_workspace', ['name' => $profile->name]),
            ],
            'management_url' => $managementUrl,
            'profile_url' => $profileUrl,
        ];
    }

    private function statusTone(PetProfileStatus $status): string
    {
        return match ($status) {
            PetProfileStatus::Active,
            PetProfileStatus::Found => 'mint',
            PetProfileStatus::Lost,
            PetProfileStatus::DisputedOwnership,
            PetProfileStatus::DeletionPending => 'sun',
            PetProfileStatus::Draft,
            PetProfileStatus::Hidden,
            PetProfileStatus::Archived,
            PetProfileStatus::Merged,
            PetProfileStatus::Transferred => 'surface',
            default => 'ink',
        };
    }

    private function statusIcon(PetProfileStatus $status): string
    {
        return match ($status) {
            PetProfileStatus::Lost => 'siren',
            PetProfileStatus::Draft => 'pencil-line',
            PetProfileStatus::Hidden => 'eye-off',
            PetProfileStatus::Archived,
            PetProfileStatus::Merged => 'archive',
            PetProfileStatus::Memorial => 'heart',
            default => 'circle-check',
        };
    }
}
