import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const plugin = path.join(root, 'release-plugin', 'patriot-web-solutions');
const allowed = new Set([
  'https://github.com/kb4beast/hive-mind-os',
  'https://projects.propublica.org/nonprofits/organizations/991238039',
  'https://app.candid.org/profile/15321808/patriot-web-solutions-99-1238039'
]);

const sources = [path.join(plugin, 'payload', 'projects.json')];
for (const dir of ['content', 'notes']) {
  const full = path.join(plugin, 'payload', dir);
  for (const file of fs.readdirSync(full)) sources.push(path.join(full, file));
}

const urls = new Set();
for (const file of sources) {
  for (const match of fs.readFileSync(file, 'utf8').matchAll(/https?:\/\/[^\s"'<>\\)\]]+/g)) {
    urls.add(match[0].replace(/[.,;:]+$/, ''));
  }
}

let failed = false;
const results = [];
for (const url of [...urls].sort()) {
  if (!allowed.has(url)) {
    failed = true;
    results.push({ url, result: 'FAIL', reason: 'outside evidence allowlist' });
    continue;
  }
  try {
    const response = await fetch(url, { redirect: 'follow', headers: { 'user-agent': 'Mozilla/5.0 (compatible; pws-release-link-check)' } });
    // 403 from a bot filter is recorded as a warning, not silently passed off as reachable.
    const result = response.ok ? 'PASS' : response.status === 403 ? 'WARN-BOT-BLOCKED' : 'FAIL';
    if (result === 'FAIL') failed = true;
    results.push({ url, result, http_status: response.status });
  } catch (error) {
    failed = true;
    results.push({ url, result: 'FAIL', reason: String(error) });
  }
}

fs.mkdirSync(path.join(root, 'dist'), { recursive: true });
fs.writeFileSync(path.join(root, 'dist', 'link-check-receipt.json'), JSON.stringify({
  checked_at_utc: new Date().toISOString(),
  allowlist_size: allowed.size,
  urls: results
}, null, 2) + '\n');
console.log(JSON.stringify({ status: failed ? 'FAIL' : 'PASS', urls: results.length, results }));
if (failed) process.exit(1);
