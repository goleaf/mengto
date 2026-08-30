# Livewire 4

## Repository Rules

- Stable Livewire 4.3 or newer compatible 4.x.
- Class-based components in `app/Livewire`.
- Separate templates in `resources/views/livewire`.
- No Volt, single-file, or anonymous PHP components.
- Actions validate and authorize on every request.
- Public state is small, typed, non-sensitive, and browser-untrusted.
- Domain operations stay in Actions/Services.

## Feature Applicability Matrix

| Feature | Candidate | Decision | Performance / accessibility | Test evidence |
| --- | --- | --- | --- | --- |
| `#[Computed]` | Administration, guide, mentorship, and group derived data | Used by bounded registries and group/guide/mentorship projections | Derived collections are not serialized as mutable public state | Guide, mentorship, and group workflow tests |
| `#[Locked]` | Password reset token | Used by `ResetPassword` | Prevents browser mutation; token still validated server-side | `tests/Feature/Auth/AuthenticationTest.php` |
| Livewire form objects | Registration/login/reset/confirmation/profile-preference forms | Used | Central validation and field errors | `tests/Feature/Auth/AuthenticationTest.php` |
| `#[Url]` | Shareable feed/directory filters | Used for group search/visibility; later for other converted components | Keeps linkable state; never secrets; resets pagination | `GroupCoreWorkflowTest` |
| `#[Session]` | Private UI preference | Not initially required | Avoid duplicating business storage | N/A reason recorded |
| `#[Lazy]` | Below-fold expensive dashboard | Not until measured candidate exists | Must have stable placeholder | N/A |
| `#[Defer]` | Non-critical first-view aggregate | Not until measured candidate exists | Accessible immediate-after-load state | N/A |
| `#[Isolate]` | Slow independent widget | Not until measured candidate exists | Prevent unrelated blocking | N/A |
| `#[Async]` | Idempotent independent operation | Not for auth or critical mutations | Race-prone state prohibited | N/A |
| `#[Js]` | Pure local UI command | Prefer existing Alpine/JS modules | No server round trip | Boundary test |
| `#[Json]` | Explicit JS data endpoint | Not currently required | Never raw models | N/A |
| `#[Renderless]` | Action with no DOM change | Avoid unless measured | Feedback still required | N/A |
| `#[On]` | Narrow parent/child event | Use only with explicit payload | Avoid global event bus | Receiver auth tests |
| Reactive/modelable props | Reusable animal selector | `#[Modelable]` used for one selected taxon ID | The child paginates/searches server-side and never serializes the taxonomy tree | taxonomy and guide Livewire tests |
| Islands | Large independently updating page | Not until profiling identifies one | No islands in loops/conditions | N/A |
| `wire:navigate` | Authentication navigation | Not used for account-access links or redirects | Full-document navigation prevents duplicate Vite preload insertion while forms remain Livewire-driven | `AuthenticationTest` plus connected registration-to-verification browser check |
| `wire:loading` / `wire:target` | Every auth and profile-preference mutation | Used | Precise status and duplicate prevention | `tests/Feature/Auth/AuthenticationTest.php` |
| `wire:dirty` | Registration, password reset, and profile preferences | Used | Communicates unsaved state | `tests/Feature/Auth/AuthenticationTest.php` |
| `wire:offline` | Authentication shell | Used | Do not claim save offline | `tests/Feature/Auth/AuthenticationTest.php` |
| `wire:cloak` | Initially hidden interactive state | Use where flicker exists | CSS support required | Render test |
| `wire:confirm` | Clear destructive action | Use when suitable | Localized; not authorization | Direct invocation test |
| `wire:sort` | Authorized ordering | Not currently required | Needs keyboard alternative and transaction | N/A |
| `wire:stream` | Progressive generated content | Not currently required | Interrupted completion state required | N/A |
| `wire:poll` | Device status | Later only if measured | Hidden throttling and stale labels | Poll tests |
| `wire:intersect` | Viewport loading | Not initially required | Must not be sole access path | N/A |
| `wire:ignore` | Map/media DOM | Candidate | Explicit init/teardown/sync | Browser lifecycle test |
| `@persist` | Media player across navigation | Not currently required | Never persist sensitive server data | N/A |
| `@teleport` | Accessible modal surface | Candidate | Preserve focus trap/restoration | Browser test |

## Form Rules

- Normalize before validation only when semantics are preserved.
- Use localized labels and messages.
- Store only validated explicit fields.
- Disable only the target action while loading.
- Preserve input after recoverable validation.
- Move focus to the error summary or first invalid field.

