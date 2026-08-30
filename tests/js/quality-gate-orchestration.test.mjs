import assert from 'node:assert/strict';
import {execFileSync, spawnSync} from 'node:child_process';
import {chmodSync, cpSync, mkdirSync, mkdtempSync, readFileSync, readdirSync, rmSync, writeFileSync} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';

const packageRoot = path.resolve(import.meta.dirname, '../..');
const gatePath = path.join(packageRoot, 'scripts/quality-gate.mjs');
const expectedIds = [
    'platform-compatibility',
    'composer-audit',
    'php-quality',
    'test-conventions',
    'phpunit',
    'pre-commit-hook-regressions',
    'cp-table-url-regressions',
    'package-boundaries',
    'test-runner-regressions',
    'compatibility-runner-regressions',
    'orchestration-regressions',
];

function definitions() {
    return JSON.parse(execFileSync('node', [gatePath, '--list'], {cwd: packageRoot, encoding: 'utf8'}));
}

function probeFixture() {
    const root = mkdtempSync(path.join(os.tmpdir(), 'base-gate-'));
    const probePath = path.join(root, 'probe.sh');
    const logPath = path.join(root, 'constituents.log');
    writeFileSync(probePath, `#!/bin/sh
printf '%s:%s\\n' "$1" "$2" >> "$BASE_GATE_PROBE_LOG"
if [ "$1" = "$BASE_GATE_FAIL_ID" ]; then exit 71; fi
exit 0
`, {mode: 0o700});
    chmodSync(probePath, 0o700);

    return {
        run(failId = '') {
            return spawnSync('node', [gatePath, '--probe', probePath], {
                cwd: packageRoot,
                encoding: 'utf8',
                env: {...process.env, BASE_GATE_PROBE_LOG: logPath, BASE_GATE_FAIL_ID: failId},
            });
        },
        ids() {
            try {
                return readFileSync(logPath, 'utf8').trim().split('\n').filter(Boolean).map((line) => line.split(':')[0]);
            } catch {
                return [];
            }
        },
        reset() {
            writeFileSync(logPath, '');
        },
        cleanup() {
            rmSync(root, {recursive: true, force: true});
        },
    };
}

function actFailureFixture() {
    const root = mkdtempSync(path.join(os.tmpdir(), 'base-act-'));
    const binRoot = path.join(root, 'bin');
    const resourceRoot = path.join(root, 'run-owned-act-resources');
    const logPath = path.join(root, 'act-arguments.log');
    mkdirSync(path.join(root, '.github/workflows'), {recursive: true});
    mkdirSync(path.join(root, 'scripts'), {recursive: true});
    mkdirSync(binRoot, {recursive: true});
    mkdirSync(resourceRoot, {recursive: true});
    cpSync(path.join(packageRoot, 'scripts/act-quality-gates'), path.join(root, 'scripts/act-quality-gates'));
    writeFileSync(path.join(root, '.github/workflows/ci.yml'), `jobs:
  quality-gates:
    steps:
      - name: Complete package quality gate
        run: composer quality-gate
`);
    const fakeAct = path.join(binRoot, 'act');
    writeFileSync(fakeAct, `#!/bin/sh
printf '%s\n' "$*" > "$BASE_ACT_ARGUMENT_LOG"
touch "$BASE_ACT_RESOURCE_ROOT/job-container"
touch "$BASE_ACT_RESOURCE_ROOT/service-container"
touch "$BASE_ACT_RESOURCE_ROOT/network"
touch "$BASE_ACT_RESOURCE_ROOT/volume"
case " $* " in
  *" --rm "*) rm -f "$BASE_ACT_RESOURCE_ROOT"/* ;;
esac
exit 73
`, {mode: 0o700});
    chmodSync(fakeAct, 0o700);

    return {
        resourceRoot,
        logPath,
        run() {
            return spawnSync('/bin/bash', ['scripts/act-quality-gates'], {
                cwd: root,
                encoding: 'utf8',
                env: {...process.env, PATH: `${binRoot}:/usr/bin:/bin`, BASE_ACT_ARGUMENT_LOG: logPath, BASE_ACT_RESOURCE_ROOT: resourceRoot},
            });
        },
        cleanup() {
            rmSync(root, {recursive: true, force: true});
        },
    };
}

