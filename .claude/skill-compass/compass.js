#!/usr/bin/env node

const crypto = require('crypto');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CONFIG_PATH = path.join(__dirname, 'directions.json');
const DEPENDENCY_FILES = new Set([
  'composer.json',
  'package.json',
  'requirements.txt',
  'requirements-dev.txt',
  'pyproject.toml',
  'Cargo.toml',
  'go.mod',
  'Gemfile',
  'pubspec.yaml'
]);
const SKIPPED_DIRECTORIES = new Set([
  '.git',
  '.next',
  '__pycache__',
  'build',
  'dist',
  'node_modules',
  'storage',
  'target',
  'vendor'
]);

function loadConfig() {
  try {
    return JSON.parse(fs.readFileSync(CONFIG_PATH, 'utf8').replace(/^\uFEFF/, ''));
  } catch (error) {
    process.stderr.write(`skill-compass: cannot read directions.json: ${error.message}\n`);
    return null;
  }
}

const CONFIG = loadConfig();

function globToRegExp(pattern) {
  const escaped = pattern
    .replace(/[.+^${}()|[\]\\]/g, '\\$&')
    .replace(/\*/g, '.*');

  return new RegExp(`^${escaped}$`, 'i');
}

function scan(root) {
  const rootFilePaths = [];
  const names = new Set();
  const relativePaths = new Set();
  const extensions = new Set();
  let dependencyText = '';

  function walk(directory, depth) {
    let entries;

    try {
      entries = fs.readdirSync(directory, { withFileTypes: true });
    } catch {
      return;
    }

    for (const entry of entries) {
      if (entry.name.startsWith('.git')) {
        continue;
      }

      const absolutePath = path.join(directory, entry.name);
      const relativePath = path.relative(root, absolutePath).split(path.sep).join('/').toLowerCase();

      names.add(entry.name.toLowerCase());
      relativePaths.add(relativePath);

      if (entry.isDirectory()) {
        if (depth < 2 && !SKIPPED_DIRECTORIES.has(entry.name)) {
          walk(absolutePath, depth + 1);
        }

        continue;
      }

      const extension = path.extname(entry.name).toLowerCase();

      if (extension) {
        extensions.add(extension);
      }

      if (entry.name.toLowerCase().endsWith('.blade.php')) {
        extensions.add('.blade.php');
      }

      if (depth !== 0) {
        continue;
      }

      rootFilePaths.push(absolutePath);

      if (DEPENDENCY_FILES.has(entry.name)) {
        try {
          dependencyText += `\n${fs.readFileSync(absolutePath, 'utf8').toLowerCase()}`;
        } catch {
          // The hook is advisory and must never block Claude Code on an unreadable file.
        }
      }
    }
  }

  walk(root, 0);

  return {
    dependencyText,
    extensions,
    names,
    relativePaths,
    rootFilePaths
  };
}

function indicatorMatches(indicator, scanResult) {
  const expression = globToRegExp(indicator.file);
  const filePath = scanResult.rootFilePaths.find(candidate => expression.test(path.basename(candidate)));

  if (!filePath) {
    return false;
  }

  if (!indicator.contains) {
    return true;
  }

  try {
    return fs
      .readFileSync(filePath, 'utf8')
      .toLowerCase()
      .includes(indicator.contains.toLowerCase());
  } catch {
    return false;
  }
}

function detect(root) {
  const scanResult = scan(root);
  const stacks = (CONFIG?.stacks || []).filter(stack =>
    (stack.indicators || []).some(indicator => indicatorMatches(indicator, scanResult))
  );
  const directions = (CONFIG?.directions || []).filter(direction =>
    (direction.deps || []).some(dependency =>
      scanResult.dependencyText.includes(dependency.toLowerCase())
    ) ||
    (direction.paths || []).some(candidate => {
      const normalized = candidate.toLowerCase().replace(/\\/g, '/');

      return scanResult.names.has(normalized) || scanResult.relativePaths.has(normalized);
    }) ||
    (direction.ext || []).some(extension =>
      scanResult.extensions.has(extension.toLowerCase())
    )
  );

  return {
    always: CONFIG?.always || [],
    directions,
    stacks
  };
}

