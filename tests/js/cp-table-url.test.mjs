import assert from 'node:assert/strict';
import {existsSync, readFileSync} from 'node:fs';
import path from 'node:path';
import test from 'node:test';

const packageRoot = path.resolve(import.meta.dirname, '../..');
const helperPath = path.join(packageRoot, 'src/templates/_layouts/_cp-table-url.twig');
const layouts = [
    ['cp-table', path.join(packageRoot, 'src/templates/_layouts/cp-table.twig')],
    ['cp-table-utility', path.join(packageRoot, 'src/templates/_layouts/cp-table-utility.twig')],
];

function extractFunction(source, name) {
    const start = source.indexOf(`function ${name}`);
    assert.notEqual(start, -1, `${name} must be present`);

    const closingParenthesis = source.indexOf(')', start);
    const openBrace = source.indexOf('{', closingParenthesis);
    let depth = 0;
    for (let index = openBrace; index < source.length; index++) {
        if (source[index] === '{') depth++;
        if (source[index] === '}') depth--;
        if (depth === 0) return source.slice(start, index + 1);
    }

    throw new Error(`Unable to extract ${name}`);
}

function builder(layoutPath, currentSearch, baseUrl, urlParams = {}) {
    const layoutSource = readFileSync(layoutPath, 'utf8');
    const helperSource = existsSync(helperPath) ? readFileSync(helperPath, 'utf8') : '';
    const buildUrlSource = extractFunction(layoutSource, 'buildUrl');
    const factory = new Function('window', 'config', `${helperSource}\n${buildUrlSource}\nreturn buildUrl;`);

    return factory(
        {location: {search: currentSearch}},
        {baseUrl, urlParams},
    );
}

function queryEntries(url) {
    return Array.from(new URL(url, 'https://example.test').searchParams.entries());
}

for (const [layoutName, layoutPath] of layouts) {
    test(`${layoutName} leaves a query-free canonical URL clean`, () => {
        assert.equal(builder(layoutPath, '', '/admin/items')(), '/admin/items');
    });

    test(`${layoutName} emits identical Craft site context once`, () => {
        const result = builder(layoutPath, '?site=ar', '/admin/items?site=ar')();
        assert.deepEqual(queryEntries(result), [['site', 'ar']]);
    });

    test(`${layoutName} normalizes an already duplicated scalar parameter`, () => {
        const result = builder(layoutPath, '?site=ar&site=ar&page=2', '/admin/items?site=ar')({page: 3});
        assert.deepEqual(queryEntries(result), [['site', 'ar'], ['page', '3']]);
    });

    test(`${layoutName} keeps repeated page and sort navigation stable`, () => {
        const firstBuilder = builder(
            layoutPath,
            '?site=ar&page=2&sort=title&dir=asc',
            '/admin/items?site=ar',
        );
        const pageResult = firstBuilder({page: 3});
        const sortResult = builder(
            layoutPath,
            new URL(pageResult, 'https://example.test').search,
            '/admin/items?site=ar',
        )({sort: 'dateCreated', dir: 'desc', page: 1});

        assert.deepEqual(queryEntries(sortResult), [
            ['site', 'ar'],
            ['page', '1'],
            ['sort', 'dateCreated'],
            ['dir', 'desc'],
        ]);
    });

    test(`${layoutName} merges canonical, current, and configured table state in precedence order`, () => {
        const result = builder(
            layoutPath,
            '?site=ar&search=old&page=4',
            '/admin/items?site=en&token=canonical',
            {
                site: 'ar',
                search: 'needle',
                sort: 'title',
                dir: 'asc',
                page: 4,
                status: 'enabled',
                language: 'de',
            },
        )();

        assert.deepEqual(queryEntries(result), [
            ['site', 'ar'],
            ['token', 'canonical'],
            ['search', 'needle'],
            ['page', '4'],
            ['sort', 'title'],
            ['dir', 'asc'],
            ['status', 'enabled'],
            ['language', 'de'],
        ]);
    });

    test(`${layoutName} applies explicit replacements and deletions last`, () => {
        const result = builder(
            layoutPath,
            '?site=ar&search=old&page=2',
            '/admin/items?site=ar&fixed=yes',
            {search: 'configured', sort: 'title'},
        )({search: '', sort: 'dateCreated', page: null, fixed: undefined});

        assert.deepEqual(queryEntries(result), [
            ['site', 'ar'],
            ['sort', 'dateCreated'],
        ]);
    });

    test(`${layoutName} preserves encoded values and canonical fragments`, () => {
        const result = builder(
            layoutPath,
            '?site=ar&search=hello%20world',
            '/admin/items?site=ar&return=%2Fadmin%2Fdashboard#results%202',
        )();
        const parsed = new URL(result, 'https://example.test');

        assert.equal(parsed.searchParams.get('return'), '/admin/dashboard');
        assert.equal(parsed.searchParams.get('search'), 'hello world');
        assert.equal(parsed.hash, '#results%202');
        assert.equal((result.match(/\?/g) ?? []).length, 1);
        assert.doesNotMatch(result, /[?&]#|[?&]$/);
    });

    test(`${layoutName} keeps the Logging Library canonical route`, () => {
        const result = builder(
            layoutPath,
            '?site=ar&page=2&level=warning',
            '/admin/logging-library?site=ar',
            {level: 'warning'},
        )({page: 3});

        assert.equal(new URL(result, 'https://example.test').pathname, '/admin/logging-library');
        assert.deepEqual(queryEntries(result), [
            ['site', 'ar'],
            ['page', '3'],
            ['level', 'warning'],
        ]);
    });

    test(`${layoutName} preserves nested detail and parent context`, () => {
        const result = builder(
            layoutPath,
            '?site=ar&page=2',
            '/admin/formie-rating-field/statistics/form/34/group/five?site=ar#rating-field',
            {
                dateRange: 'last30days',
                groupBy: 'rating',
                siteId: 2,
                fieldHandle: 'rating-field',
            },
        )({page: 3});
        const parsed = new URL(result, 'https://example.test');

        assert.equal(parsed.pathname, '/admin/formie-rating-field/statistics/form/34/group/five');
        assert.equal(parsed.hash, '#rating-field');
        assert.deepEqual(queryEntries(result), [
            ['site', 'ar'],
            ['page', '3'],
            ['dateRange', 'last30days'],
            ['groupBy', 'rating'],
            ['siteId', '2'],
            ['fieldHandle', 'rating-field'],
        ]);
    });

    test(`${layoutName} preserves bracketed and explicit array values`, () => {
        const result = builder(
            layoutPath,
            '?tag%5B%5D=one&tag%5B%5D=two',
            '/admin/items?site=ar',
            {category: ['news', 'events']},
        )();

        assert.deepEqual(queryEntries(result), [
            ['site', 'ar'],
            ['tag[]', 'one'],
            ['tag[]', 'two'],
            ['category', 'news'],
            ['category', 'events'],
        ]);
    });
}
