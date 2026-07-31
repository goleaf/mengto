<?php

declare(strict_types=1);

namespace App\Livewire\Social;

use App\Actions\CancelSocialRelationshipRequest;
use App\Actions\CreateSocialControl;
use App\Actions\EndSocialRelationship;
use App\Actions\FollowSocialActor;
use App\Actions\RespondToSocialRelationshipRequest;
use App\Actions\SendSocialRelationshipRequest;
use App\Actions\UpdateSocialActorSettings;
use App\Enums\SocialActorType;
use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Livewire\Forms\SocialActorSettingsForm;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\ProfilePresenter;
use App\Services\SocialActorDirectory;
use App\Services\SocialActorPresenter;
use App\Services\SocialActorResolver;
use App\Services\SocialGraphQuery;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class RelationshipCenter extends Component
{
    public SocialActorSettingsForm $settingsForm;

    public string $selectedActorKey = '';

    public string $feedback = '';

    public string $actorSearch = '';

    private AuthFactory $auth;

    private SocialActorResolver $actors;

    private SocialActorPresenter $presenter;

    private SocialGraphQuery $graph;

    private SocialActorDirectory $directory;

    private FollowSocialActor $follow;

    private SendSocialRelationshipRequest $sendRequest;

    private RespondToSocialRelationshipRequest $respond;

    private CancelSocialRelationshipRequest $cancel;

    private EndSocialRelationship $end;

    private CreateSocialControl $control;

    private UpdateSocialActorSettings $updateSettings;

    private ProfilePresenter $profiles;

    private ?SocialActor $resolvedActor = null;

    public function boot(
        AuthFactory $auth,
        SocialActorResolver $actors,
        SocialActorPresenter $presenter,
        SocialGraphQuery $graph,
        SocialActorDirectory $directory,
        FollowSocialActor $follow,
        SendSocialRelationshipRequest $sendRequest,
        RespondToSocialRelationshipRequest $respond,
        CancelSocialRelationshipRequest $cancel,
        EndSocialRelationship $end,
        CreateSocialControl $control,
        UpdateSocialActorSettings $updateSettings,
        ProfilePresenter $profiles,
    ): void {
        $this->auth = $auth;
        $this->actors = $actors;
        $this->presenter = $presenter;
        $this->graph = $graph;
        $this->directory = $directory;
        $this->follow = $follow;
        $this->sendRequest = $sendRequest;
        $this->respond = $respond;
        $this->cancel = $cancel;
        $this->end = $end;
        $this->control = $control;
        $this->updateSettings = $updateSettings;
        $this->profiles = $profiles;
    }

    public function mount(): void
    {
        $available = $this->actors->controlledBy($this->requireUser());
        abort_if($available->isEmpty(), 403);

        $this->selectedActorKey = $available->firstOrFail()->actor_key;
        $this->settingsForm->fillFrom($this->currentActor()->settings()->firstOrFail());
    }

    /** @return list<array{key: string, name: string, type: string, type_label: string}> */
    #[Computed]
    public function availableActors(): array
    {
        return $this->actors
            ->controlledBy($this->requireUser())
            ->map(fn (SocialActor $actor): array => $this->presenter->present($actor))
            ->values()
            ->all();
    }

    /** @return array{relationships: int, incoming_requests: int, outgoing_requests: int} */
    #[Computed]
    public function counts(): array
    {
        return $this->graph->counts($this->currentActor(), $this->requireUser());
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function directoryResults(): array
    {
        return $this->directory->search(
            $this->currentActor(),
            $this->requireUser(),
            $this->actorSearch,
        );
    }

    #[Computed]
    public function directorySearchReady(): bool
    {
        return mb_strlen(trim($this->actorSearch)) >= 2;
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function inbox(): array
    {
        return $this->graph
            ->inbox($this->currentActor(), $this->requireUser())
            ->map(fn (SocialRelationshipRequest $request): array => [
                'key' => $request->request_key,
                'actor' => $this->presenter->present($request->sourceActor),
                'type' => $request->relationship_type->label(),
                'status' => $request->status->label(),
                'sent_at' => $request->sent_at->toDateTimeString(),
            ])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function outbox(): array
    {
        return $this->graph
            ->outbox($this->currentActor(), $this->requireUser())
            ->map(fn (SocialRelationshipRequest $request): array => [
                'key' => $request->request_key,
                'actor' => $this->presenter->present($request->targetActor),
                'type' => $request->relationship_type->label(),
                'status' => $request->status->label(),
                'sent_at' => $request->sent_at->toDateTimeString(),
            ])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function relationships(): array
    {
        $currentActor = $this->currentActor();

        return $this->graph
            ->relationships($currentActor, $this->requireUser())
            ->map(function (SocialRelationship $relationship) use ($currentActor): array {
                $other = $relationship->source_actor_id === $currentActor->id
                    ? $relationship->targetActor
                    : $relationship->sourceActor;

                return [
                    'key' => $relationship->relationship_key,
                    'actor' => $this->presenter->present($other),
                    'type' => $relationship->relationship_type->label(),
                    'type_key' => $relationship->relationship_type->value,
                    'status' => $relationship->status->label(),
                    'started_at' => $relationship->started_at->toDateTimeString(),
                    'can_control' => ! in_array($relationship->relationship_type, [
                        SocialRelationshipType::Block,
                        SocialRelationshipType::Restrict,
                        SocialRelationshipType::Mute,
                    ], true),
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function friendRequestPolicies(): array
    {
        return collect(SocialFriendRequestPolicy::enforceableCases())
            ->mapWithKeys(static fn (SocialFriendRequestPolicy $policy): array => [$policy->value => $policy->label()])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function followPolicies(): array
    {
        return collect(SocialFollowPolicy::cases())
            ->mapWithKeys(static fn (SocialFollowPolicy $policy): array => [$policy->value => $policy->label()])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function listVisibilityOptions(): array
    {
        return collect(SocialListVisibility::cases())
            ->mapWithKeys(static fn (SocialListVisibility $visibility): array => [$visibility->value => $visibility->label()])
            ->all();
    }

    public function selectActor(string $actorKey): void
    {
        abort_unless(Str::isUuid($actorKey), 404);
        $this->selectedActorKey = $actorKey;
        $this->resolvedActor = null;
        $actor = $this->currentActor();
        $this->settingsForm->fillFrom($actor->settings()->firstOrFail());
        $this->feedback = '';
        $this->actorSearch = '';
        $this->resetGraphComputeds();
    }

    public function updatedActorSearch(): void
    {
        unset($this->directoryResults);
    }

    public function followActor(string $actorKey): void
    {
        $source = $this->currentActor();
        $target = $this->targetActor($actorKey);
        $this->follow->handle(
            $source,
            $target,
            'livewire:follow:'.Str::uuid(),
        );
        $this->feedback = __('social_relationships.feedback.followed');
        $this->resetGraphComputeds();
    }

    public function requestFriendship(string $actorKey): void
    {
        $source = $this->currentActor();
        $target = $this->targetActor($actorKey);
        $type = match ([$source->actor_type, $target->actor_type]) {
            [SocialActorType::User, SocialActorType::User] => SocialRelationshipType::OwnerFriendship,
            [SocialActorType::Pet, SocialActorType::Pet] => SocialRelationshipType::PetFriendship,
            default => throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.friendship_type_unavailable'),
            ]),
        };
        $this->sendRequest->handle(
            $source,
            $target,
            $type,
            'livewire:friendship:'.Str::uuid(),
        );
        $this->feedback = __('social_relationships.feedback.request_sent');
        $this->resetGraphComputeds();
    }

    public function accept(string $requestKey): void
    {
        $request = $this->request($requestKey);
        $this->respond->handle(
            $request,
            SocialRequestStatus::Accepted,
            "livewire:accept:{$request->request_key}",
        );
        $this->feedback = __('social_relationships.feedback.accepted');
        $this->resetGraphComputeds();
    }

    public function decline(string $requestKey): void
    {
        $request = $this->request($requestKey);
        $this->respond->handle(
            $request,
            SocialRequestStatus::Declined,
            "livewire:decline:{$request->request_key}",
        );
        $this->feedback = __('social_relationships.feedback.declined');
        $this->resetGraphComputeds();
    }

    public function cancelRequest(string $requestKey): void
    {
        $request = $this->request($requestKey);
        $this->cancel->handle($request, "livewire:cancel:{$request->request_key}");
        $this->feedback = __('social_relationships.feedback.cancelled');
        $this->resetGraphComputeds();
    }

    public function endRelationship(string $relationshipKey): void
    {
        $relationship = $this->relationship($relationshipKey);
        $this->end->handle($relationship, "livewire:end:{$relationship->relationship_key}");
        $this->feedback = __('social_relationships.feedback.ended');
        $this->resetGraphComputeds();
    }

    public function applyControl(string $relationshipKey, string $control): void
    {
        $type = SocialRelationshipType::tryFrom($control);
        abort_unless(in_array($type, [
            SocialRelationshipType::Block,
            SocialRelationshipType::Restrict,
            SocialRelationshipType::Mute,
        ], true), 404);
        $relationship = $this->relationship($relationshipKey);
        $currentActor = $this->currentActor();
        if ($currentActor->id !== $relationship->source_actor_id
            && $currentActor->id !== $relationship->target_actor_id) {
            abort(403);
        }
        $target = $relationship->source_actor_id === $currentActor->id
            ? $relationship->targetActor
            : $relationship->sourceActor;

        $this->control->handle(
            $currentActor,
            $target,
            $type,
            "livewire:{$type->value}:{$currentActor->actor_key}:{$target->actor_key}",
        );
        $this->feedback = __('social_relationships.feedback.control_created');
        $this->resetGraphComputeds();
    }

    public function saveSettings(): void
    {
        $this->settingsForm->validate();
        $settings = $this->updateSettings->handle(
            actor: $this->currentActor(),
            friendRequestPolicy: SocialFriendRequestPolicy::from($this->settingsForm->friendRequestPolicy),
            followPolicy: SocialFollowPolicy::from($this->settingsForm->followPolicy),
            friendListVisibility: SocialListVisibility::from($this->settingsForm->friendListVisibility),
            followerListVisibility: SocialListVisibility::from($this->settingsForm->followerListVisibility),
            isRecommendable: $this->settingsForm->isRecommendable,
            allowMessageRequests: $this->settingsForm->allowMessageRequests,
            expectedLockVersion: $this->settingsForm->lockVersion,
        );
        $this->settingsForm->fillFrom($settings);
        $this->feedback = __('social_relationships.feedback.settings_saved');
    }

    public function render(): View
    {
        return view('livewire.social.relationship-center')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('social_relationships.title'),
                'activeSection' => 'circle',
            ]);
    }

    private function currentActor(): SocialActor
    {
        if ($this->resolvedActor instanceof SocialActor) {
            return $this->resolvedActor;
        }

        $actor = $this->actors
            ->controlledBy($this->requireUser())
            ->firstWhere('actor_key', $this->selectedActorKey);

        abort_unless($actor instanceof SocialActor, 403);

        return $this->resolvedActor = $actor;
    }

    private function request(string $requestKey): SocialRelationshipRequest
    {
        abort_unless(Str::isUuid($requestKey), 404);

        return SocialRelationshipRequest::query()
            ->where('request_key', $requestKey)
            ->firstOrFail();
    }

    private function relationship(string $relationshipKey): SocialRelationship
    {
        abort_unless(Str::isUuid($relationshipKey), 404);

        return SocialRelationship::query()
            ->with(['sourceActor', 'targetActor'])
            ->where('relationship_key', $relationshipKey)
            ->firstOrFail();
    }

    private function targetActor(string $actorKey): SocialActor
    {
        abort_unless(Str::isUuid($actorKey), 404);

        return SocialActor::query()
            ->directoryFields()
            ->where('actor_key', $actorKey)
            ->firstOrFail();
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }

    private function resetGraphComputeds(): void
    {
        unset(
            $this->availableActors,
            $this->counts,
            $this->directoryResults,
            $this->directorySearchReady,
            $this->inbox,
            $this->outbox,
            $this->relationships,
        );
    }
}
