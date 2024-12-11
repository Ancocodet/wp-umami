const {test, expect} = require('@playwright/test')
const {updateOption, getOption, runCommand} = require("./helper/wpcli-command");
const TEST_USER = process.env.TEST_USER || 'admin'
const TEST_PASS = process.env.TEST_PASS || 'password'

test.describe('settings page', () => {
    test('enable analytics', async ({page}) => {
        await login(page);
        await switchToSettings(page);

        await page.locator('#integrate_umami_enabled').check();
        await page.locator('#integrate_umami_script_url').fill('https://umami.example.com/umami.js')
        await page.locator('#integrate_umami_website_id').fill('12345678')
        await page.getByRole('button', {name: 'Save Changes'}).click();

        await logout(page);
        await page.goto('/', {waitUntil: 'networkidle'});

        let script = await page.locator("script[src='https://umami.example.com/umami.js']");
        await expect(script).toHaveAttribute('async');
        await expect(script).toHaveAttribute('defer');
        await expect(script).toHaveAttribute('data-website-id', '12345678');
        await expect(script).toHaveAttribute('data-do-not-track', 'true');
    });

    test('disable analytics', async ({page}) => {
        await login(page);
        await switchToSettings(page);

        await page.locator('#integrate_umami_enabled').check();
        await page.getByRole('button', {name: 'Save Changes'}).click();

        await logout(page);
        await page.goto('/', {waitUntil: 'networkidle'});

        !page.locator("script[src='https://umami.example.com/umami.js']")
    });

    test('migrate from old settings', async ({page}) => {
        const oldOptions = {
            enabled: 1,
            script_url: 'https://umami.example.com/umami.js',
            website_id: '12345678',
            do_not_track: 1,
            auto_track: 1,
            cache: 0,
            track_comments: 0,
            ignore_admins: 1,
            use_host_url: 0,
            host_url: '',

        }
        const jsonOptions = JSON.stringify(oldOptions);

        runCommand(`option update umami_options ${jsonOptions} --format=json --quiet`);
        runCommand(`option delete integrate_umami_options --quiet`);

        await login(page);
        await switchToSettings(page);

        let value;
        try{
            value = runCommand('option get integrate_umami_options --format=json --quiet').toString();
        } catch (e) { }

        let jsonValue = JSON.parse(value);
        expect(jsonValue).toEqual(oldOptions);

        let testValue;
        try {
            testValue = runCommand('option get umami_options --format=json --quiet').toString();
        } catch (e) { }
        expect(testValue).toEqual(undefined)
    });
});

async function login(page) {
    await page.goto('/wp-login.php', {waitUntil: 'networkidle'})
    await expect(page).toHaveTitle(/Log In/)

    /** initiate login process */
    await page.fill('#user_login', TEST_USER)
    await page.fill('#user_pass', TEST_PASS)
    await page.click('#wp-submit')

    /** correct redirect to dashboard */
    await page.waitForLoadState('networkidle')
    await expect(page).toHaveTitle(/Dashboard/)
}

async function logout(page) {
    await page.context().clearCookies();
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
