# Round 5 Implementation Report — Patriot Web Solutions Release Candidate

- **Author:** Claude (Fable 2.0 working mode)
- **Date:** 2026-09-15 (America/Chicago) / 2026-09-16 UTC
- **Scope:** Implementation of the complete Round 4→5 consolidated fix set, full completion gate, and this report.
- **Status:** Package and all receipts are green. **This report does not call the result approved.** Codex independently reviews the final pixels and re-runs the release gate.

---

## 1. TL;DR

All eight accepted Round 4 fixes, the H1/metadata set, the Codex evidence corrections, and the full-route visual/accessibility gate are implemented and verified. The final artifact is `dist/patriot-web-solutions-release-1.0.0.zip` (sha256 `eae6f987…7117`), built 2026-09-16T00:49:05Z, with a fully green end-to-end receipt generated at 00:50:51Z from that exact zip: 18 routes, 36 visitor-only screenshots at 1440px and 390px, **0 axe violations** across WCAG 2.0/2.1 A+AA and 2.2 AA on all 18 routes, **0 documented exceptions**, **empty console-error and HTTP-error lists**, and rollback/reapply/edit-preservation all PASS. Every receipt quoted below was regenerated or re-read from disk during this final pass, not quoted from memory.

Round 5 also caught and fixed two defects of its own making (a mobile table-caption collapse introduced by the R5 linearization, and a UTC build-date stamp that contradicted authored dates) — both are in the corrections ledger in §5, with new automated canaries guarding the first.

---

## 2. Final receipts (all regenerated this pass)

Evidence class for everything in this section: **local artifact on disk, produced by a command I ran, with timestamp.** Boundary (from the e2e receipt itself): *"Local WordPress Playground without GiveWP, WooCommerce, live mail, payments, Hostinger cache, or private data."*

### 2.1 Content validator — `npm test` (re-run 2026-09-16 ~00:56Z)

```json
{"status":"PASS","pages":18,"redirects":12,"gone":11,"preserved":13,"php_files":14}
```

The validator now also scans visitor-facing PHP strings (deferral/banned-phrase truth rules) and enforces the external URL allowlist on the visitor corpus — both Round 5 additions.

### 2.2 Package verifier — `npm run verify:package` (re-run 2026-09-16 ~00:56Z)

```json
{
    "status":  "PASS",
    "archive_entries":  52,
    "verified_payload_files":  51,
    "top_level_directory":  "patriot-web-solutions/",
    "path_safety":  "PASS",
    "manifest_hashes":  "PASS"
}
```

### 2.3 Build receipt — `dist/release-receipt.json` (built 2026-09-16T00:49:05Z; zip re-hashed independently this pass with `Get-FileHash`, matches exactly)

```json
{
    "generated_at_utc":  "2026-09-16T00:49:05.7490570Z",
    "artifact":  "dist/patriot-web-solutions-release-1.0.0.zip",
    "bytes":  2114923,
    "sha256":  "eae6f9870bcbfe434dfa200eb2fe5723c5c410c2faa4f65273697e7893ce7117",
    "file_count_excluding_manifest":  51
}
```

### 2.4 External links — `npm run verify:links` (re-run 2026-09-16T00:55:39Z)

```json
{"status":"PASS","urls":3,"results":[
 {"url":"https://github.com/kb4beast/hive-mind-os","result":"PASS","http_status":200},
 {"url":"https://projects.propublica.org/nonprofits/organizations/991238039","result":"PASS","http_status":200},
 {"url":"https://www.guidestar.org/profile/99-1238039","result":"PASS","http_status":200}]}
```

These three URLs are the entire external allowlist; the validator fails the build if any other external URL appears in visitor-facing content.

### 2.5 Full browser/accessibility/rollback gate — `npm run test:e2e` → `dist/playground-e2e-receipt.json` (written 2026-09-16T00:50:51Z, after the final build; the runner copies the dist zip at start, so this receipt corresponds to the shipped sha256)

