import { test, expect } from '@playwright/test';
import {
  attachDiagnostics,
  completeMfaChallenge,
  enrollMfa,
  installDiagnostics,
  login,
  register,
  uniqueEmail,
  waitForDifferentTotp,
  waitForResetLink,
} from './helpers.mjs';

test.beforeEach(async ({ page }) => {
  page.__acceptanceDiagnostics = installDiagnostics(page);
});

test.afterEach(async ({ page }, testInfo) => {
  await attachDiagnostics(testInfo, page.__acceptanceDiagnostics);
});

test('Flow 3b — password recovery uses real SMTP, revokes old sessions and rejects token replay', async ({ browser, page }) => {
  const email = uniqueEmail('password-recovery');
  const originalPassword = 'AcceptanceRecovery!234';
  const changedPassword = 'AcceptanceRecovered!567';

  await register(page, email, originalPassword);
  await login(page, email, originalPassword);
  await expect(page).toHaveURL(/\/$/u);

  const mfa = await enrollMfa(page, originalPassword);
  let lastTotp = mfa.enrollmentCode;
  await page.goto('/mfa');
  await expect(page.getByText('MFA is enabled for your Oteryn Platform web sign in.')).toBeVisible();

  const resetContext = await browser.newContext();
  const resetPage = await resetContext.newPage();
  try {
    await resetPage.goto('/forgot-password');
    await resetPage.getByLabel('Email').fill(email);
    await resetPage.getByRole('button', { name: 'Send reset link' }).click();
    await expect(resetPage.getByRole('status')).toBeVisible();

    const resetLink = await waitForResetLink(email);
    await resetPage.goto(resetLink);
    await resetPage.getByLabel('Email').fill(email);
    await resetPage.getByLabel('New password', { exact: true }).fill(changedPassword);
    await resetPage.getByLabel('Confirm new password', { exact: true }).fill(changedPassword);
    await resetPage.getByRole('button', { name: 'Reset password' }).click();
    await expect(resetPage.getByRole('status')).toContainText('Your password has been reset. Sign in again.');

    await page.goto('/mfa');
    await expect(page).toHaveURL(/\/login$/u);

    await resetPage.getByLabel('Email').fill(email);
    await resetPage.getByLabel('Password').fill(originalPassword);
    await resetPage.getByRole('button', { name: 'Sign in' }).click();
    await expect(resetPage.getByRole('alert')).toBeVisible();

    await resetPage.goto(resetLink);
    await resetPage.getByLabel('Email').fill(email);
    await resetPage.getByLabel('New password', { exact: true }).fill('AcceptanceReplay!890');
    await resetPage.getByLabel('Confirm new password', { exact: true }).fill('AcceptanceReplay!890');
    await resetPage.getByRole('button', { name: 'Reset password' }).click();
    await expect(resetPage.getByRole('alert')).toContainText('This password reset link is invalid or expired.');

    await login(resetPage, email, changedPassword);
    const code = await waitForDifferentTotp(mfa.secret, lastTotp);
    await completeMfaChallenge(resetPage, code);
    lastTotp = code;
    await expect(resetPage).toHaveURL(/\/$/u);
    expect(lastTotp).not.toBe(mfa.enrollmentCode);
  } finally {
    await resetContext.close();
  }
});
