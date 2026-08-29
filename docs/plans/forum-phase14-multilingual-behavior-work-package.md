# Forum Phase 14 Multilingual Behavior Work Package

Last updated: 2026-07-31.

Status: implemented and verified for all 41 selected IDs.

## Contract

This package implements source section 72 and its directly linked
localization verification requirements without creating a second translation
system. It covers:

- `forum.translation.0013` through `forum.translation.0038`;
- `forum.category.1401`;
- `forum.moderation.0329` through `forum.moderation.0331`;
- `forum.reputation.0282` through `forum.reputation.0284`;
- `animal.taxonomy.0109`;
- `forum.translation.0040` through `forum.translation.0046`.

The source prompt, generated requirements catalogue, traceability matrix,
current progress, root instructions, localization guide, collaborative-guide
contract, application code, migrations, tests, and current worktree were
reread before this plan was written. The package must end with file, test,
browser, and documentation evidence for every selected requirement.

## Current Implementation Analysis

- Laravel language catalogues under `lang/{en,lt,ru}` are the only
  platform-text translation mechanism. `en` is the configured fallback.
- `SetLocale` allow-lists account/session locale values through
  `config('platform.supported_locales')`.
- The current localization test enforces complete catalogue-key and
  placeholder parity and renders authentication pages in all supported
  locales.
- Forum categories store stable keys plus one database translation row per
  supported locale. Topic-type, moderation, reputation, trust, badge, and
  community-animal-group definitions store stable translation keys.
- Public forum and administration interfaces use catalogue keys, but no
  domain-level test currently proves that every seeded definition key
  resolves in every locale.
- Community guides support one article per locale in a stable translation
  family. They preserve each locale version as a separate article and support
  versioned corrections, but do not record the source article or the
  translation source explicitly and expose no connected action for creating a
  translated version.
- Draft and group-private guides are authorization-sensitive. A translation
  path must not expose or copy them for an unauthorized actor.
- Taxon scientific names are source data and are not translated. Common names
  have locale, source, preferred, and verified metadata, but selector results
  do not use one canonical verified locale-to-fallback-to-scientific order.
- There is no generic automatic or artificial-intelligence translation
  service. This package will not introduce one.

## Desired Result

All platform-controlled forum text must resolve through the existing EN/LT/RU
catalogues or database category translations. Seeded definition translation
keys must be checked in every supported locale. Community guide translations
must retain a durable source relation, identify a human-community source,
remain independently versioned and correctable, and require authorization for
non-public source content. Taxonomy presentation must preserve scientific
names exactly while selecting verified localized common names with a
deterministic scientific-name fallback.

## Affected Files

Expected production additions or modifications:

- `database/migrations/*_add_knowledge_translation_provenance.php`
- `app/Enums/KnowledgeTranslationSource.php`
- `app/Actions/CreateKnowledgeGuideTranslation.php`
- `app/Models/KnowledgeArticle.php`
- `app/Policies/KnowledgeArticlePolicy.php`
- `app/Http/Controllers/KnowledgeGuideTranslationCreateController.php`
- `app/Livewire/Forum/KnowledgeGuideEditor.php`
- `app/Livewire/Forms/KnowledgeGuideForm.php`
- `app/Services/KnowledgePresenter.php`
- `app/Services/LocalizedTaxonName.php`
- `app/Livewire/Forum/AnimalTaxonomySelector.php`
- `routes/web.php`
- `resources/views/knowledge/show.blade.php`
- `resources/views/livewire/forum/knowledge-guide-editor.blade.php`
- `lang/{en,lt,ru}/knowledge.php`
- `lang/{en,lt,ru}/taxonomy.php`

Expected tests:

- `tests/Feature/Forum/ForumMultilingualBehaviorTest.php`
- `tests/Feature/Forum/CollaborativeGuideWorkflowTest.php`
- `tests/Feature/LocalizationTest.php`
- `tests/Feature/ArchitectureComplianceTest.php`
- affected schema/factory/seeder tests

Expected documentation:

- `docs/localization.md`
- `docs/guides.md`
- `docs/data-model.md`
- `docs/testing.md`
- `docs/plans/forum-current-progress.md`
- `docs/traceability/forum-requirements-matrix.md`
- deterministic requirement evidence files

## Implementation Plan

### Pass 1: Platform-Controlled Catalogue Coverage

Requirement IDs:

- `forum.translation.0013` through `forum.translation.0028`;
- `forum.category.1401`;
- `forum.moderation.0329` through `forum.moderation.0331`;
- `forum.reputation.0282` through `forum.reputation.0284`;
- `animal.taxonomy.0109`;
- `forum.translation.0041` through `forum.translation.0043`.

Actions:

1. Inventory category, topic-type, moderation, appeal, trust, reputation,
   badge, notification, accessibility, empty-state, search-filter, safety,
   legal, medical, and taxonomy interface keys used by the current forum.
