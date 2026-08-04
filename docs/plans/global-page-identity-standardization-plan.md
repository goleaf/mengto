# Global Page Identity Standardization Plan

Plan date: 2026-08-03

Status: implementation in progress; the shared component, priority directory
wave, localized identity copy, and complete priority browser matrix are
verified, while the stable requirement/query baselines and classified
detail/workspace exception audit remain open as recorded below

This plan is intentionally not time-boxed. Work advances only when the current
package satisfies its acceptance, accessibility, data, and quality gates.

## 1. Problem

PawCircle does not currently have one dependable visual and semantic contract
for the introductory block at the top of a portal page. The same hierarchy —
context, page title, description, optional count, and actions — is rendered by
four different implementations on the twelve named routes:

1. the shared `x-page-header` component;
2. duplicated Tailwind markup inside individual Blade views;
3. the care-specific `.care-directory-header` pattern;
4. the forum-specific `.forum-header` pattern with a separate serif scale.

The global audit found a fifth page-identity family in `/messages`, additional
inline `h1` implementations, and several valid detail/profile hero components.
The solution therefore cannot be a blind source replacement. It must migrate
directory identity, preserve deliberate detail semantics, and classify every
first-party GET route.

The result is visible drift in typeface, title size, line height, spacing,
alignment, borders, action placement, responsive behaviour, and accessible
structure. It also makes later maintenance page-specific when the content is
conceptually the same.

The named forum directory has an additional information-architecture problem:
the server already has a large localized category hierarchy, but the main
directory does not expose its subcategories in a bounded, understandable
navigation flow. The named meetup directory also needs a durable schema and
query compatibility gate because it previously failed when the local database
did not contain `forum_event_team_memberships`.

## 2. User Outcome

- Every eligible portal page begins with the same visual hierarchy:
  eyebrow/context, one `h1`, description, optional metadata, and optional
  actions.
- Typography, spacing, borders, responsive behaviour, focus treatment, and
  touch targets are identical wherever the same page-introduction pattern is
  used.
- Private-care context remains obvious without receiving a separate design
  system.
- Page actions stay visible, wrap predictably, and never require horizontal
  scrolling.
- The forum exposes categories and the selected category's subcategories as
  normal, keyboard-accessible navigation.
- `/meetups` remains renderable after a fresh installation and after a real
  incremental upgrade.
- The first implementation covers the twelve newly named routes plus the
  previously repaired `/messages` regression surface, then continues through a
  complete portal inventory so the same drift cannot remain on less visible
  pages.

## 3. Audited Baseline

Authenticated browser inspection on 2026-08-03 produced this baseline:

| Route | Current header family | Observed title treatment | Planned result |
| --- | --- | --- | --- |
| `/pets` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px desktop | canonical reference |
| `/medical-records` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/care-journals` | `.care-directory-header` | shared sans, 36/40 px | migrate |
| `/meetups` | `.forum-header` | Georgia, about 54/53 px | migrate after stability gate |
| `/places` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px | retain and verify |
| `/lost-found` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/marketplace` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/experts` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/forum` | `.forum-header` | Georgia, about 54/53 px | migrate and restructure IA |
| `/groups` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px | retain and verify |
| `/neighbors` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px | retain and verify |
| `/discover` | `x-page-header` | shared sans, 30/36 px | retain and verify |

All twelve routes currently return an application page without horizontal page
overflow in the audited local environment. `/meetups` currently returns `200`
and the event query explicitly selects `owner_user_id`. The migration that
creates event versions and team memberships is now tracked on `main`, and the
table is present in the current SQLite schema. This is current-state evidence,
not proof that every incremental environment is repaired; the upgrade path
remains an explicit work package below.

Repository-wide search also found `.forum-header` on knowledge, forum editors,
topic detail, event and group workspaces, journals, mentorship, expert
sessions, administration, and community notes. Those screens belong to the
global inventory wave even when they are not part of the first twelve routes.

### 3.1 Repository-Wide Gap Audit

The expanded static and route audit on 2026-08-03 found:

- 344 first-party Blade templates;
- 108 first-party routes that accept `GET` after vendor routes are excluded;
- 62 Blade templates containing an `h1`;
- 13 templates using `x-page-header`;
- 17 templates using `.forum-header`;
- one template using `.care-directory-header`;
- one template using `.messaging-page__header`;
- additional inline directory, create/edit, detail, profile, print, poster,
  emergency, and shared-access headings;
- existing purposeful hero components: `x-detail-page`, `x-detail-identity`,
  `x-context-hero`, `x-profile-identity`, `x-place-hero`, and `x-group-hero`;
- `x-section-heading` renders an `h1` for the feed and composer even though its
  normal role is section-level identity, so those uses require classification.

The counts are baseline evidence, not permanent target assertions. The target
is one canonical directory/page-introduction family plus a small documented
set of token-compatible detail, profile, authentication, print, and scoped
access exceptions.

The audit also found documentation drift:

- `docs/design-system.md` describes page/header hierarchy as already shared;
- `docs/ui-component-inventory.md` identifies `x-page-header` as the event page
  identity;
- `docs/ui-migration-matrix.md` describes the event directory as migrated;
- the current event directory and workspace still render `.forum-header`.
- `docs/portal/route-matrix.md` records only event routes rather than the full
  first-party GET classification required by this plan.

Documentation may describe a target state only when it is labelled as a
target. It must not be treated as implementation evidence while the rendered
Blade disagrees.

### 3.2 Completion Ledger

| Area | Current status | Evidence still required |
| --- | --- | --- |
| Repository, route, schema, and twelve-page browser baseline | Complete | Refresh immediately before implementation if `main` changes |
| Global linked-media navigation | Implemented and verified in its own plan | Preserve its links and accessibility during header migration |
| Message folder placement (`All` through `Archive`) | Implemented and tested | Keep the nine-folder toolbar above the messaging shell |
| Plan registration and current-versus-target documentation | Complete | Reconcile again whenever implementation changes the rendered state |
| `/messages` canonical page identity | Implemented and targeted-verified | Preserve one canonical header, nine folders above the messaging shell, and zero horizontal overflow |
| Package 0 requirements and red contracts | Partial | All 111 current first-party GET routes now have an executable one-class ledger and canonical routes have structural contracts; the stable requirement ID and broader query baselines remain open |
| Package 1 current `/meetups` schema/runtime | Implemented and verified | Fresh migration, complete migration lifecycle, explicit projection, team-membership schema, `/meetups`, and event-workspace runtime checks pass |
| Package 2 shared component | Implemented and browser-verified for current consumers | Explicit page-specific heading IDs, empty/count/single/multiple action states, escaped long content, metadata/actions slots, compatibility, wrapping, semantic tests, localized identity copy, 200% zoom, reduced-motion, and forced-colors focus checks pass |
| Package 3 reference directories | Implemented and browser-verified | All listed routes consume the shared component and pass the complete priority golden matrix at seven responsive/accessibility profiles |
| Package 4 private care directories | Implemented and targeted-verified | Retain authorization/privacy regression coverage in the final full gate |
| Package 5 operational directories | Implemented and targeted-verified | Retain domain and responsive regression coverage in the final full gate |
| Package 6 event directory and workspace | Implemented and targeted-verified | Directory and database-backed event workspaces use the canonical identity; lifecycle, privacy, migration-cycle, and browser runtime gates pass |
| Package 7 forum | Implemented and targeted-verified | Forum routes use the canonical identity; all 44 roots remain visible and only the active root exposes its validated, indexed, server-filterable direct children |
| Package 8 global migration | Implemented and structurally verified | All former migration candidates use the canonical contract or a documented deliberate detail hero; the classified detail/workspace exception audit remains open |
| Packages 9 and 10 | Partial | Retired care/messages/device-directory selectors and reconciled living UI documents; forum/global cleanup and full documentation remain open |
| Package 11 release verification | Partial | The dedicated 13-route browser matrix, focused and full Pest, Pint, Larastan, dependency audits, Vite build, isolated migration/seed/idempotency, cache smoke, forum-source checks, and scoped publication as `f237f2f` pass; only the final global follow-up audit remains open |

The first implementation slice is covered by
`PageIdentityStandardizationTest` plus the existing module, responsive, media,
and messaging suites. The isolated targeted run passed 105 tests and 1,029
assertions. Authenticated browser checks at 375 px and 1440 px confirmed one
canonical header, one `h1`, no horizontal overflow, 44 px mobile actions, and
the required message-folder order. After the route-ledger and explicit heading
ID package, the complete isolated Pest run passed 2,384 tests and 78,892
assertions. After the workflow, forum, event, touch-target, and subcategory
waves, the affected-domain run passed 275 tests and 3,488 assertions and the
complete isolated run passed 2,484 tests and 80,398 assertions. Authenticated
browser checks covered 16 route families at 375 px plus forum and event
workspaces at desktop, including active-child state, 44-pixel actions, zero
horizontal overflow, and zero console or SQL errors. SQLite `EXPLAIN QUERY
PLAN` selects `forum_topics_subcategory_status_activity_idx` for the legacy
subcategory branch. In the shared checkout, both `APP_CONFIG_CACHE` and `DB_DATABASE`
must be isolated so a concurrent cache build cannot redirect a nominal
`:memory:` run to `database/database.sqlite`. This is evidence for the current
implemented slices only, not for the remaining exception audit.

