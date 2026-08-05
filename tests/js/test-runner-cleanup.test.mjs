import assert from 'node:assert/strict';
import {spawn, spawnSync} from 'node:child_process';
import {chmodSync, cpSync, existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';

const packageRoot = path.resolve(import.meta.dirname, '../..');
const runnerSource = path.join(packageRoot, 'scripts/run-tests');

function executable(pathname, source) {
    writeFileSync(pathname, source, {mode: 0o700});
    chmodSync(pathname, 0o700);
}

function fixture({phpunitExit = 0, wait = false, cleanupExit = 0, tempRoot = null} = {}) {
    const root = mkdtempSync(path.join(os.tmpdir(), 'base-test-runner-'));
    const workspaceRoot = path.join(root, 'workspace');
    const fixturePackageRoot = path.join(workspaceRoot, 'plugins/base');
    const binRoot = path.join(root, 'bin');
    const cacheRoot = tempRoot ?? path.join(root, 'cache-parent');
    const logPath = path.join(root, 'runner.log');
    mkdirSync(path.join(fixturePackageRoot, 'scripts'), {recursive: true});
    mkdirSync(path.join(workspaceRoot, 'vendor/bin'), {recursive: true});
    mkdirSync(binRoot, {recursive: true});
    mkdirSync(cacheRoot, {recursive: true});
    cpSync(runnerSource, path.join(fixturePackageRoot, 'scripts/run-tests'));
    writeFileSync(path.join(cacheRoot, 'unrelated-sentinel.txt'), 'owner state\n');

    executable(path.join(workspaceRoot, 'vendor/bin/phpunit'), `#!/bin/sh
printf '%s\\n' "$LINDEMANNROCK_BASE_TEST_CACHE_PATH" > "$BASE_RUNNER_TEST_LOG"
mkdir -p "$LINDEMANNROCK_BASE_TEST_CACHE_PATH/nested"
printf 'owned\\n' > "$LINDEMANNROCK_BASE_TEST_CACHE_PATH/nested/cache.bin"
${wait ? "trap 'exit 143' TERM; while :; do sleep 1; done" : `exit ${phpunitExit}`}
`);
    if (cleanupExit !== 0) {
        executable(path.join(binRoot, 'rm'), `#!/bin/sh
exit ${cleanupExit}
`);
    }

    const command = '/bin/bash';
    const args = [path.join(fixturePackageRoot, 'scripts/run-tests')];
    const environment = {
        ...process.env,
        PATH: `${binRoot}:/usr/bin:/bin`,
        LINDEMANNROCK_BASE_TEST_CACHE_TEMP_ROOT: cacheRoot,
        BASE_RUNNER_TEST_LOG: logPath,
    };
    return {
        root,
        cacheRoot,
        logPath,
        run() {
            return spawnSync(command, args, {cwd: fixturePackageRoot, env: environment, encoding: 'utf8'});
        },
        spawn() {
            return spawn(command, args, {cwd: fixturePackageRoot, env: environment, stdio: ['ignore', 'pipe', 'pipe']});
        },
        cachePath() {
            return readFileSync(logPath, 'utf8').trim();
        },
        cleanup() {
            rmSync(root, {recursive: true, force: true});
        },
    };
}

async function waitForFile(pathname) {
    for (let attempt = 0; attempt < 100; attempt++) {
        if (existsSync(pathname)) {
            return;
        }
        await new Promise((resolve) => setTimeout(resolve, 20));
    }
    throw new Error(`Timed out waiting for ${pathname}`);
}

test('successful and failing test processes clean only their unique owned cache', async (context) => {
    for (const [name, phpunitExit] of [['success', 0], ['failure', 47]]) {
        await context.test(name, () => {
            const current = fixture({phpunitExit});
            try {
                const result = current.run();
                assert.equal(result.status, phpunitExit, result.stderr);
                assert.equal(existsSync(current.cachePath()), false);
                assert.equal(readFileSync(path.join(current.cacheRoot, 'unrelated-sentinel.txt'), 'utf8'), 'owner state\n');
            } finally {
                current.cleanup();
            }
        });
    }
});

test('sequential and concurrent runners never share a cache directory', async () => {
    const sharedRoot = mkdtempSync(path.join(os.tmpdir(), 'base-shared-cache-parent-'));
    const first = fixture({tempRoot: sharedRoot});
    const second = fixture({tempRoot: sharedRoot});
    try {
        assert.equal(first.run().status, 0);
        assert.equal(second.run().status, 0);
        assert.notEqual(first.cachePath(), second.cachePath());

        const waitingFirst = fixture({wait: true, tempRoot: sharedRoot});
        const waitingSecond = fixture({wait: true, tempRoot: sharedRoot});
        try {
            const childOne = waitingFirst.spawn();
            const childTwo = waitingSecond.spawn();
            await Promise.all([waitForFile(waitingFirst.logPath), waitForFile(waitingSecond.logPath)]);
            assert.notEqual(waitingFirst.cachePath(), waitingSecond.cachePath());
            assert.equal(existsSync(waitingFirst.cachePath()), true);
            assert.equal(existsSync(waitingSecond.cachePath()), true);
            childOne.kill('SIGTERM');
            childTwo.kill('SIGTERM');
            const statuses = await Promise.all([
                new Promise((resolve) => childOne.once('close', resolve)),
                new Promise((resolve) => childTwo.once('close', resolve)),
            ]);
            assert.deepEqual(statuses, [143, 143]);
            assert.equal(existsSync(waitingFirst.cachePath()), false);
            assert.equal(existsSync(waitingSecond.cachePath()), false);
        } finally {
            waitingFirst.cleanup();
            waitingSecond.cleanup();
        }
    } finally {
        first.cleanup();
        second.cleanup();
        rmSync(sharedRoot, {recursive: true, force: true});
    }
});

test('cleanup failure preserves a primary failure and fails an otherwise successful run', async (context) => {
    for (const [name, phpunitExit, expected] of [
        ['primary failure', 47, 47],
        ['cleanup-only failure', 0, 88],
    ]) {
        await context.test(name, () => {
            const current = fixture({phpunitExit, cleanupExit: 88});
            try {
                const result = current.run();
                assert.equal(result.status, expected);
                assert.match(result.stderr, /cache cleanup failed \(exit 88\)/);
                assert.equal(existsSync(current.cachePath()), true);
            } finally {
                current.cleanup();
            }
        });
    }
});