test('aggregate declares every Base constituent exactly once', () => {
    const declared = definitions();
    assert.deepEqual(declared.map(({id}) => id), expectedIds);
    assert.equal(new Set(declared.map(({family}) => family)).size, declared.length);
    assert.equal(declared.find(({id}) => id === 'composer-audit').standalone, 'bash scripts/composer-audit');
    assert.match(declared.find(({id}) => id === 'php-quality').workspace, /ddev exec .*composer ci/);
    assert.match(declared.find(({id}) => id === 'cp-table-url-regressions').standalone, /cp-table-url\.test\.mjs/);
    assert.match(declared.find(({id}) => id === 'package-boundaries').standalone, /package-boundaries\.test\.mjs/);
    assert.match(declared.find(({id}) => id === 'compatibility-runner-regressions').standalone, /compatibility-runner-cleanup\.test\.mjs/);
});

test('canonical Composer quality gate disables only its process timeout', () => {
    const composer = JSON.parse(readFileSync(path.join(packageRoot, 'composer.json'), 'utf8'));
    assert.deepEqual(composer.scripts['quality-gate'], [
        'Composer\\Config::disableProcessTimeout',
        'node scripts/quality-gate.mjs',
    ]);
    for (const [name, script] of Object.entries(composer.scripts)) {
        if (name !== 'quality-gate') {
            assert.doesNotMatch(JSON.stringify(script), /disableProcessTimeout/);
        }
    }
});

test('successful aggregate invokes every constituent in canonical order', () => {
    const current = probeFixture();
    try {
        const result = current.run();
        assert.equal(result.status, 0, result.stderr);
        assert.deepEqual(current.ids(), expectedIds);
    } finally {
        current.cleanup();
    }
});

test('every constituent failure makes the aggregate preserve that status', async (context) => {
    const current = probeFixture();
    try {
        for (const id of expectedIds) {
            await context.test(id, () => {
                current.reset();
                const result = current.run(id);
                assert.equal(result.status, 71, `${id}\n${result.stdout}\n${result.stderr}`);
                assert.equal(current.ids().at(-1), id);
                assert.match(result.stderr, new RegExp(`${id} failed with exit 71`));
            });
        }
    } finally {
        current.cleanup();
    }
});

test('CI and Act select the same aggregate authority', () => {
    const workflow = readFileSync(path.join(packageRoot, '.github/workflows/ci.yml'), 'utf8');
    const act = readFileSync(path.join(packageRoot, 'scripts/act-quality-gates'), 'utf8');
    const standaloneRunner = readFileSync(path.join(packageRoot, 'scripts/run-standalone-tests'), 'utf8');
    const checkoutOffset = workflow.indexOf('uses: actions/checkout@v6');
    const trustOffset = workflow.indexOf('run: git config --global --add safe.directory "$GITHUB_WORKSPACE"');
    const qualityGateOffset = workflow.indexOf('run: composer quality-gate');
    assert.equal((workflow.match(/run:\s+composer quality-gate/g) ?? []).length, 1);
    assert.doesNotMatch(workflow, /run:\s+composer (?:audit|phpstan|check-cs|test|ci:full)/);
    assert.ok(checkoutOffset >= 0);
    assert.ok(trustOffset > checkoutOffset);
    assert.ok(qualityGateOffset > trustOffset);
    assert.doesNotMatch(workflow, /safe\.directory\s+["']?\*/);
    assert.match(workflow, /uses:\s+ramsey\/composer-install@v4/);
    assert.match(workflow, /container:\s+node:24-bookworm/);
    assert.match(workflow, /^\s{6}db:/m);
    assert.match(workflow, /^\s{6}redis:/m);
    assert.match(standaloneRunner, /CRAFT_DB_CHARSET="utf8mb4"/);
    assert.match(standaloneRunner, /CRAFT_DB_COLLATION="utf8mb4_0900_ai_ci"/);
    assert.match(standaloneRunner, /CRAFT_EDITION="pro"/);
    assert.match(standaloneRunner, /'@webroot' => CRAFT_BASE_PATH \. '\/web'/);
    assert.match(standaloneRunner, /\\lindemannrock\\base\\Base::register\(\)/);
    assert.match(act, /-j quality-gates/);
    assert.match(act, /composer quality-gate/);
    assert.match(act, /^\s*--rm\s*$/m);
});

test('controlled Act failure stays nonzero and removes every run-owned resource', () => {
    const current = actFailureFixture();
    try {
        const result = current.run();
        assert.equal(result.status, 73, result.stderr);
        assert.match(readFileSync(current.logPath, 'utf8'), /(?:^|\s)--rm(?:\s|$)/);
        assert.deepEqual(readdirSync(current.resourceRoot), []);
    } finally {
        current.cleanup();
    }
});