The refreshed authenticated browser matrix on 2026-08-03 passed 91 audits:
13 priority routes across 320, 375, 768, 1024 forced-colors, 1280 at an
effective 200% zoom, 1440, and 1920 profiles. It produced 26 golden
screenshots and recorded zero horizontal overflow, clipped header regions,
sub-44-pixel header targets, legacy header families, missing focus treatments,
or console errors. All 52 non-English audits proved that the document title,
eyebrow, heading, and description differ from the English baseline; the
matrix covered `en`, `lt`, and `ru`. Manual review confirmed the localized
identity blocks and also exposed older English fallback values below the
header on some pages. That body-copy debt is outside the identity component
contract and remains an explicit localization follow-up rather than a passed
whole-page translation claim.

The same isolated package then passed 46 focused Pest tests with 610
assertions, the combined page-identity/architecture/localization slice with 73
tests and 62,134 assertions, and the complete sequential suite with 2,701
tests and 86,271 assertions. Full Pint and Larastan passed with zero findings;
Composer validation/audit/platform requirements, NPM audit, production Vite
build, fresh migration and complete seed, repeat seed (5 users, 14 places, 215
tables), route/config/view cache smoke, and both forum-source checks also
passed. These counts are release evidence for the staged package based on
`284f3ed`, not evidence that the remaining global exception/localization audit
is complete.

The attributable implementation and evidence were committed as `f237f2f` and
pushed directly to `origin/main`; local `HEAD`, the local remote-tracking ref,
and `git ls-remote origin refs/heads/main` all resolved to
`f237f2fdf01e231e17af9934f6a97f70c88a2359` after publication.

The first non-header localization follow-up closes the `/medical-records`
directory slice. Its privacy strip, section heading, populated-card labels,
empty state, action label, and image alternative text now use reviewed RU/LT
copy instead of English fallback values. The medical card also switches from
the desktop split layout to a full-width 16:9 media region above the body below
44rem, preventing long Russian and Lithuanian labels from collapsing into
letter-by-letter columns. The repeatable browser contract now compares eleven
medical copy and accessibility fields with the English baseline and verifies
the stacked 320/375px geometry. The isolated 13-route matrix passed all 91
audits, produced 26 screenshots, and reported zero console errors; observed
mobile card/media widths were 288/286px at 320px and 343/341px at 375px, with
the body starting exactly where the media ended. This closes only the medical
directory body-copy slice, not the remaining priority-page fallback audit.

After `main` advanced independently to `faca4c1d`, the exact medical package
was rebuilt and reverified from that commit rather than relying on the earlier
snapshot. The affected slice passed 109 tests with 62,838 assertions and the
complete sequential suite passed 2,748 tests with 87,294 assertions. Full Pint
and Larastan passed with zero findings; Composer validation, audit, and platform
requirements, NPM audit and production build, fresh migration plus repeated
seed (5 users, 14 places, 2 medical records, 216 tables), 177-line route and
config/view cache smoke, and both forum-source checks also passed.

The second non-header localization follow-up closes the `/care-journals`
directory slice. Its family notice, directory label, populated and empty card
states, actions, missing-value and missing-breed fallbacks, relative values,
and accessible media label now use reviewed RU/LT copy. Species labels are
prepared by the shared `PetSpeciesLabel` service instead of `Str::headline()`;
this changes presentation without adding a query. Below 44rem the card now
uses the same full-width 16:9 media-above-body contract as medical records,
with wrapping identity controls for long translations. The browser matrix
compares sixteen care copy/value fields with the English baseline and verifies
the stacked geometry: all 91 audits and 26 screenshots passed with zero console
errors, and the 375px card/media widths were 343/341px with coincident media
bottom and body top. The affected domain/localization run passed 26 tests with
32,114 assertions; the complete sequential suite passed 2,754 tests with
87,377 assertions. Full Pint, Larastan, dependency audits, production build,
fresh migration, repeated seed (5 users, 14 places, 2 care journals, 216
tables), 177-line route and cache smoke, and both forum-source checks passed.

The global shell localization follow-up removes the English-only navigation
that remained visible below every localized page identity. All thirteen
desktop destinations, the eleven mobile-dock destinations, both navigation
labels, and the unavailable state now come from one EN/LT/RU `navigation`
contract prepared by the class-based Blade component. Existing canonical
Lucide destination icons and routes are retained, and the change adds no
query. The complete browser matrix compares all twenty-six navigation strings
with the English baseline on every route and viewport: all 91 audits and 26
screenshots passed with zero console errors, while the 375px Russian profile
reported no horizontal overflow, clipped page region, sub-44px target, or
English navigation fallback. The focused navigation/icon/page-identity run
passed 57 tests with 750 assertions; the complete sequential suite passed
2,768 tests with 87,713 assertions. Full Pint and Larastan passed with zero
findings; Composer validation, audit, and platform requirements, NPM audit,
production build, isolated migration plus repeated seed (133 migrations, 216
tables, stable 5 users), 178-line route and cache smoke, and both forum-source
checks also passed.

The second global-shell localization follow-up closes the seven utility labels
that still fell back to English above the already-localized destination rows.
The brand home link, discovery search name and prompt, circle, notifications,
messages, and owner-profile label now share the same EN/LT/RU `navigation`
contract. Existing routes, active states, canonical Lucide icons, keyboard
path, and prepared owner data remain unchanged, so this presentation change
adds no query. The browser matrix now compares all thirty-three global-shell
strings with the English baseline on every route and viewport. All 91 audits
and 26 screenshots passed with zero console errors, overflow, clipped regions,
raw translation keys, or undersized header targets. The focused navigation
contract passed 5 tests with 123 assertions; the affected navigation,
localization, responsive, architecture, and page-identity run passed 84 tests
with 64,442 assertions; the complete sequential suite passed 2,836 tests with
91,276 assertions. Full Pint and Larastan passed with zero findings; Composer
validation, audit, and platform requirements, NPM audit, production build,
isolated migration plus repeated seed (135 migrations, 217 tables, stable 5
users and 10 expert profiles), 178-line route and cache smoke, and both
forum-source checks also passed.

The third non-header localization follow-up closes the `/lost-found`
directory slice. Forty-two directory, filter, status, map, guidance, empty,
and defensive UI values now have reviewed RU/LT translations; the unchanged
Lithuanian `Vilnius` value is the correct proper noun rather than an English
fallback. Lithuanian sighting and task counts retain their native diacritics.
The mobile statistics grid now gives its fifth and final item a full-width
row below 48rem instead of leaving a blank sixth cell. This is presentation
only: the existing prepared directory data and query path are unchanged.
The repeatable browser contract compares thirty-four lost-and-found system
fields with the English baseline and measures the statistics geometry. All 91
audits and 26 screenshots passed with zero console errors; at 375px the
Russian statistics container was 344px wide and its final item was 342px wide,
with zero overflow, clipped regions, or sub-44px targets. The affected
directory/responsive/page-identity run passed 93 tests with 1,168 assertions;
the complete sequential suite passed 2,792 tests with 88,411 assertions. Full
Pint and Larastan passed with zero findings; Composer validation, audit, and
platform requirements, NPM audit, production build, isolated migration plus
repeated seed (134 migrations, 217 tables, stable 5 users), 184-line route and
cache smoke, and both forum-source checks also passed.

The fourth non-header localization follow-up closes the `/marketplace`
directory system-copy slice and removes a shared source of English fallback
from the wider marketplace workflow. Forty-five previously English RU/LT UI
values are now reviewed translations, while the complete fifty-five-key
directory surface remains contract-tested. A dedicated EN/LT/RU marketplace
domain supplies 106 listing type, request, category, species, condition,
seller, availability, age, hygiene, delivery, price, sorting, dispute, and
report labels. `ListingTaxonomy`, `ListingType`, and `SellerType` resolve this
contract without changing stored enum/filter values or adding a query, so the
same labels also reach create, detail, reservation, order, and moderation
flows. User-authored and demo listing titles/descriptions remain attributable
content and are deliberately not machine-translated.

The browser contract compares forty-one marketplace system fields with the
English baseline, including six statistics, eleven filter labels, nine
default options, result guidance, and card taxonomy/actions. All 91 audits and
26 screenshots passed with zero console errors; the 375px Russian profile had
zero overflow, clipped regions, or sub-44px targets. The affected marketplace,
responsive, card, and page-identity run passed 84 tests with 1,368 assertions;
the complete sequential suite passed 2,800 tests with 89,166 assertions. Full
Pint and Larastan passed with zero findings; Composer validation, audit, and
platform requirements, NPM audit, production build, isolated migration plus
repeated seed (134 migrations, 217 tables, stable 5 users), 184-line route and
cache smoke, and both forum-source checks also passed.

