import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const coverageRoot = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(coverageRoot, '../../..');

function parseArgument(name, fallback) {
  const prefix = `--${name}=`;
  const value = process.argv.find((argument) => argument.startsWith(prefix));
  return value ? value.slice(prefix.length) : fallback;
}

const manifestPath = path.resolve(repoRoot, parseArgument('manifest', 'scripts/acceptance/coverage/portal-coverage-manifest.json'));
const contractPath = path.resolve(repoRoot, parseArgument('contract', 'scripts/acceptance/coverage/portal-dimension-evidence.json'));
const packagePath = path.resolve(repoRoot, parseArgument('package', 'scripts/acceptance/package.json'));
const errors = [];

function readJson(file) {
  try {
    return JSON.parse(fs.readFileSync(file, 'utf8'));
  } catch (error) {
    errors.push(`Cannot parse ${path.relative(repoRoot, file)}: ${error.message}`);
    return null;
  }
}

function readSurfaces(manifest) {
  const surfaces = Array.isArray(manifest?.surfaces) ? [...manifest.surfaces] : [];
  const fragmentRoot = path.join(path.dirname(manifestPath), 'surfaces');
  if (!fs.existsSync(fragmentRoot)) return surfaces;

  for (const entry of fs.readdirSync(fragmentRoot, { withFileTypes: true })
    .filter((candidate) => candidate.isFile() && candidate.name.endsWith('.json'))
    .sort((left, right) => left.name.localeCompare(right.name))) {
    const value = readJson(path.join(fragmentRoot, entry.name));
    const fragmentSurfaces = Array.isArray(value) ? value : value?.surfaces;
    if (Array.isArray(fragmentSurfaces)) surfaces.push(...fragmentSurfaces);
  }
  return surfaces;
}

function requireString(value, context) {
  if (typeof value !== 'string' || value.trim() === '') {
    errors.push(`${context} must be a non-empty string.`);
    return null;
  }
  return value;
}

function validateRepositoryFile(relative, context) {
  const value = requireString(relative, context);
  if (!value) return null;
  const absolute = path.resolve(repoRoot, value);
  if (!absolute.startsWith(`${repoRoot}${path.sep}`)) {
    errors.push(`${context} escapes the repository root: ${value}`);
    return null;
  }
  if (!fs.existsSync(absolute) || !fs.statSync(absolute).isFile()) {
    errors.push(`${context} references missing file ${value}.`);
    return null;
  }
  return absolute;
}

function validateExecutable(executable, context, packageJson) {
  if (!executable || typeof executable !== 'object') {
    errors.push(`${context}.executable must be an object.`);
    return;
  }
  if (executable.kind === 'npm') {
    const profile = requireString(executable.profile, `${context}.executable.profile`);
    if (profile && typeof packageJson?.scripts?.[profile] !== 'string') {
      errors.push(`${context} references missing npm profile ${profile}.`);
    }
    return;
  }
  if (executable.kind === 'playwright-project') {
    const project = requireString(executable.project, `${context}.executable.project`);
    const config = validateRepositoryFile(executable.config, `${context}.executable.config`);
    if (project && config && !fs.readFileSync(config, 'utf8').includes(`name: '${project}'`)) {
      errors.push(`${context} references Playwright project ${project} not found in ${executable.config}.`);
    }
    return;
  }
  errors.push(`${context}.executable.kind must be npm or playwright-project.`);
}

function validateMapping(mapping, context, packageJson) {
  if (!mapping || typeof mapping !== 'object') {
    errors.push(`${context} must be an object.`);
    return;
  }
  const evidenceFile = validateRepositoryFile(mapping.evidence_file, `${context}.evidence_file`);
  const marker = requireString(mapping.marker, `${context}.marker`);
  if (evidenceFile && marker && !fs.readFileSync(evidenceFile, 'utf8').includes(marker)) {
    errors.push(`${context} marker not found in ${mapping.evidence_file}: ${marker}`);
  }
  validateExecutable(mapping.executable, context, packageJson);
}