function keywordMatches(term, prompt) {
  const normalizedTerm = term.toLowerCase();

  if (/\s/.test(normalizedTerm)) {
    return prompt.includes(normalizedTerm);
  }

  const escaped = normalizedTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

  try {
    return new RegExp(
      `(^|[^\\p{L}\\p{N}])${escaped}([^\\p{L}\\p{N}]|$)`,
      'iu'
    ).test(prompt);
  } catch {
    return prompt.includes(normalizedTerm);
  }
}

function detectKeywords(prompt) {
  const normalizedPrompt = String(prompt || '').toLowerCase();

  if (!normalizedPrompt) {
    return [];
  }

  return (CONFIG?.keywords || []).filter(keyword =>
    (keyword.match || []).some(term => keywordMatches(term, normalizedPrompt))
  );
}

function render(detection, keywords = []) {
  if (!detection.stacks.length && !keywords.length) {
    return '';
  }

  const sources = [
    ...keywords.map(item => ({ item, reason: 'по тексту задачи' })),
    ...detection.stacks.map(item => ({ item, reason: 'по стеку проекта' })),
    ...detection.directions.map(item => ({ item, reason: 'по структуре проекта' })),
    ...detection.always.map(item => ({ item, reason: 'обязательная проверка' }))
  ];
  const emittedSkills = new Set();
  const lines = [];

  for (const source of sources) {
    const newSkills = (source.item.skills || []).filter(skill => {
      if (emittedSkills.has(skill)) {
        return false;
      }

      emittedSkills.add(skill);

      return true;
    });

    if (newSkills.length) {
      lines.push(`- ${source.item.name} (${source.reason}): ${newSkills.join(', ')}`);
    }
  }

  if (!lines.length) {
    return '';
  }

  return [
    'Skill Compass: перед изменениями подключи релевантные проектные skills:',
    ...lines,
    'Используй их как рабочие инструкции и сохрани требования текущей задачи приоритетными.'
  ].join('\n');
}

function signature(detection, keywords = []) {
  const records = [
    ...detection.stacks.map(item => ['stack', item]),
    ...detection.directions.map(item => ['direction', item]),
    ...detection.always.map(item => ['always', item]),
    ...keywords.map(item => ['keyword', item])
  ]
    .map(([type, item]) => `${type}:${item.id}:${item.name}:${(item.skills || []).join(',')}`)
    .sort();

  return crypto.createHash('sha1').update(records.join('|')).digest('hex');
}

function markerPath(root, sessionId) {
  const session = sessionId || `process-${process.pid}`;
  const key = crypto
    .createHash('sha1')
    .update(`${session}|${root}`)
    .digest('hex')
    .slice(0, 20);

  return path.join(os.tmpdir(), 'mengto-skill-compass', `${key}.txt`);
}

function shouldEmit(marker, currentSignature, force = false) {
  if (!force) {
    try {
      if (fs.readFileSync(marker, 'utf8') === currentSignature) {
        return false;
      }
    } catch {
      // A missing marker means this signature has not been emitted in this session.
    }
  }

  try {
    fs.mkdirSync(path.dirname(marker), { recursive: true });
    fs.writeFileSync(marker, currentSignature);
  } catch {
    // Failure to store a marker must not block this advisory hook.
  }

  return true;
}

function emit(additionalContext, hookEventName) {
  process.stdout.write(JSON.stringify({
    hookSpecificOutput: {
      additionalContext,
      hookEventName
    }
  }));
}

function safeJson(value) {
  const seen = new WeakSet();

  try {
    return JSON.stringify(value, (key, nestedValue) => {
      if (nestedValue && typeof nestedValue === 'object') {
        if (seen.has(nestedValue)) {
          return '[Circular]';
        }

        seen.add(nestedValue);
      }

      return nestedValue;
    });
  } catch {
    return '[Unserializable]';
  }
}