## Testing

Use `Livewire::test()` for mount authorization, initial state, form validation,
locked/tampered IDs, direct action authorization, repeated submit, dispatched
events, redirects, and rendered loading/dirty/offline states.

## Profile Preferences

`ProfileSettings` is a protected class-based component with a separate Blade
template and `ProfilePreferencesForm`. Registration never serializes language
or time-zone input. The settings component derives bounded locale and time-zone
options server-side, validates both browser-controlled values, and delegates
the current-user-only mutation to `UpdateProfilePreferences`. Saving the locale
also updates the current auth-guard model, session and application locale so
the translated response is immediate. `ProfilePreferenceRules` is the one
neutral validation/message/attribute source used by both the form object and
Action: configured locale codes are exact, and timezones use Laravel's
`timezone:all` IANA validation.
The component also locks the account ID captured at mount and checks it on
hydration and mutation. A signed Profile Settings snapshot cannot cross an
authentication change; older snapshots without the binding fail closed and
must refresh.

## Resumable Onboarding Wizard

`App\Livewire\Onboarding` remains the single class-based component for
`GET /onboarding`. It renders through the focused onboarding layout and asks
the persisted `UserOnboarding` aggregate for the current screen; no route
segment, integer counter, Alpine value, or writable browser step determines
progress.

The component composes the canonical `ProfilePreferencesForm` and
`OnboardingPrivacyForm` plus the controlled `OnboardingPetChoiceForm`.
Mutations call the existing transition Actions with locked step and version
snapshots, reload canonical state after success, and redirect a stale tab to
fresh progress without applying old input. The mount account ID is locked and
rechecked against the authenticated user on every hydration so a signed
snapshot cannot cross accounts.

The Preferences mutation delegates persistence to the same
`UpdateProfilePreferences` Action as normal settings, then advances through
`CompleteOnboardingPreferences`. A successful locale change synchronizes the
fresh authenticated model before redirecting, so `SetLocale` renders the pet
step in the selected language without a refresh. Exact two-tab replays are
idempotent; changed or malformed stale payloads and freshly inactive accounts
are rejected without moving progress.

The view exposes one H1, one semantic ordered progress list, native
fieldset/legend and input controls, associated validation errors, a focusable
error summary, step-heading focus after successful movement, and
action-specific loading, dirty, and offline status. The state graph is
currently forward-only, so no decorative or client-only Back mutation is
rendered. Pet creation remains the canonical duplicate-aware subflow; the
wizard never creates a fake pet or accepts a return URL.

## Collaborative Guide Editor

`KnowledgeGuideEditor` is a normal class component with
`KnowledgeGuideForm`. Its public state is limited to IDs, form fields,
transition input, lock versions, and short action controls. `articleId` is
locked, but policies remain authoritative. The component composes the reusable
modelable taxonomy selector and uses action-specific `wire:target` loading
states. See `docs/guides.md`.

## Community Notes Panel

`CommunityNotesPanel` uses a locked topic ID, typed scalar form state,
computed bounded projections, eager-loaded panel state, action-specific
loading targets, offline feedback, and direct server-side authorization for
every mutation. It never stores models, evidence graphs, or reviewer lists in
public state. Numeric action arguments are untrusted and resolved again by the
Action layer. See `docs/community-review.md`.

## Peer Mentorship Components

`MentorDiscovery`, `MentorshipInbox`, and `MentorProfileManager` are separate
class-based components composed by `/forum/mentorship`. They use form objects,
computed bounded projections, locked idempotency identity, precise loading
targets, offline/status feedback, and the modelable taxonomy selector.

Tampered filter state returns an empty computed projection and fails explicit
validation without causing a render exception. Mentorship, scope, message, and
report IDs are always resolved and authorized again server-side. The first
render has a tested budget of at most 45 queries. See `docs/mentorship.md`.

## Persistent Group Components

`GroupDirectory`, `GroupWorkspace`, and `GroupManagement` use separate Blade
templates, typed scalar state, a `ForumGroupForm`, computed bounded
projections, precise loading/offline targets, and direct Action invocation.
Search and visibility use stable URL state. Group IDs and action arguments are
treated as untrusted even when rendered by the server.

Eager loads select only fields required by presentation; owner and taxonomy
relations are read from loaded relation values so Blade cannot trigger lazy
queries. The group package has a focused query-budget and direct-action
authorization suite. See `docs/groups.md`.

## Group Content And Poll Component