2. Extend localization tests to load fixed definitions and prove every stored
   translation key resolves in EN, LT, and RU.
3. Retain complete key and placeholder parity, plural semantics, and localized
   validation.
4. Fix any raw key, hardcoded platform label, or missing catalogue entry
   discovered by the tests.

Acceptance:

- Every selected platform-controlled definition resolves in all supported
  locales without returning the raw key.
- Category names and descriptions exist for each supported locale.
- Existing architecture checks report no new hardcoded first-party text.
- Existing catalogue key and placeholder parity remains exact.

### Pass 2: User-Generated Translation Provenance

Requirement IDs:

- `forum.translation.0029` through `forum.translation.0036`;
- `forum.translation.0045`.

Actions:

1. Add nullable, additive translation provenance to community guides:
   source article, controlled source type, and translator identity through the
   existing creator relation.
2. Backfill existing locale families conservatively: preserve every article,
   infer no source when provenance is ambiguous, and never rewrite content.
3. Add an authorized transactional action for creating a translation draft
   from a visible source guide.
4. Connect the action to the class-based Livewire guide editor and a named
   authenticated route.
5. Keep title, summary, and body empty for the translator to supply; copy only
   non-prose scope metadata and cited sources. Never silently replace the
   source article.
6. Display the source language, source title, and human-community translation
   label. Retain the existing correction and immutable version workflow.
7. Require source visibility and group membership/update authority for private
   group guidance.

Acceptance:

- The original article and all versions remain unchanged after translation
  creation and later translation edits.
- One locale per translation family remains database-enforced.
- Public translations identify their source article and translation source.
- Draft/private source content cannot be translated by an unauthorized user.
- Translation correction uses the existing versioned correction workflow.

### Pass 3: Scientific And Common Taxon Names

Requirement IDs:

- `forum.translation.0037`;
- `forum.translation.0038`;
- `forum.translation.0044`;
- `forum.translation.0046`;
- `animal.taxonomy.0109`.

Actions:

1. Introduce one presentation service for localized taxon names.
2. Select a verified preferred common name for the requested locale, then a
   verified alternate for that locale, then the configured fallback locale,
   and finally the exact scientific name.
3. Keep source scientific names byte-for-byte unchanged and never pass them
   through Laravel translation.
4. Use the service in the reusable Livewire taxonomy selector for selected and
   searched taxa without loading the full tree or introducing N+1 queries.
5. Preserve synonym and ambiguity indicators and expose the matched alias only
   as context, never as a replacement for the accepted scientific identity.

Acceptance:

- Scientific names render identically in EN, LT, and RU.
- Verified localized common names win only according to the documented order.
- Missing common names fall back to the scientific name.
- Unverified names do not silently become preferred display facts.
- Selector queries remain bounded and eager loaded.

### Pass 4: Cache And Final Localization Verification

Requirement IDs:

- `forum.translation.0040`;
- `forum.translation.0041` through `forum.translation.0046`.

Actions:

1. Verify category cache keys vary by locale.
2. Verify taxonomy caches contain only language-independent source or stable
   grouping data; localized common names remain bounded database lookups.
3. Verify category translation updates clear every supported locale cache and
   taxonomy activation clears the language-independent taxonomy caches.
4. Add browser coverage for EN, LT, and RU forum pages, translation-source
   presentation, responsive behavior, locale switching, and raw-key
   detection. Keep connected draft creation, source attribution, taxonomy
   fallback, and private-source denial in deterministic PHP/Livewire tests.
5. Synchronize documentation and deterministic traceability evidence only
   after all relevant checks pass.

Acceptance:

- A translation update cannot serve a stale cross-locale category or taxonomy
  label.
- Critical forum pages render in every locale without raw keys.
- No private source content appears in unauthorized responses, search, or
  translation forms.
- Every selected requirement has file-level and passing-test evidence.

## Schema, Migration, And Backfill

The migration is additive:

- nullable `translated_from_article_id` references `knowledge_articles` with
  `nullOnDelete`;
- nullable `translation_source` stores a controlled enum value;
- a composite index supports source/family lookups.

Existing rows retain their IDs, slugs, translation groups, locale, content,
versions, collaborators, corrections, topics, and publication state. The
backfill does not guess an original when a family contains multiple existing
locale variants. New translations record provenance at creation time.

Rollback before production use may remove the additive columns. After
translation provenance exists, operational rollback must retain the columns
or export the relationships before schema rollback; application rollback can
ignore the nullable metadata safely.

## Legacy Compatibility

- Existing standalone guides and existing translation families remain valid.
- Existing public locale links continue to work.
- Existing category and taxonomy identifiers are unchanged.
- No topic, reply, reaction, subscription, bookmark, report, attachment, pet,
  adoption, lost/found, marketplace, or administrator-created category row is
  rewritten.
