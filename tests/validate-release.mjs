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

assert.equal(content.version, '1.0.0');
assert.equal(content.pages.length, 15);
assert.equal(new Set(content.pages.map((page) => page.slug)).size, content.pages.length);
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
for (const phrase of ['Monday', 'Wednesday', 'Friday', 'No flunk-out', '20–28', '60–84', 'military members', 'families']) {
  assert.ok(allContent.toLowerCase().includes(phrase.toLowerCase()), `missing mission requirement: ${phrase}`);
}
for (const prohibited of ['job guarantee', 'guaranteed employment', 'official partner of OpenAI', 'official partner of Google']) {
  assert.ok(!allContent.toLowerCase().includes(prohibited), `unsupported claim: ${prohibited}`);
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
