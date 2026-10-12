import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../src/web/assets/components/src/js/components.js', import.meta.url), 'utf8');
const context = {window: {}};
vm.runInNewContext(source.slice(0, source.indexOf('\n})();') + 6), context);
const {makeUnique, normalizeSlug} = context.window.lrIdentifiers;

test('unique identifiers preserve authored keys and use the first free numeric suffix', () => {
    const existing = ['Brand_Red', 'Brand_Red-1', 'Brand_Red-3'];
    assert.equal(makeUnique('Brand_Red', existing), 'Brand_Red-2');
    assert.equal(makeUnique('brand_red', existing), 'brand_red');
    assert.equal(makeUnique('50', ['50']), '50-1');
    assert.equal(makeUnique('Brand_Red-1', existing), 'Brand_Red-1-1');
    assert.deepEqual(existing, ['Brand_Red', 'Brand_Red-1', 'Brand_Red-3']);
    assert.equal(normalizeSlug('My Brand'), 'my-brand');
});

test('unique identifiers include unsaved additions and honor length limits', () => {
    const used = new Set(['red']);
    for (let index = 1; index <= 12; index++) {
        const key = makeUnique('red', used);
        assert.equal(key, `red-${index}`);
        used.add(key);
    }
    const long = 'A'.repeat(64);
    const siblings = [long, 'A'.repeat(62) + '-1'];
    assert.equal(makeUnique(long, siblings, {maxLength: 64}), 'A'.repeat(62) + '-2');
    assert.equal(makeUnique('another-group', []), 'another-group');
});

test('unique identifiers reject unusable input and bounded suffix exhaustion', () => {
    assert.throws(() => makeUnique('', []), /cannot be empty/);
    assert.throws(() => makeUnique('red', [], {maxLength: 2}), /at least 3/);
    const used = ['red', ...Array.from({length: 9}, (_, index) => `r-${index + 1}`)];
    assert.throws(() => makeUnique('red', used, {maxLength: 3}), /exceeds maxLength/);
});
