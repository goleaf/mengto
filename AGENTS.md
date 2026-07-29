<project-execution-contract>
=== full prompt coverage rules ===

# Giant Plan and Full Prompt Coverage

These rules apply to every substantial task and are mandatory for long, detailed, numbered, or multi-message requests.

## Treat the Full Conversation as the Specification

- Before planning or editing, review every available user message for the current project task from the earliest relevant prompt through the newest one.
- Treat related prompts as one cumulative specification. A newer prompt adds requirements unless it explicitly replaces or contradicts an older requirement.
- When requirements conflict, follow the newest explicit instruction and record which older requirement was superseded.
- Preserve exact constraints, exclusions, naming rules, architecture choices, requested tools, verification methods, and definition-of-done statements.
- Never focus only on the latest message when earlier messages contain unfinished requirements.
- After context compaction or continuation, use the available conversation summary, plans, repository documentation, and current code to reconstruct the complete task contract before continuing.

## Build a Giant Plan Before Implementation

- For every non-trivial or multi-message task, create a detailed implementation plan before changing code.
- The plan must be large enough to cover every requirement individually. Do not collapse a long specification into a few vague phases.
- Convert the complete prompt history into a requirements ledger with stable identifiers.
- For each requirement, record its source, intended behavior, affected surface, dependencies, acceptance criteria, verification method, and current status.
- The plan must cover repository discovery, architecture, data model, authorization, privacy, security, backend behavior, frontend states, responsive behavior, accessibility, performance, moderation, failure states, and verification whenever they apply.
- Identify reusable components, Actions, Services, Requests, Policies, Models, scopes, events, jobs, Blade components, and SCSS primitives before creating page-specific code.
- Distinguish MVP requirements, later-stage requirements, and explicitly excluded work only when the user made that distinction. Do not silently defer requested work.
- Present the plan to the user, then continue into implementation unless the user explicitly requested planning only.
- Keep the plan updated while working. Mark items complete only after implementation and verification evidence exist.

## Autonomous Execution: No Questions

- Do not ask the user clarification, preference, confirmation, scope, continuation, or implementation questions during normal project work.
- Do not pause after presenting a plan. Begin implementation immediately and continue through every phase without waiting for permission to proceed.
- Do not ask whether to continue, whether to run checks, whether to fix discovered in-scope issues, or which reasonable implementation option to choose.
- Resolve uncertainty by inspecting the complete prompt history, repository, schemas, routes, sibling files, documentation, installed skills, and runtime behavior.
- When several valid approaches remain, choose the safest solution that best matches the existing architecture and the complete user specification.
- State necessary assumptions briefly and continue. An assumption is not a reason to stop.
- Treat the user's task as authorization for all non-destructive, in-repository actions required to complete and verify it.
- Automatically perform applicable implementation, formatting, tests, builds, browser checks, responsive checks, accessibility checks, and regression checks.
- If a true blocker requires unavailable credentials, inaccessible external state, destructive authority, or a decision that cannot be inferred safely, finish every unblocked item first. Then report the exact blocker and required external action without turning the response into a questionnaire.
- Never fabricate missing information, bypass security boundaries, expose secrets, or perform destructive external actions merely to avoid asking a question.

## Automatic Commit and Push

- Every completed prompt that changes repository files must end with an automatic Git commit and push before the final response.
- Treat commit and push as acceptance criteria, not optional follow-up work.
- First complete implementation and all applicable verification. Do not publish work that is knowingly broken merely to satisfy this rule.
- Inspect the branch, remotes, upstream, `git status`, staged diff, unstaged diff, and untracked files before staging anything.
- Stage only the files and exact changes owned by the current prompt. Never use `git add .`, `git add -A`, broad path staging, or any command that can absorb unrelated work.
- In a dirty or shared worktree, create the commit with a temporary scoped `GIT_INDEX_FILE` initialized from `HEAD`. Preserve the user's existing normal index, staged changes, unstaged changes, and untracked files.
- Inspect the scoped staged diff before committing and confirm that it contains no secrets, credentials, generated caches, debug artifacts, unrelated formatting, or changes from another task.
- Use a concise Conventional Commit message that accurately describes the current prompt.
- Never amend, squash, reset, rebase, rewrite history, or force-push unless the user explicitly requested that exact operation.
- Push the new commit to the current branch's configured upstream. If no upstream exists, establish the matching branch on `origin` with a normal non-force push.
- After pushing, verify the local commit hash, remote branch hash, upstream relationship, and exact committed file list.
- Do not claim that delivery succeeded when commit or push failed. Preserve the local commit, complete all other safe work, and report the exact failure.
- Do not create empty commits for prompts that make no repository changes.
- A newer explicit instruction from the user not to commit or not to push overrides this default for that prompt only.

## Requirements Ledger

Maintain a checklist or matrix for the full task with these statuses:

- `pending`: understood but not started
- `in_progress`: actively being implemented
- `implemented`: code exists but verification is incomplete
- `verified`: implementation and relevant checks pass
- `blocked`: cannot be completed without a concrete external dependency or user decision
- `superseded`: replaced by a newer explicit user instruction

Every numbered point, bullet, workflow, role, state, permission, privacy rule, responsive rule, and verification request in the user's prompts must map to an entry. Similar items may share one implementation only when the ledger explicitly shows how that implementation satisfies each source requirement.

## Execute the Whole Plan

- Do not stop after producing the plan unless the user asked for a plan only.
- Continue through implementation, integration, validation, and final audit.
- Do not replace a requested feature with an adjacent feature, a visual approximation, or a smaller convenient scope.
- Do not claim a long prompt is complete because its primary screen exists.
- A static fixture, UI-only mock, placeholder link, disabled control, session-only simulation, or hard-coded catalog is not complete functionality when the user requested persistent working behavior.
- If the user explicitly requested a prototype or interface-only implementation, label it accurately and verify only the promised prototype surface.
- Preserve prior completed behavior while adding later prompt sections. Re-check earlier workflows after shared components or architecture change.
- When the task is too large for one uninterrupted pass, continue in ordered phases with the same ledger. Never mark deferred work complete and never hide remaining items.
- Do not request confirmation between phases. Document conservative assumptions, continue automatically, and report only proven blockers.

## End-to-End Prompt Audit

Before finalizing any substantial task:

1. Re-read the available prompt chain from the first relevant message to the last.
2. Re-open the requirements ledger and inspect every entry.
3. Compare each requirement with the actual repository, routes, schemas, controllers, Actions, Services, Policies, views, components, styles, and runtime behavior.
4. Search for prohibited names, prefixes, technologies, placeholders, dead controls, duplicated logic, and unfinished states mentioned anywhere in the prompt history.
5. Verify desktop, tablet, and mobile behavior when UI work is involved.
6. Run the relevant formatter, static checks, focused tests, build, route checks, browser checks, accessibility checks, and runtime smoke tests.
7. Record evidence for every verified requirement.
8. Re-run affected regression checks after fixes.
9. Commit and push the verified prompt-owned changes using the automatic delivery rules.
10. Report every remaining `pending`, `implemented`, or `blocked` item explicitly.

Completion means every in-scope requirement is `verified` or has an honestly documented blocker. A plan, code volume, visual polish, or confidence statement is never completion evidence by itself.

## Final Response Contract

- State what was implemented.
- State what was verified and include the exact evidence.
- State the commit hash and pushed branch when repository files changed.
- State what remains incomplete or blocked.
- Never use phrases such as "everything is complete" or "works 100%" unless the full requirements ledger has been audited and every required gate passed.
- Keep the final response concise, but never omit unfinished requirements from a long prompt.

</project-execution-contract>

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
