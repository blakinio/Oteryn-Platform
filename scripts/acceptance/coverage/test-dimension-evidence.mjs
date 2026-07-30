import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const coverageRoot = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(coverageRoot, '../../..');
const validator = path.join(coverageRoot, 'validate-dimension-evidence.mjs');
const tempRoot = fs.mkdtempSync(path.join(coverageRoot, '.dimension-fixture-'));

function write(relative, value) {
  const absolute = path.join(tempRoot, relative);
  fs.mkdirSync(path.dirname(absolute), { recursive: true });
  fs.writeFileSync(absolute, typeof value === 'string' ? value : `${JSON.stringify(value, null, 2)}\n`);
  return path.relative(repoRoot, absolute);
}

const evidencePath = write('evidence.spec.mjs', "test('@fixture rendered evidence', async () => {});\n");
const configPath = write('playwright.config.mjs', "export default { projects: [{ name: 'fixture-chromium-mobile' }] };\n");
const packagePath = write('package.json', { scripts: { 'test:fixture': 'playwright test' } });

const baseSurface = {
  id: 'fixture.surface',
  owner: 'Fixture',
  status: 'covered',
  route_names: ['fixture.route'],
  roles: ['guest'],
  states: ['rendered'],
  viewports: ['desktop-1440x1000', 'tablet-820x1180', 'mobile-390x844'],
  browsers: ['chromium-primary'],
  evidence_layers: ['playwright'],
  evidence: [{ file: evidencePath, markers: ['@fixture rendered evidence'] }],
  gaps: [],
};

function mapping(executable = { kind: 'npm', profile: 'test:fixture' }) {
  return {
    evidence_file: evidencePath,
    marker: '@fixture rendered evidence',
    executable,
  };
}

const baseContract = {
  schema_version: 1,
  allowed_viewports: ['desktop-1440x1000', 'tablet-820x1180', 'mobile-390x844', 'resource-endpoint'],
  allowed_browsers: ['chromium-primary', 'firefox-bounded', 'webkit-bounded', 'bounded-portability', 'http-contract', 'chromium-primary-where-rendered'],
  surface_policies: {
    'fixture.surface': {
      critical: true,
      portability_exclusion_rationale: 'Fixture validates Chromium-only secret-sensitive coverage.',
      viewports: {
        'desktop-1440x1000': mapping(),
        'tablet-820x1180': mapping(),
        'mobile-390x844': mapping({ kind: 'playwright-project', project: 'fixture-chromium-mobile', config: configPath }),
      },
      browsers: { 'chromium-primary': mapping() },
    },
  },
};

function execute(name, mutate, expectedFragment, shouldPass = false) {
  const manifest = { schema_version: 1, allowed_statuses: ['covered'], surfaces: [structuredClone(baseSurface)] };
  const contract = structuredClone(baseContract);
  mutate({ manifest, contract });
  const manifestPath = write(`${name}/portal-coverage-manifest.json`, manifest);
  const contractPath = write(`${name}/portal-dimension-evidence.json`, contract);
  let output = '';
  try {
    output = execFileSync('node', [validator, `--manifest=${manifestPath}`, `--contract=${contractPath}`, `--package=${packagePath}`], {
      cwd: repoRoot,
      encoding: 'utf8',
      stdio: ['ignore', 'pipe', 'pipe'],
    });
    if (!shouldPass) throw new Error(`${name} unexpectedly passed.`);
  } catch (error) {
    output = `${error.stdout ?? ''}${error.stderr ?? ''}`;
    if (shouldPass) throw new Error(`${name} unexpectedly failed:\n${output}`);
  }
  if (!output.includes(expectedFragment)) throw new Error(`${name} did not report ${expectedFragment}:\n${output}`);
}

try {
  execute('valid', () => {}, '"errors": []', true);
  execute('missing-mobile', ({ contract }) => { delete contract.surface_policies['fixture.surface'].viewports['mobile-390x844']; }, 'mobile-390x844 has no exact evidence mapping');
  execute('unknown-browser', ({ manifest }) => { manifest.surfaces[0].browsers = ['netscape-future']; }, 'declares unknown browser/profile netscape-future');
  execute('missing-rationale', ({ contract }) => { delete contract.surface_policies['fixture.surface'].portability_exclusion_rationale; }, 'portability_exclusion_rationale must be a non-empty string');
  execute('orphan-mapping', ({ contract }) => { contract.surface_policies['fixture.surface'].viewports['resource-endpoint'] = mapping(); }, 'has orphan viewport mapping resource-endpoint');
  execute('missing-marker', ({ contract }) => { contract.surface_policies['fixture.surface'].browsers['chromium-primary'].marker = '@missing'; }, 'marker not found');
  execute('missing-project', ({ contract }) => { contract.surface_policies['fixture.surface'].viewports['mobile-390x844'].executable.project = 'missing-project'; }, 'Playwright project missing-project not found');
  execute('missing-profile', ({ contract }) => { contract.surface_policies['fixture.surface'].browsers['chromium-primary'].executable.profile = 'test:missing'; }, 'missing npm profile test:missing');
  execute('orphan-policy', ({ contract }) => { contract.surface_policies['fixture.orphan'] = structuredClone(contract.surface_policies['fixture.surface']); }, 'Orphan dimension policy references unknown surface fixture.orphan');
  process.stdout.write(`${JSON.stringify({ result: 'PASS', fixtures: 9 }, null, 2)}\n`);
} finally {
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