The fifth non-header localization follow-up closes the `/experts` directory
system-copy slice and removes the same taxonomy fallback from the wider expert
workflow. Thirty-eight previously English RU/LT interface values and five
statistics labels now have reviewed translations. A dedicated EN/LT/RU expert
domain supplies 80 specialist type, species, specialization, consultation
format, language, availability, sorting, example-pet, and profile-status
labels. `ExpertTaxonomy` and `ExpertProfileStatus` resolve that contract while
preserving stored filter and enum values. Matching explanations, card
languages, and booking-pet species reuse the prepared localized maps. The
directory remains bounded at exactly six queries with both one and sixteen
published profiles, so the localization change adds no query and no N+1 path.
User-authored and demo professional names, headlines, biographies, service
descriptions, and credentials remain attributable content and are not
machine-translated.

The repeatable browser contract compares thirty-seven expert system fields
with the English baseline, including five statistics strings, ten filter
labels, seven default options, result guidance, card taxonomy/facts, and card
actions. The audit also corrected its stale unauthenticated entry URL from `/`
to the allowed `/login` route, retaining the global private-portal boundary.
It exposed and closed one earlier marketplace taxonomy gap: the seeded
`pet-sitting` code now belongs to the shared 106-label marketplace contract
instead of falling back to `Pet Sitting`. All 91 browser audits and 26
screenshots then passed with zero console errors; the 375px Russian expert
directory had zero overflow, clipped regions, raw translation keys, or
sub-44px header targets.

The affected expert, marketplace, responsive, card, linked-media, and
page-identity run passed 100 tests with 1,910 assertions; the complete
sequential suite passed 2,810 tests with 89,895 assertions. Full Pint and
Larastan passed with zero findings; Composer validation, audit, and platform
requirements, NPM audit, production build, isolated migration plus repeated
seed (134 migrations, 217 tables, stable 5 users and 10 expert profiles),
184-line route and cache smoke, and both forum-source checks also passed.

The sixth non-header localization follow-up closes the `/groups` directory
system-copy slice. A dedicated EN/LT/RU group domain now owns the summary,
filters, sorting, privacy guidance, empty state, card facts, actions, and all
six curated group fixtures. Proper group and organizer names remain stable
identities, while categories, locations, languages, topics, descriptions,
roles, image alternatives, tags, recommendation reasons, requirements, and
event summaries are localized explicitly. The shared recommendation label is
also corrected for Lithuanian and Russian connection cards. `GroupCatalog`,
`GroupPresenter`, and the shared group card resolve prepared translation maps
without changing stored filter values, adding a query, or creating an N+1
path. Detail-page and user-authored group content remain outside this
directory slice and stay open for their own evidence-backed audit.

The browser contract compares thirty-two group system fields with the English
baseline, including the summary, filters, sorting, search, results heading,
card taxonomy, recommendation, description, tags, and metrics. All 91 browser
audits and 26 screenshots passed with zero console errors; the 375px Russian
group directory had zero overflow, clipped regions, raw translation keys, or
sub-44px targets. The affected group, responsive, card, linked-media, and
page-identity run passed 87 tests with 1,559 assertions; the complete
sequential suite passed 2,836 tests with 91,212 assertions. Full Pint and
Larastan passed with zero findings; Composer validation, audit, and platform
requirements, NPM audit, production build, isolated migration plus repeated
seed (135 migrations, 217 tables, stable 5 users and 10 expert profiles),
178-line route and cache smoke, and both forum-source checks also passed.

The seventh non-header localization follow-up closes the system-chrome slice
for `/groups/{group}` across the overview and all seven secondary tabs. The
EN/LT/RU group domain now owns 87 hero, privacy, action, statistic, access,
tab, section, chat, poll, event, post, membership, and notification labels.
`GroupPresenter` and the group Blade components receive prepared labels and do
not query or translate stored values in the view. The same visual audit found
that the hero's `paper` status-badge tone had no style implementation; the
shared tone now has an opaque token-backed surface, ink contrast, border, and
shadow instead of rendering dark text directly over the cover image.

The repeatable groups-only browser contract compares 39 detail fields with the
English baseline at six detail viewports in addition to the six directory
viewports. It observed one hero, one dashboard, three actions, eight tabs,
zero horizontal overflow, zero sub-44px targets, zero raw translation keys,
and zero console errors; six evidence screenshots were written. The focused
detail contract passed 4 tests with 392 assertions, and the complete isolated
sequential suite passed 2,840 tests with 92,099 assertions. Demonstration and
attributable post, event, poll, chat, rule, resource, member, and pet content in
`GroupContentCatalog` remains open for the next evidence-backed package and is
not represented as completed by this system-chrome checkpoint.

Full Pint and Larastan passed with zero findings; Composer strict validation,
locked audit, PHP 8.5 platform requirements, NPM audit, Vite production build,
and config, event, route, and view cache compilation passed. Fresh disposable
SQLite applied 135 migrations, retained 217 tables and five users across
repeated seeding, and the 178-line route smoke, both forum-source checks, and
both icon-system checks passed. The icon audit observed 845 canonical calls,
no foreign or legacy icon path, and no ratchet violation.

The eighth non-header localization follow-up closes the first-party fixture
content rendered by `GroupContentCatalog` on all eight group-detail tabs. The
catalog contains 122 calls to 119 unique fixture keys. All 109 translatable
values now have reviewed Lithuanian and Russian copy; ten proper names for
people and pets remain stable identities. The hardcoded August abbreviation
and the visible `event` and `local` tags also moved into the EN/LT/RU group
contract. This localization applies only to deterministic first-party demo
fixtures and does not translate genuine member-authored database content.

The HTTP contract renders overview, posts, discussions, events, members,
pets, resources, and rules in both non-English locales and passed 8 tests with
941 assertions together with the prior detail-chrome ratchet. Real Chrome
added 24 tab audits at representative EN desktop, LT 320px, and RU 375px
viewports to the six directory and six detail audits. Every tab retained its
active state, meaningful content length, complete page audit, 44px mobile
targets, and localized body; all runs had zero overflow, raw translation keys,
or console errors. The six existing screenshot artifacts now show localized
fixture narratives, roles, dates, event metadata, poll options, chat messages,
resources, and rules while preserving identity names.

The affected group, localization, responsive, shared-card, page-identity, and
forum-group run passed 132 tests with 37,365 assertions; the complete isolated
sequential suite passed 2,844 tests with 92,663 assertions. Full Pint and
Larastan passed with zero findings; Composer strict validation, locked audit,
PHP 8.5 platform requirements, NPM audit, Vite production build, and config,
event, route, and view cache compilation passed. Fresh disposable SQLite
applied 135 migrations, retained 217 tables and five users across repeated
seeding, and the 178-line route smoke, both forum-source checks, and both
icon-system checks passed with the 845-call canonical icon ratchet intact.

The ninth non-header localization follow-up closes the complete `/neighbors`
directory surface. A dedicated 71-leaf EN/LT/RU neighbor contract owns the
page identity, summary, filters, sorting, search, result and empty states,
actions, and all four first-party directory fixtures. Ten proper person/place
identities remain stable; every other Lithuanian and Russian leaf is reviewed
localized copy. Filter membership now uses explicit locale-independent search
tokens, while closest-first sorting uses a numeric distance projection rather
than parsing localized display text. Each neighbor category also uses the
canonical Lucide badge treatment instead of a text-only badge. Blade remains
passive and the authenticated directory retains its bounded two-query render
without a per-card query path.

The repeatable page-identity browser contract compares 43 translated neighbor
fields with the English baseline while excluding stable proper identities.
All 91 route/viewport audits and 26 screenshots passed across 320-1920 pixels,
RU/LT, reduced motion, forced colors, and effective 200% zoom, with zero
overflow, raw translation keys, undersized header controls, or console errors.
The focused neighbor contract passed 7 tests with 484 assertions; the affected
authorization, directory, linked-media, and page-identity slice passed 123
tests with 1,802 assertions; and the complete sequential suite passed 2,874
tests with 94,123 assertions. Full Pint and Larastan passed with zero findings;
Composer strict validation, locked audit, PHP 8.5 platform requirements, NPM
audit, production Vite build, and isolated config/event/route/view cache
compilation passed. Fresh disposable SQLite applied 136 migrations, retained
218 tables and five users across repeated seeding; the 180-route smoke, both
forum-source checks, and the 847-call canonical icon audit also passed.

The tenth non-header localization follow-up closes the `/messages` system
directory and inbox slice. A dedicated 32-leaf EN/LT/RU messaging contract now
owns the nine folder labels, folder and inbox accessibility names, search and
empty states, eight system conversation-type labels, and six relative dates.
Stable filter/type codes remain locale-independent. Genuine member-authored
names and message bodies remain attributable content and are deliberately not
translated. `MessagePresenter` and `MessageCatalog` prepare the localized
values before Blade rendering, so this presentation change adds no query and
does not introduce a per-conversation query path.

