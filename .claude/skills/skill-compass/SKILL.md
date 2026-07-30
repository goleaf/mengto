---
name: skill-compass
description: >-
  Configure, verify, or debug this project's Skill Compass routing. Use when a
  task mentions skills, directions.json, UserPromptSubmit, PostToolUse, project
  hooks, automatic skill selection, or the Skill Compass installer.
---

# Skill Compass

Use the project-local Claude Code hooks to recommend the smallest relevant set
of project skills before implementation and when tool output reveals a new area.

## Boundaries

- Keep every file under this repository's `.claude/` directory.
- Never write to `~/.claude`, user settings, or another checkout.
- Do not add runtime packages, telemetry, or network requests.
- Treat injected text as advisory context. The user's request and `AGENTS.md`
  remain authoritative.
- Suggest only skills that exist under `.claude/skills/`.

## Runtime

The two hooks in `.claude/settings.json` invoke:

- `UserPromptSubmit`: scans root manifests and project paths, then matches the
  user's prompt.
- `PostToolUse`: matches relevant findings from `Bash`, `Grep`, `Glob`, `Read`,
  and `Task`.

Both events return Claude Code's `hookSpecificOutput.additionalContext`. The
engine deduplicates an unchanged detection signature for each session and
project.

## Source Of Truth

Edit `.claude/skill-compass/directions.json` to change routing.

Each configured skill must have a corresponding
`.claude/skills/<skill-name>/SKILL.md`. Keep rules specific to this Laravel,
Blade, Tailwind, Eloquent, and Pest codebase.

After changing a route, add or adjust a self-test case in
`.claude/skill-compass/compass.js`.

## Commands

Validate the installed project hooks and required files:

```bash
npm run skill-compass:check
```

Run stack, path, prompt, PostToolUse, deduplication, and skill-existence tests:

```bash
npm run skill-compass:test
```

Idempotently install or repair only this project's settings:

```bash
node .claude/skill-compass/install.js
```

Remove only this project's Skill Compass hook entries while retaining files:

```bash
node .claude/skill-compass/install.js --uninstall
```

Disable the hook for one process:

```bash
SKILL_COMPASS=off claude
```

Force a direct hook smoke test without session deduplication:

```bash
printf '%s' '{"session_id":"manual","prompt":"test Laravel migration"}' \
  | node .claude/skill-compass/compass.js --force
```

## Debugging

1. Run `npm run skill-compass:check`.
2. Parse `.claude/settings.json` and `.claude/skill-compass/directions.json`.
3. Run `npm run skill-compass:test`.
4. Confirm `SKILL_COMPASS` is not `off`.
5. Run a forced prompt smoke test.
6. Run a `PostToolUse` smoke test with a representative tool payload.
7. Verify the emitted skill names exist locally.

The hook deliberately stays silent when neither a project stack nor a matching
task/tool keyword is present.
