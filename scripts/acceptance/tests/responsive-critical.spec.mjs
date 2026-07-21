import { test, expect } from '@playwright/test';
import {
  assertAccessibilitySmoke,
  attachDiagnostics,
  completeMfaChallenge,
  installDiagnostics,
  login,
  runBinary,
  uniqueEmail,
} from './helpers.mjs';

const accountPassword = 'AcceptanceResponsiveAccount!234';
const browserAdminPassword = 'Acceptance-Browser-Admin-9!Pass';
const browserAdminRecoveryCode = 'BROWSER-00001';

function seedReadyAccount(email) {
  return JSON.parse(runBinary('php', [
    'scripts/acceptance/seed-account-overview-state.php',
    email,
    accountPassword,
    'ready',
  ]));
}

function seedVisualFixtures() {
  runBinary('php', ['scripts/acceptance/seed.php', 'seed']);
}

function seedBrowserAdmin(email) {
  runBinary('php', [
    'scripts/acceptance/seed-browser-admin.php',
    email,
    browserAdminPassword,
    browserAdminRecoveryCode,
  ]);
}

async function openPublicNewsThroughVisibleNavigation(page) {
  const desktopNavigation = page.getByRole('navigation', { name: 'Public navigation' });
  if (await desktopNavigation.isVisible()) {
    await desktopNavigation.getByRole('link', { name: 'News' }).click();
    return;
  }

  await page.getByText('Menu', { exact: true }).click();
  const mobilePanel = page.locator('.mobile-nav-panel');
  await expect(mobilePanel).toBeVisible();
  await mobilePanel.getByRole('link', { name: 'News' }).click();
}

test.setTimeout(120_000);
test.describe.configure({ retries: 0 });

test.beforeEach(async ({ page }) => {
  page.__acceptanceDiagnostics = installDiagnostics(page);
});

test.afterEach(async ({ page }, testInfo) => {
  await attachDiagnostics(testInfo, page.__acceptanceDiagnostics);
});

test('@responsive public navigation and Identity entry forms stay usable without horizontal overflow', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByRole('heading', { name: 'Oteryn Platform' })).toBeVisible();
  await assertAccessibilitySmoke(page);

  await openPublicNewsThroughVisibleNavigation(page);
  await expect(page.getByRole('heading', { name: 'News' })).toBeVisible();
  await assertAccessibilitySmoke(page);

  await page.goto('/register');
  await expect(page.getByRole('heading', { name: 'Create an Oteryn Platform identity' })).toBeVisible();
  await expect(page.getByLabel('Email')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Register' })).toBeVisible();
  await assertAccessibilitySmoke(page);

  await page.goto('/login');
  await expect(page.getByRole('heading', { name: 'Sign in to Oteryn Platform' })).toBeVisible();
  await expect(page.getByLabel('Email')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Sign in' })).toBeVisible();
  await assertAccessibilitySmoke(page);

  await page.goto('/forgot-password');
  await expect(page.getByLabel('Email')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Send reset link' })).toBeVisible();
  await assertAccessibilitySmoke(page);
});

test('@responsive authenticated Account Overview remains operable at representative viewports', async ({ page }) => {
  const email = uniqueEmail('responsive-account');
  seedReadyAccount(email);

  await login(page, email, accountPassword);
  await page.goto('/account');
  await expect(page.getByRole('heading', { name: 'Account overview' })).toBeVisible();
  await expect(page.getByText('Ready', { exact: true })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Create a character' })).toBeVisible();
  await assertAccessibilitySmoke(page);
});

test('@responsive MFA challenge and privileged administrator surface remain operable at representative viewports', async ({ page }) => {
  const adminEmail = uniqueEmail('responsive-admin');
  seedVisualFixtures();
  seedBrowserAdmin(adminEmail);

  await login(page, adminEmail, browserAdminPassword);
  await expect(page.getByRole('heading', { name: 'Complete your sign in' })).toBeVisible();
  await expect(page.getByLabel('Authenticator or recovery code')).toBeVisible();
  await assertAccessibilitySmoke(page);

  await completeMfaChallenge(page, browserAdminRecoveryCode);
  await page.goto('/admin');
  await expect(page.getByRole('heading', { name: 'Administration' })).toBeVisible();
  await assertAccessibilitySmoke(page);

  const pagesResponse = await page.goto('/admin/pages');
  expect(pagesResponse?.status()).toBe(200);
  await expect(page.getByText('about-oteryn')).toBeVisible();
  await assertAccessibilitySmoke(page);
});
