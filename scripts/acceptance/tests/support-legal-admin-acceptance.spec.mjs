import { test, expect } from '@playwright/test';
import {
  assertAccessibilitySmoke,
  attachDiagnostics,
  completeMfaChallenge,
  evidenceScreenshot,
  installDiagnostics,
  login,
  logout,
  runBinary,
  uniqueEmail,
} from './helpers.mjs';

const password = 'AcceptanceSupportLegal!234';

function supportFixture(...args) {
  return JSON.parse(runBinary('php', ['scripts/acceptance/seed-browser-support-legal.php', ...args]));
}

function seedIdentity(label, { confirmedMfa, permissions }) {
  const email = uniqueEmail(label);
  const recoveryCode = `SUP-${label.toUpperCase().replace(/[^A-Z0-9]/gu, '').slice(0, 14)}-01`;
  supportFixture(
    'seed-identity',
    email,
    password,
    recoveryCode,
    confirmedMfa ? 'confirmed' : 'unconfirmed',
    permissions.join(','),
  );
  return { email, password, recoveryCode, confirmedMfa };
}

async function signIn(page, identity) {
  await login(page, identity.email, identity.password);
  if (identity.confirmedMfa) {
    await completeMfaChallenge(page, identity.recoveryCode);
  }
}

async function assertNoPageOverflow(page) {
  const dimensions = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, `Unexpected page overflow on ${page.url()}`).toBeLessThanOrEqual(dimensions.viewport + 1);
}

async function submitTranslation(page) {
  const button = page.getByRole('button', { name: 'Save translation' });
  await button.scrollIntoViewIfNeeded();
  const receivesPointer = await button.evaluate((element) => {
    const rect = element.getBoundingClientRect();
    const target = document.elementFromPoint(rect.left + rect.width / 2, rect.top + rect.height / 2);
    return target === element || element.contains(target);
  });
  expect(receivesPointer).toBe(true);
  await button.focus();
  await expect(button).toBeFocused();
  await page.keyboard.press('Enter');
}

test.setTimeout(180_000);
test.describe.configure({ retries: 0 });

test.beforeEach(async ({ page }) => {
  page.__acceptanceDiagnostics = installDiagnostics(page);
});

test.afterEach(async ({ page }, testInfo) => {
  await attachDiagnostics(testInfo, page.__acceptanceDiagnostics);
});

test('@portal-support-legal guest, MFA and exact support-content permission boundaries fail closed', async ({ page }) => {
  supportFixture('reset');

  await page.goto('/admin/support-content');
  await expect(page).toHaveURL(/\/login$/u);

  const noMfa = seedIdentity('support-no-mfa', {
    confirmedMfa: false,
    permissions: ['support.content.manage'],
  });
  await signIn(page, noMfa);
  let response = await page.goto('/admin/support-content');
  expect(response?.status()).toBe(403);
  await expect(page.getByRole('heading', { name: 'You do not have access to this page' })).toBeVisible();
  await logout(page);

  const noPermission = seedIdentity('support-no-permission', {
    confirmedMfa: true,
    permissions: [],
  });
  await signIn(page, noPermission);
  response = await page.goto('/admin/support-content');
  expect(response?.status()).toBe(403);
  await expect(page.getByRole('heading', { name: 'You do not have access to this page' })).toBeVisible();

  expect(page.__acceptanceDiagnostics.pageErrors).toEqual([]);
  expect(page.__acceptanceDiagnostics.serverErrors).toEqual([]);
});

