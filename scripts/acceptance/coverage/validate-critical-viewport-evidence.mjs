import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const coverageRoot = path.dirname(fileURLToPath(import.meta.url));
const defaultRepoRoot = path.resolve(coverageRoot, '../../..');
const requiredCriticalViewports = ['desktop-1440x1000', 'tablet-820x1180', 'mobile-390x844'];

export function validateCriticalViewportEvidence({ contract, repoRoot = defaultRepoRoot }) {
  const errors = [];
  const profiles = new Map((contract.profiles ?? []).map((profile) => [profile.id, profile]));
  const critical = (contract.surfaces ?? []).filter((record) => record.kind === 'rendered' && record.critical === true);
  let derivedMappings = 0;

  for (const record of critical) {
    const evidenceById = new Map((record.evidence ?? []).map((evidence) => [evidence.id, evidence]));

    for (const viewport of requiredCriticalViewports) {
      const explicit = record.viewports?.[viewport];
      if (explicit) {
        const profile = profiles.get(explicit.profile);
        const project = profile?.projects?.find((candidate) => candidate.name === explicit.project);
        if (profile?.blocking !== true || project?.browser !== 'chromium' || project?.viewport !== viewport) {
          errors.push(`${record.id} critical viewport ${viewport} is not tied to a blocking zero-retry Chromium project.`);
        }
        continue;
      }

      let proved = false;
      for (const mapping of Object.values(record.viewports ?? {})) {
        const profile = profiles.get(mapping.profile);
        const project = profile?.projects?.find((candidate) => candidate.browser === 'chromium' && candidate.viewport === viewport);
        const evidence = evidenceById.get(mapping.evidence);
        if (profile?.blocking !== true || !project || !evidence) continue;

        const configPath = path.resolve(repoRoot, profile.config_file ?? '');
        if (!configPath.startsWith(`${repoRoot}${path.sep}`) || !fs.existsSync(configPath)) continue;
        const configSource = fs.readFileSync(configPath, 'utf8');
        const specBasename = path.basename(evidence.file ?? '');
        if (specBasename === '' || !configSource.includes(specBasename)) continue;

        const evidencePath = path.resolve(repoRoot, evidence.file);
        if (!evidencePath.startsWith(`${repoRoot}${path.sep}`) || !fs.existsSync(evidencePath)) continue;
        const evidenceSource = fs.readFileSync(evidencePath, 'utf8');
        if (!(evidence.markers ?? []).every((marker) => evidenceSource.includes(marker))) continue;

        proved = true;
        derivedMappings += 1;
        break;
      }

      if (!proved) {
        errors.push(`${record.id} critical viewport ${viewport} has neither an explicit mapping nor a blocking project-selected evidence mapping.`);
      }
    }
  }

  return {
    critical_surface_count: critical.length,
    required_viewports: requiredCriticalViewports,
    derived_mapping_count: derivedMappings,
    errors,
  };
}

if (process.argv[1] === fileURLToPath(import.meta.url)) {
  const { loadBaseline } = await import('./validate-portal-evidence-dimensions.mjs');
  const report = validateCriticalViewportEvidence(loadBaseline());
  process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
  if (report.errors.length > 0) process.exitCode = 1;
}
