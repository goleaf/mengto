# План измеряемой оптимизации производительности

Дата: 2026-08-30

Статус: `план утверждён запросом пользователя; существующий непубликованный performance-срез проходит повторную независимую проверку и финальные gates`.

Канонический delivery ledger находится в `docs/implementation-plan.md` под
идентификаторами `PERF-AUD-01` — `PERF-AUD-10`. Эксклюзивные read-only области
семи обязательных суперагентов и reviewer-волны фиксируются в
`docs/audits/repository-performance-audit-work-ledger.md`. Чужие staged,
unstaged и untracked изменения не сбрасываются и не попадают в task-owned
временный Git index.

## Baseline и измерения

До принятия нового исправления одинаковые изолированные fixtures измеряют:

- количество, суммарную длительность и медленные SQL-запросы;
- время генерации HTML и API latency, response bytes и peak memory delta;
- число Livewire interaction requests, initial HTML, snapshot и update bytes;
- raw/gzip размеры JS, CSS и динамических chunks;
- число и байтовый объём изображений, intrinsic dimensions и variants;
- время полного migration cycle, fresh seed и повторного seed;
- cold/warm source-query delta и cache hit ratio, когда store его публикует.

Wall time и память остаются наблюдаемыми значениями, если среда не даёт
стабильного portable порога. Query count, cardinality, boundedness, payload
bytes, index choice, cache isolation и invalidation становятся executable
regression budgets.

## Bottlenecks и порядок исправлений

1. Воспроизвести N+1, repeated queries/calculations, over-selection,
   нестабильную или неограниченную выборку.
2. Исправить scope, eager loading/aggregate query, selected columns,
   database pagination и deterministic tie-breaker.
3. Добавить только query-plan-обоснованные additive indexes без дублирующих
   префиксов и с reversible pre-production rollback.
4. Уменьшить только подтверждённое избыточное публичное Livewire state;
   большие derived projections оставить server-side/computed и измерить
   initial/snapshot/update отдельно.
5. Удалять или split-ить frontend assets и oversized images только при
   доказанном consumer/network/variant дефекте; активный совместимый SCSS не
   удалять ради числа.
6. Bounded processing, streaming/chunking и resumable/idempotent ownership
   исправляются до cache.
7. Cache добавляется или меняется только после исправления source query и при
   измеренном cold/warm выигрыше.

## Cache candidates и lifecycle

Для каждого принятого entry обязательны owner, purpose, versioned key,
tenant/user/role/locale/permission scope, TTL, stale semantics, invalidation,
atomic lock TTL, bounded wait, cache-unavailable fallback и tests. Значения не
пересекают tenant, user, role, locale или permission context. Единый key
builder допускается только если реально уменьшает повторяющиеся ошибки scope;
спекулятивная абстракция не создаётся.

Текущие измеренные кандидаты повторной проверки: public-only listing/search
aggregates, forum category-tree audiences и bounded topic-type schema
registry. Private detail/event/care/medical/device projections не кешируются
без отдельного доказательства. Redis принимается как production store для
cache/locks/counters/rate limits/sessions/queues только после deployment
readiness; Memcached не добавляется. `Cache::touch` не используется без
семантически доказанного продления существующего TTL.

## Locks и concurrency

- Cache regeneration: 10-second owner lock, bounded wait не более 2 seconds,
  затем тот же scoped source-query fallback.
- Imports, tokens, payments, commands и concurrent state changes сохраняют
  существующие Action/transaction/idempotency/row-lock boundaries; новые
  distributed locks добавляются только после race reproduction.
- Maintenance/web-batch ownership имеет ограниченный lease, гарантированное
  release, timeout/failure result и безопасный retry/resume.
- Queue не становится критической скрытой зависимостью; допустима только при
  документированном worker и idempotent bounded job.

## Test plan и target measurements

- Query budgets и constant-growth проверки на малом и расширенном fixture;
- отсутствие N+1, bounded pagination/processing и stable ordering;
- cache miss/hit/invalidation/TTL/fallback/stampede, tenant/user/role/locale/
  permission separation;
- lock acquisition, ownership-safe release, timeout и concurrent replay;
- Livewire direct-action authorization плюс initial/snapshot/update byte
  ceilings и duplicate-request browser trace;
- Vite raw/gzip manifest table, image request/byte/dimension/variant audit;
- одинаковые before/after fixtures и команды, затем targeted Pest, Pint,
  Larastan, полный serial Pest, migration/seed/idempotency, Composer/npm
  audits, Vite, cache smokes и browser gates.

Целевые инварианты: 100% валидных строк доступны через server pagination;
query growth остаётся постоянным; ни один cache hit не делает SQL; Livewire
snapshot остаётся не более 24 KiB и update не более 64 KiB для выбранного
максимального fixture; response/export budgets соответствуют каноническому
audit report; asset regression свыше 10% требует доказанного объяснения.

## Независимое ревью и публикация

