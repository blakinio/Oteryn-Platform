import { defineConfig } from '@playwright/test';

const baseURL = process.env.ACCEPTANCE_BASE_URL ?? 'http://127.0.0.1:8080';
const outputDir = process.env.ACCEPTANCE_OUTPUT_DIR ?? '../../artifacts/acceptance/test-results';

export default defineConfig({
  testDir: './tests',
  outputDir,
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  timeout: 120_000,
  reporter: [
    ['line'],
    ['html', { outputFolder: '../../artifacts/acceptance/html-report', open: 'never' }],
    ['junit', { outputFile: '../../artifacts/acceptance/junit.xml' }],
  ],
  use: {
    baseURL,
    browserName: 'chromium',
    viewport: { width: 1440, height: 1000 },
    actionTimeout: 15_000,
    navigationTimeout: 30_000,
    // Raw Playwright traces and automatic failure screenshots can capture session
    // cookies, reset URLs, TOTP enrollment secrets or recovery codes. Secret-bearing
    // full flows therefore use sanitized diagnostics rather than raw trace artifacts.
    // The non-secret smoke suite opts into raw trace/screenshot evidence explicitly.
    trace: 'off',
    screenshot: 'off',
    video: 'off',
  },
  expect: {
    timeout: 10_000,
  },
});
