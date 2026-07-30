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
| `#[Computed]` | Account/auth derived display data | Not currently needed | Current components have no expensive derived state to duplicate | N/A |
| `#[Locked]` | Password reset token | Used by `ResetPassword` | Prevents browser mutation; token still validated server-side | `tests/Feature/Auth/AuthenticationTest.php` |
| Livewire form objects | Registration/login/reset/confirmation forms | Used | Central validation and field errors | `tests/Feature/Auth/AuthenticationTest.php` |
| `#[Url]` | Shareable feed/directory filters | Later per converted component | Keeps linkable state; never secrets | URL/reset tests |
| `#[Session]` | Private UI preference | Not initially required | Avoid duplicating business storage | N/A reason recorded |
| `#[Lazy]` | Below-fold expensive dashboard | Not until measured candidate exists | Must have stable placeholder | N/A |
| `#[Defer]` | Non-critical first-view aggregate | Not until measured candidate exists | Accessible immediate-after-load state | N/A |
| `#[Isolate]` | Slow independent widget | Not until measured candidate exists | Prevent unrelated blocking | N/A |
| `#[Async]` | Idempotent independent operation | Not for auth or critical mutations | Race-prone state prohibited | N/A |
| `#[Js]` | Pure local UI command | Prefer existing Alpine/JS modules | No server round trip | Boundary test |
| `#[Json]` | Explicit JS data endpoint | Not currently required | Never raw models | N/A |
| `#[Renderless]` | Action with no DOM change | Avoid unless measured | Feedback still required | N/A |
| `#[On]` | Narrow parent/child event | Use only with explicit payload | Avoid global event bus | Receiver auth tests |
| Reactive/modelable props | Reusable form child | Not initially needed | Prefer clear parent contract | N/A |
| Islands | Large independently updating page | Not until profiling identifies one | No islands in loops/conditions | N/A |
| `wire:navigate` | Authentication navigation | Used after JS lifecycle audit | Normal links remain functional; title/focus reviewed | Connected Playwright repetition check |
| `wire:loading` / `wire:target` | Every auth mutation | Used | Precise status and duplicate prevention | `tests/Feature/Auth/AuthenticationTest.php` |
| `wire:dirty` | Registration and password reset | Used | Communicates unsaved state | `tests/Feature/Auth/AuthenticationTest.php` |
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