После freeze task-owned diff запускаются Performance Regression Reviewer,
Cache Leakage Reviewer и Concurrency Reviewer. Каждый material finding
воспроизводится, получает disposition, валидный дефект исправляется test-first,
после чего measurements и affected/full gates повторяются. Commit и push
разрешены только в `main`, через временный `GIT_INDEX_FILE`, после полного
staged-diff/secret/diff-check review и зелёных обязательных gates. Красный
материальный gate означает no-go без commit/push.

---

# План полного аудита локализации

Дата: 2026-08-30

Статус: `план утверждён запросом пользователя; аудит начат; production-код ещё не изменён`.

Канонический delivery ledger остаётся в `docs/implementation-plan.md` под
идентификаторами `LC15-*`. Подробное распределение независимых областей и
исходный Git-снимок находятся в
`docs/audits/localization-audit-work-ledger.md`.

## Архитектурная граница

- Поддерживаемые locales: `en`, `lt`, `ru`; ни один locale не удаляется.
- Source и documented fallback: `en`.
- Единственная система переводов: Laravel language files в `lang/{locale}`.
- JSON-каталоги инвентаризируются, но новая JSON- или JavaScript-система
  переводов не создаётся.
- Locale пользователя хранится в аккаунте; до входа допускается только
  allow-listed session preference. Timezone валидируется отдельно.
- Авторский пользовательский текст, научные имена и стабильные domain-коды не
  переводятся; first-party interface copy переводится стабильными ключами.

## Аудит и исправления

| ID | Область | Исходный статус | Критерий закрытия |
| --- | --- | --- | --- |
| LC15-01 | Supported locales, fallback, middleware, routes, persistence, account/session locale | аудит ожидается | `en`/`lt`/`ru` подтверждены; invalid locale безопасно отклоняется; переключение и persistence проверены |
| LC15-02 | Missing keys, nested-key consistency и raw-key output | аудит ожидается | все используемые first-party ключи разрешаются; деревья и placeholders совпадают; critical UI не печатает raw keys |
| LC15-03 | Hardcoded strings в PHP, Blade, Livewire, JavaScript, validation, exceptions, notifications, mail, API, a11y и SEO | аудит ожидается | классифицированные user-facing literals заменены существующими или новыми читаемыми стабильными ключами во всех locales |
| LC15-04 | Duplicate localization systems | аудит ожидается | подтверждена одна Laravel-архитектура; параллельные каталоги или runtime-переводчики не добавлены |
| LC15-05 | Placeholder mismatches | аудит ожидается | точное множество именованных/позиционных placeholders и escaping совпадает для `en`, `lt`, `ru` |
| LC15-06 | Pluralization defects и sentence fragments | аудит ожидается | counts используют полные plural templates; грамматически зависимые fragments не конкатенируются |
| LC15-07 | Date/time/relative time/number/currency/percentage/list/measurement formatting | аудит ожидается | форматирование locale- и timezone-aware, исходная точность и domain units сохранены |
| LC15-08 | SEO localization | аудит ожидается | title/description/canonical/Open Graph/structured metadata используют разрешённые локализованные значения; не применимые hreflang/RTL решения задокументированы |
| LC15-09 | Unknown or linguistically uncertain translations | аудит ожидается | ключ и documented `en` fallback сохраняются; UI не ломается; элементы для native-speaker review отмечены без ложного статуса verified |
| LC15-10 | Long translation expansion, Unicode и RTL applicability | аудит ожидается | EN/LT/RU проходят responsive/a11y проверки; RTL явно классифицирован как применимый или неприменимый без удаления locale |

## TDD и тест-план

1. Сначала добавить или уточнить failing Pest/architecture contracts для
   обнаруженных дефектов; наблюдать ожидаемый RED.
2. Проверить rendering каждого critical locale, documented fallback,
   missing/raw keys, exact nested-key и placeholder parity.
3. Проверить pluralization и полные грамматические предложения без склейки
   translated fragments.
4. Проверить localized validation, exceptions, notifications, email HTML/text
   и recipient locale, а также user-facing API envelopes.
5. Проверить locale-aware date, time, relative time, number, currency,
   percentage, list и measurement formatting в валидированной timezone.
6. Проверить локализованные SEO title/description и escaping.
7. Проверить locale switching и persistence для account/session, invalid
   locale и documented fallback.
8. Проверить long/Unicode translations и применимость RTL; browser-аудит
   выполняется только в изолированном окружении проекта.
9. Расширить low-false-positive hardcoded-literal ratchet для точных
   user-facing sinks; операторские логи, domain identifiers, тестовые входы и
   пользовательский контент должны иметь явные безопасные исключения.
10. После GREEN выполнить focused tests, localizer checks, Pint, Larastan,
    полный serial Pest, fresh migration/seed, audits/build/cache/browser gates.

## Независимое ревью и публикация

- `LC15-R1`: Translation Coverage Reviewer.
- `LC15-R2`: Locale Behaviour Reviewer.
- `LC15-R3`: Localization Regression Reviewer.
- Каждый finding получает воспроизведение и disposition; валидные замечания
  исправляются, после чего затронутые и финальные проверки повторяются.
