import {spawnSync} from 'node:child_process';
import {
    cpSync,
    existsSync,
    mkdirSync,
    mkdtempSync,
    readFileSync,
    rmSync,
    statSync,
    symlinkSync,
} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

export const packageRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const generatedOutputs = [
    'src/web/assets/analytics/dist/js/analytics.js',
    'src/web/assets/components/dist/css/components.css',
    'src/web/assets/components/dist/js/components.js',
    'src/web/assets/install/dist/css/install-experience.css',
    'src/web/assets/install/dist/js/install-experience.js',
];

const activeTemporaryPaths = new Set();
let signalHandlersInstalled = false;

function removeTemporaryPath(temporaryPath) {
    rmSync(temporaryPath, {recursive: true, force: true});
    activeTemporaryPaths.delete(temporaryPath);
}

function installSignalHandlers() {
    if (signalHandlersInstalled) {
        return;
    }
    signalHandlersInstalled = true;
    for (const [signal, status] of [['SIGINT', 130], ['SIGTERM', 143], ['SIGHUP', 129]]) {
        process.once(signal, () => {
            for (const temporaryPath of activeTemporaryPaths) {
                try {
                    removeTemporaryPath(temporaryPath);
                } catch (error) {
                    process.stderr.write(`Unable to clean package-boundary path ${temporaryPath}: ${error.message}\n`);
                }
            }
            process.exit(status);
        });
    }
}

function ownedTemporaryDirectory(prefix, onTemporaryPath) {
    installSignalHandlers();
    const temporaryPath = mkdtempSync(path.join(os.tmpdir(), prefix));
    activeTemporaryPaths.add(temporaryPath);
    onTemporaryPath?.(temporaryPath);
    return temporaryPath;
}

function copyBuildInputs(sourceRoot, buildRoot) {
    mkdirSync(path.join(buildRoot, 'src/web/assets'), {recursive: true});
    for (const relativePath of [
        'package.json',
        'src/web/assets/package.json',
        'src/web/assets/package-lock.json',
        'src/web/assets/analytics/src',
        'src/web/assets/components/src',
        'src/web/assets/install/src',
    ]) {
        const source = path.join(sourceRoot, relativePath);
        const destination = path.join(buildRoot, relativePath);
        mkdirSync(path.dirname(destination), {recursive: true});
        cpSync(source, destination, {recursive: true});
    }

}

function installBuildDependencies(sourceRoot, buildRoot) {
    const sourceNodeModules = path.join(sourceRoot, 'src/web/assets/node_modules');
    const sourceEsbuild = path.join(sourceNodeModules, '.bin/esbuild');
    if (existsSync(sourceNodeModules)
        && statSync(sourceNodeModules).isDirectory()
        && spawnSync(sourceEsbuild, ['--version'], {encoding: 'utf8'}).status === 0) {
        symlinkSync(sourceNodeModules, path.join(buildRoot, 'src/web/assets/node_modules'), 'dir');
        return;
    }

    const install = spawnSync('npm', ['ci', '--prefix', 'src/web/assets'], {
        cwd: buildRoot,
        encoding: 'utf8',
    });
    if (install.error) {
        throw new Error(`Locked asset install could not start: ${install.error.message}`);
    }
    if (install.status !== 0) {
        throw new Error(`Locked asset install failed with exit ${install.status ?? 1}.\n${install.stdout}${install.stderr}`);
    }
}

export function checkBuildParity(sourceRoot = packageRoot, {onTemporaryPath} = {}) {
    const buildRoot = ownedTemporaryDirectory('base-build-parity-', onTemporaryPath);
    try {
        copyBuildInputs(sourceRoot, buildRoot);
        installBuildDependencies(sourceRoot, buildRoot);
        const result = spawnSync('npm', ['run', 'build'], {
            cwd: buildRoot,
            encoding: 'utf8',
        });
        if (result.error) {
            throw new Error(`Package build could not start: ${result.error.message}`);
        }
        if (result.status !== 0) {
            throw new Error(`Package build failed with exit ${result.status ?? 1}.\n${result.stdout}${result.stderr}`);
        }

        const mismatches = generatedOutputs.filter((relativePath) => {
            const expected = path.join(sourceRoot, relativePath);
            const generated = path.join(buildRoot, relativePath);
            return !existsSync(expected)
                || !existsSync(generated)
                || !readFileSync(expected).equals(readFileSync(generated));
        });
        if (mismatches.length > 0) {
            throw new Error(`Generated assets are stale: ${mismatches.join(', ')}`);
        }

        return generatedOutputs;
    } finally {
        removeTemporaryPath(buildRoot);
    }
}

export function validateArchiveMembers(members) {
    const forbidden = members.filter((member) => member === 'package.json'
        || /^(?:ecs\.php|phpstan\.neon|phpunit\.xml\.dist)$/.test(member)
        || /^(?:tests|scripts|\.github|\.githooks)\//.test(member)
        || /^src\/web\/assets\/(?:analytics|components|install)\/src\//.test(member)
        || /^src\/web\/assets\/(?:package(?:-lock)?\.json|node_modules\/)/.test(member)
        || /^docs\//.test(member));
    if (forbidden.length > 0) {
        throw new Error(`Customer archive contains development files: ${forbidden.join(', ')}`);
    }

    for (const required of [
        'composer.json',
        'src/Base.php',
        'src/cache/DisposableCacheStorageDecision.php',
        'src/cache/DisposableCacheStoragePresentation.php',
        'src/cache/DisposableCacheStoragePresenter.php',
        'src/cache/DisposableCacheStorageResolver.php',
        'src/queue/DeferredQueueJob.php',
        'src/queue/PortableQueueScheduler.php',
        'src/templates/_components/cache-storage-status.twig',
        'src/templates/_partials/field-cache-storage.twig',
        'src/translations/en/lindemannrock-base.php',
        ...generatedOutputs,
    ]) {
        if (!members.includes(required)) {
            throw new Error(`Customer archive is missing required runtime file: ${required}`);
        }
    }
}

export function checkPackageExport(sourceRoot = packageRoot, {onTemporaryPath} = {}) {
    const archiveRoot = ownedTemporaryDirectory('base-package-export-', onTemporaryPath);
    const archivePath = path.join(archiveRoot, 'package.tar');
    try {
        const tree = spawnSync('git', ['write-tree'], {
            cwd: sourceRoot,
            encoding: 'utf8',
        });
        if (tree.error) {
            throw new Error(`Git index tree could not be created: ${tree.error.message}`);
        }
        if (tree.status !== 0) {
            throw new Error(`Git index tree creation failed with exit ${tree.status ?? 1}.\n${tree.stderr}`);
        }

        const archive = spawnSync('git', ['archive', '--worktree-attributes', `--output=${archivePath}`, tree.stdout.trim()], {
            cwd: sourceRoot,
            encoding: 'utf8',
        });
        if (archive.error) {
            throw new Error(`Git archive could not start: ${archive.error.message}`);
        }
        if (archive.status !== 0) {
            throw new Error(`Git archive failed with exit ${archive.status ?? 1}.\n${archive.stderr}`);
        }
        const listing = spawnSync('tar', ['-tf', archivePath], {encoding: 'utf8'});
        if (listing.error) {
            throw new Error(`Archive listing could not start: ${listing.error.message}`);
        }
        if (listing.status !== 0) {
            throw new Error(`Archive listing failed with exit ${listing.status ?? 1}.\n${listing.stderr}`);
        }
        const members = listing.stdout.trim().split('\n').filter(Boolean);
        validateArchiveMembers(members);
        return members;
    } finally {
        removeTemporaryPath(archiveRoot);
    }
}
