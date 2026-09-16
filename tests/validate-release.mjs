import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const plugin = path.join(root, 'release-plugin', 'patriot-web-solutions');
const read = (...parts) => fs.readFileSync(path.join(plugin, ...parts), 'utf8');
const content = JSON.parse(read('payload', 'content.json'));
const redirects = JSON.parse(read('payload', 'redirects.json'));

assert.equal(content.version, '2.0.0');
assert.equal(content.pages.length, 18);
assert.equal(new Set(content.pages.map((page) => page.slug)).size, content.pages.length);
const seenSlugs = new Set();
for (const page of content.pages) {
  if (page.parent !== undefined) {
    assert.ok(typeof page.parent === 'string' && seenSlugs.has(page.parent), `parent must exist and precede child: ${page.slug}`);
  }
  seenSlugs.add(page.slug);
}
const livePaths = new Set(content.pages.map((page) => '/' + (page.parent ? page.parent + '/' : '') + page.slug + '/'));
for (const legacy of [...Object.keys(redirects.redirects), ...redirects.gone]) {
  assert.ok(!livePaths.has(legacy), `legacy route collides with a live route: ${legacy}`);
}
for (const target of Object.values(redirects.redirects)) {
  assert.ok(livePaths.has(target), `redirect target is not a live route: ${target}`);
}
for (const page of content.pages) {
  assert.match(page.slug, /^[a-z0-9-]+$/);
  const sourcePath = path.join(plugin, 'payload', page.source);
  assert.ok(fs.existsSync(sourcePath) && fs.statSync(sourcePath).isFile(), `missing ${page.source}`);
  const html = fs.readFileSync(sourcePath, 'utf8');
  assert.ok(html.trim().length > 100, `thin content: ${page.slug}`);
  assert.doesNotMatch(html, /lorem ipsum|\[insert|coming soon|placeholder/i, `unfinished copy: ${page.slug}`);
  assert.doesNotMatch(html, /<script|on(click|load|error)=/i, `unsafe inline behavior: ${page.slug}`);
}

const allContent = content.pages.map((page) => read('payload', page.source)).join('\n');
for (const phrase of ['Monday', 'Wednesday', 'Friday', 'Train to standard', '20–28', '60–84', 'military members', 'families']) {
  assert.ok(allContent.toLowerCase().includes(phrase.toLowerCase()), `missing mission requirement: ${phrase}`);
}
for (const prohibited of ['job guarantee', 'guaranteed employment', 'official partner of OpenAI', 'official partner of Google']) {
  assert.ok(!allContent.toLowerCase().includes(prohibited), `unsupported claim: ${prohibited}`);
}

assert.ok(Array.isArray(content.notes) && content.notes.length === 3, 'three seeded field notes required');
assert.equal(new Set(content.notes.map((note) => note.slug)).size, 3);
const noteHtml = [];
for (const note of content.notes) {
  assert.match(note.slug, /^[a-z0-9-]+$/);
  assert.ok(typeof note.title === 'string' && note.title.length > 5, `note title: ${note.slug}`);
  const html = read('payload', note.source);
  noteHtml.push(html);
  assert.ok(html.trim().length > 400, `thin note: ${note.slug}`);
  assert.doesNotMatch(html, /<script|on(click|load|error)=/i, `unsafe note: ${note.slug}`);
}

const projects = JSON.parse(read('payload', 'projects.json'));
assert.equal(projects.projects.length, 3);
assert.deepEqual(projects.projects.map((project) => project.slug).sort(), ['ai-developer-workbench', 'coupon-hive', 'hive-mind-os']);
for (const project of projects.projects) {
  assert.ok(['public', 'historical', 'development'].includes(project.state), `state: ${project.slug}`);
  for (const field of ['name', 'state_label', 'checked_label', 'checked', 'summary']) {
    assert.ok(typeof project[field] === 'string' && project[field].length > 0, `${field}: ${project.slug}`);
  }
  assert.match(project.checked, /^\d{4}-\d{2}-\d{2}$/);
  assert.ok(project.checked_label.includes(project.checked), `checked_label must carry its date: ${project.slug}`);
  assert.ok(Array.isArray(project.links), `links: ${project.slug}`);
  if (project.state !== 'public') {
    assert.equal(project.links.length, 0, `non-public project must not carry links: ${project.slug}`);
  }
}

const facts = JSON.parse(read('payload', 'facts.json'));
assert.ok(facts.facts && typeof facts.facts === 'object' && !Array.isArray(facts.facts), 'facts.json must carry a facts object');
for (const value of Object.values(facts.facts)) assert.equal(typeof value, 'string');
const orgStatusConfirmed = facts.facts.org_status_confirmed === 'true';

const deferral = /will publish|will be published|will appear|will be displayed|will be linked|only after|being verified|will grow|are being prepared|must be confirmed|will be confirmed|being confirmed/gi;
let deferralInsideImpact = 0;
for (const page of content.pages) {
  const html = read('payload', page.source);
  const matches = html.match(deferral) ?? [];
  if (page.source === 'content/impact.html') {
    deferralInsideImpact = matches.length;
  } else {
    assert.equal(matches.length, 0, `deferral language outside impact: ${page.slug} -> ${matches.join(', ')}`);
  }
}
assert.ok(deferralInsideImpact >= 1 && deferralInsideImpact <= 2, `impact deferral budget exceeded: ${deferralInsideImpact}`);

// Visitor-facing PHP renders copy too (form notices, donation fallback, metas); hold it to the same truthfulness scans.
const visitorPhpFiles = [
  ['includes/class-pws-public.php', read('includes', 'class-pws-public.php')],
  ['includes/class-pws-forms.php', read('includes', 'class-pws-forms.php')],
  ...['header.php', 'footer.php', 'functions.php', 'front-page.php', 'page.php', 'home.php', 'index.php', '404.php']
    .map((name) => [`payload/theme/patriot-web-solutions/${name}`, read('payload', 'theme', 'patriot-web-solutions', name)]),
];
for (const [name, source] of visitorPhpFiles) {
  const phpDeferrals = source.match(deferral) ?? [];
  assert.equal(phpDeferrals.length, 0, `deferral language in visitor-facing PHP: ${name} -> ${phpDeferrals.join(', ')}`);
}

const visitorCorpus = allContent + '\n' + noteHtml.join('\n');
const truthCorpus = visitorCorpus + '\n' + visitorPhpFiles.map(([, source]) => source).join('\n');
for (const banned of [/google-approved/i, /openai[- ]partner/i, /partnered with openai/i, /compliant with (google|openai)/i, /chatgpt\.com\/g\//i]) {
  assert.doesNotMatch(truthCorpus, banned, `banned claim pattern: ${banned}`);
}
if (!orgStatusConfirmed) {
  assert.match(truthCorpus, /ProPublica[^\n]*lists[^\n]*as a 501\(c\)\(3\)/i, 'third-party status must be attributed');
  assert.match(truthCorpus, /Owner confirmation[^\n]*pending/i, 'status qualification must remain visible');
  assert.doesNotMatch(truthCorpus, /(?:Patriot Web Solutions|we|our organization)\s+(?:is|are)\s+(?:a\s+)?501\(c\)\(3\)/i, 'organization must not claim unconfirmed status in its own voice');
  assert.doesNotMatch(truthCorpus, /(?:your\s+)?donations?\s+(?:is|are)\s+tax[- ]deductible/i, 'deductibility claim requires owner confirmation');
}

const allowedUrls = new Set([
  'https://github.com/kb4beast/hive-mind-os',
  'https://projects.propublica.org/nonprofits/organizations/991238039',
  'https://app.candid.org/profile/15321808/patriot-web-solutions-99-1238039'
]);
const urlCorpus = visitorCorpus + '\n' + JSON.stringify(projects);
for (const match of urlCorpus.matchAll(/https?:\/\/[^\s"'<>\\)\]]+/g)) {
  const url = match[0].replace(/[.,;:]+$/, '');
  assert.ok(allowedUrls.has(url), `URL outside evidence allowlist: ${url}`);
}

const publicPhp = read('includes', 'class-pws-public.php');
const formPhp = read('includes', 'class-pws-forms.php');
const installerPhp = read('includes', 'class-pws-installer.php');
for (const shortcode of ['pws_interest_form', 'pws_contact_form', 'pws_donation', 'pws_project_catalog', 'pws_hero_image']) {
  assert.ok((publicPhp + formPhp).includes(`'${shortcode}'`), `missing shortcode: ${shortcode}`);
}
for (const control of ['wp_verify_nonce', 'sanitize_email', 'wp_mail', 'set_transient', 'wp_safe_redirect']) {
  assert.ok(formPhp.includes(control), `form control missing: ${control}`);
}
for (const control of ['current_user_can', 'page_conflicts', 'replace_conflicts', 'updated_pages', 'wp_save_post_revision', 'page_fingerprint', '_pws_release_state_hash', 'checkpoint', 'pending_page', 'pws_recovery_required', 'switch_theme', 'pws_release_rollback_v1', 'hash_equals']) {
  assert.ok(installerPhp.includes(control), `installer control missing: ${control}`);
}

const preserved = new Set(redirects.preserve_until_transaction_review);
for (const route of ['/checkout/', '/my-account/', '/donor-dashboard/', '/give/donation-form/']) {
  assert.ok(preserved.has(route), `transaction route not preserved: ${route}`);
  assert.ok(!(route in redirects.redirects), `transaction route redirected: ${route}`);
}
for (const target of Object.values(redirects.redirects)) {
  assert.ok(target.startsWith('/') && target.endsWith('/'), `invalid redirect target: ${target}`);
}

const css = read('payload', 'theme', 'patriot-web-solutions', 'assets', 'css', 'site.css');
assert.ok(css.includes('@media (prefers-reduced-motion: reduce)'));
assert.ok(css.includes(':focus-visible'));
assert.ok(css.includes('@media (max-width: 620px)'));
assert.doesNotMatch(css, /url\(\s*['"]?https?:/i, 'remote CSS asset');

const themeFunctions = read('payload', 'theme', 'patriot-web-solutions', 'functions.php');
for (const control of ['pre_get_posts', 'pws-field-notes', 'wp_sitemaps_posts_query_args', 'wp_sitemaps_taxonomies_query_args', "name === 'users'"]) {
  assert.ok(themeFunctions.includes(control), `legacy post isolation missing: ${control}`);
}
assert.ok(publicPhp.includes("get_post_type($configured) === 'give_forms'"), 'configured donation ID must be a GiveWP form');

const phpFiles = [];
const walk = (dir) => {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full);
    else if (entry.name.endsWith('.php')) phpFiles.push(full);
  }
};
walk(plugin);
assert.ok(phpFiles.length >= 9);
for (const file of phpFiles) {
  const source = fs.readFileSync(file, 'utf8');
  assert.ok(source.includes('<?php') || source.startsWith('<!doctype html>'), `unexpected PHP entry: ${file}`);
  assert.doesNotMatch(source, /eval\s*\(|base64_decode\s*\(|shell_exec\s*\(/, `dangerous PHP function: ${file}`);
}

const manifestPath = path.join(plugin, 'release-manifest.json');
if (process.argv.includes('--manifest')) {
  assert.ok(fs.existsSync(manifestPath), 'release manifest missing');
  const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
  for (const entry of manifest.files) {
    const bytes = fs.readFileSync(path.join(plugin, ...entry.path.split('/')));
    assert.equal(bytes.length, entry.bytes, `manifest size: ${entry.path}`);
    assert.equal(crypto.createHash('sha256').update(bytes).digest('hex'), entry.sha256, `manifest hash: ${entry.path}`);
  }
}

console.log(JSON.stringify({ status: 'PASS', pages: content.pages.length, redirects: Object.keys(redirects.redirects).length, gone: redirects.gone.length, preserved: redirects.preserve_until_transaction_review.length, php_files: phpFiles.length }));
