const {test, expect} = require('@playwright/test')
const {updateOption, runCommand} = require("./helper/wpcli-command");
const TEST_USER = process.env.TEST_USER || 'admin'
const TEST_PASS = process.env.TEST_PASS || 'password'

/**
 * E2E tests for comment form event tracking (#39).
 * Verifies the JS-based approach correctly adds data-umami-event
 * attributes to comment form submit buttons.
 */

test.describe('comment event tracking', () => {

    test.beforeEach(async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: 'https://stats.wavedepth.com/script.js',
            website_id: '837d5df1-a19a-4cac-831c-34a53ab951b5',
            host_url: '',
            use_host_url: 0,
            ignore_admins: 0,
            auto_track: 1,
            do_not_track: 0,
            cache: 0,
            track_comments: 1,
            tag: '',
            domains: '',
            exclude_search: 0,
            exclude_hash: 0,
            before_send: ''
        });
    });

    test('injects comment tracking JS when track_comments enabled', async ({page}) => {
        // Need a post with comments open. Create one via WP-CLI (no spaces in title).
        const postId = runCommand('post create --post_title=CommentTestPost --post_status=publish --comment_status=open --porcelain').trim();

        await page.goto(`/?p=${postId}`, {waitUntil: 'networkidle'});

        // The comment tracking script should be present in the page source.
        const html = await page.content();
        expect(html).toContain('Integrate Umami: Comment Tracking');

        // If the comment form is rendered (depends on theme), the submit button
        // should get the data-umami-event attribute via the JS snippet.
        const submitBtn = page.locator('#commentform input[type="submit"], #commentform button[type="submit"]');
        if (await submitBtn.count() > 0) {
            await expect(submitBtn.first()).toHaveAttribute('data-umami-event', 'comment');
        }

        // Clean up test post.
        runCommand(`post delete ${postId} --force`);
    });

    test('does NOT inject comment tracking JS when disabled', async ({page}) => {
        updateOption('integrate_umami_options', {
            enabled: 1,
            script_url: 'https://stats.wavedepth.com/script.js',
            website_id: '837d5df1-a19a-4cac-831c-34a53ab951b5',
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
            before_send: ''
        });

        const postId = runCommand('post create --post_title=NoCommentTracking --post_status=publish --comment_status=open --porcelain').trim();

        await page.goto(`/?p=${postId}`, {waitUntil: 'networkidle'});

        // The comment tracking script should NOT be present.
        const html = await page.content();
        expect(html).not.toContain('Integrate Umami: Comment Tracking');

        runCommand(`post delete ${postId} --force`);
    });
});
