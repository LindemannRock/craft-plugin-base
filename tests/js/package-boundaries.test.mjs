import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {cpSync, existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, symlinkSync, writeFileSync} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';

import {
    checkBuildParity,
    checkPackageExport,
    packageRoot,
    validateArchiveMembers,
} from '../../scripts/package-boundaries.mjs';

function buildFixture() {
    const root = mkdtempSync(path.join(os.tmpdir(), 'base-stale-build-'));
    for (const relativePath of [
        'package.json',
        'src/web/assets/package.json',
        'src/web/assets/package-lock.json',
        'src/web/assets/analytics/src',
        'src/web/assets/analytics/dist',
        'src/web/assets/components/src',
        'src/web/assets/components/dist',
        'src/web/assets/install/src',
        'src/web/assets/install/dist',
    ]) {
        const source = path.join(packageRoot, relativePath);
        const destination = path.join(root, relativePath);
        mkdirSync(path.dirname(destination), {recursive: true});
        cpSync(source, destination, {recursive: true});
    }
    const nodeModules = path.join(packageRoot, 'src/web/assets/node_modules');
    if (spawnSync(path.join(nodeModules, '.bin/esbuild'), ['--version'], {encoding: 'utf8'}).status === 0) {
        symlinkSync(nodeModules, path.join(root, 'src/web/assets/node_modules'), 'dir');
    }
    return root;
}

test('locked package build reproduces every generated asset without mutating source', () => {
    const before = readFileSync(path.join(packageRoot, 'src/web/assets/install/dist/js/install-experience.js'));
    const outputs = checkBuildParity();
    assert.equal(outputs.length, 5);
    assert.deepEqual(readFileSync(path.join(packageRoot, 'src/web/assets/install/dist/js/install-experience.js')), before);
});

test('stale generated output fails and removes only its owned build directory', () => {
    const fixtureRoot = buildFixture();
    let temporaryPath = '';
    try {
        writeFileSync(path.join(fixtureRoot, 'src/web/assets/install/dist/js/install-experience.js'), 'stale\n');
        assert.throws(
            () => checkBuildParity(fixtureRoot, {onTemporaryPath: (value) => { temporaryPath = value; }}),
            /Generated assets are stale/,
        );
        assert.notEqual(temporaryPath, '');
        assert.equal(existsSync(temporaryPath), false);
        assert.equal(readFileSync(path.join(fixtureRoot, 'src/web/assets/install/dist/js/install-experience.js'), 'utf8'), 'stale\n');
    } finally {
        rmSync(fixtureRoot, {recursive: true, force: true});
    }
});

test('customer archive excludes development inputs and retains runtime outputs', () => {
    const members = checkPackageExport();
    assert.equal(members.includes('package.json'), false);
    assert.equal(members.includes('composer.json'), true);
    assert.equal(members.includes('src/cache/DisposableCacheStorageDecision.php'), true);
    assert.equal(members.includes('src/cache/DisposableCacheStoragePresentation.php'), true);
    assert.equal(members.includes('src/cache/DisposableCacheStoragePresenter.php'), true);
    assert.equal(members.includes('src/cache/DisposableCacheStorageResolver.php'), true);
    assert.equal(members.includes('src/templates/_components/cache-storage-status.twig'), true);
    assert.equal(members.includes('src/templates/_partials/field-cache-storage.twig'), true);
    assert.equal(members.includes('src/translations/en/lindemannrock-base.php'), true);
    assert.equal(members.includes('src/web/assets/install/dist/js/install-experience.js'), true);
});

test('archive validation rejects build metadata leakage', () => {
    assert.throws(
        () => validateArchiveMembers([
            'composer.json',
            'package.json',
            'src/Base.php',
            'src/web/assets/analytics/dist/js/analytics.js',
            'src/web/assets/components/dist/css/components.css',
            'src/web/assets/components/dist/js/components.js',
            'src/web/assets/install/dist/css/install-experience.css',
            'src/web/assets/install/dist/js/install-experience.js',
        ]),
        /development files: package\.json/,
    );
});
