const {test, expect} = require('@playwright/test')
const {runCommand, updateOption} = require("./helper/wpcli-command");

/**
 * E2E tests for v3 tracker attributes using the live Umami instance
 * at stats.wavedepth.com. These tests verify the plugin correctly
 * renders all v3-specific data attributes on the tracking script tag.
 */

const LIVE_UMAMI_SCRIPT = 'https://stats.wavedepth.com/script.js'
const LIVE_WEBSITE_ID = '837d5df1-a19a-4cac-831c-34a53ab951b5' // wavedepth.com

test.describe('v3 tracker attributes', () => {

    test.beforeEach(async ({page}) => {
        // Reset options to a known state before each test.
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: 'https://stats.wavedepth.com',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: '',
            domains: '',
            exclude_search: 0,
            exclude_hash: 0,
            before_send: ''
        });
    });

    test('renders live Umami tracking script with correct src and website ID', async ({page}) => {
        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toBeAttached();
        await expect(script).toHaveAttribute('async', '');
        await expect(script).toHaveAttribute('defer', '');
        await expect(script).toHaveAttribute('data-website-id', LIVE_WEBSITE_ID);
    });

    test('renders data-tag attribute when tag is set', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: '',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: 'wp-test-tag',
            domains: '',
            exclude_search: 0,
            exclude_hash: 0,
            before_send: ''
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-tag', 'wp-test-tag');
    });

    test('renders data-domains attribute with comma-separated values', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: '',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: '',
            domains: 'localhost,wavedepth.com',
            exclude_search: 0,
            exclude_hash: 0,
            before_send: ''
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-domains', 'localhost,wavedepth.com');
    });

    test('renders data-exclude-search when enabled', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: '',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: '',
            domains: '',
            exclude_search: 1,
            exclude_hash: 0,
            before_send: ''
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-exclude-search', 'true');
    });

    test('renders data-exclude-hash when enabled', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: '',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: '',
            domains: '',
            exclude_search: 0,
            exclude_hash: 1,
            before_send: ''
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-exclude-hash', 'true');
    });

    test('renders data-before-send with function name', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: '',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: '',
            domains: '',
            exclude_search: 0,
            exclude_hash: 0,
            before_send: 'myBeforeSend'
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-before-send', 'myBeforeSend');
    });

    test('renders data-host-url with proper quoting when enabled', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: 'https://stats.wavedepth.com',
            use_host_url: 1,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 0,
            tag: '',
            domains: '',
            exclude_search: 0,
            exclude_hash: 0,
            before_send: ''
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-host-url', 'https://stats.wavedepth.com');
    });

    test('does NOT render absent v3 attributes when options are empty', async ({page}) => {
        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toBeAttached();

        // These attributes should NOT be present when options are empty/disabled.
        const html = await script.evaluate(el => el.outerHTML);
        expect(html).not.toContain('data-tag');
        expect(html).not.toContain('data-domains');
        expect(html).not.toContain('data-exclude-search');
        expect(html).not.toContain('data-exclude-hash');
        expect(html).not.toContain('data-before-send');
        expect(html).not.toContain('data-cache');
    });

    test('renders all v3 attributes simultaneously', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: LIVE_UMAMI_SCRIPT,
            website_id: LIVE_WEBSITE_ID,
            host_url: 'https://stats.wavedepth.com',
            use_host_url: 1,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 1,
            cache: 0,
            track_comments: 0,
            tag: 'full-test',
            domains: 'localhost',
            exclude_search: 1,
            exclude_hash: 1,
            before_send: 'filterPayload'
        });

        await page.goto('/', {waitUntil: 'networkidle'});

        const script = page.locator(`script[src='${LIVE_UMAMI_SCRIPT}']`);
        await expect(script).toHaveAttribute('data-website-id', LIVE_WEBSITE_ID);
        await expect(script).toHaveAttribute('data-do-not-track', 'true');
        await expect(script).toHaveAttribute('data-host-url', 'https://stats.wavedepth.com');
        await expect(script).toHaveAttribute('data-tag', 'full-test');
        await expect(script).toHaveAttribute('data-domains', 'localhost');
        await expect(script).toHaveAttribute('data-exclude-search', 'true');
        await expect(script).toHaveAttribute('data-exclude-hash', 'true');
        await expect(script).toHaveAttribute('data-before-send', 'filterPayload');
    });
});