The mobile folder grid now stacks each canonical Lucide icon above a centered,
wrapping label instead of truncating long Russian text. The repeatable browser
contract compares all 32 messaging fields with the English baseline and checks
folder clipping and 44-pixel targets. All 91 route/viewport audits and 26
screenshots passed with zero clipped folder labels, undersized messaging
controls, raw translation keys, or console errors. The focused messaging
contract passed 6 tests with 291 assertions; the affected authorization,
interface, linked-media, and page-identity slice passed 102 tests with 1,399
assertions; and the complete sequential shared-checkout suite passed 2,903
tests with 94,998 assertions. Full Pint and Larastan passed with zero findings;
Composer strict validation, locked audit, PHP 8.5 platform requirements, NPM
audit, production Vite build, and isolated config/event/route/view cache
compilation passed. Fresh disposable SQLite applied 137 migrations, retained
218 tables and five users across repeated seeding; the 180-route smoke, both
forum-source checks, and the 847-call canonical icon audit also passed.

The eleventh non-header localization follow-up closes the `/places` directory
system surface. A dedicated 188-leaf EN/LT/RU contract owns the page identity,
summary, search, categories, filters, location status, catalog modes, views,
sorting, map controls, layers, empty states, comparison labels, and actions.
Stable category, species, size, filter, mode, view, and layer codes remain
locale-independent. Proper place and pet identities and attributable place
content remain outside this system-copy contract. Generalized-location state
now stores a stable internal marker and resolves its visible label in the
active locale, so changing locale cannot replay an English string persisted by
an earlier session. The directory render adds no query; the existing
catalog-growth regression confirms its query count remains bounded.

Category, mode, view, and layer controls now use the canonical Lucide primitive
with server-prepared icon names instead of presentation matching in Blade. The
browser ratchet compares 113 rendered system fields with the English baseline,
checks category/mode/view/layer cardinality, text clipping, and every visible
directory target. It exposed and closed a 20-pixel comparison-link target and
a Lithuanian map heading whose 20-pixel line box clipped diacritics. All 91
route/viewport audits and 26 screenshots then passed across 320-1920 pixels,
RU/LT, reduced motion, forced colors, and effective 200% zoom with zero clipped
labels, sub-44-pixel targets, raw keys, or console errors. The dedicated
directory/detail browser run also passed four desktop/mobile surfaces, loaded
all six visible images, synchronized map selection, and found no private exact
location leak.

The focused place contract passed 18 tests with 581 assertions; the affected
place slice passed 53 tests with 1,620 assertions; the architecture slice
passed 25 tests with 31,271 assertions; and the complete sequential suite
passed 2,923 tests with 96,639 assertions. Full Pint and Larastan passed with
zero findings; Composer strict validation, locked audit, PHP 8.5 platform
requirements, NPM audit, production Vite build, and isolated
config/event/route/view cache compilation passed. Fresh disposable SQLite
applied 137 migrations and retained 218 tables, five users, and fourteen places
across repeated seeding. The 180-route smoke, both forum-source checks, and the
849-call canonical icon audit also passed.

The twelfth non-header localization follow-up extends the `/messages` contract
from the directory into the top-level conversation surface. Twenty-nine new
EN/LT/RU leaves now own the page shell, canonical page identity, thread header,
call-preflight labels, request gate, professional-state landmark, channel
navigation, message-log landmark, search reset, declined-request state, and
empty message result. Together with the previously verified folder, inbox,
conversation-type, and relative-time contract, `messaging.php` now contains 61
exact-parity leaves. Accountable member or organization names, linked pet
identities, and authored message bodies remain verbatim content and are not
misclassified as system fallback.

The browser ratchet now compares 42 rendered messaging fields with the English
baseline and measures every visible control in the thread. Its first expanded
run exposed 32-pixel message menus and 36-pixel menu actions, audio playback,
attachment tools, quiet-send labeling, and schedule disclosure. All affected
controls and channel/search actions now expose at least a 44-pixel hit area;
checkboxes are measured through their associated clickable label rather than
their decorative inner square. The canonical empty-channel fallback also uses
the shared Lucide primitive. The repeated matrix then passed all 91 route and
viewport audits and 26 screenshots across 320-1920 pixels, EN/RU/LT,
forced-colors, reduced motion, and effective 200% zoom with zero English system
fallbacks, raw keys, undersized audited thread targets, or console errors.

The focused messaging and page-identity slice passed 75 tests with 1,455
assertions; the affected localization, authorization, interface, linked-media,
and page-identity slice passed 117 tests with 1,741 assertions; and the complete
sequential suite passed 2,925 tests with 97,025 assertions. Full Pint and
Larastan passed with zero findings; Composer strict validation, locked audit,
PHP 8.5 platform requirements, NPM audit, production Vite build, isolated
config/event/route/view cache compilation, the 180-route smoke, both
forum-source checks, PHP localizer, and the 850-call canonical icon audit also
passed. No route, persistence, Eloquent query, or authorization boundary
changed in this presentation package.

The thirteenth non-header localization follow-up completes the visible message
composer and per-message menu chrome. Seventy-one new EN/LT/RU leaves extend the
dedicated messaging contract from 61 to 132 exact-parity values for reply and
draft state, eight attachment tools, recipient and placeholder framing, quiet
and scheduled delivery, attachment privacy, twenty message types, six reaction
labels, sent/read/delivered state, audio playback, and every visible message
action. The class-based composer prepares the fixed tool-code/icon/label
contract outside Blade; the message component resolves stable type and reaction
codes through explicit localized mappings instead of locale-dependent headline
generation. Accountable names, message bodies, and authored attachment content
remain verbatim.

The browser ratchet now compares 87 rendered messaging fields with the English
baseline. For viewports below 832 pixels it opens `conversation=ari`, making the
thread, message actions, attachment tools, textarea, quiet-send label, schedule
disclosure, and privacy notice visible to the same 44-pixel target audit instead
of accepting hidden DOM as mobile evidence. The repeated matrix passed all 91
route and viewport audits and 26 screenshots across 320-1920 pixels, EN/RU/LT,
forced colors, reduced motion, and effective 200% zoom with zero English system
fallbacks, raw keys, clipped folder labels, undersized visible messaging
targets, or console errors. The focused contract passed 17 tests with 1,342
assertions, the affected messaging/authentication/interface/page-identity slice
passed 113 tests with 2,448 assertions, focused PHPStan reported zero errors,
and the canonical icon audit retained 850 shared-icon calls with zero debt.
The complete sequential suite passed 2,960 tests with 98,927 assertions. The
attributable PHP slice passed Pint and full Larastan passed with zero findings;
Composer strict validation, locked audit, PHP 8.5 platform requirements, NPM
audit, production Vite build, and isolated config/event/route/view cache
compilation passed. Fresh disposable SQLite applied 137 migrations and retained
218 tables and five users across repeated seeding; the 180-route smoke, both
forum-source checks, deterministic 38,377-requirement generation, and PHP
localizer also passed. This presentation package adds no Eloquent query, schema,
route, persistence, or authorization change.

The fourteenth non-header localization follow-up completes the `/messages`
conversation metadata, right context rail, and action-result feedback. The
dedicated EN/LT/RU contract grows from 132 to 329 exact-parity leaves: 56 own
the seven system metadata fields for all eight stable conversations, 87 own
the context identity, search, controls, professional case, poll, tasks,
members, shared content, safety, and delivery boundary, and 54 own every
request, conversation, send, message, poll, task, notification-level, and call
feedback result or error. Accountable people, pet names, and member-authored
message content remain verbatim. Stable conversation, action, message,
notification, poll, task, and call codes remain locale-independent.

`MessagingContext` prepares the fixed action/icon/label maps outside Blade,
while `MessageCatalog`, `MessagePresenter`, and `PerformMessageAction` resolve
all system copy through the messaging domain. The browser ratchet now compares
127 rendered messaging fields with the English baseline and separately guards
the seven context action codes and canonical Lucide icons. Its first expanded
run exposed 40-pixel search input, 32-pixel disclosure summaries, and 36-pixel
safety actions; search, summary, safety, poll, task, and shared-content targets
now retain at least 44 pixels.

The repeated matrix passed 91 route/viewport audits and 26 screenshots across
320-1920 pixels, EN/RU/LT, forced colors, reduced motion, and effective 200%
zoom with zero English system fallbacks, raw keys, clipped folder labels,
undersized messaging controls, or console errors. The focused contract passed
21 tests with 3,007 assertions; the affected slices passed 133 tests with
4,233 assertions; and the complete sequential suite passed 2,964 tests with
101,228 assertions. Full Pint and Larastan passed with zero findings. Composer
strict validation, locked audit, PHP 8.5 platform requirements, NPM audit,
production Vite build, and config/event/route/view cache compilation passed.
Fresh disposable SQLite applied 137 migrations, retained 218 tables and five
users across repeated seeding; the 180-route smoke, both forum-source checks,
deterministic 38,377-requirement generation, PHP localizer, and 851-call
canonical icon audit also passed. The package adds no Eloquent query, schema,
route, or authorization change; it localizes existing authorized mutations.

