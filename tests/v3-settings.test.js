const {test, expect} = require('@playwright/test')
const {updateOption, getOption, runCommand} = require("./helper/wpcli-command");
const TEST_USER = process.env.TEST_USER || 'admin'
const TEST_PASS = process.env.TEST_PASS || 'password'

/**
 * E2E tests for the v3 settings fields on the admin settings page.
 * Verifies that all new v3 fields are visible, saveable, and persist correctly.
 */

test.describe('v3 settings fields', () => {

    test.beforeEach(async ({page}) => {
        // Reset to baseline options.
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
        await login(page);
        await switchToSettings(page);
    });

    test('v3 settings fields are present in the admin UI', async ({page}) => {
        // Expand advanced options.
        await page.locator('label[for="advanced-options"]').click();

        // Verify v3 fields exist.
        await expect(page.locator('#integrate_umami_tag')).toBeVisible();
        await expect(page.locator('#integrate_umami_domains')).toBeVisible();
        await expect(page.locator('#integrate_umami_exclude_search')).toBeVisible();
        await expect(page.locator('#integrate_umami_exclude_hash')).toBeVisible();
        await expect(page.locator('#integrate_umami_before_send')).toBeVisible();
    });

    test('saves tag field via settings page', async ({page}) => {
        await page.locator('label[for="advanced-options"]').click();

        await page.locator('#integrate_umami_tag').fill('my-ab-test');
        await page.getByRole('button', {name: 'Save Changes'}).click();

        // Verify saved in database.
        const options = getOption('integrate_umami_options');
        expect(options.tag).toBe('my-ab-test');
    });

    test('saves domains field via settings page', async ({page}) => {
        await page.locator('label[for="advanced-options"]').click();

        await page.locator('#integrate_umami_domains').fill('example.com,test.com');
        await page.getByRole('button', {name: 'Save Changes'}).click();

        const options = getOption('integrate_umami_options');
        expect(options.domains).toBe('example.com,test.com');
    });

    test('saves exclude_search checkbox via settings page', async ({page}) => {
        await page.locator('label[for="advanced-options"]').click();

        await page.locator('#integrate_umami_exclude_search').check();
        await page.getByRole('button', {name: 'Save Changes'}).click();

        const options = getOption('integrate_umami_options');
        expect(options.exclude_search).toBe(1);
    });

    test('saves exclude_hash checkbox via settings page', async ({page}) => {
        await page.locator('label[for="advanced-options"]').click();

        await page.locator('#integrate_umami_exclude_hash').check();
        await page.getByRole('button', {name: 'Save Changes'}).click();

        const options = getOption('integrate_umami_options');
        expect(options.exclude_hash).toBe(1);
    });

    test('saves before_send field via settings page', async ({page}) => {
        await page.locator('label[for="advanced-options"]').click();

        await page.locator('#integrate_umami_before_send').fill('myFilterFn');
        await page.getByRole('button', {name: 'Save Changes'}).click();

        const options = getOption('integrate_umami_options');
        expect(options.before_send).toBe('myFilterFn');
    });

    test('shows cache deprecation notice', async ({page}) => {
        await page.locator('label[for="advanced-options"]').click();

        const deprecatedLabel = page.locator('text=Deprecated');
        await expect(deprecatedLabel.first()).toBeVisible();
    });

    test('shows Umami Cloud helper text in script URL field', async ({page}) => {
        const cloudHelp = page.locator('text=cloud.umami.is/script.js');
        await expect(cloudHelp).toBeVisible();
    });
});

async function login(page) {
    await page.goto('/wp-login.php', {waitUntil: 'networkidle'})
    await expect(page).toHaveTitle(/Log In/)

    await page.fill('#user_login', TEST_USER)
    await page.fill('#user_pass', TEST_PASS)
    await page.click('#wp-submit')

    await page.waitForLoadState('networkidle')
    await expect(page).toHaveTitle(/Dashboard/)
}

async function switchToSettings(page) {
    const menu = await page.locator('.wp-menu-name').getByText('Settings')
    await expect(menu).toBeVisible();
    await menu.click()
    await page.waitForLoadState('networkidle')
    await expect(page).toHaveTitle(/General Settings/)

    const settings = await page.locator('a').getByText('Integrate Umami')
    await expect(settings).toBeVisible();
    await settings.click()
    await page.waitForLoadState('networkidle')

    const title = await page.locator('h1').getByText('Integrate Umami Settings')
    await expect(title).toBeVisible()
}