`GroupContentWorkspace` is a normal class-based component with separate
`ForumGroupActivityForm`, `ForumGroupAnnouncementForm`,
`ForumGroupAssociationForm`, and `ForumPollForm` objects. Public state is
limited to a locked group ID, idempotency tokens, bounded form fields, one
temporary upload, and current ballot selections. Models, memberships, voter
identities, files, and policy results remain server-side.

The component uses computed bounded presentation data, precise
`wire:loading`/`wire:target` feedback, offline status, stable database-backed
keys, native fieldsets, and server-authorized actions. Its first-render query
budget is constant between one and ten polls; poll eligibility consumes the
already authorized and loaded membership instead of issuing a policy query per
poll. See `docs/polls.md`.

## Forum Journal Components

`ForumJournalDirectory` and `ForumJournalTimeline` are normal class-based,
multi-file Livewire components. Their separate form objects own normalized
creation, entry, collaborator, comment, and media input.

Public state is limited to locked scalar identity, stable URL filters,
idempotency tokens, bounded form values, and one temporary upload. Models,
query builders, policies, files, paths, historical versions, and full
relationship graphs remain server-side. Computed methods resolve their
dependencies through the container because Livewire invokes computed methods
without arbitrary service arguments.

Each mutation delegates to an Action that reloads and authorizes the subject.
Directory/timeline queries paginate or cap their results, select presentation
columns, eager load constrained relations, and prepare passive Blade arrays.
See `docs/journals.md`.

## Expert Session Components

`ForumExpertSessionDirectory` and `ForumExpertSessionWorkspace` use locked
scalar identity, URL-backed bounded discovery, separate session/question/
answer/correction/moderation/report forms, and prepared presentation arrays.
Pending queue rows are policy-filtered before rendering. Credential evidence,
models, service instances, and protected moderation data never enter public
component state. See `docs/expert-question-sessions.md`.

## Organization Components

`OrganizationDirectory`, `OrganizationWorkspace`, and
`OrganizationInvitationResponse` are class-based components with separate
Blade templates and form objects. Public state contains bounded form values,
feedback, idempotency values, and locked numeric identity only. The raw
invitation token is never a public property; signed-route verification is
account-bound and only an encrypted temporary session value survives until
the single response mutation.

Directory and workspace data are tenant-scoped, explicitly selected,
paginated/capped, eager loaded, and converted to prepared arrays before
Blade. Every mutation delegates to an Action that reloads and authorizes its
subject. Ordinary members receive neither email nor internal restriction
reason projections.

## Topic Lifecycle Panel

`ForumTopicLifecyclePanel` is a normal class-based component with a separate
`ForumTopicLifecycleForm` and passive Blade view. Its locked topic ID and
optimistic version are hydration guards only; every action reloads and
authorizes its target.

Computed properties return bounded presentation arrays for the lifecycle
projection, abilities, public-safe history, and viewer-scoped update requests.
Public state never contains models, query builders, relationship graphs,
private legal-hold reasons, or moderator evidence. Precise loading targets,
offline feedback, native confirmations, and stable database keys cover every
mutation. See `docs/topic-lifecycle.md`.

## Pet Profile Completion Component

`ManagePetProfile` keeps one locked numeric profile ID, a normalized
`#[Url(history: true)]` step string, bounded form values, upload state, status
input, and feedback. The request-scoped private profile memo prevents repeated
component lookups without serializing a model. A step change clears validation
and reloads only the detailed relationship needed by that destination. One
filtered current-manager projection is reused by profile and protected-fact
policy checks during the request.

`PetProfileForm` validates each descriptive section independently;
`PetProfileDocumentsForm` validates the protected microchip readiness record.
Every mutation delegates to its Action and resets the request memo/computed
presentations afterward. Tests cover direct invalid URL state, mutation-free
skip, independent fields, replay, stale versions, protected fact denial, and
query stability as unrelated history grows.
## Onboarding Lifecycle Requests

`RequirePortalAccess` and `EnsureOnboardingIsComplete` remain persistent on
Livewire update requests. The transport endpoint itself is not treated as a
product authorization bypass: the original component route context is
replayed, and onboarding plus pet components re-resolve the authenticated
account and persisted lifecycle state for every mutation. Stale portal and pet
snapshots receive a conflict/forbidden response before domain writes.

Verification resend clears only its own prior success/error state before each
attempt. A failed notification reports a localized error, a later success
removes that stale error, and neither result is inferred from disabled buttons
or client state.