The fifteenth non-header localization follow-up completes the active call
stage and the real conversation-details route. Thirty-four call leaves and one
details-return leaf extend the dedicated EN/LT/RU messaging contract from 329
to 364 exact-parity values. `MessageState` now persists stable
`type`/`status`/`quality_code` values rather than copy resolved under the locale
active when a call started. `MessagePresenter` explicitly maps those codes in
the current locale, including a compatibility fallback for older state, and
the class-based `MessagingCallStage` prepares microphone, camera, captions,
audio-only, and reconnect code/icon/label maps outside Blade. The active call
template uses only the messaging domain and canonical Lucide icons.

The audit also proved that `messages.details` already reuses the current
protected messaging presenter and context component, while a responsive CSS
rule hid that requested context below 1200 pixels. The route now emits an
explicit details marker, renders the same policy-scoped context full-width on
mobile and tablet, hides the inbox/thread only in that compact details mode,
and provides a localized 44-pixel return control. Desktop retains the complete
three-column conversation. No route, query, persistence schema, or
authorization boundary changed.

The focused contract passes 25 tests and 3,394 assertions; the affected
messaging, preview, responsive, page-identity, and linked-media slice passes 98
tests and 4,367 assertions. The expanded browser ratchet passes the existing
91 route/viewport audits plus seven real video-preflight flows and seven real
details-route flows across 320-1920 pixels, EN/RU/LT, forced colors, reduced
motion, and effective 200% zoom. It checks 22 call-stage values, stable codes,
four control icons, first focus, scroll-reachable footer, unclipped labels,
responsive details visibility, 44-pixel targets, horizontal overflow, and
console output. Thirty screenshots, including dedicated 375/1440 call and
details captures, passed and were visually reviewed. Final repository-wide
gates then passed: the complete sequential shared-checkout suite reports
2,998 tests and 102,279 assertions in 187.881 seconds; full Pint and Larastan
report zero findings; Composer strict validation, locked audit, PHP platform
requirements,
NPM audit, production Vite build, and isolated config/event/route/view cache
compilation pass. Fresh disposable SQLite applies 138 migrations, retains 219
tables and five users across repeated seeding; the 180-route smoke, both
forum-source checks, deterministic 38,377-requirement generation, PHP
localizer, and 854-call canonical icon audit also pass.

The sixteenth non-header localization follow-up audits the deliberate
`/share/{target}` detail hub. A dedicated 42-leaf EN/LT/RU contract now owns
the complete page, delivery-channel, neighbor-action, details, privacy,
subject, message, and empty-state surface. `SharePresenter` maps five stable
active-section codes to target taxonomy and prepares three stable channel
codes (`email`, `text`, and `original`) with canonical `mail`,
`message-square-text`, and `external-link` icons. The recipient action keeps
the canonical `send` icon. Existing destinations, authenticated access,
message mutation, public-link boundaries, and target media remain unchanged;
the presenter adds zero Eloquent queries.

The focused share contract passes 11 tests and 458 assertions, and the
affected preview, responsive, page-identity, linked-media, and portal-boundary
slice passes 124 tests and 1,698 assertions. The browser matrix retains 91
primary audits, seven video-call flows, and seven details flows, and adds seven
real share routes across 320-1920 pixels, EN/RU/LT, forced colors, reduced
motion, and effective 200% zoom. All 30 visible share values, three channel
codes, 13 page icons, touch targets, long localized labels, clipping,
horizontal overflow, raw keys, and console output pass; 32 screenshots,
including dedicated RU 375px and EN 1440px share captures, were visually
reviewed.

The stabilized complete sequential shared-checkout suite passes 3,009 tests
and 102,905 assertions in 183.677 seconds. Full Pint and Larastan report zero
findings; Composer strict validation, locked audit, PHP 8.5 platform
requirements, NPM audit, production Vite build, JavaScript syntax, and
isolated config/event/route/view cache compilation pass. Fresh disposable
SQLite applies, fully rolls back, reapplies, and seeds all 138 migrations,
retains 219 tables and five users across repeated seeding, and the 180-route
smoke, both forum-source checks, deterministic 38,377-requirement generation,
PHP localizer, and 854-call canonical icon audit also pass.

The seventeenth non-header localization follow-up audits the deliberate
`/neighbors/ari-jensen` profile. Eighty-nine profile leaves extend the existing
neighbors domain from 71 to 160 exact-parity EN/LT/RU values and own the page,
hero, section, action, identity, statistics, interests, pet, mutual-neighbor,
community, and moment surface. `NeighborProfilePresenter` extracts the
projection from the broad `PreviewService`, prepares follow, message, and walk
actions outside Blade, and adds zero Eloquent queries. Existing authenticated
state reads, interaction state, routes, media destinations, and mutation
authorization remain unchanged.

The profile retains its intentional profile-led hero and gains canonical
Lucide icons for section headings, metadata, the three routine facts, and both
communities. The focused contract passes 7 tests and 403 assertions; the
combined neighbor directory/profile contract passes 14 tests and 887
assertions; and the affected authorization, preview, responsive, page-identity,
linked-media, and shared-component slice passes 136 tests and 2,210 assertions.
The browser matrix retains 91 primary audits, seven video-call flows, seven
message-details flows, and seven share flows, and adds seven real neighbor
profile flows across 320-1920 pixels, EN/RU/LT, forced colors, reduced motion,
and effective 200% zoom. All 57 audited profile values, targeted icon maps,
44-pixel controls, clipping, horizontal overflow, raw keys, and console output
pass; 34 screenshots, including RU 375px and EN 1440px profile captures, were
visually reviewed.

The complete sequential shared-checkout suite passes 3,016 tests and 103,577
assertions in 180.402 seconds. Full Pint and Larastan report zero findings;
Composer strict validation, locked audit, PHP 8.5 platform requirements, NPM
audit, production Vite build, JavaScript syntax, and isolated
config/event/route/view cache compilation pass. Fresh disposable SQLite
applies, fully rolls back, reapplies, and seeds all 138 migrations, retains
219 tables and five users across repeated seeding, and the 180-route smoke,
both forum-source checks, deterministic 38,377-requirement generation, PHP
localizer, and 859-call canonical icon audit also pass.

The eighteenth non-header localization follow-up audits the deliberate
`/@mia-carter` owner profile. A dedicated 131-leaf exact-parity EN/LT/RU
contract now owns its page title, hero, statistics, actions, tabs, audience
preview, overview, pets, posts, about, privacy, safety, completion, badges,
availability, and first-party fixture copy. Stable four-tab and four-audience
codes remain independent of the active locale. `ProfilePresenter` prepares
the full projection and canonical Lucide icon map, while the owner Blade
component consumes prepared values without generic translation domains,
business rules, route construction, or mutations.

The tab-aware projection skips pet and moment work when those collections are
not rendered. The `about/friend` presenter path drops from seven queries to
one cached-state query and performs no `pet_profiles` query. The focused
contract passes 10 tests and 1,086 assertions; the affected profile, preview,
responsive, page-identity, linked-media, and authentication-boundary slice
passes 128 tests and 2,368 assertions.

The browser matrix retains 91 primary route/viewport audits and the existing
call, message-details, share, and neighbor-profile flows, then adds seven real
owner-profile flows across 320-1920 pixels, EN/RU/LT, forced colors, reduced
motion, and effective 200% zoom. It checks 54 localized values, four stable tab
codes, four stable audience codes, all owner-profile icon maps, visible focus,
clipping, horizontal overflow, raw keys, 44-pixel targets, and console output.
The first 320-pixel run exposed 42-pixel audience controls; the shared audience
tabs now use the canonical 44-pixel target. The repeated matrix passes and
produces 36 screenshots, including dedicated RU 375px and EN 1440px owner
profile captures.

The exact staged tree, based on the current `origin/main`, passes 3,055 tests
and 105,425 assertions in 176.605 seconds. Full Pint and Larastan report zero
findings; Composer strict validation, locked audit, PHP 8.5 platform
requirements, NPM audit, production Vite build, and JavaScript syntax pass.
Disposable SQLite applies all 139 migrations, fully rolls them back, reapplies
them, retains 219 tables and five users across repeated seeding, and both cache
smoke paths pass after clearing the archive's inherited config cache. The
179-route/173-first-party smoke, both forum-source checks, deterministic
38,377-requirement generation, PHP localizer, and 859-call canonical icon audit
also pass.

### 3.3 Next Execution Checkpoint

1. Complete Package 0 by assigning the stable requirement ID and recording the
   remaining representative query baselines; keep the executable 111-route
   ledger synchronized.
2. Retain the verified incremental `/meetups` and event lifecycle checks as
   release gates for Package 6.
3. Audit every classified detail/workspace exception, retaining purposeful
   heroes and migrating accidental page headers; never mark a later wave
   complete from source intent or screenshots alone.