function toolText(input) {
  const parts = [];

  if (input.tool_name) {
    parts.push(String(input.tool_name));
  }

  parts.push(safeJson(input.tool_input || {}));

  if (typeof input.tool_response === 'string') {
    parts.push(input.tool_response);
  } else if (input.tool_response && typeof input.tool_response === 'object') {
    parts.push(
      typeof input.tool_response.text === 'string'
        ? input.tool_response.text
        : safeJson(input.tool_response)
    );
  }

  return parts.join('\n').slice(0, 50000);
}

function selfTest() {
  if (!CONFIG) {
    process.exit(1);
  }

  const temporaryRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'mengto-compass-test-'));
  let failures = 0;

  function create(relativePath, contents = '') {
    const filePath = path.join(temporaryRoot, relativePath);

    fs.mkdirSync(path.dirname(filePath), { recursive: true });
    fs.writeFileSync(filePath, contents);
  }

  function check(name, condition) {
    if (condition) {
      process.stdout.write(`ok: ${name}\n`);

      return;
    }

    failures++;
    process.stderr.write(`FAIL: ${name}\n`);
  }

  try {
    const projectRoot = path.join(temporaryRoot, 'laravel-app');

    create('laravel-app/artisan', '#!/usr/bin/env php');
    create('laravel-app/composer.json', JSON.stringify({
      require: {
        'laravel/framework': '^12.0',
        'laravel/sanctum': '^4.0'
      },
      'require-dev': {
        'pestphp/pest': '^3.0'
      }
    }));
    create('laravel-app/package.json', JSON.stringify({
      devDependencies: {
        tailwindcss: '^4.0'
      }
    }));
    create('laravel-app/resources/views/home.blade.php', '<x-layout />');
    create('laravel-app/app/Http/Controllers/HomeController.php', '<?php');
    create('laravel-app/app/Models/User.php', '<?php');
    create('laravel-app/app/Policies/UserPolicy.php', '<?php');
    create('laravel-app/database/migrations/create_users_table.php', '<?php');
    create('laravel-app/tests/Feature/HomeTest.php', '<?php');

    const detection = detect(projectRoot);
    const scanResult = scan(projectRoot);

    check('Laravel stack detected', detection.stacks.some(item => item.id === 'php-laravel'));
    check('Blade and Tailwind direction detected', detection.directions.some(item => item.id === 'ui'));
    check('backend direction detected', detection.directions.some(item => item.id === 'backend'));
    check('database direction detected', detection.directions.some(item => item.id === 'database'));
    check('testing direction detected', detection.directions.some(item => item.id === 'testing'));
    check('security direction detected', detection.directions.some(item => item.id === 'security'));
    check('nested relative path recorded', scanResult.relativePaths.has('resources/views'));
    check('Blade extension recorded', scanResult.extensions.has('.blade.php'));

    check(
      'English prompt routes UI',
      detectKeywords('Build an accessible Blade component').some(item => item.id === 'ui')
    );
    check(
      'Russian prompt routes database',
      detectKeywords('Проверь миграцию и индекс').some(item => item.id === 'database')
    );
    check(
      'transliterated prompt routes security',
      detectKeywords('prover bezopasnost platezha').some(item => item.id === 'security')
    );
    check('empty prompt has no keywords', detectKeywords('').length === 0);
    check(
      'single word does not match a substring',
      !keywordMatches('test', 'the latest testament is unrelated')
    );

    const rendered = render(detection, detectKeywords('test database'));
    const pestMentions = rendered.match(/pest-testing/g) || [];

    check('render emits context', rendered.startsWith('Skill Compass:'));
    check('render deduplicates skills', pestMentions.length === 1);

    const emptyRoot = path.join(temporaryRoot, 'empty');

    fs.mkdirSync(emptyRoot, { recursive: true });
    check('empty directory stays silent', render(detect(emptyRoot)) === '');
    check(
      'prompt signal works without a project manifest',
      render(detect(emptyRoot), detectKeywords('skill-compass')).includes('skill-compass')
    );
    check(
      'signature changes when task signal changes',
      signature(detect(emptyRoot)) !== signature(detect(emptyRoot), detectKeywords('skill-compass'))
    );

    const deduplicationMarker = markerPath(
      projectRoot,
      `self-test-${process.pid}-${Date.now()}`
    );
    const deduplicationSignature = signature(detection);

    check(
      'first session signature emits',
      shouldEmit(deduplicationMarker, deduplicationSignature)
    );
    check(
      'unchanged session signature is deduplicated',
      !shouldEmit(deduplicationMarker, deduplicationSignature)
    );
    check(
      'force bypasses session deduplication',
      shouldEmit(deduplicationMarker, deduplicationSignature, true)
    );
    fs.rmSync(deduplicationMarker, { force: true });

    check(
      'PostToolUse routes a discovered migration',
      detectKeywords(toolText({
        tool_input: { pattern: 'database/migrations/*' },
        tool_name: 'Glob',
        tool_response: { text: 'database/migrations/create_users_table.php' }
      })).some(item => item.id === 'database')
    );

    const circular = {};

    circular.self = circular;
    check(
      'tool payload handles circular data',
      toolText({ tool_response: circular }).includes('[Circular]')
    );
    check(
      'tool payload is capped',
      toolText({ tool_response: 'x'.repeat(60000) }).length === 50000
    );

    const skillsRoot = path.resolve(__dirname, '..', 'skills');
    const configuredSkills = new Set([
      ...(CONFIG.stacks || []).flatMap(item => item.skills || []),
      ...(CONFIG.directions || []).flatMap(item => item.skills || []),
      ...(CONFIG.keywords || []).flatMap(item => item.skills || []),
      ...(CONFIG.always || []).flatMap(item => item.skills || [])
    ]);

    check(
      'every configured skill exists in this project',
      [...configuredSkills].every(skill =>
        fs.existsSync(path.join(skillsRoot, skill, 'SKILL.md'))
      )
    );

    const settingsPath = path.resolve(__dirname, '..', 'settings.json');
    const settings = JSON.parse(fs.readFileSync(settingsPath, 'utf8'));
    const settingsText = JSON.stringify(settings);

    check('UserPromptSubmit hook configured', Boolean(settings.hooks?.UserPromptSubmit?.length));
    check('PostToolUse hook configured', Boolean(settings.hooks?.PostToolUse?.length));
    check(
      'hooks use the project directory variable',
      settingsText.includes('${CLAUDE_PROJECT_DIR}/.claude/skill-compass/compass.js')
    );
    check(
      'upstream commit is pinned',
      CONFIG.upstream?.commit === 'de3810304e274cb855b9ddfbf2d40317d0bcf9d0'
    );
  } finally {
    fs.rmSync(temporaryRoot, { force: true, recursive: true });
  }

  process.stdout.write(failures ? `\n${failures} FAILED\n` : '\nALL PASSED\n');
  process.exit(failures ? 1 : 0);
}