```json
{
  "status": "PASS",
  "baseURL": "http://127.0.0.1:9411",
  "wordpress": "6.5",
  "php": "8.1",
  "routes": 18,
  "screenshots": 36,
  "screenshot_dir": "dist/screenshots",
  "screenshot_context": "logged-out visitor browser context; admin bar asserted absent on every route at both viewports",
  "axe": {
    "engine": "@axe-core/playwright",
    "tags": ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"],
    "routes_scanned": 18,
    "violations": 0,
    "documented_exceptions": []
  },
  "impact_ledger_mobile_canary": "PASS — first entry cell 358px wide at 390px (minimum 200px)",
  "table_caption_mobile_canary": "PASS — ledger caption 358px, syllabus caption 356px wide at 390px (minimum 200px)",
  "desktop_overflow": "PASS (18 routes, 1440px)",
  "mobile_overflow": "PASS (18 routes, 390px)",
  "redirect": "PASS",
  "gone": "PASS",
  "form_controls": "PASS",
  "employer_routing_option": "PASS",
  "volunteer_routing_option": "PASS",
  "donation_fallback_truthful": "PASS",
  "evidence_chips_and_repo_link": "PASS",
  "json_ld_gated": "PASS",
  "draft_notes_invisible": "PASS",
  "rollback_and_reapply": "PASS",
  "existing_page_revision_and_restore": "PASS",
  "title_only_edit_preserved": "PASS",
  "nested_child_edit_preserved": "PASS",
  "error_paths": "blocking preflight and unapproved page conflict PASS",
  "activation_inert": "PASS",
  "legacy_sitemap_isolation": "PASS",
  "console_errors": [],
  "visitor_http_errors": [],
  "boundary": "Local WordPress Playground without GiveWP, WooCommerce, live mail, payments, Hostinger cache, or private data."
}
```

Key gate properties demanded by the Round 5 directive, each visible above: 36 visitor-only screenshots (admin bar asserted absent per route per viewport), axe on all 18 routes with **zero violations and zero suppressed exceptions**, an **empty** console-error list (no allowlisting — see ledger item 1), an empty visitor HTTP-error list, and the full rollback → reapply → edit-preservation suite green.

### 2.6 Screenshot inspection (human-readable evidence, not just automated)

All 36 screenshots in `dist/screenshots/` were visually inspected in three passes: 18 directly in the main session (home, impact, learn, donate, our-work, hive-mind-os, get-involved, about, contact × both viewports), 18 by a fresh-context verification agent (solutions, accessibility, privacy, terms, ai-developer-workbench, coupon-hive, join, stories, learner-code × both — zero blocking defects), and 7 regenerated critical captures by a second fresh-context agent after the caption fix (learn/impact/get-involved/home mobile captions confirmed full-width; all our-work dates identical at 2026-09-15; proofstrip kickers confirmed readable gold; verdict: *"Both fixes confirmed; no regressions observed"*). Two agent-flagged nits were verified against source and accepted as intentional: the tricolor header accent (deliberate flag-stripe gradient in `site.css`) and the footer email mid-wrap (intended `overflow-wrap` on long mailto links at 390px).

---

## 3. What changed in Round 5

### Fix 1 — Overflow strategy and mobile table linearization
- `payload/theme/patriot-web-solutions/assets/css/site.css`: replaced the global body overflow-wrap rule with a targeted selector list; added ≤620px linearization for `.pws-ledger`/`.pws-syllabus` (label-stacked cells via `td:nth-child(3)`-scoped generated labels, headers preserved for assistive tech); added the caption fix (ledger item 4); scrollable containers (`pre`, `.pws-tablewrap`, `.pws-exhibit`, `.pws-docframe`) get `overflow-x: auto; max-width: 100%`.
- `tests/e2e.mjs`: impact-ledger mobile canary (first entry cell ≥200px at 390px) plus two new caption canaries (§5, item 4).
- **Deliberate deviation from the R4 spec:** labels target `td:nth-child(3)` rather than R4's blanket `td:last-child`, so two-column tables don't get a mislabeled second cell. Recorded in the ledger (item 3).

### Fix 2 — CTA labels
- `header.php`: header CTA reads `Join the list` (verified in source this pass, line 20).
- `footer.php`: footer link reads `Join the interest list` (verified in source this pass, line 5).

### Fix 3 — Truthful donate copy in both states
- `payload/content/donate.html` and the GiveWP-absent fallback in `includes/class-pws-public.php` (verified this pass, line 38): *"No online donations today — This page has no live donation form. To give or ask a question now, email support@patriotwebsolutions.org."* No promise of processing that doesn't exist.
- `tests/validate-release.mjs`: truth scans extended to visitor-facing PHP strings so regressions in PHP-emitted copy fail the build, not just HTML payload copy.

### Fixes 4–7 — Copy corrections
- `includes/class-pws-forms.php`: contact topic `Volunteer or mentor` added (verified in source, line 64); volunteer CTA on `get-involved.html`.
- `payload/content/solutions.html`: employer claim now "finish with artifacts, not just certificates."
- `payload/content/home.html`: one hero-image disclosure; shortened org proof line ("record checked 2026-09-15").

### Fix 8 + favicon — E2E hardening
- `tests/e2e.mjs` rebuilt around a logged-out visitor browser context: console errors captured **with URLs** and asserted empty; unexplained 404s fail the run; all 36 screenshots taken without the admin bar (asserted absent per route/viewport); axe (`@axe-core/playwright`, added to `package.json`/`package-lock.json` as a dev dependency — not shipped in the zip) on all 18 routes at 1440px.
- `functions.php`: theme-level favicon `<link>` emitted **only when no site icon is configured**; WordPress's `site_icon` option is never set or overwritten, per the directive.

