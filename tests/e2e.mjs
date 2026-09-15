import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const baseURL = process.env.PWS_TEST_URL || 'http://127.0.0.1:9411';
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
const consoleErrors = [];
page.on('console', (message) => {
  if (message.type() === 'error') consoleErrors.push(message.text());
});

try {
  await page.goto(`${baseURL}/wp-admin/options-permalink.php`, { waitUntil: 'domcontentloaded' });
  await page.locator('input[name="selection"][value=""]').check();
  await page.getByRole('button', { name: /save changes/i }).click();
  await page.getByText(/permalink structure updated/i).waitFor();

  const inertHeaders = await page.request.get(`${baseURL}/`);
  assert.equal(inertHeaders.headers()['x-content-type-options'], undefined, 'activation must not add public headers before apply');
  await page.goto(`${baseURL}/wp-admin/tools.php?page=pws-release`, { waitUntil: 'domcontentloaded' });
  const blockedApply = page.getByRole('button', { name: /apply release/i });
  assert.ok(await blockedApply.isDisabled(), 'plain permalinks should block apply');
  await blockedApply.evaluate((button) => button.removeAttribute('disabled'));
  await blockedApply.click();
  await page.getByText(/Resolve the blocking preflight failures/i).waitFor();

  await page.goto(`${baseURL}/wp-admin/options-permalink.php`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.title(), /Permalink Settings/i);
  await page.locator('input[value="/%postname%/"]').check();
  await page.getByRole('button', { name: /save changes/i }).click();
  await page.getByText(/permalink structure updated/i).waitFor();

  await page.goto(`${baseURL}/wp-admin/post-new.php?post_type=page`, { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => window.wp?.data?.dispatch('core/editor'));
  await page.evaluate(async () => {
    const editor = window.wp.data.dispatch('core/editor');
    editor.editPost({ title: 'Legacy About', slug: 'about', content: '<p>Legacy about content retained for rollback.</p>', status: 'publish' });
    await editor.savePost();
  });
  await page.goto(`${baseURL}/about/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.locator('body').innerText(), /Legacy about content retained for rollback/);

  await page.goto(`${baseURL}/wp-admin/post.php?post=1&action=edit`, { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => window.wp?.data?.dispatch('core/editor') && window.wp?.data?.dispatch('core'));
  await page.evaluate(async () => {
    const core = window.wp.data.dispatch('core');
    const general = await core.saveEntityRecord('taxonomy', 'category', { name: 'General', slug: 'general' });
    const blog = await core.saveEntityRecord('taxonomy', 'category', { name: 'Blog', slug: 'blog' });
    const editor = window.wp.data.dispatch('core/editor');
    editor.editPost({ categories: [general.id, blog.id] });
    await editor.savePost();
  });

  await page.goto(`${baseURL}/wp-admin/tools.php?page=pws-release`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.getByRole('heading', { level: 1 }).textContent(), /Patriot Web Solutions site release/);
  assert.match(await page.locator('body').innerText(), /Existing pages need an explicit migration decision/);
  await page.locator('#pws_form_recipient').fill('support@patriotwebsolutions.org');
  await page.getByRole('button', { name: /save integration settings/i }).click();
  await page.getByText(/Integration settings saved/i).waitFor();
  const apply = page.getByRole('button', { name: /apply release/i });
  assert.ok(await apply.isEnabled(), 'apply release should pass blocking preflight');
  const conflictApproval = page.locator('input[name="pws_replace_conflicts"]');
  await conflictApproval.evaluate((input) => input.removeAttribute('required'));
  await apply.click();
  await page.getByText(/explicitly authorize their replacement/i).waitFor();
  const approvedApply = page.getByRole('button', { name: /apply release/i });
  const approvedConflict = page.locator('input[name="pws_replace_conflicts"]');
  await approvedConflict.check();
  await approvedApply.click();
  await page.getByText(/Release applied/i).waitFor();

  const expected = new Map([
    ['/', /Learn to use AI/], ['/learn/', /Practical skills/], ['/join/', /Tell us what you want to learn/],
    ['/our-work/', /Tools should show their evidence/], ['/solutions/', /Useful automation/],
    ['/impact/', /Show the work/], ['/about/', /Service experience/], ['/get-involved/', /Learn, contribute/],
    ['/donate/', /Help make patient/], ['/contact/', /Start with the right conversation/],
    ['/privacy/', /Privacy notice/], ['/terms/', /Terms of use/], ['/accessibility/', /Access is part/],
    ['/learner-code/', /Practice with patience/], ['/stories/', /What we are learning/]
  ]);
  for (const [route, heading] of expected) {
    const response = await page.goto(`${baseURL}${route}`, { waitUntil: 'domcontentloaded' });
    assert.equal(response.status(), 200, route);
    assert.match(await page.getByRole('heading', { level: 1 }).first().textContent(), heading, route);
    assert.equal(await page.locator('text=/Fatal error|Warning:/i').count(), 0, `PHP output: ${route}`);
  }
  await page.goto(`${baseURL}/stories/`, { waitUntil: 'domcontentloaded' });
  assert.doesNotMatch(await page.locator('body').innerText(), /Hello world!/i, 'unreviewed default post leaked into Field Notes');
  assert.match(await page.locator('body').innerText(), /Updates are being prepared/i);
  const sitemapIndex = await (await page.request.get(`${baseURL}/wp-sitemap.xml`)).text();
  assert.doesNotMatch(sitemapIndex, /wp-sitemap-users/i, 'user sitemap should be disabled');
  const categorySitemap = await (await page.request.get(`${baseURL}/wp-sitemap-taxonomies-category-1.xml`)).text();
  assert.doesNotMatch(categorySitemap, /\/category\/(general|blog)\//i, 'retired categories leaked into sitemap');
  const postSitemap = await (await page.request.get(`${baseURL}/wp-sitemap-posts-post-1.xml`)).text();
  assert.doesNotMatch(postSitemap, /hello-world/i, 'unreviewed post leaked into sitemap');

  await page.goto(`${baseURL}/`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'dist/home-desktop.png', fullPage: true });
  const headers = await page.request.get(`${baseURL}/`);
  assert.equal(headers.headers()['x-content-type-options'], 'nosniff');
  assert.equal(headers.headers()['referrer-policy'], 'strict-origin-when-cross-origin');

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth), true, 'mobile horizontal overflow');
  await page.screenshot({ path: 'dist/home-mobile.png', fullPage: true });
  const menu = page.getByRole('button', { name: /open menu/i });
  await menu.click();
  assert.equal(await menu.getAttribute('aria-expanded'), 'true');

  const redirect = await page.request.get(`${baseURL}/offerings/`, { maxRedirects: 0 });
  assert.equal(redirect.status(), 301);
  assert.equal(new URL(redirect.headers().location).pathname, '/solutions/');
  const gone = await page.request.get(`${baseURL}/post-1/`);
  assert.equal(gone.status(), 410);

  await page.goto(`${baseURL}/contact/`, { waitUntil: 'domcontentloaded' });
  assert.equal(await page.locator('form.pws-form').count(), 1);
  assert.equal(await page.locator('input[name="pws_nonce"]').count(), 1);
  assert.equal(await page.locator('input[name="website"]').count(), 1);

  await page.goto(`${baseURL}/learn/`, { waitUntil: 'domcontentloaded' });
  const editPageHref = await page.locator('#wp-admin-bar-edit a').getAttribute('href');
  assert.ok(editPageHref, 'logged-in page should expose its editor link');
  await page.goto(editPageHref, { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => window.wp?.data?.dispatch('core/editor'));
  await page.evaluate(async () => {
    const editor = window.wp.data.dispatch('core/editor');
    editor.editPost({ title: 'Learner-edited title' });
    await editor.savePost();
  });

  await page.goto(`${baseURL}/wp-admin/tools.php?page=pws-release`, { waitUntil: 'domcontentloaded' });
  await page.getByRole('button', { name: /roll back release/i }).click();
  await page.getByText(/Scoped rollback completed/i).waitFor();
  await page.goto(`${baseURL}/about/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.locator('body').innerText(), /Legacy about content retained for rollback/);
  await page.goto(`${baseURL}/learn/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.locator('body').innerText(), /Learner-edited title/, 'title-only modification must be preserved by rollback');
  await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  assert.doesNotMatch(await page.locator('body').innerText(), /Learn to use AI with skill/);

  await page.goto(`${baseURL}/wp-admin/tools.php?page=pws-release`, { waitUntil: 'domcontentloaded' });
  const reapply = page.getByRole('button', { name: /apply release/i });
  assert.ok(await reapply.isEnabled(), 'release should be re-applicable after scoped rollback');
  await page.locator('input[name="pws_replace_conflicts"]').check();
  await reapply.click();
  await page.getByText(/Release applied/i).waitFor();
  await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.getByRole('heading', { level: 1 }).textContent(), /Learn to use AI/);

  fs.writeFileSync('dist/playground-e2e-receipt.json', JSON.stringify({
    status: 'PASS',
    baseURL,
    wordpress: '6.5',
    php: '8.1',
    routes: expected.size,
    redirect: 'PASS',
    gone: 'PASS',
    form_controls: 'PASS',
    mobile_overflow: 'PASS',
    rollback_and_reapply: 'PASS',
    existing_page_revision_and_restore: 'PASS',
    title_only_edit_preserved: 'PASS',
    error_paths: 'blocking preflight and unapproved page conflict PASS',
    activation_inert: 'PASS',
    legacy_sitemap_isolation: 'PASS',
    console_errors: consoleErrors,
    boundary: 'Local WordPress Playground without GiveWP, WooCommerce, live mail, payments, Hostinger cache, or private data.'
  }, null, 2) + '\n');
  console.log(JSON.stringify({ status: 'PASS', routes: expected.size, consoleErrors: consoleErrors.length }));
} finally {
  await browser.close();
}