function main() {
  if (process.argv.includes('--self-test')) {
    selfTest();

    return;
  }

  if (!CONFIG || String(process.env.SKILL_COMPASS || '').toLowerCase() === 'off') {
    return;
  }

  let input = {};

  try {
    input = JSON.parse((fs.readFileSync(0, 'utf8') || '{}').replace(/^\uFEFF/, ''));
  } catch {
    // Invalid hook payloads stay silent so the advisory hook cannot add false context.
    return;
  }

  const projectRoot = path.resolve(
    process.env.CLAUDE_PROJECT_DIR || input.cwd || process.cwd()
  );
  const isPostToolUse =
    process.argv.includes('--post') ||
    input.hook_event_name === 'PostToolUse';
  const hookEventName = isPostToolUse ? 'PostToolUse' : 'UserPromptSubmit';
  const detection = isPostToolUse
    ? { always: CONFIG.always || [], directions: [], stacks: [] }
    : detect(projectRoot);
  const keywords = detectKeywords(
    isPostToolUse ? toolText(input) : input.prompt
  );
  const additionalContext = render(detection, keywords);

  if (!additionalContext) {
    return;
  }

  const currentSignature = signature(detection, keywords);
  const marker = markerPath(projectRoot, input.session_id);

  if (!shouldEmit(marker, currentSignature, process.argv.includes('--force'))) {
    return;
  }

  emit(additionalContext, hookEventName);
}

main();
