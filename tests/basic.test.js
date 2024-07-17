const { test, expect } = require('@playwright/test')
const TEST_USER = process.env.TEST_USER || 'admin'
const TEST_PASS = process.env.TEST_PASS || 'password'

test.describe('basic functionality', () => {
    test.beforeEach(async ({page}) => {
        await page.goto( '/wp-login.php', {waitUntil: 'networkidle'})
        await expect(page).toHaveTitle(/Log In/)

        /** initiate login process */
        await page.fill('#user_login', TEST_USER)
        await page.fill('#user_pass', TEST_PASS)
        await page.click('#wp-submit')

        /** correct redirect to dashboard */
        await page.waitForLoadState('networkidle')
        await expect(page).toHaveTitle(/Dashboard/)
    })

    test('plugin active', async ({page}) => {
        await page.goto('/wp-admin/plugins.php', {waitUntil: 'networkidle'})
        await expect(page).toHaveTitle(/Plugins/)

        /** check if the plugin is activated */
        const plugin = page.locator('[data-slug="integrate-umami"]')
        await expect(plugin).toHaveClass('active')
    })

    test('menu registered', async ({page}) => {
        await page.goto('/wp-admin/', {waitUntil: 'networkidle'})
        await expect(page).toHaveTitle(/Dashboard/)

        /** menu element is clickable and the page is accessible */
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
    })
})