- Документация обновляется вместе с кодом: этот файл,
  `docs/implementation-plan.md`, `docs/localization.md`, `AGENTS.md`,
  `docs/testing.md`, `docs/requirements/compliance-matrix.md` и `CHANGELOG.md`.
- Commit и push разрешены только из `main`, через изолированный индекс с
  task-owned diff, после зелёных обязательных gates и финальной проверки
  staged diff. При красном gate публикация запрещена и причина фиксируется.

## Tailwind CSS 4 и CSS-first design system

Дата: 2026-08-30

Статус: утверждено для немедленного read-only аудита семью специалистами,
RED-first реализации, независимого ревью и публикации только после зелёных
проверок на `main`.

### Текущий baseline

- **Tailwind version:** установлены и зафиксированы `tailwindcss@4.3.3` и
  `@tailwindcss/vite@4.3.3`; официальный release feed и npm-тег `latest`
  подтверждают 4.3.3 как текущую стабильную версию.
- **Config architecture:** `tailwind.config.*` отсутствует. `vite.config.js`
  уже подключает `@tailwindcss/vite`; `resources/css/app.css` начинается с
  `@import 'tailwindcss' source(none)`, использует явные `@source`, один
  `@custom-variant`, `@theme`, базовые accessibility и print-правила.
- **Obsolete plugins:** legacy Tailwind plugins/presets, typography/forms
  plugins, старая PostCSS wiring, `postcss-import` и `autoprefixer` не являются
  прямыми зависимостями. Без доказанного consumer ничего не удаляется.
- **PostCSS dependencies:** first-party PostCSS config отсутствует. `postcss`
  остаётся транзитивной lock-зависимостью и не является устаревшим прямым
  пакетом проекта.
- **Source detection:** явный registry покрывает Blade, JavaScript,
  `app/Livewire`, `app/View/Components`, Laravel pagination и Livewire
  pagination. Новые PHP/vendor/generated paths добавляются только после
  доказательства реального utility emitter.
- **Dynamic classes:** существующая architecture-проверка ловит известные
  fragment-patterns, но полный актуальный Blade/PHP/JS scan и emitted-selector
  proof выполняют TW4-A2/TW4-A6.
- **Repeated arbitrary values:** SCSS/utility corpus требует свежего frequency
  inventory. Повтор переносится в intentional token или focused utility;
  одноразовое значение остаётся лишь когда кодирует реальный component fact.
- **Responsive defects:** page overflow, локальные table/filter rails,
  dialog/mobile-keyboard bounds, длинные RU/LT actions, media/grid/card
  geometry и hover-only enhancements проверяются на 320, 375, 768, 1024,
  1280, 1440 и 1920px до правок.
- **Design-token gaps:** текущий Tailwind theme содержит brand/status palette,
  font family, два radius, две shadow, `xs`, один easing/duration и четыре
  z-index. Требуется завершить semantic surface/text/focus, type
  size/leading/weight, spacing, container, breakpoint, radius, shadow,
  transition и animation families.
- **Accessibility defects:** focus возле sticky surfaces, 44px targets,
  forced-colors, reduced motion, long-copy wrapping и pointer/hover capability
  styling изменяются только после точного component/browser evidence.

### Migration steps

1. Зафиксировать dirty-tree baseline и согласовать семь read-only отчетов в
   `docs/audits/tailwind-css4-modernization-work-ledger.md`.
2. Добавить failing architecture contracts для CSS-first import/source/token/
   class safety и обязательных generated selectors.
3. Завершить `@theme` namespaces и только оправданные `@custom-variant`,
   `@utility`, `@variant`, сохранив отдельный SCSS asset.
4. Заменить подтвержденные dynamic fragments полными controlled maps,
   добавить только недостающие sources и токенизировать повторяющиеся
   arbitrary values.
5. Исправить воспроизведенные responsive/accessibility defects mobile-first;
   container queries, logical properties, state/pointer variants, modern
   viewport/scrollbar behavior используются только в подходящих компонентах.
6. Собрать и исследовать CSS, выполнить responsive/keyboard/preference/console
   browser checks и заморозить diff для четырех независимых reviewers.
7. Исправить валидные findings, повторить frontend build/tests, синхронизировать
   документацию/generated evidence и публиковать только после final gates.

### Tests

- focused Pest contracts для source/token/dynamic-class architecture;
- существующие JavaScript syntax/frontend lint команды без выдуманного lint;
- `npm run build`, manifest/raw/gzip comparison и required-selector checks;
- isolated browser runner на полной viewport/locale/preference matrix, включая
  overflow, long strings, keyboard focus и console errors;
- targeted PHP tests, затем Pint, Larastan, полный sequential Pest и применимые
  dependency, migration/seed, cache и generator gates;
- `git diff --check`, полный temporary-index staged diff и secret review.
