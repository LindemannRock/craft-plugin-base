<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

/** @return list<string> */
function identifierSegments(string $identifier): array
{
    $spaced = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', $identifier) ?? $identifier;
    $segments = preg_split('/[^A-Za-z0-9]+/', $spaced, -1, PREG_SPLIT_NO_EMPTY);

    return is_array($segments) ? array_values($segments) : [];
}

function forbiddenReason(string $identifier): ?string
{
    $segments = array_map(static fn(string $segment): string => strtolower($segment), identifierSegments($identifier));
    foreach ($segments as $segment) {
        if (preg_match('/^(?:pr|a)\d+(?:\d+)?$/', $segment) === 1) {
            return 'work-history ID';
        }
        if (in_array($segment, ['audit', 'debt', 'amendment'], true)) {
            return 'work-history label';
        }
    }
    for ($index = 0, $count = count($segments) - 1; $index < $count; $index++) {
        $pair = $segments[$index] . '-' . $segments[$index + 1];
        if (in_array($pair, ['regression-batch', 'fix-batch', 'other-tests'], true)) {
            return 'catch-all or batch label';
        }
        if ($segments[$index] === 'batch' && ctype_digit($segments[$index + 1])) {
            return 'numbered batch label';
        }
    }

    return null;
}

/** @return list<array{path: string, kind: string, identifier: string, reason: string}> */
function scanPhpSource(string $relativePath, string $source): array
{
    $tokens = token_get_all($source);
    $declarations = [];
    $declarationTokens = [T_CLASS, T_INTERFACE, T_TRAIT, T_FUNCTION];
    if (defined('T_ENUM')) {
        $declarationTokens[] = T_ENUM;
    }
    for ($index = 0, $count = count($tokens); $index < $count; $index++) {
        $token = $tokens[$index];
        if (!is_array($token) || !in_array($token[0], $declarationTokens, true)) {
            continue;
        }
        $kind = $token[0] === T_FUNCTION ? 'function' : 'class';
        for ($candidate = $index + 1; $candidate < $count; $candidate++) {
            $next = $tokens[$candidate];
            if (is_array($next) && in_array($next[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            if ($kind === 'function' && is_string($next) && $next === '&') {
                continue;
            }
            if (!is_array($next) || $next[0] !== T_STRING) {
                break;
            }
            $reason = forbiddenReason($next[1]);
            if ($reason !== null) {
                $declarations[] = [
                    'path' => $relativePath,
                    'kind' => $kind,
                    'identifier' => $next[1],
                    'reason' => $reason,
                ];
            }
            break;
        }
    }

    return $declarations;
}

/** @return list<array{type: string, value: string}> */
function javascriptTokens(string $source): array
{
    $tokens = [];
    $length = strlen($source);
    for ($index = 0; $index < $length;) {
        $character = $source[$index];
        $next = $index + 1 < $length ? $source[$index + 1] : '';
        if (ctype_space($character)) {
            $index++;
            continue;
        }
        if ($character === '/' && $next === '/') {
            $newline = strpos($source, "\n", $index + 2);
            $index = $newline === false ? $length : $newline + 1;
            continue;
        }
        if ($character === '/' && $next === '*') {
            $end = strpos($source, '*/', $index + 2);
            $index = $end === false ? $length : $end + 2;
            continue;
        }
        if (str_contains("'\"`", $character)) {
            $quote = $character;
            $value = '';
            $index++;
            while ($index < $length) {
                if ($source[$index] === '\\' && $index + 1 < $length) {
                    $value .= $source[$index + 1];
                    $index += 2;
                    continue;
                }
                if ($source[$index] === $quote) {
                    $index++;
                    break;
                }
                $value .= $source[$index++];
            }
            $tokens[] = ['type' => 'string', 'value' => $value];
            continue;
        }
        if (preg_match('/[A-Za-z_$]/', $character) === 1) {
            $start = $index++;
            while ($index < $length && preg_match('/[A-Za-z0-9_$]/', $source[$index]) === 1) {
                $index++;
            }
            $tokens[] = ['type' => 'identifier', 'value' => substr($source, $start, $index - $start)];
            continue;
        }
        $tokens[] = ['type' => 'punctuation', 'value' => $character];
        $index++;
    }

    return $tokens;
}

/** @return list<array{path: string, kind: string, identifier: string, reason: string}> */
function scanJavascriptSource(string $relativePath, string $source): array
{
    $tokens = javascriptTokens($source);
    $declarations = [];
    for ($index = 0, $count = count($tokens) - 2; $index < $count; $index++) {
        if ($tokens[$index]['type'] !== 'identifier' || !in_array($tokens[$index]['value'], ['test', 'it', 'describe'], true)) {
            continue;
        }
        if ($tokens[$index + 1] !== ['type' => 'punctuation', 'value' => '('] || $tokens[$index + 2]['type'] !== 'string') {
            continue;
        }
        $identifier = $tokens[$index + 2]['value'];
        $reason = forbiddenReason($identifier);
        if ($reason !== null) {
            $declarations[] = [
                'path' => $relativePath,
                'kind' => 'javascript-test',
                'identifier' => $identifier,
                'reason' => $reason,
            ];
        }
    }

    return $declarations;
}

/** @return list<array{path: string, kind: string, identifier: string, reason: string}> */
function scanTree(string $packageRoot): array
{
    $violations = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($packageRoot . '/tests', FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['php', 'js', 'mjs', 'cjs'], true)) {
            continue;
        }
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($packageRoot) + 1));
        $pathIdentifier = pathinfo($relativePath, PATHINFO_FILENAME);
        $pathReason = forbiddenReason($pathIdentifier);
        if ($pathReason !== null) {
            $violations[] = [
                'path' => $relativePath,
                'kind' => 'path',
                'identifier' => $pathIdentifier,
                'reason' => $pathReason,
            ];
        }
        $source = file_get_contents($file->getPathname());
        if (!is_string($source)) {
            throw new RuntimeException("Unable to read {$relativePath}.");
        }
        array_push(
            $violations,
            ...($extension === 'php'
                ? scanPhpSource($relativePath, $source)
                : scanJavascriptSource($relativePath, $source)),
        );
    }

    return $violations;
}

function runSelfTests(): void
{
    $php = <<<'PHP'
<?php
// Audit labels in comments are not declarations.
final class ProductBehaviorTest {
    public function testSupportedBehavior(): void {}
}
final class Pr41FixtureHelper {
    public function testRegressionBatch(): void {}
}
PHP;
    $phpViolations = scanPhpSource('tests/Integration/SampleTest.php', $php);
    if (array_column($phpViolations, 'identifier') !== ['Pr41FixtureHelper', 'testRegressionBatch']) {
        throw new RuntimeException('PHP declaration convention self-test failed.');
    }

    $javascript = <<<'JS'
// test('PR4 comment', () => {});
const label = 'Audit batch display';
test('supported behavior remains available', () => {});
test('PR4 regression batch', () => {});
JS;
    $jsViolations = scanJavascriptSource('tests/js/sample.test.mjs', $javascript);
    if (array_column($jsViolations, 'identifier') !== ['PR4 regression batch']) {
        throw new RuntimeException('JavaScript declaration convention self-test failed.');
    }
}

$packageRoot = dirname(__DIR__);
runSelfTests();
$violations = scanTree($packageRoot);
if ($violations !== []) {
    foreach ($violations as $violation) {
        fwrite(STDERR, "Forbidden test identifier: {$violation['path']} [{$violation['kind']}] {$violation['identifier']} ({$violation['reason']})" . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, "Test convention guard passed: no work-history or catch-all identifiers.\n");