test('@portal-support-legal administrator publication, legal version, Polish translation, stale recovery and audit lifecycle', async ({ page }) => {
  supportFixture('reset');
  const manager = seedIdentity('support-manager', {
    confirmedMfa: true,
    permissions: ['support.content.manage', 'audit.view'],
  });
  await signIn(page, manager);

  await page.goto('/admin/support-content');
  await expect(page.getByRole('heading', { name: 'Support, rules and legal content' })).toBeVisible();
  await expect(page.locator('tbody tr')).toHaveCount(8);
  await assertAccessibilitySmoke(page);
  await assertNoPageOverflow(page);

  const englishSupportBody = 'Account security\nMFA guide\nFAQ\nKnown issues\nContact and support\nSecret support body must not enter audit metadata.';
  await page.goto('/admin/support-content/support/edit');
  await page.getByLabel('Title').fill('Acceptance Support Admin');
  await page.getByLabel('Body (plain text)').fill(englishSupportBody);
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('status')).toContainText('Editorial page created.');

  let response = await page.goto('/en/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByText('This content is not published.')).toBeVisible();

  await page.goto('/admin/support-content/support/edit');
  await page.getByLabel('Publish at').fill('2000-01-01T00:00');
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('status')).toContainText('Editorial page saved.');

  response = await page.goto('/en/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByRole('heading', { name: 'Acceptance Support Admin' })).toBeVisible();

  await page.goto('/admin/support-content');
  const supportRow = page.locator('tbody tr').filter({ hasText: 'Support' });
  const translationHref = await supportRow.getByRole('link', { name: 'Polish translation' }).getAttribute('href');
  expect(translationHref).toBeTruthy();
  await page.goto(translationHref);
  await page.getByLabel('Polish title').fill('Pomoc akceptacyjna');
  await page.getByLabel('Polish content (plain text)').fill('Bezpieczeństwo konta, MFA i kontakt z pomocą.');
  await page.getByLabel('Publish Polish translation at (UTC)').fill('2000-01-01T00:00');
  await submitTranslation(page);
  await expect(page.getByRole('status')).toContainText('Polish translation saved.');

  response = await page.goto('/pl/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByRole('heading', { name: 'Pomoc akceptacyjna' })).toBeVisible();
  await expect(page.getByText('Acceptance Support Admin')).toHaveCount(0);

  await page.waitForTimeout(1100);
  await page.goto('/admin/support-content/support/edit');
  await page.getByLabel('Title').fill('Acceptance Support Admin Updated');
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('status')).toContainText('Editorial page saved.');

  response = await page.goto('/pl/support');
  expect(response?.status()).toBe(200);
  await expect(page.getByText('This translation is temporarily unavailable.')).toBeVisible();
  await expect(page.getByText('Pomoc akceptacyjna')).toHaveCount(0);

  await page.goto(translationHref);
  await expect(page.getByText('The source changed after this translation was reviewed.')).toBeVisible();
  await submitTranslation(page);
  await expect(page.getByRole('status')).toContainText('Polish translation saved.');
  await page.goto('/pl/support');
  await expect(page.getByRole('heading', { name: 'Pomoc akceptacyjna' })).toBeVisible();

  const legalBodyV1 = 'Terms of Service\nAcceptance legal body version one.';
  await page.goto('/admin/support-content/terms/edit');
  await page.getByLabel('Title').fill('Acceptance Terms');
  await page.getByLabel('Body (plain text)').fill(legalBodyV1);
  await page.getByLabel('Publish at').fill('2000-01-01T00:00');
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('alert')).toContainText('Published legal documents require a version and effective date.');

  await page.getByLabel('Legal version').fill('v2026.1');
  await page.getByLabel('Effective date').fill('2026-07-01');
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('status')).toContainText('Editorial page created.');
  await expect(page.getByRole('heading', { name: 'Preserved published versions' })).toBeVisible();
  await expect(page.getByText('v2026.1', { exact: true })).toBeVisible();

  await page.getByLabel('Body (plain text)').fill('Terms of Service\nChanged legal meaning.');
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('alert')).toContainText('This published legal version is immutable.');

  await page.getByLabel('Legal version').fill('v2026.2');
  await page.getByLabel('Effective date').fill('2026-08-01');
  await page.getByRole('button', { name: 'Save editorial page' }).click();
  await expect(page.getByRole('status')).toContainText('Editorial page saved.');
  await expect(page.getByText('v2026.1', { exact: true })).toBeVisible();
  await expect(page.getByText('v2026.2', { exact: true })).toBeVisible();

  response = await page.goto('/en/legal/terms');
  expect(response?.status()).toBe(200);
  await expect(page.getByRole('heading', { name: 'Acceptance Terms' })).toBeVisible();
  await expect(page.getByText(/v2026\.2/u)).toBeVisible();

  await page.goto('/admin/audit');
  await expect(page.getByRole('heading', { name: 'Administrator audit' })).toBeVisible();
  await expect(page.getByText('support.content_created').first()).toBeVisible();
  await expect(page.getByText('support.content_updated').first()).toBeVisible();
  await expect(page.getByText('cms.translation_saved').first()).toBeVisible();
  await expect(page.locator('body')).not.toContainText(englishSupportBody);
  await expect(page.locator('body')).not.toContainText(legalBodyV1);
  await expect(page.locator('body')).not.toContainText(manager.password);
  await expect(page.locator('body')).not.toContainText(manager.recoveryCode);
  await assertAccessibilitySmoke(page);
  await assertNoPageOverflow(page);
  await evidenceScreenshot(page, `support-legal-admin-audit-${test.info().project.name}`);

  expect(page.__acceptanceDiagnostics.pageErrors).toEqual([]);
  expect(page.__acceptanceDiagnostics.serverErrors).toEqual([]);
});