4. Continue the non-header English fallback audit on the remaining priority
   RU/LT pages; the global navigation and utility header, `/medical-records`,
   `/care-journals`, `/places`, `/lost-found`, `/marketplace`, `/experts`, and
   the `/groups`, `/neighbors`, and `/messages` system directories are
   browser-verified. Group detail system chrome and first-party fixture content
   are verified across all eight tabs, and the message directory, top-level
   thread, composer, per-message action chrome, conversation metadata, context
   rail, and action-result feedback are verified across default, request,
   professional, channel, poll, task, declined, and empty-message states.
   The active call stage, real message details route, share detail hub, Ari
   neighbor profile, and Mia owner profile are now browser-verified; genuine
   future member-authored
   content, coherent
   removal of the unused
   historical details presenter/template chain, and the other priority bodies
   remain open. Do not conflate body-copy completion with the verified
   page-identity contract.

## 4. Scope

### 4.1 Required first wave

- `/pets`
- `/medical-records`
- `/care-journals`
- `/meetups`
- `/places`
- `/lost-found`
- `/marketplace`
- `/experts`
- `/forum`
- `/groups`
- `/neighbors`
- `/discover`

The previously reported `/messages` surface is a required regression route.
Its nine folder controls and linked conversation media are already repaired,
but its bespoke page identity still needs migration to the canonical contract.

### 4.2 Required global continuation

Inventory every first-party GET route, including authenticated, guest,
temporary-access, compatibility, and non-HTML responses. Classify its output
before deciding whether a page header applies:

1. **Directory or index:** use the canonical `x-page-header`.
2. **Create or edit workflow:** use the same page-identity typography and
   spacing, with form actions in a predictable action region.
3. **Resource detail or operational workspace:** use a deliberately documented
   detail/workspace hero that consumes the same tokens but may include entity
   state, ownership, or operational controls.
4. **Authentication:** retain `x-auth-page-header` as a documented exception.
5. **Full-screen modal, media viewer, or focused tool:** no page header when a
   semantic page introduction would be redundant.
6. **Special document or scoped access:** print, poster, emergency, export,
   download, and token-scoped pages use a separate documented contract and do
   not inherit portal chrome blindly.
7. **Redirect or file response:** record as a non-page exception; never add
   presentation markup.

Every exception must be recorded with its route, component, reason, and owner.
“It already looks different” is not a valid exception.

The known route families that must appear explicitly in the matrix are:

- primary directories: content/feed, pets, medical records, care journals,
  devices, places, lost-and-found, marketplace, experts, forum, knowledge,
  groups, meetups, messages, relationships, discovery, notifications, and
  walks;
- create/edit/manage workflows for pets, medical records, care journals,
  devices, lost-and-found cases, listings, experts, topics, and knowledge
  guides;
- detail/workspace routes for profiles, pets, records, journals, devices,
  places, cases, listings, orders, experts, consultations, groups, events,
  forum topics, journals, mentorship, expert sessions, and publications;
- guest/authentication routes: home/join, login, registration, password, email
  verification, and confirmation;
- scoped/special responses: medical, care, and device access tokens; private
  documents/media; forum files/media/exports; knowledge export/print;
  lost-pet poster/emergency output; and compatibility redirects.

### 4.3 Explicit non-goals of the header package

- It does not redesign every card, table, map, form, or empty state in the same
  commit.
- It does not merge public and private authorization rules.
- It does not move domain queries into Blade.
- It does not introduce a second CSS framework, JavaScript UI framework,
  Filament, Flux, Volt, React, Vue, or Inertia.
- It does not render all 1,637 forum subcategories into every forum response.
- It does not edit an already-used historical migration to repair an upgrade.
- It does not make an unavailable page or action discoverable merely for
  visual consistency.

### 4.4 Cross-Plan Boundaries

- `docs/plans/global-linked-media-navigation-plan.md` is implemented and
  remains authoritative for image/avatar/placeholder navigation. Header work
  must not remove, duplicate, or retarget those links.
- The messaging folder toolbar and conversation-only inbox scrolling were
  implemented before this plan. The canonical header moves only page identity
  and summary actions; it does not return folders to the left inbox column.
- The desktop application header is governed by its own design specification.
  This plan changes content-page identity below the application shell, not the
  global brand/account navigation.
- Existing detail/profile heroes remain valid candidates when their semantics
  require media, status, ownership, or operational commands. They must consume
  the shared typography, spacing, focus, and action tokens instead of being
  replaced blindly by a directory header.

## 5. Canonical Shared Component Contract

Evolve `resources/views/components/page-header.blade.php`; do not create a
parallel general-purpose header component.

### 5.1 Semantic order

The component renders this stable structure:

1. `<header>` landmark with an optional stable `data-section` hook;
2. localized eyebrow or context label;
3. exactly one page-level `h1`;
4. localized description;
5. optional prepared metadata such as a result count or privacy status;
6. optional action slot containing server-authorized controls.

The component remains presentational. It receives strings, URLs, scalar state,
and prepared action data. It does not access models, policies, facades,
services, routes by guessed identifiers, or the database.

The header is the first identity region inside the main content column. It
spans that column before page-local sidebars, category navigation, filters, or
result grids. No department, folder, filter, or local-navigation block may
split the title/description region into a competing left column.

### 5.2 Proposed API

Keep the existing public API compatible during migration, then converge on:

- `eyebrow` — required localized text;
- `title` — required localized text;
- `description` — required localized text;
- `headingId` — optional stable ID when another region references the title;
- `meta` slot — optional count, privacy label, or concise status;
- `actions` slot — optional action controls;
- normal Blade attributes for `aria-*` and stable testing hooks.

The current `count` and single-action props remain as a temporary compatibility
layer. After all consumers have moved to slots, remove compatibility only in a
separate, test-backed cleanup commit.

Back links and breadcrumbs use their own shared navigation component before
the page identity when the route depth requires them. They are not encoded as
an eyebrow and do not change the heading hierarchy.

### 5.3 Visual token contract

Use the existing `/pets` family as the locked directory reference:

- one application sans-serif family; no directory-specific Georgia override;
- title at `1.5rem`, increasing to `1.875rem` from the existing `40rem`
  breakpoint, with line-height `1.2` and weight 600;
- eyebrow at `0.75rem`, weight 600, uppercase, using the canonical leaf token;
- description at `0.875rem` with `1.5rem` line-height and a maximum measure of
  `70ch`;
- content width capped by the existing `42rem` measure;
- a single border, padding, content gap, and desktop alignment contract;
- actions that wrap below or beside the copy without clipping;
- minimum 44 px interactive targets;
- visible focus, forced-colors support, and reduced-motion compatibility;
- no hover-only meaning and no horizontal page overflow.
- identical main-content gutters and alignment with the page body;
- stable block height during Livewire loading, filtering, and pagination;
- a localized browser document title consistent with the visible `h1` while
  retaining the PawCircle product suffix.

Tokens belong in the existing CSS-first Tailwind/theme and SCSS component
layer. Page views must not restate the title scale with one-off utility lists.

### 5.4 Layout Wireframe

Desktop:

```text
EYEBROW                                      META / ACTIONS
PAGE TITLE
One concise localized description
---------------------------------------------------------
LOCAL NAVIGATION / FILTERS / CATEGORY NAVIGATION
PAGE CONTENT OR PAGE-LOCAL SIDEBAR + RESULTS
```

Mobile:

```text
EYEBROW
PAGE TITLE
One concise localized description
META
PRIMARY ACTION
SECONDARY ACTIONS (wrapped)
--------------------------------
LOCAL NAVIGATION / FILTERS
PAGE CONTENT
```

Negative rules:

- no serif title variant for a directory;
- no title card floating beside a page-local sidebar;
- no folder/category/filter row before the page identity;
- no fixed-width action group that can force overflow;
- no sticky page identity unless a separately measured requirement adds it;
- no layout shift when result counts or Livewire loading text change.

### 5.5 Content Policy

- Eyebrow states the context, not a promotional slogan.
- Title names the current page in plain language.
- Description explains the page outcome in one or two short sentences.
- Private contexts may show the existing lock icon or privacy label in `meta`,
  but use the same typography and spacing.
- A header contains at most one primary action and two secondary actions.
  Additional controls move to the following page toolbar.
- Counts describe the current result set and remain semantically separate from
  the page title.

## 6. Data Flow and Reusable Presentation Shape

Controllers, presenters, and Livewire components prepare the header data before
rendering. A reusable presentation array or typed view-data object may use this
shape:

```php
[
    'eyebrow' => __('...'),
    'title' => __('...'),
    'description' => __('...'),
    'count' => $preparedCountLabel,
    'actions' => $authorizedActions,
]
```

This is a data-shape example, not permission to add translation or policy logic
to Blade. URLs and action visibility remain server-authoritative. Livewire
public state stays small and contains no secrets or large model graphs.

Where `x-directory-page` already composes `x-page-header`, retain that path.
Do not duplicate the header in the calling view.

## 7. Work Packages

Each package is independently reviewable. Do not begin a later package while a
blocking gate in the current one is unresolved.

### Package 0 — Requirements, inventory, and red contracts

1. Assign a stable product requirement ID for canonical page identity.
2. Record the twelve-route baseline, the `/messages` regression surface, and
   all 108 current first-party GET routes by expanding
   `docs/portal/route-matrix.md` into the global classification matrix.
3. Add a focused architecture/feature contract that finds every eligible page
   and detects duplicate general-purpose header families.
