<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CreateForumGroup;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupVisibility;
use App\Livewire\Forms\ForumGroupForm;
use App\Models\ForumGroup;
use App\Models\ForumGroupInvitation;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class GroupDirectory extends Component
{
    use WithPagination;

    public ForumGroupForm $form;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $visibility = 'all';

    public string $feedback = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedVisibility(): void
    {
        $this->resetPage();
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function groups(): LengthAwarePaginator
    {
        $filters = $this->validatedFilters();
        $user = $this->requireUser();
        $query = ForumGroup::query()
            ->discoverableTo($user)
            ->select([
                'id',
                'owner_user_id',
                'stable_key',
                'is_system_managed',
                'name',
                'name_translation_key',
                'description',
                'description_translation_key',
                'visibility',
                'status',
                'default_locale',
                'location_scope',
                'active_member_count',
                'updated_at',
            ])
            ->with([
                'owner:id,name',
                'taxa:id,stable_key',
                'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
                'memberships' => function (Relation $membershipQuery) use ($user): void {
                    $membershipQuery->select([
                        'id',
                        'forum_group_id',
                        'user_id',
                        'role',
                        'state',
                        'lock_version',
                    ])
                        ->where('user_id', $user->id);
                },
            ]);

        if ($filters['search'] !== '') {
            $query->where(function (Builder $searchQuery) use ($filters): void {
                $searchQuery
                    ->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%')
                    ->orWhere('location_scope', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('taxa.activeVersion', fn (Builder $taxonQuery): Builder => $taxonQuery
                        ->where('scientific_name', 'like', '%'.$filters['search'].'%'));
            });
        }

        match ($filters['visibility']) {
            ForumGroupVisibility::Public->value,
            ForumGroupVisibility::RequestToJoin->value => $query
                ->where('visibility', $filters['visibility']),
            'joined' => $query->whereHas(
                'memberships',
                fn (Builder $membershipQuery): Builder => $membershipQuery
                    ->where('user_id', $user->id)
                    ->where('state', ForumGroupMembershipState::Active->value),
            ),
            default => null,
        };

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->through(fn (ForumGroup $group): array => $this->presentGroup($group, $user));
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function invitations(): array
    {
        return ForumGroupInvitation::query()
            ->select([
                'id',
                'forum_group_id',
                'invited_user_id',
                'invited_by_user_id',
                'role',
                'state',
                'message',
                'expires_at',
            ])
            ->where('invited_user_id', $this->requireUser()->id)
            ->where('state', 'pending')
            ->where('expires_at', '>', now())
            ->with([
                'group:id,stable_key,name,name_translation_key,visibility,status',
                'inviter:id,name',
            ])
            ->orderBy('expires_at')
            ->limit(20)
            ->get()
            ->map(static fn (ForumGroupInvitation $invitation): array => [
                'id' => $invitation->id,
                'group_name' => $invitation->group->displayName(),
                'group_url' => route('groups.show', $invitation->group),
                'inviter_name' => $invitation->inviter->name,
                'role' => $invitation->role->label(),
                'message' => $invitation->message,
                'expires_at' => $invitation->expires_at->isoFormat('LLL'),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function visibilityOptions(): array
    {
        return [
            'all' => __('forum_groups.filters.all'),
            ForumGroupVisibility::Public->value => ForumGroupVisibility::Public->label(),
            ForumGroupVisibility::RequestToJoin->value => ForumGroupVisibility::RequestToJoin->label(),
            'joined' => __('forum_groups.filters.joined'),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function creationVisibilityOptions(): array
    {
        return collect(ForumGroupVisibility::cases())
            ->mapWithKeys(static fn (ForumGroupVisibility $visibility): array => [
                $visibility->value => $visibility->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __('forum_groups.locales.'.$locale),
            ])
            ->all();
    }

    public function create(CreateForumGroup $createGroup): void
    {
        Gate::authorize('create', ForumGroup::class);
        $group = $createGroup->handle($this->requireUser(), $this->form->data());
        $this->form->reset();
        $this->redirectRoute('groups.show', $group, navigate: true);
    }

    public function render()
    {
        return view('livewire.forum.group-directory');
    }

    /** @return array{search: string, visibility: string} */
    private function validatedFilters(): array
    {
        $validator = validator([
            'search' => trim($this->search),
            'visibility' => $this->visibility,
        ], [
            'search' => ['nullable', 'string', 'max:120'],
            'visibility' => [
                'required',
                Rule::in([
                    'all',
                    'joined',
                    ForumGroupVisibility::Public->value,
                    ForumGroupVisibility::RequestToJoin->value,
                ]),
            ],
        ]);

        if ($validator->fails()) {
            $this->search = '';
            $this->visibility = 'all';

            return ['search' => '', 'visibility' => 'all'];
        }

        /** @var array{search: string|null, visibility: string} $validated */
        $validated = $validator->validated();

        return [
            'search' => (string) ($validated['search'] ?? ''),
            'visibility' => $validated['visibility'],
        ];
    }

    /** @return array<string, mixed> */
    private function presentGroup(ForumGroup $group, User $user): array
    {
        $membership = $group->memberships->first();
        $owner = $group->getRelation('owner');

        return [
            'id' => $group->id,
            'stable_key' => $group->stable_key,
            'name' => $group->displayName(),
            'description' => $group->displayDescription(),
            'visibility' => $group->visibility->label(),
            'visibility_key' => $group->visibility->value,
            'status' => $group->status->label(),
            'location_scope' => $group->location_scope,
            'member_count' => $group->active_member_count,
            'owner_name' => $owner instanceof User ? $owner->name : null,
            'taxa' => $group->taxa
                ->map($this->presentTaxonName(...))
                ->filter()
                ->implode(', '),
            'is_member' => $membership?->state === ForumGroupMembershipState::Active,
            'membership_state' => $membership?->state->label(),
            'url' => route('groups.show', $group),
            'owned_by_user' => $group->owner_user_id === $user->id,
        ];
    }

    private function presentTaxonName(Taxon $taxon): ?string
    {
        $activeVersion = $taxon->getRelation('activeVersion');

        return $activeVersion instanceof TaxonVersion
            ? $activeVersion->scientific_name
            : null;
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
