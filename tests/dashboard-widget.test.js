const {test, expect} = require('@playwright/test')
const {updateOption} = require("./helper/wpcli-command");
const TEST_USER = process.env.TEST_USER || 'admin'
const TEST_PASS = process.env.TEST_PASS || 'password'

/**
 * E2E tests for the dashboard widget with live Umami API data.
 * Uses the live instance at stats.wavedepth.com.
 */

const LIVE_UMAMI_SCRIPT = 'https://stats.wavedepth.com/script.js'
const LIVE_WEBSITE_ID = '837d5df1-a19a-4cac-831c-34a53ab951b5'

test.describe('dashboard widget', () => {

    test('shows link to Umami without API credentials', async ({page}) => {
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
            before_send: '',
            api_key: '',
            api_username: '',
            api_password: ''
        });

        await login(page);
        // Navigate to dashboard.
        await page.goto('/wp-admin/', {waitUntil: 'networkidle'});

        // The widget should exist.
        const widget = page.locator('#umami_widget');
        await expect(widget).toBeVisible();

        // Should show link to Umami.
        const link = widget.locator('a[href*="stats.wavedepth.com"]');
        await expect(link).toBeVisible();
    });

    test('shows API connection section in settings', async ({page}) => {
        await login(page);
        await switchToSettings(page);

        // Expand API section.
        await page.locator('label[for="api-options"]').click();

        // Verify API fields exist.
        await expect(page.locator('#integrate_umami_api_key')).toBeVisible();
        await expect(page.locator('#integrate_umami_api_username')).toBeVisible();
        await expect(page.locator('#integrate_umami_api_password')).toBeVisible();
    });

    test('saves API credentials via settings page', async ({page}) => {
        await login(page);
        await switchToSettings(page);

        // Expand API section and enter credentials.
        await page.locator('label[for="api-options"]').click();
        await page.locator('#integrate_umami_api_username').fill('admin');
        await page.locator('#integrate_umami_api_password').fill('testpass');
        await page.getByRole('button', {name: 'Save Changes'}).click();

        // Verify page reloads and credentials are saved.
        await page.locator('label[for="api-options"]').click();
        await expect(page.locator('#integrate_umami_api_username')).toHaveValue('admin');
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