4. Add route-level assertions for one `h1`, the canonical header hook, escaped
   localized content, and authorized actions.
5. Capture browser screenshots and computed typography at the required
   breakpoints before changing CSS.
6. Record representative query counts so the component migration cannot hide a
   query regression.
7. Add an explicit allowlist for redirects, downloads, exports, print/poster,
   token-scoped, auth, and deliberate detail/profile hero routes.
8. Correct current-versus-target wording in design-system, component inventory,
   and UI migration documents before using them as evidence.

Acceptance:

- every named route has an owner, current template/component, desired
  component, and test target;
- every first-party GET route is classified as rendered page, deliberate hero,
  special document/scoped access, file response, or redirect;
- new contract tests fail for the known divergent implementations and pass for
  the current canonical pages;
- no production markup has changed yet.

### Package 1 — `/meetups` schema and query stability

1. Verify the tracked event lifecycle migration on a fresh empty SQLite
   database and on a copy representing the previous application state.
2. Verify that migration history and physical schema agree for
   `forum_event_team_memberships` and its required indexes/foreign keys.
3. If a deployed environment can have a migration marked as applied while the
   table is absent, add a new forward-only reconciliation migration or a safe
   diagnostic/repair command. Never edit the historical migration.
4. Keep event visibility in model scopes and retain all columns needed by the
   scope, policy, presenter, and relationships in explicit projections.
5. Add regression coverage for authenticated visibility, private/group/event
   membership paths, missing optional relationships, and pagination.
6. Verify `/meetups` before any visual refactor so a design change cannot mask
   a data failure.

Acceptance:

- a fresh migrate and seed renders `/meetups`;
- an incremental migration path renders `/meetups`;
- no missing-table or missing-selected-attribute exception occurs;
- visibility and authorization tests include negative cases;
- event directory pagination does not introduce N+1 queries.

### Package 2 — Shared component foundation

1. Extend `x-page-header` with stable heading ID, metadata, and action slots.
2. Centralize type, spacing, border, layout, focus, touch, and wrapping rules.
3. Preserve compatibility with `x-directory-page` and current callers.
4. Add component rendering tests for no action, count only, one action,
   multiple actions, long translations, and escaped content.
5. Add browser fixtures for narrow viewport, 200% zoom, and forced colors.
6. Align the header with the same max-width and inline gutters as its page
   body, before any local sidebar or toolbar.
7. Keep the header stable across Livewire loading/filtering updates and align
   the localized document title with the visible title.

Acceptance:

- the component performs zero database queries;
- its DOM order and accessible name hierarchy are stable;
- long Russian, Lithuanian, and English strings do not overlap or overflow;
- actions remain reachable by keyboard and touch.

### Package 3 — Reference directories

Apply and verify the canonical contract on:

- `/pets`;
- `/places`;
- `/groups`;
- `/neighbors`;
- `/discover`;
- `/notifications`;
- `/walks`;
- `/circle`, `/circle/connections`, and `/circle/pet-friends`;
- `/messages`, while preserving its repaired folder toolbar and inbox layout.

Most of these use `x-page-header` directly or through `x-directory-page`.
`/messages` is the deliberate regression target that still uses its own header.
This package removes local overrides, normalizes content and action placement,
and establishes the golden browser snapshots.

Acceptance:

- the five named reference directories share the same computed font family,
  size, line height,
  padding, and border behaviour at the same breakpoint;
- the additional existing shared-header routes and `/messages` match the same
  contract without moving folders back into the inbox sidebar;
- page-specific filters, maps, counts, and actions begin below or inside their
  documented slots without layout shifts.

### Package 4 — Private care directories

Apply the shared component on:

- `/medical-records`;
- `/care-journals`.

Preserve the “private care/family workspace” meaning as localized metadata.
Keep privacy explanations, pet scoping, alerts, and creation permissions
unchanged. Move `Create journal` into the canonical action region only when the
current user is authorized.

Acceptance:

- both pages use the same visual structure as `/pets`;
- privacy is still announced to sighted and assistive-technology users;
- no private record, pet, file, or family data becomes publicly discoverable;
- medical and journal query counts do not increase.

### Package 5 — Operational directories

Replace duplicated utility headers on:

- `/lost-found`;
- `/marketplace`;
- `/experts`.

Keep emergency/status communication below the page identity. Keep marketplace
money, listing state, and expert verification server-authoritative. Do not
blend page actions with card-level actions.

Acceptance:

- the three pages use canonical page identity and action slots;
- urgent lost-pet states retain stronger semantic status treatment without a
  separate page-title system;
- filters and actions wrap at 320 px without page overflow.

### Package 6 — Event directory presentation

After Package 1 is green, replace the `.forum-header` in the Livewire event
directory with `x-page-header`.

1. Keep filter values in typed, validated Livewire URL state.
2. Keep loading, offline, empty, error, and pagination states.
3. Keep event creation authorization and substantial form logic in the
   component/form object, not the view.
4. Move creation to the canonical action/toolbar hierarchy without making the
   entire creation form part of the header.

Acceptance:

- `/meetups` uses the canonical title system;
- filtering and pagination continue to work through direct Livewire action
  invocation tests and browser interaction;
- no event fields or authorization paths are lost.

### Package 7 — Forum page identity and category information architecture

The repository currently seeds and tests 44 root categories and 1,637 direct
subcategories. The main forum view receives category data but renders only root
entries in its current sidebar. The fix must make the hierarchy usable without
placing the entire taxonomy into one DOM.

1. Replace the forum directory's `.forum-header` with `x-page-header`.
2. Place category navigation immediately below the page header rather than in
   an ambiguous decorative sidebar.
3. Render all 44 root categories as ordinary, localized GET links in a
   responsive grid or disclosure navigator.
4. When a root is active, render only that root's direct subcategories in a
   second bounded region.
5. Add a validated `subcategory` input to the browse request.
6. Validate that the selected subcategory is a child of the selected root;
   reject or normalize mismatched pairs on the server.
7. Add focused `ForumCategoryTree` methods for root navigation and children of
   one active root. Cache by locale and selected root with documented TTL and
   invalidation; do not load all descendants by default.
8. Add an Eloquent topic scope/presenter path for the selected normalized
   category/subcategory. Preserve legacy category mapping until its documented
   migration is complete.
9. Preserve filter, sort, search, language, and pagination query parameters in
   category navigation.
10. Keep category preparation in the presenter/service. Blade only renders the
    prepared hierarchy.

Acceptance:

- the 44 roots are visible, reachable, and localized;
- selecting a root exposes exactly its direct children;
- selecting a child filters the topic list and retains an understandable
  active/breadcrumb state;
- invalid or cross-root child input cannot escape the selected hierarchy;
- the response does not render all 1,637 subcategories;
- query count remains bounded and has no category/topic N+1 path;
- navigation works with keyboard, touch, 200% zoom, and JavaScript disabled.

### Package 8 — Global page inventory and migration

Use route inventory plus Blade/Livewire searches to migrate every remaining
eligible first-party page. The known starting list includes:

- forum topic detail and editor;
- content feed, publication detail, share context, and composer;
- knowledge directory and guide editor;
- forum groups and group workspaces;
- forum events and event workspaces;
- forum journals and timelines;
- mentorship and expert sessions;
- community notes and forum administration;
- connections, pet friends, circle, notifications, walks, profiles, and
  settings;
- content/feed, messages, devices, medical/care create/manage/detail screens,
  lost-and-found coordination/poster screens, marketplace create/order
  screens, and all expert create/edit/booking/consultation/workspace screens;
- create/manage pet flows and other authenticated directories discovered by
  route inspection.

The route matrix also records, without wrapping them in portal identity:

- private media/document/download endpoints;
- export responses and print-only documents;
- token-scoped medical, care, and device access;
- redirects and compatibility endpoints;
- poster/emergency layouts whose task-specific hierarchy is intentionally
  different.

Do not blindly replace a detail/workspace hero. First classify it, then either
use `x-page-header` or a documented token-compatible detail/workspace variant.
Page-level `x-section-heading level="1"` consumers move to a page-identity or
documented focused-workflow contract; `x-section-heading` remains the canonical
section-level `h2` primitive.

Acceptance:

- every first-party GET route is present in the migration matrix, including
  authenticated, guest, scoped-access, special-response, and redirect routes;
- every eligible directory uses `x-page-header`;
- every exception is deliberate, documented, and covered by a structural
  test;
- no new `.forum-header`, `.care-directory-header`, or duplicated utility
  header is allowed by architecture tests.

### Package 9 — CSS and compatibility cleanup

1. Remove `.care-directory-header` only after its last valid consumer moves.
2. Split `.forum-header` usages that are true page identity from small internal
   section headings; replace internal headings with a distinct, semantically
   named section component.
3. Remove the oversized serif page-title rules after the last page-identity
   consumer moves.
4. Remove temporary `x-page-header` compatibility props after all callers use
   slots.
5. Delete duplicated inline header utility sequences.
6. Retain detail typography only where the global classification explicitly
   permits it.
7. Remove undocumented page-level `x-section-heading level="1"` usage while
   preserving normal section-level headings.

