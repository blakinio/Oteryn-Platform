import { test, expect } from '@playwright/test';
import {
  assertAccessibilitySmoke,
  attachDiagnostics,
  evidenceScreenshot,
  installDiagnostics,
  runBinary,
} from './helpers.mjs';

const routes = [
  { key: 'getting-started', path: '/getting-started', label: "Beginner's Guide" },
  { key: 'server-information', path: '/server-information', label: 'Server Information' },
  { key: 'support', path: '/support', label: 'Support' },
  { key: 'report-a-bug', path: '/support/report-a-bug', label: 'Report a Bug' },
  { key: 'rules', path: '/rules', label: 'Rules' },
  { key: 'terms', path: '/legal/terms', label: 'Terms of Service', legal: true },
  { key: 'privacy', path: '/legal/privacy', label: 'Privacy Policy', legal: true },
  { key: 'cookies', path: '/legal/cookies', label: 'Cookie Policy', legal: true },
];

function supportFixture(...args) {
  return JSON.parse(runBinary('php', ['scripts/acceptance/seed-browser-support-legal.php', ...args]));
}

async function assertNoPageOverflow(page) {
  const dimensions = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, `Unexpected page overflow on ${page.url()}`).toBeLessThanOrEqual(dimensions.viewport + 1);
}

test.setTimeout(180_000);
test.describe.configure({ retries: 0 });

test.beforeEach(async ({ page }) => {
  page.__acceptanceDiagnostics = installDiagnostics(page);
});

test.afterEach(async ({ page }, testInfo) => {
  await attachDiagnostics(testInfo, page.__acceptanceDiagnostics);
});

test('@portal-support-legal every typed route proves missing, unpublished, published, approved-channel, legal-version and locale-isolated states', async ({ page }) => {
  supportFixture('reset');

  let response = await page.goto('/en/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByText('This content has not been created yet.')).toBeVisible();

  supportFixture('seed-page', 'support', 'unpublished');
  response = await page.goto('/en/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByText('This content is not published.')).toBeVisible();
  await expect(page.getByText('Acceptance Support')).toHaveCount(0);

  const fixture = supportFixture('seed-public');

  for (const route of routes) {
    response = await page.goto(`/en${route.path}`);
    expect(response?.status(), `English route ${route.path}`).toBe(200);
    await expect(page.getByRole('heading', { name: `Acceptance ${route.label}` })).toBeVisible();
    await expect(page.getByText("<img src=x onerror=alert('support')> Plain-text acceptance content.", { exact: false })).toBeVisible();
    await expect(page.locator('article script, article img')).toHaveCount(0);

    if (route.legal) {
      await expect(page.getByText(/v2026\.1/u)).toBeVisible();
    }

    response = await page.goto(`/pl${route.path}`);
    expect(response?.status(), `Polish route ${route.path}`).toBe(200);
    await expect(page.getByRole('heading', { name: `PL ${route.label}` })).toBeVisible();
    await expect(page.getByText('Polska treść akceptacyjna:', { exact: false })).toBeVisible();
    await expect(page.getByText(`Acceptance ${route.label}`)).toHaveCount(0);
  }

  response = await page.goto('/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByRole('heading', { name: 'Acceptance Support' })).toBeVisible();

  await page.goto('/en/support');
  await expect(page.getByRole('heading', { name: 'Approved support channels' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Open' }).filter({ has: page.locator('xpath=ancestor::article[.//h3[contains(., "Join the official Discord")]]') })).toHaveAttribute('href', 'https://discord.gg/oteryn-acceptance');
  await expect(page.getByRole('link', { name: 'Open' }).filter({ has: page.locator('xpath=ancestor::article[.//h3[contains(., "Contact support")]]') })).toHaveAttribute('href', 'mailto:support@example.test');
  await expect(page.getByRole('link', { name: 'Open' }).filter({ has: page.locator('xpath=ancestor::article[.//h3[contains(., "Open the approved support service")]]') })).toHaveAttribute('href', 'https://support.example.test/help');

  await page.goto('/en/support/report-a-bug');
  await expect(page.locator('form')).toHaveCount(0);
  await expect(page.locator('main')).toContainText('Do not include passwords, recovery codes, MFA secrets or other credentials');

  await assertAccessibilitySmoke(page);
  await assertNoPageOverflow(page);
  await evidenceScreenshot(page, `support-legal-public-${test.info().project.name}`);

  expect(Object.keys(fixture.pages)).toHaveLength(8);
  expect(page.__acceptanceDiagnostics.pageErrors).toEqual([]);
  expect(page.__acceptanceDiagnostics.serverErrors).toEqual([]);
});
