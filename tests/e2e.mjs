import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const baseURL = process.env.PWS_TEST_URL || 'http://127.0.0.1:9411';
const browser = await chromium.launch({ headless: true });

const consoleErrors = [];
const trackConsole = (target, context) => {
  target.on('console', (message) => {
    if (message.type() === 'error') {
      consoleErrors.push({ context, page: target.url(), text: message.text(), source: message.location().url || '' });
    }
  });
};

const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
trackConsole(page, 'admin');

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

  // Visitor context: fresh cookies (logged out). The pre-set cookie is Playground's own auto-login
  // opt-out marker; every route below also asserts the admin bar is genuinely absent.
  const visitorContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  await visitorContext.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: baseURL }]);
  const visitor = await visitorContext.newPage();
  trackConsole(visitor, 'visitor');
  const httpErrors = [];
  visitor.on('response', (response) => {
    if (response.status() >= 400) httpErrors.push({ url: response.url(), status: response.status() });
  });

  const expected = new Map([
    ['/', /Practical AI, taught live/],
    ['/learn/', /One live hour/],
    ['/join/', /Tell us what you want to learn/],
    ['/our-work/', /Three projects, each with its evidence state/],
    ['/our-work/hive-mind-os/', /Hive Mind OS/],
    ['/our-work/ai-developer-workbench/', /AI Developer Workbench/],
    ['/our-work/coupon-hive/', /Coupon Hive/],
    ['/solutions/', /Useful automation/],
    ['/impact/', /The accountability ledger/],
    ['/about/', /The record behind Patriot Web Solutions/],
    ['/get-involved/', /Hire, mentor, host, or fund/],
    ['/donate/', /Support live, patient AI instruction/],
    ['/contact/', /Learner, donor, employer, or client/],
    ['/privacy/', /Privacy notice/],
    ['/terms/', /Terms of use/],
    ['/accessibility/', /Access is part/],
    ['/learner-code/', /Practice with patience/],
    ['/stories/', /Dated, reviewed working notes/]
  ]);
  const routeName = (route) => (route === '/' ? 'home' : route.replace(/^\/+|\/+$/g, '').split('/').join('-'));
  fs.mkdirSync('dist/screenshots', { recursive: true });

  // Documented, reviewed axe exceptions would be listed here ({ route, rule, target, reason }); none are expected.
  const axeExceptions = [];
  let axeChecks = 0;
  const assertVisitorChrome = async (route) => {
    assert.equal(await visitor.evaluate(() => document.body.classList.contains('admin-bar')), false, `admin-bar body class: ${route}`);
    assert.equal(await visitor.locator('#wpadminbar').count(), 0, `admin bar node: ${route}`);
    assert.ok(await visitor.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth), `horizontal overflow: ${route}`);
  };
  const assertAxe = async (route, viewport) => {
    const axe = await new AxeBuilder({ page: visitor }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
    const unexplained = axe.violations.filter((violation) => !axeExceptions.some((entry) => entry.route === route && entry.rule === violation.id));
    assert.equal(unexplained.length, 0, `axe violations on ${route} @${viewport}: ` + JSON.stringify(unexplained.map((violation) => ({
      id: violation.id,
      impact: violation.impact,
      nodes: violation.nodes.map((node) => node.target.join(' ')).slice(0, 5)
    }))));
    axeChecks += 1;
  };

  for (const [route, heading] of expected) {
    const response = await visitor.goto(`${baseURL}${route}`, { waitUntil: 'networkidle' });
    assert.equal(response.status(), 200, route);
    assert.match(await visitor.getByRole('heading', { level: 1 }).first().textContent(), heading, route);
    assert.equal(await visitor.locator('text=/Fatal error|Warning:/i').count(), 0, `PHP output: ${route}`);
    await assertVisitorChrome(route);
    await visitor.screenshot({ path: `dist/screenshots/${routeName(route)}-desktop.png`, fullPage: true });
    await assertAxe(route, '1440px');
  }

  // The table layout changes at 620px. Exercise the band immediately above that breakpoint
  // so a keyboard-inaccessible horizontal scroller cannot hide between desktop and phone checks.
  await visitor.setViewportSize({ width: 768, height: 900 });
  for (const [route] of expected) {
    await visitor.goto(`${baseURL}${route}`, { waitUntil: 'networkidle' });
    await assertVisitorChrome(`${route} @768px`);
    await assertAxe(route, '768px');
  }

  await visitor.setViewportSize({ width: 1440, height: 1000 });

  await visitor.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  assert.ok((await visitor.title()).includes('Patriot Web Solutions'), 'site identity missing from document title');
  assert.equal(await visitor.locator('link[rel="icon"][href*="logo.png"]').count(), 1, 'theme fallback favicon link missing');
  const homeBody = await visitor.locator('body').innerText();
  assert.match(homeBody, /Join the list/, 'header CTA should invite the interest list, not a cohort');
  assert.match(homeBody, /Join the interest list/, 'footer CTA should name the interest list');
  assert.equal(await visitor.locator('.pws-hero__photo').count(), 0, 'no class photo should appear without a consented owner fact');
  assert.doesNotMatch(homeBody, /Illustration, not a class photo/, 'retired illustration must stay removed');
  const jsonLdRaw = await visitor.locator('script[type="application/ld+json"]').first().textContent();
  const jsonLd = JSON.parse(jsonLdRaw);
  assert.ok(JSON.stringify(jsonLd).includes('NonprofitOrganization'), 'organization schema missing');
  assert.ok(!JSON.stringify(jsonLd).includes('taxID'), 'taxID must stay gated until org_status_confirmed');

  await visitor.goto(`${baseURL}/our-work/`, { waitUntil: 'domcontentloaded' });
  assert.equal(await visitor.locator('.pws-record .tag').count(), 3, 'catalog must label all three evidence states');
  assert.match(await visitor.locator('.pws-record__checked').first().innerText(), /\d{4}-\d{2}-\d{2}/, 'records must carry a checked date');
  await visitor.goto(`${baseURL}/our-work/hive-mind-os/`, { waitUntil: 'domcontentloaded' });
  assert.ok(await visitor.locator('a[href*="github.com/kb4beast/hive-mind-os"]').count() >= 1, 'public project must link its repository');

  await visitor.goto(`${baseURL}/donate/`, { waitUntil: 'domcontentloaded' });
  assert.match(await visitor.locator('body').innerText(), /No online donations today/, 'donation fallback must state plainly that no live form exists');

  await visitor.goto(`${baseURL}/stories/`, { waitUntil: 'domcontentloaded' });
  const storiesBody = await visitor.locator('body').innerText();
  assert.doesNotMatch(storiesBody, /Hello world!/i, 'unreviewed default post leaked into Field Notes');
  assert.match(storiesBody, /Three notes are drafted and in owner review/i);
  assert.equal(await visitor.locator('a[href*="why-we-rebuilt"]').count(), 0, 'draft seed note must stay invisible until published');
  const sitemapIndex = await (await visitor.request.get(`${baseURL}/wp-sitemap.xml`)).text();
  assert.doesNotMatch(sitemapIndex, /wp-sitemap-users/i, 'user sitemap should be disabled');
  const categorySitemap = await (await visitor.request.get(`${baseURL}/wp-sitemap-taxonomies-category-1.xml`)).text();
  assert.doesNotMatch(categorySitemap, /\/category\/(general|blog)\//i, 'retired categories leaked into sitemap');
  const postSitemap = await (await visitor.request.get(`${baseURL}/wp-sitemap-posts-post-1.xml`)).text();
  assert.doesNotMatch(postSitemap, /hello-world|why-we-rebuilt/i, 'unreviewed or draft post leaked into sitemap');

  const headers = await visitor.request.get(`${baseURL}/`);
  assert.equal(headers.headers()['x-content-type-options'], 'nosniff');
  assert.equal(headers.headers()['referrer-policy'], 'strict-origin-when-cross-origin');

  await visitor.setViewportSize({ width: 390, height: 844 });
  let impactCanary = 0;
  let ledgerCaptionCanary = 0;
  let syllabusCaptionCanary = 0;
  for (const [route] of expected) {
    await visitor.goto(`${baseURL}${route}`, { waitUntil: 'networkidle' });
    await assertVisitorChrome(`${route} @390px`);
    if (route === '/impact/') {
      impactCanary = await visitor.locator('.table tbody th').first().evaluate((cell) => cell.getBoundingClientRect().width);
      assert.ok(impactCanary >= 200, `impact ledger rows must stay readable at 390px, entry cell was ${impactCanary}px`);
      ledgerCaptionCanary = await visitor.locator('.table caption').first().evaluate((cell) => cell.getBoundingClientRect().width);
      assert.ok(ledgerCaptionCanary >= 200, `ledger caption must not collapse to a sliver at 390px, was ${ledgerCaptionCanary}px`);
    }
    if (route === '/learn/') {
      syllabusCaptionCanary = await visitor.locator('.table caption').first().evaluate((cell) => cell.getBoundingClientRect().width);
      assert.ok(syllabusCaptionCanary >= 200, `syllabus caption must not collapse to a sliver at 390px, was ${syllabusCaptionCanary}px`);
    }
    await visitor.screenshot({ path: `dist/screenshots/${routeName(route)}-mobile.png`, fullPage: true });
  }
  await visitor.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  const menu = visitor.getByRole('button', { name: /^menu$/i });
  await menu.click();
  assert.equal(await menu.getAttribute('aria-expanded'), 'true');
  await visitor.setViewportSize({ width: 320, height: 740 });
  for (const route of ['/about/', '/contact/', '/donate/']) {
    await visitor.goto(`${baseURL}${route}`, { waitUntil: 'networkidle' });
    await assertVisitorChrome(`${route} @320px`);
    const emailLinks = visitor.locator('a[href="mailto:support@patriotwebsolutions.org"]');
    assert.ok(await emailLinks.count() > 0, `contact address missing on ${route}`);
    for (const email of await emailLinks.all()) {
      const visible = await email.evaluate((link) => {
        const bounds = link.getBoundingClientRect();
        return link.textContent.trim() === 'support@patriotwebsolutions.org'
          && bounds.left >= -1 && bounds.right <= window.innerWidth + 1
          && getComputedStyle(link).whiteSpace !== 'nowrap';
      });
      assert.ok(visible, `email clipped or forced onto an unbreakable line: ${route}`);
    }
  }
  await visitor.setViewportSize({ width: 1440, height: 1000 });

  const redirect = await visitor.request.get(`${baseURL}/offerings/`, { maxRedirects: 0 });
  assert.equal(redirect.status(), 301);
  assert.equal(new URL(redirect.headers().location).pathname, '/solutions/');
  const gone = await visitor.request.get(`${baseURL}/post-1/`);
  assert.equal(gone.status(), 410);

  await visitor.goto(`${baseURL}/contact/`, { waitUntil: 'domcontentloaded' });
  assert.equal(await visitor.locator('form.pws-form').count(), 1);
  assert.equal(await visitor.locator('input[name="pws_nonce"]').count(), 1);
  assert.equal(await visitor.locator('input[name="website"]').count(), 1);
  const topicOptions = await visitor.locator('form.pws-form select').innerText();
  assert.match(topicOptions, /Employer \/ workforce partnership/, 'employer routing option missing');
  assert.match(topicOptions, /Volunteer or mentor/, 'volunteer routing option missing');

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

  await page.goto(`${baseURL}/our-work/hive-mind-os/`, { waitUntil: 'domcontentloaded' });
  const childEditHref = await page.locator('#wp-admin-bar-edit a').getAttribute('href');
  assert.ok(childEditHref, 'nested child should expose its editor link');
  const childId = new URL(childEditHref).searchParams.get('post');
  assert.ok(childId, 'child post id');
  await page.goto(childEditHref, { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => window.wp?.data?.dispatch('core/editor'));
  await page.evaluate(async () => {
    const editor = window.wp.data.dispatch('core/editor');
    editor.editPost({ title: 'Child-edited record' });
    await editor.savePost();
  });

  await page.goto(`${baseURL}/wp-admin/tools.php?page=pws-release`, { waitUntil: 'domcontentloaded' });
  await page.getByRole('button', { name: /roll back release/i }).click();
  await page.getByText(/Scoped rollback completed/i).waitFor();
  await page.goto(`${baseURL}/about/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.locator('body').innerText(), /Legacy about content retained for rollback/);
  await page.goto(`${baseURL}/learn/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.locator('body').innerText(), /Learner-edited title/, 'title-only modification must be preserved by rollback');
  const childAfterRollback = await page.goto(`${baseURL}/?page_id=${childId}`, { waitUntil: 'domcontentloaded' });
  assert.equal(childAfterRollback.status(), 200, 'modified nested child must survive rollback');
  assert.match(await page.locator('body').innerText(), /Child-edited record/, 'modified nested child content must be preserved');
  // Expected-removal probe uses the request API: navigating the page into a 404 would log a console
  // error for the document itself, and the console-error gate below must stay meaningful.
  const workAfterRollback = await page.request.get(`${baseURL}/our-work/`);
  if (workAfterRollback.status() !== 404) {
    await page.goto(`${baseURL}/our-work/`, { waitUntil: 'domcontentloaded' });
    assert.ok(!(await page.locator('body').innerText()).includes('Three projects, each with its evidence state'), 'unmodified release parent must be removed by rollback');
  }
  await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  assert.doesNotMatch(await page.locator('body').innerText(), /Practical AI, taught live/);

  await page.goto(`${baseURL}/wp-admin/tools.php?page=pws-release`, { waitUntil: 'domcontentloaded' });
  const reapply = page.getByRole('button', { name: /apply release/i });
  assert.ok(await reapply.isEnabled(), 'release should be re-applicable after scoped rollback');
  await page.locator('input[name="pws_replace_conflicts"]').check();
  await reapply.click();
  await page.getByText(/Release applied/i).waitFor();
  await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded' });
  assert.match(await page.getByRole('heading', { level: 1 }).textContent(), /Practical AI, taught live/);

  assert.equal(consoleErrors.length, 0, 'unexplained console errors: ' + JSON.stringify(consoleErrors, null, 2));
  assert.equal(httpErrors.length, 0, 'visitor HTTP >= 400 responses: ' + JSON.stringify(httpErrors, null, 2));

  fs.writeFileSync('dist/playground-e2e-receipt.json', JSON.stringify({
    status: 'PASS',
    baseURL,
    wordpress: '6.5',
    php: '8.1',
    routes: expected.size,
    screenshots: expected.size * 2,
    screenshot_dir: 'dist/screenshots',
    screenshot_context: 'logged-out visitor browser context; admin bar asserted absent on every route at both viewports',
    axe: {
      engine: '@axe-core/playwright',
      tags: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'],
      routes_scanned: expected.size,
      viewports_scanned: ['1440px', '768px'],
      checks: axeChecks,
      violations: 0,
      documented_exceptions: axeExceptions
    },
    impact_ledger_mobile_canary: `PASS — first entry cell ${Math.round(impactCanary)}px wide at 390px (minimum 200px)`,
    table_caption_mobile_canary: `PASS — ledger caption ${Math.round(ledgerCaptionCanary)}px, syllabus caption ${Math.round(syllabusCaptionCanary)}px wide at 390px (minimum 200px)`,
    desktop_overflow: 'PASS (18 routes, 1440px)',
    tablet_overflow: 'PASS (18 routes, 768px)',
    mobile_overflow: 'PASS (18 routes, 390px)',
    email_320px_canary: 'PASS (about, contact, donate; full address and breakable links)',
    redirect: 'PASS',
    gone: 'PASS',
    form_controls: 'PASS',
    employer_routing_option: 'PASS',
    volunteer_routing_option: 'PASS',
    donation_fallback_truthful: 'PASS',
    evidence_chips_and_repo_link: 'PASS',
    json_ld_gated: 'PASS',
    draft_notes_invisible: 'PASS',
    rollback_and_reapply: 'PASS',
    existing_page_revision_and_restore: 'PASS',
    title_only_edit_preserved: 'PASS',
    nested_child_edit_preserved: 'PASS',
    error_paths: 'blocking preflight and unapproved page conflict PASS',
    activation_inert: 'PASS',
    legacy_sitemap_isolation: 'PASS',
    console_errors: consoleErrors,
    visitor_http_errors: httpErrors,
    boundary: 'Local WordPress Playground without GiveWP, WooCommerce, live mail, payments, Hostinger cache, or private data.'
  }, null, 2) + '\n');
  console.log(JSON.stringify({ status: 'PASS', routes: expected.size, screenshots: expected.size * 2, axeChecks, consoleErrors: consoleErrors.length }));
} finally {
  await browser.close();
}
