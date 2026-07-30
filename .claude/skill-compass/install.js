#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const SOURCE_DIRECTORY = __dirname;
const SOURCE_SKILL_DIRECTORY = path.resolve(__dirname, '..', 'skills', 'skill-compass');
const PROJECT_ROOT = path.resolve(
  process.env.SKILL_COMPASS_PROJECT_DIR || path.resolve(__dirname, '..', '..')
);
const CLAUDE_DIRECTORY = path.join(PROJECT_ROOT, '.claude');
const DESTINATION_DIRECTORY = path.join(CLAUDE_DIRECTORY, 'skill-compass');
const DESTINATION_SKILL_DIRECTORY = path.join(CLAUDE_DIRECTORY, 'skills', 'skill-compass');
const SETTINGS_PATH = path.join(CLAUDE_DIRECTORY, 'settings.json');
const COMPASS_COMMAND = 'node "${CLAUDE_PROJECT_DIR}/.claude/skill-compass/compass.js"';
const POST_TOOL_COMMAND = `${COMPASS_COMMAND} --post`;
const POST_TOOL_MATCHER = 'Bash|Grep|Glob|Read|Task';
const CHECK_MODE = process.argv.includes('--check');
const UNINSTALL_MODE = process.argv.includes('--uninstall');

function readSettings() {
  if (!fs.existsSync(SETTINGS_PATH)) {
    return {};
  }

  try {
    return JSON.parse(fs.readFileSync(SETTINGS_PATH, 'utf8').replace(/^\uFEFF/, ''));
  } catch (error) {
    throw new Error(`cannot parse ${SETTINGS_PATH}: ${error.message}`);
  }
}

function writeSettings(settings) {
  fs.mkdirSync(path.dirname(SETTINGS_PATH), { recursive: true });
  fs.writeFileSync(SETTINGS_PATH, `${JSON.stringify(settings, null, 2)}\n`);
}

function commandHooks(groups = []) {
  return groups.flatMap(group => group.hooks || []);
}

function hasCommand(groups, command) {
  return commandHooks(groups).some(hook => hook.command === command);
}

function isSkillCompassHook(hook) {
  return String(hook.command || '').includes('/.claude/skill-compass/compass.js');
}

function removeSkillCompassHooks(groups = []) {
  return groups
    .map(group => ({
      ...group,
      hooks: (group.hooks || []).filter(hook => !isSkillCompassHook(hook))
    }))
    .filter(group => group.hooks.length);
}

function addHookGroup(groups, matcher, command) {
  if (hasCommand(groups, command)) {
    return;
  }

  const matchingGroup = groups.find(group => String(group.matcher || '') === matcher);
  const hook = {
    command,
    timeout: 5,
    type: 'command'
  };

  if (matchingGroup) {
    matchingGroup.hooks = matchingGroup.hooks || [];
    matchingGroup.hooks.push(hook);

    return;
  }

  groups.push({
    hooks: [hook],
    matcher
  });
}

function copyFile(fileName, preserveExisting = false) {
  const source = path.join(SOURCE_DIRECTORY, fileName);
  const destination = path.join(DESTINATION_DIRECTORY, fileName);

  if (path.resolve(source) === path.resolve(destination)) {
    return;
  }

  if (preserveExisting && fs.existsSync(destination)) {
    return;
  }

  fs.mkdirSync(path.dirname(destination), { recursive: true });
  fs.copyFileSync(source, destination);
}

function copySkill() {
  const source = path.join(SOURCE_SKILL_DIRECTORY, 'SKILL.md');
  const destination = path.join(DESTINATION_SKILL_DIRECTORY, 'SKILL.md');

  if (path.resolve(source) === path.resolve(destination)) {
    return;
  }

  fs.mkdirSync(path.dirname(destination), { recursive: true });
  fs.copyFileSync(source, destination);
}