const manifest = readJson(manifestPath);
const contract = readJson(contractPath);
const packageJson = readJson(packagePath);

if (manifest && contract && packageJson) {
  if (contract.schema_version !== 1) errors.push('dimension evidence schema_version must be 1.');
  const allowedViewports = new Set(Array.isArray(contract.allowed_viewports) ? contract.allowed_viewports : []);
  const allowedBrowsers = new Set(Array.isArray(contract.allowed_browsers) ? contract.allowed_browsers : []);
  if (allowedViewports.size === 0) errors.push('allowed_viewports must be non-empty.');
  if (allowedBrowsers.size === 0) errors.push('allowed_browsers must be non-empty.');

  const policies = contract.surface_policies && typeof contract.surface_policies === 'object'
    ? contract.surface_policies
    : {};
  const surfaces = readSurfaces(manifest);
  const surfaceIds = new Set(surfaces.map((surface) => surface.id));

  for (const policyId of Object.keys(policies)) {
    if (!surfaceIds.has(policyId)) errors.push(`Orphan dimension policy references unknown surface ${policyId}.`);
  }

  for (const surface of surfaces) {
    if (!['covered', 'supporting_endpoint'].includes(surface.status)) continue;
    const policy = policies[surface.id];
    if (!policy || typeof policy !== 'object') {
      errors.push(`${surface.id} has no dimension evidence policy.`);
      continue;
    }

    const declaredViewports = Array.isArray(surface.viewports) ? surface.viewports : [];
    const declaredBrowsers = Array.isArray(surface.browsers) ? surface.browsers : [];
    for (const viewport of declaredViewports) {
      if (!allowedViewports.has(viewport)) errors.push(`${surface.id} declares unknown viewport ${viewport}.`);
      if (!policy.viewports?.[viewport]) errors.push(`${surface.id} viewport ${viewport} has no exact evidence mapping.`);
      else validateMapping(policy.viewports[viewport], `${surface.id}.viewports.${viewport}`, packageJson);
    }
    for (const mappedViewport of Object.keys(policy.viewports ?? {})) {
      if (!declaredViewports.includes(mappedViewport)) errors.push(`${surface.id} has orphan viewport mapping ${mappedViewport}.`);
    }

    for (const browser of declaredBrowsers) {
      if (!allowedBrowsers.has(browser)) errors.push(`${surface.id} declares unknown browser/profile ${browser}.`);
      if (!policy.browsers?.[browser]) errors.push(`${surface.id} browser/profile ${browser} has no exact evidence mapping.`);
      else validateMapping(policy.browsers[browser], `${surface.id}.browsers.${browser}`, packageJson);
    }
    for (const mappedBrowser of Object.keys(policy.browsers ?? {})) {
      if (!declaredBrowsers.includes(mappedBrowser)) errors.push(`${surface.id} has orphan browser mapping ${mappedBrowser}.`);
    }

    const rendered = surface.status === 'covered' && !declaredViewports.includes('resource-endpoint');
    if (rendered && policy.critical === true) {
      for (const viewport of ['desktop-1440x1000', 'tablet-820x1180', 'mobile-390x844']) {
        if (!declaredViewports.includes(viewport)) errors.push(`${surface.id} is critical but does not declare ${viewport}.`);
      }
    }

    const hasFirefox = declaredBrowsers.some((browser) => browser.includes('firefox') || browser === 'bounded-portability');
    const hasWebkit = declaredBrowsers.some((browser) => browser.includes('webkit') || browser === 'bounded-portability');
    if (rendered && (!hasFirefox || !hasWebkit)) {
      requireString(policy.portability_exclusion_rationale, `${surface.id}.portability_exclusion_rationale`);
    }
  }
}

const report = {
  schema_version: contract?.schema_version ?? null,
  manifest: path.relative(repoRoot, manifestPath),
  contract: path.relative(repoRoot, contractPath),
  errors,
};
process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
if (errors.length > 0) process.exitCode = 1;
