import { loadBaseline, validatePortalEvidenceDimensions } from './validate-portal-evidence-dimensions.mjs';

const report = validatePortalEvidenceDimensions(loadBaseline());
process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
if (report.errors.length > 0) process.exitCode = 1;
