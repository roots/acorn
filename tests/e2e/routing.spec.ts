import { test, expect } from '@playwright/test';

test.describe('Routing', () => {
  test('Acorn handles the welcome route', async ({ page }) => {
    const response = await page.goto('/welcome/');
    expect(response?.status()).toBe(200);
    await expect(page).toHaveURL('/welcome/');
    await expect(page.locator('h1')).toContainText('Welcome to Radicle');
  });

  test('WordPress admin routes are not intercepted', async ({ page }) => {
    const response = await page.goto('/wp-admin/');
    expect(response?.status()).toBe(302); // Redirect status
    await expect(page).toHaveURL(/wp-login\.php/);
  });

  test('WordPress handles non-existent routes with 404', async ({ page }) => {
    const response = await page.goto('/non-existent-' + Date.now());
    expect(response?.status()).toBe(404);
    await expect(page.locator('body')).toContainText('Page not found');
    await expect(page.locator('body.error404')).toBeVisible();
  });

  test('WordPress handles default homepage', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);
    await expect(page.locator('body.home')).toBeVisible();
  });

  test('WordPress REST API routes work', async ({ request }) => {
    const response = await request.get('/wp-json/');
    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.name).toBeDefined();
    expect(data.url).toBeDefined();
  });

  test('WordPress search route works', async ({ page }) => {
    const response = await page.goto('/?s=test');
    expect(response?.status()).toBe(200);
    await expect(page.locator('body.search')).toBeVisible();
  });
});