### H1 / metadata set
- `payload/content/about.html` H1: `The record behind Patriot Web Solutions.`; `contact.html` H1: `Learner, donor, employer, or client: start here.` (no "reply from a person" promise); `payload/content.json` meta rewrites (About, Coupon Hive); `functions.php` meta description map; `tests/e2e.mjs` H1 assertions updated to match.

### Codex evidence corrections
- `payload/content/work-hive-mind-os.html`: Fort Cavazos → Fort Hood, **no new external link added**.
- `payload/content/privacy.html`: states only that this release package adds no optional analytics/advertising measurement, and that the notice must be updated before enabling any; neutral sensitive-information rule.
- `payload/content/accessibility.html`: claims only release-backed checks (now literally true: axe WCAG A/AA on all 18 routes with zero violations).

### Accessibility fixes forced by the axe gate (found by the gate, fixed in release-controlled markup/CSS)
- **Color contrast (serious):** proofstrip kickers were red `#a63632` on navy `#0b2233` (≈2.48:1). Fixed to the existing gold accent `#e9c685` (≈10:1) via `.pws-proofstrip .pws-kicker` in `site.css`. The full palette was then hand-audited (chips 5.6–6.6:1, muted text ≥5.5:1, lede 8.75:1) — the proofstrip was the only failing pair.
- **Scrollable region focusable (serious):** the two `overflow-x:auto` exhibit `<pre>` blocks (`home.html`, `work-hive-mind-os.html`) needed keyboard focus. Added `tabindex="0" role="region" aria-label="…"` — and, because `wp_kses_post()` strips `tabindex` at install time, a narrow `wp_kses_allowed_html` filter in `patriot-web-solutions.php` allowing `tabindex` on `pre` in the `post` context only. **Installer sanitization was not weakened**; `class-pws-installer.php` still runs all content through `wp_kses_post()` and was not modified in Round 5.

### Build script
- `scripts/Build-Release.ps1`: evidence-check dates in `projects.json` are now stamped with the builder's **local** date instead of UTC (ledger item 5).

### Housekeeping
- Deleted 12 stale untracked Round-3-era PNGs from the `dist/` root; `dist/` now carries only the zip, the three receipts, and `dist/screenshots/` (36 files, count verified this pass).

### Complete uncommitted branch state
Rounds 1–5 are all uncommitted on `codex/patriot-ai-redesign` (base commit `3cdba5b`). Full `git status --short` regenerated this pass: 39 modified files (plugin bootstrap, 3 includes, 15 content pages + `content.json`, 5 theme files, 4 shipped docs, manifest, zip, receipts, both test files, build script, `package.json`/`package-lock.json`, blueprint) and new untracked files (`facts.json`, `projects.json`, `class-pws-facts.php`, 3 work pages, notes payload, brand images, `verify-links.mjs`, `New-BrandAssets.ps1`, `dist/screenshots/`, `docs/claude-collaboration/`). Per-file Round 5 attribution above lists only what Round 5 itself changed; the remaining modifications are earlier-round work already reviewed in Rounds 1–4.

### Verified not stale (checked this pass)
The four shipped docs (`ROUTE-MAP.md`, `PRELAUNCH-CHECKLIST.md`, `DATA-FLOWS.md`, `LICENSES.md`) were grepped for outdated "12 screenshots" / receipt-shape / axe references: none found (all "receipt" hits are donation-receipt copy). `LICENSES.md` correctly omits `@axe-core/playwright` — it is a dev-only dependency and not among the 51 shipped files.

---

## 4. How to re-verify (exact commands)

```
npm test                 # content/truth/allowlist validator
npm run verify:links     # 3 allowlisted external URLs
npm run build            # rebuild zip + release-receipt.json (re-stamps check dates to local today)
npm run verify:package   # manifest hashes + zip path safety
npm run test:e2e         # full Playground gate; REQUIRES a build first (copies dist zip)
```

Note for the re-run: `npm run build` re-stamps `projects.json` check dates to the build day, so the zip sha256 will legitimately differ from `eae6f987…7117` on any other day. Judge the rebuilt artifact by its own regenerated receipts, not by hash equality with mine.

---

## 5. Corrections ledger (what this round got wrong, then fixed — kept visible per Fable 2.0)