function runCheck() {
  const failures = [];
  let settings = {};

  try {
    settings = readSettings();
  } catch (error) {
    failures.push(error.message);
  }

  const requiredFiles = [
    path.join(DESTINATION_DIRECTORY, 'compass.js'),
    path.join(DESTINATION_DIRECTORY, 'directions.json'),
    path.join(DESTINATION_DIRECTORY, 'install.js'),
    path.join(DESTINATION_DIRECTORY, 'LICENSE'),
    path.join(DESTINATION_DIRECTORY, 'package.json'),
    path.join(DESTINATION_SKILL_DIRECTORY, 'SKILL.md')
  ];

  for (const filePath of requiredFiles) {
    if (!fs.existsSync(filePath)) {
      failures.push(`missing ${filePath}`);
    }
  }

  if (!hasCommand(settings.hooks?.UserPromptSubmit, COMPASS_COMMAND)) {
    failures.push('UserPromptSubmit hook is not configured');
  }

  if (!hasCommand(settings.hooks?.PostToolUse, POST_TOOL_COMMAND)) {
    failures.push('PostToolUse hook is not configured');
  }

  const hookText = JSON.stringify(settings.hooks || {});

  if (
    hookText.includes('~/.claude/skill-compass') ||
    /(?:\/Users\/|[A-Za-z]:\\\\Users\\\\).+?\.claude[\\/]skill-compass/.test(hookText)
  ) {
    failures.push('a hook points to a user-global skill-compass path');
  }

  if (!hookText.includes('${CLAUDE_PROJECT_DIR}/.claude/skill-compass/compass.js')) {
    failures.push('hooks do not use ${CLAUDE_PROJECT_DIR}');
  }

  if (failures.length) {
    failures.forEach(failure => process.stderr.write(`FAIL: ${failure}\n`));
    process.exit(1);
  }

  process.stdout.write(`skill-compass: project-local setup is valid in ${PROJECT_ROOT}\n`);
}

function uninstall() {
  const settings = readSettings();

  if (settings.hooks?.UserPromptSubmit) {
    settings.hooks.UserPromptSubmit = removeSkillCompassHooks(
      settings.hooks.UserPromptSubmit
    );

    if (!settings.hooks.UserPromptSubmit.length) {
      delete settings.hooks.UserPromptSubmit;
    }
  }

  if (settings.hooks?.PostToolUse) {
    settings.hooks.PostToolUse = removeSkillCompassHooks(
      settings.hooks.PostToolUse
    );

    if (!settings.hooks.PostToolUse.length) {
      delete settings.hooks.PostToolUse;
    }
  }

  if (settings.hooks && !Object.keys(settings.hooks).length) {
    delete settings.hooks;
  }

  writeSettings(settings);
  process.stdout.write(
    `skill-compass: project hooks removed; files kept in ${DESTINATION_DIRECTORY}\n`
  );
}

function install() {
  fs.mkdirSync(DESTINATION_DIRECTORY, { recursive: true });
  copyFile('compass.js');
  copyFile('directions.json', true);
  copyFile('install.js');
  copyFile('LICENSE');
  copyFile('package.json');
  copySkill();

  const settings = readSettings();

  settings.$schema = settings.$schema || 'https://json.schemastore.org/claude-code-settings.json';
  settings.hooks = settings.hooks || {};
  settings.hooks.UserPromptSubmit = settings.hooks.UserPromptSubmit || [];
  settings.hooks.PostToolUse = settings.hooks.PostToolUse || [];

  addHookGroup(settings.hooks.UserPromptSubmit, '', COMPASS_COMMAND);
  addHookGroup(settings.hooks.PostToolUse, POST_TOOL_MATCHER, POST_TOOL_COMMAND);
  writeSettings(settings);

  process.stdout.write(`skill-compass: installed only for ${PROJECT_ROOT}\n`);
  process.stdout.write('skill-compass: run npm run skill-compass:check\n');
  process.stdout.write('skill-compass: run npm run skill-compass:test\n');
}

try {
  if (CHECK_MODE) {
    runCheck();
  } else if (UNINSTALL_MODE) {
    uninstall();
  } else {
    install();
  }
} catch (error) {
  process.stderr.write(`skill-compass: ${error.message}\n`);
  process.exit(1);
}