- Existing unverified common-name rows remain searchable but do not become the
  preferred display label without verification.

## Authorization And Validation

- Translation creation requires an active verified user with guide-create
  capability and source-view authorization.
- Group-private source guides additionally require group content access; an
  actor without that access receives no source metadata.
- Target locale is allow-listed, must differ from the source locale, and must
  be unique in the translation family.
- Public Livewire source IDs are locked and reloaded server-side.
- All content fields retain the existing bounded guide validation.

## Privacy, Security, And Abuse Risks

- Translation must not expose draft, private-group, correction, lock, or
  unpublished-version content to an unauthorized actor.
- No remote translation or network request is introduced.
- Source URLs remain rendered only as links and are not fetched.
- User prose remains escaped plain text.
- Locale and provenance identifiers are controlled values, not arbitrary
  executable translation keys.
- Translation creation is transaction-safe and database uniqueness prevents
  duplicate locale races.

## Cache Impact

Category trees retain versioned locale-scoped keys and synchronization clears
every supported locale. Taxonomy high-level caches contain language-independent
source hierarchy or stable grouping identifiers and are invalidated after an
active-version change; localized common names are bounded database queries and
are not stored in those shared cache values. Guide translation families are
queried directly as bounded public rows; no new guide cache is introduced.

## Tests

Create or update PHP tests for:

- all supported locale catalogue and placeholder parity;
- every seeded category and definition translation key;
- localized validation and critical page rendering;
- original user content preservation;
- explicit translation source and source-article attribution;
- correction availability for translated guidance;
- source/target locale validation and unique-family race protection;
- private/group source authorization;
- direct Livewire action authorization and locked source identity;
- scientific-name invariance across locales;
- verified common-name preference and scientific fallback;
- bounded taxonomy selector queries and no full-tree browser state;
- locale-scoped category/taxonomy cache invalidation;
- architecture restrictions and no parallel translation system.

Browser verification must cover EN and RU public forum rendering plus the
authenticated Lithuanian translation-source editor at desktop and mobile
widths. It must inspect locale switching, raw translation keys, source
presentation, empty target prose, focus, overflow, controls, and console
errors. Deterministic PHP/Livewire tests cover connected draft creation,
public attribution, taxonomy fallback, and private-source denial.

## Documentation

Update localization with the platform/UGC boundary, translation provenance,
fallback order, cache invalidation, and contributor workflow. Update the guide
contract with translation creation, authorization, correction, rollback, and
source attribution. Update the data model, tests, progress, changelog where
applicable, and requirement evidence after verification.

## Verification Procedure

1. Inspect the complete diff.
2. Run focused multilingual, guide, taxonomy, definition, architecture, and
   cache tests.
3. Run Pint on modified PHP files.
4. Run Larastan over first-party code.
5. Run the production Vite build if interface assets changed.
6. Run EN/LT/RU browser checks at 375x812 and 1440x900.
7. Run the full serial PHP suite.
8. Run fresh migration, full seed, and repeated seed in isolated SQLite.
9. Run Composer validation/audit and npm audit.
10. Run source-preservation and requirements-generation checks.
11. Recalculate evidence and prove every selected ID has passing evidence.
12. Inspect the final diff, staged content, and repository status before
    commit and push.

## Completion Evidence

All 41 selected IDs are implemented and verified:

- final focused multilingual/guide/auth/localization/architecture regression:
  75 tests, 56,753 assertions;
- final serial Pest: 1,684 tests, 64,597 assertions, 97.037 seconds;
- full Pint: passed;
- Larastan/PHPStan level 5: zero errors;
- fresh temporary SQLite: 99 migrations, 172 tables, five users before and
  after repeated `DatabaseSeeder`;
- additive migration rollback and re-application: passed;
- Composer strict validation and audit: passed, zero advisories;
- npm high-severity audit: passed, zero vulnerabilities;
- Vite 8.2.0 production build: passed;
- config, event, route, and view cache compilation: passed;
- requirement generation and source checksum checks: passed for 7,284 atomic
  records and SHA-256
  `6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773`;
- dependency-free headless Chrome: EN public forum at desktop/mobile/320px,
  a real Livewire locale change, LT translation-source editor at desktop and
  375px, RU forum, restored original locale, and no raw keys, horizontal
  overflow, unnamed controls, invalid tables/images, or console errors.

The coverage command was executed but PHP 8.5 has neither Xdebug nor PCOV, so
percentage coverage remains an environmental limitation rather than a passing
claim. Existing topics, replies, reactions, votes, subscriptions, bookmarks,
reports, moderation cases, attachments, pet profiles, adoption cases,
lost/found cases, marketplace records, translations, and
administrator-created categories were not rewritten.