1. **R4 misclassification, corrected:** Round 4 called the lone console 404 a "harmless local favicon artifact." Wrong. It was the e2e's own post-rollback expected-404 probe polluting the console listener. Round 5 replaced that navigation with a request-API probe, after which the console-error list is **empty with zero allowlisting** — exactly what the directive demanded ("diagnose and fix the actual asset request rather than allowlisting it"). The favicon itself was never the cause; Playwright's headless shell doesn't fetch favicons.
2. **Two-cycle kses discovery:** first fix for `scrollable-region-focusable` added `tabindex` to the HTML payload — and still failed, because `wp_kses_post()` strips `tabindex` at install (while `role`/`aria-label` survive as kses globals). Root cause traced to `class-pws-installer.php` line 153; fixed with the narrow filter rather than by weakening sanitization. Cost: one extra full e2e cycle.
3. **Spec deviations from accepted R4 text (deliberate, flagged for Codex):** (a) linearization labels use `td:nth-child(3)` instead of `td:last-child` so two-column tables aren't mislabeled; (b) the donate-page bullets were consolidated rather than kept verbatim while making both states truthful. Both deviations preserve the accepted fixes' intent; Codex should judge whether the letter matters.
4. **Self-inflicted, self-caught — caption sliver:** the R5 mobile linearization itself made table captions collapse into one-character-wide vertical slivers on learn, impact, get-involved, and home at 390px (a `table-caption` inside a `display:block` table gets an anonymous shrink-to-fit table box). Caught by R5's own screenshot inspection step, **not** by axe or the overflow assertions. Fixed with `caption { display: block; width: 100%; }` and locked in with two new e2e canaries (measured 358px/356px in the final receipt). Lesson recorded: automated a11y scans do not catch layout collapse; the visual pass is load-bearing.
5. **Self-inflicted, self-caught — UTC date stamp:** the build stamped `projects.json` check dates in UTC, so an evening US-Central build wrote 2026-09-16 next to authored 2026-09-15 dates — same-page contradiction. The old code comment claimed UTC prevented "future" claims; it caused one. Fixed to local date; all visible dates now agree (confirmed in the regenerated our-work screenshots).
6. **Process failure, worked around:** after ~18 screenshot reads, the session's image channel saturated and every further image read failed regardless of file size. Remaining inspection was delegated to two fresh-context verification agents rather than skipped — noted here so the inspection evidence chain in §2.6 is honest about who looked at what.

---

## 6. Remaining owner-gated facts (documented, not done — nothing here was fabricated to fill the gap)

| Gate | Current shipped state |
| --- | --- |
| `org_status_confirmed` (facts.json) | Off. JSON-LD and status-dependent claims stay gated; site shows "record checked 2026-09-15" with the two registry links only. |
| GiveWP form + processor + receipt language | Not configured. Donate page and PHP fallback truthfully say no online donations today; installer preflight marks the missing form as a **warning, not a blocker**. `PRELAUNCH-CHECKLIST.md` carries the full sandbox QA list. |
| Street address / founding date | Absent by design until the owner supplies them. |
| Three field-note drafts | Installed as drafts, asserted invisible to visitors (`draft_notes_invisible: PASS`); owner reviews and publishes. |
| Site icon | Owner sets it in the Customizer; the theme only emits a fallback `<link>` when none exists and never writes `site_icon`. |
| Production deploy (Hostinger, DNS, cache), live payments, external publication | Untouched, per the standing constraint. The Hive Mind OS source repo was not touched; it is referenced only via the allowlisted GitHub URL (HTTP 200). |

---

## 7. Candid self-critique

- **The 621–980px band is a coverage gap.** `.pws-tablewrap` wrappers have no `tabindex`; they provably don't overflow at 1440px (axe-clean) and the tables linearize at ≤620px, but no assertion covers 621–980px. If a table overflows there, it would be a scrollable region without keyboard focus. Cheap to close with one mid-viewport e2e pass; I did not do it in this round.
- **Round 5 shipped a regression to its own inspection step.** The caption sliver (ledger 4) means my first "linearization done" state was confidently wrong for four pages; only the mandated visual pass caught it. The two new canaries are the structural fix, but the honest reading is that the gate design saved me, not my implementation care.
- **One-third of screenshot inspection was delegated.** Fresh-context agents are independent eyes (arguably a feature under Fable's panel principle), but I could not personally re-view their 18 pages after the image-channel failure. Their two nit reports were source-verified; their "no blocking defects" claims I could not independently re-confirm pixel-by-pixel.
- **Stories page is a deliberate empty state** ("no published stories yet") — truthful but thin. If Codex judges it weak for launch, the honest options are owner-supplied content or unlinking it; fabricating a story is not on the table.
- **The e2e boundary is real:** WordPress 6.5 / PHP 8.1 in Playground, no GiveWP, no live mail, no Hostinger cache layer. Production behavior at those seams is asserted nowhere in these receipts, only in the prelaunch checklist as owner QA.

---

*Round 5 complete: package and receipts green in their final regenerated state. Not calling it approved — Codex independently reviews the final pixels and runs the release gate again.*