Acceptance:

- source search finds one general-purpose portal page-header family;
- the CSS build has no orphaned page-header rules;
- no visual regression is hidden by stale selectors.

### Package 10 — Localization and documentation

1. Normalize header translation keys across `en`, `lt`, and `ru` without
   creating a second translation system.
2. Keep placeholders and plural forms identical across locales.
3. Update design-system documentation, frontend rules, component inventory,
   UI migration matrix, implementation plan, requirements, compliance matrix,
   testing guidance, and changelog.
4. Mark a requirement implemented and verified only after its actual gates
   pass.
5. Keep forum requirement/evidence generation and immutable-source checks in
   the same change whenever forum requirements or evidence are touched.
6. Reconcile the inaccurate current-state claims in `docs/design-system.md`,
   `docs/ui-component-inventory.md`, and `docs/ui-migration-matrix.md` with the
   rendered implementation.
7. Register this plan in `docs/index.md` and the living implementation plan.

Acceptance:

- no new user-facing header text is hardcoded;
- all supported locales render without missing-key fallbacks;
- current documentation and implementation describe the same component
  contract.

### Package 11 — Verification, scoped publication, and follow-up audit

1. Run targeted tests after each package.
2. Run Pint and Larastan before the full suite.
3. Run the complete Pest suite sequentially.
4. Run fresh isolated migration and complete seed, then fixed-seeder
   idempotency.
5. Run Composer validation/security audit and NPM audit/production build.
6. Run route, config, and view cache smoke checks.
7. Run authenticated browser checks on all twelve named routes and the global
   representative matrix.
8. Review complete diff, secrets, documentation, requirements, and query
   delta.
9. Commit coherent packages on `main` with a temporary scoped Git index when
   the shared tree contains unrelated work.
10. Push only verified attributable commits to `origin/main`.
11. Re-run the route/header inventory after cleanup; any remaining unexplained
    variant reopens the plan.

Repeatable priority matrix command:

```bash
BROWSER_BASE_URL=http://127.0.0.1:8898 \
BROWSER_OUTPUT_DIR=/tmp/mengto-page-identity-browser \
npm run test:browser:page-identity
```

## 8. Query Delta Contract

The header itself must add zero queries on every page.

| Area | Before | Required after |
| --- | --- | --- |
| Header component | presentation only | zero queries |
| Existing directory data | current measured baseline | no increase |
| Forum roots | current full-tree preparation may include all children | bounded root navigation query/cache |
| Active forum children | mixed into full hierarchy | one bounded prepared lookup for the selected root, cacheable by locale/root |
| Forum topics | current paginated presenter query | same or fewer queries, no N+1 |
| Meetups | paginated event query with eager loads/aggregates | same bounded pattern, no missing projection data |

Record observed counts in tests or profiling evidence before claiming an
improvement. An estimate is not a passed gate.

## 9. Blade Usage Contract

Eligible pages should reduce to prepared data plus the shared component:

```blade
<x-page-header
    :eyebrow="$header['eyebrow']"
    :title="$header['title']"
    :description="$header['description']"
>
    <x-slot:meta>
        {{ $header['count'] }}
    </x-slot:meta>

    <x-slot:actions>
        {{-- Render only server-authorized, prepared controls. --}}
    </x-slot:actions>
</x-page-header>
```

Blade must not calculate permissions, query categories, derive URLs from IDs,
or call models/services. Loops use prepared collections and meaningful empty
states.

## 10. Filament Integration

Filament is not installed and is outside this plan. No Filament Resource,
widget, panel, dependency, or styling layer will be introduced. If a future
current requirement adds Filament, it must consume the same documented design
tokens independently rather than becoming a prerequisite for portal pages.

## 11. Tests and Quality Gates

### 11.1 Focused automated coverage

- `PageHeaderComponentTest` for slots, escaping, semantic order, and variants;
- a portal page-header contract test covering the twelve named routes plus
  `/messages`;
- a route-classification contract covering every current first-party GET route
  and failing when an unclassified route is added;
- medical record and care journal feature/authorization tests;
- pet, place, lost-and-found, marketplace, expert, group, neighbor, and
  discover feature tests;
- Livewire event directory tests for mount, validation, authorization,
  filtering, pagination, offline/loading-compatible markup, and repeated
  actions;
- event lifecycle migration/schema regression tests;
- forum directory tests for root category, subcategory, invalid hierarchy,
  preserved filters, locale, authorization, empty state, and pagination;
- forum category seed tests preserving 44 roots and 1,637 children;
- architecture tests preventing new duplicate header families and forbidden
  Blade logic.
- messaging interface regressions proving all nine folders remain before the
  messaging shell and only conversations scroll in the inbox.

### 11.2 Browser matrix

For every named route plus `/messages`, test at 320, 375, 768, 1024, 1440, and
1920 px:

- HTTP/application success with no exception page;
- exactly one page `h1`;
- a localized document title consistent with that `h1`;
- canonical header hook and computed typography;
- header alignment with the main content column before local navigation and
  sidebars;
- no horizontal page overflow;
- no overlap, clipping, or detached actions;
- complete keyboard route and visible focus;
- 44 px action targets;
- 200% zoom;
- reduced motion and forced colors;
- no browser console errors;
- `en`, `lt`, and `ru` long-string behaviour.

Additional browser flows:

- create journal action visibility and navigation;
- meetup search/filter/pagination and event opening;
- forum root selection, child selection, direct URL restoration, back/forward
  navigation, empty result, and invalid child handling.
- message folder selection across all nine folders, conversation search, and
  narrow-screen transition without returning the folder toolbar to the inbox.

Observed priority-matrix evidence on 2026-08-03: 91 audits, 26 screenshots,
52 non-English identity audits, 13 forced-colors audits, 13 200%-zoom audits,
and zero overflow, clipping, undersized header targets, legacy headers, focus
failures, raw translation keys, or console errors.

### 11.3 Final repository gates

- `composer validate`;
- Composer security audit;
- dependency compatibility inspection;
- PHP syntax checks;
- Pint;
- Larastan;
- complete sequential Pest suite including architecture tests;
- fresh isolated migration and complete seed;
- fixed-seeder idempotency;
- NPM audit;
- production Vite build;
- route, config, and view cache smoke checks;
- final requirement, documentation, secret, and staged-diff review;
- `git diff --check`.

## 12. Stop Conditions

Stop the current package and report the blocker when any of these occurs:

- the event migration ledger and physical schema disagree and a safe forward
  repair has not been established;
- `/meetups` fails before the visual migration;
- a named page lacks an authoritative route, authorization rule, or product
  requirement needed for its action;
- the forum taxonomy no longer matches the canonical seeded hierarchy without
  a documented requirement change;
- category/subcategory filtering would expose a private topic or bypass a
  visibility scope;
- an implementation would require queries or authorization logic in Blade;
- an `en`, `lt`, or `ru` translation is incomplete;
- a query budget, accessibility check, browser flow, static analysis, build,
  migration, seed, or test gate fails;
- an attributable edit overlaps unrelated uncommitted user/agent work and
  cannot be isolated safely;
- a current design/component/migration document claims a page is standardized
  while the rendered template still uses a legacy family;
- the proposed component API requires a broad breaking change without a
  compatibility wave.

Do not hide or waive a stop condition to keep the visual migration moving.

## 13. Commit Strategy

Work only on `main`. Suggested coherent commits are:

1. tests/docs: establish page-identity and route inventory contracts;
2. fix: harden meetup incremental schema/query compatibility, if still needed;
3. feat: extend canonical page-header component and tokens;
4. refactor: migrate reference and private-care directories;
5. refactor: migrate operational and event directories;
6. feat: expose bounded forum category/subcategory navigation;
7. refactor: migrate remaining portal page identities;
8. chore: remove legacy header families and close documentation evidence.

Each commit includes its own relevant tests and documentation. In a dirty
shared worktree, use a temporary `GIT_INDEX_FILE`, inspect the complete staged
diff, and never commit or revert unrelated work.

## 14. Definition of Done

The plan is complete only when:

- all twelve named routes use the canonical page-identity contract;
- `/messages` uses the canonical page identity while retaining its repaired
  folder and inbox behaviour;
- every first-party GET route has been classified and every eligible rendered
  page has been migrated;
- the portal has one general-purpose page-header family;
- `/meetups` passes fresh-install and incremental-upgrade schema/query tests;
- `/forum` visibly exposes all root categories and only the active root's
  subcategories, with valid server-side filtering;
- header rendering adds no queries and directory query budgets do not regress;
- authorization, privacy, localization, responsive behaviour, keyboard access,
  focus, touch, zoom, forced colors, and no-overflow checks pass;
- obsolete markup and CSS are removed;
- special documents, scoped access, downloads, exports, redirects, auth pages,
  and deliberate detail/profile heroes remain documented rather than being
  forced into the directory component;
- requirements, design documentation, migration matrix, compliance evidence,
  testing notes, and changelog match the implementation;
- every applicable quality gate was actually executed and observed green;
- only attributable commits were pushed to `origin/main`.
