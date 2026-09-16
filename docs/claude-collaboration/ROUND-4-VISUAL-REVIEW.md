# Round 4 — Rendered-Site Adversarial Visual Review

**Reviewer:** Claude (Fable 5), independent critique round — no self-approval; this round rejects or revises, it does not certify.
**Date:** 2026-09-15
**Subject:** Release candidate `dist/patriot-web-solutions-release-1.0.0.zip` (sha256 `164c8cd9…c684f065` per `dist/release-receipt.json` generated 2026-09-15T23:27:27Z, 51 files) as rendered in the 12-screenshot matrix and the release source.
**Standard applied:** try to reject. Every finding below is tied to a page, viewport, and visible or file-level evidence. Claims I could not verify are marked as such.

---

## 0. Evidence base and boundaries (read first)

**Inspected directly this round:**
- All 12 screenshots in `dist/`: `{home,learn,work,hive-mind-os,impact,donate}-{desktop,mobile}.png` (desktop 1440×1000 per `tests/e2e.mjs:7`; mobile 390×844 per `tests/e2e.mjs:134`).
- Source of record for copy and markup: `payload/content/{home,learn,our-work→catalog,work-hive-mind-os,impact,donate,about,get-involved,solutions,join,contact,stories,accessibility}.html`, `content.json`, `projects.json`, `facts.json`, theme `header.php`, `footer.php`, `functions.php`, `home.php`, `assets/css/site.css`, plugin `class-pws-public.php`, `class-pws-forms.php`, `tests/validate-release.mjs`, `tests/e2e.mjs`, `release-manifest.json`, `PRELAUNCH-CHECKLIST.md`, `LICENSES.md`, both receipts and `link-check-receipt.json`.

**Not verified this round (declared, not implied):**
- The owner's original prompt file (`USER_REQUEST.md`, outside this worktree) was not readable under this round's permissions; owner intent is grounded in the verbatim quotes preserved in `ROUND-1-REVIEW.md`.
- The live Playground render was not re-fetched (shell access denied for that purpose this round); pixels come from the 12 PNGs, copy from source HTML. Interactive states — open mobile menu, form focus/error states, the 404 page, hover — were **not visually inspected**, nor were renders of privacy, terms, learner-code, stories, ai-developer-workbench, coupon-hive (they have no screenshots; their copy was reviewed in source, and e2e asserts their H1s and status codes).
- Long full-page captures (home, learn) compress in review tooling; they were judged at composition level plus source-level copy. Short pages (work, donate, impact, hive-mind-os mobile) were legible to letter level.
- Color-contrast ratios were not measured. Palette values exist in `site.css`; an automated axe/contrast pass is a round-5 recommendation, not a claim.
- Provenance of the phone/ZIP/email (`(254) 761-5991`, `76549`, `support@patriotwebsolutions.org`) could not be re-verified against git history this round. They are consistent across 5 files and predate round 3; **owner must confirm them in the prelaunch pass** (the checklist's contact-details row covers this).
- One material caveat on all screenshots: **every capture includes the logged-in WordPress admin bar** (visible strip at top of `work-desktop.png`, `work-mobile.png`, etc.). The matrix therefore shows an admin render, not a visitor render (admin bar adds a 32px html offset). See R4-09.

---

## 1. Verdict

**REVISE — then ship.** Not reject, not ship-as-is.

- **One defect is release-blocking (P1):** the impact ledger is unreadable at 390px — the accountability page fails on the device class most donors and family members will use. It is a CSS-mechanism defect with a precise, verified root cause and a contained fix (§5, Fix 1).
- **Four P2 defects** are copy/conversion truthfulness gaps that contradict the site's own stated standard ("say only what is true now"). All are one-to-three-line fixes.
- **No evidence violations found.** External URLs are exactly the three allowlisted (re-verified in `link-check-receipt.json`, all 200). `facts.json` is empty, so every gated claim (taxID, 501(c)(3), tax-deductible, receipt language) is verifiably absent from rendered output (`functions.php:115-121`, validator lines 98-100, e2e JSON-LD assertion). No invented people, counts, testimonials, or links. The AI hero illustration is disclosed on-page and in `LICENSES.md` with prompt retention.
- **The composition holds.** Desktop pages are disciplined: one dark exhibit per page, no card-grid walls (the single 2-card grid lives on solutions where the contract allows it), dated status lines everywhere, buttons that state consequences. This is no longer "a well-engineered apology for a website" — it argues from objects (a README status table, a ledger, a syllabus, a verify command) rather than adjectives.

**What would flip this verdict to ship:** Fix 1 verified by a fresh `impact-mobile.png` plus the e2e overflow assertion still green; Fixes 2–5 applied; validator/e2e extended per §5 so the fixed states are locked. Nothing else below blocks.

---

## 2. Ranked defect register

| ID | Sev | Page / viewport | Finding | Evidence |
|----|-----|-----------------|---------|----------|
| R4-01 | **P1** | `/impact/` @390px | Ledger's 1st and 3rd columns collapse to ~4-character slivers; "Organization record", "Program design", "Repository (external)" render as near-vertical letter stacks. Contained (no horizontal scroll — e2e passed) but unusable. Independently reported by Codex; confirmed by my own read of `impact-mobile.png`. | `impact-mobile.png`; root cause in `site.css:274-279` + `impact.html` markup (below) |
| R4-02 | **P2** | Global header + footer, all pages | CTA labeled **"Join a cohort"** while the site's own ledger says "first cohort not yet run" (`impact.html`), learn's status line says "first cohort dates not set", and every in-page CTA correctly says "Join the interest list". The one label on the site that claims a state that does not exist — on its two most repeated surfaces. | `header.php:20`, `footer.php:5` vs `impact.html`, `learn.html:12`, `home.html:1`, `join.html:1` |
| R4-03 | **P2** | `/donate/` (fallback state — the state the screenshots show) | Two-part contradiction: bullet says "**The form below** runs through the site's configured donation system" while no form renders (GiveWP absent → `[pws_donation]` fallback), and the fallback headline "**Donation setup is being verified**" is deferral language the site's own §E.3 rule bans outside `/impact/`. The validator honestly reports 0 deferrals outside impact because §E.3 scoped the scan to `payload/content/` — PHP-rendered copy was never in the corpus. | `donate-desktop.png`, `donate-mobile.png`; `donate.html:3`; `class-pws-public.php:38`; `validate-release.mjs:81-92`; contract §E.3/§E.5 |
| R4-04 | **P2** | `/get-involved/#volunteer`, `/contact/` | The mentor/volunteer journey dead-ends. Home's "I can teach or host" door lands on a one-paragraph section with **no CTA**, and the contact form's topic select has **no volunteer/mentor option** (choices: Learning cohorts, Donations, Custom AI solutions, Employer/workforce partnership, Accessibility, Media or partnership, Something else). The only audience whose journey has no action at either end. | `get-involved.html:3`; `class-pws-forms.php:64`; `home.html:11` |
| R4-05 | **P2** | `/get-involved/#employers` | "**Our learners bring artifacts**, not just certificates" — present tense about learners that do not yet exist ("first cohort not yet run"). The blockquoted standard beneath it is fine (it is a published standard); this sentence asserts current practice. | `get-involved.html:2` vs `impact.html` |
| R4-06 | P3 | Home @1440 & 390 | Double honesty caption on the hero: the shortcode hard-codes an overlay figcaption ("Illustrative scene. We use real participant images only with permission.") **and** `home.html` adds `p.pws-fine` directly beneath ("Illustration, not a class photo…"). Two near-identical disclosures stacked — a committee-wrote-this tell on the most-seen module. | `class-pws-public.php:66`; `home.html:1`; `site.css:81` (overlay is styled and visible, nothing hides either) |
| R4-07 | P3 | Home proofstrip @1440 | The full 43-word qualified-EIN sentence appears **verbatim four times** on visitor surfaces (home proofstrip, donate lede, impact ledger row, about #record). On about/donate/impact it is load-bearing; on the home band it is a small-type compliance paragraph in a hero-adjacent scanning zone. | `home.html:2`, `donate.html:1`, `impact.html:2`, `about.html:3` |
| R4-08 | P3 | Copy layer | Weakest H1s break round-1's own §8 rule 1 (every H1 carries ≥1 concrete noun/number/name/place): about — "Patient instruction. Useful work. Shown, not asserted." (a triad **and** a reversal, zero concretes — the exact patterns round 2's self-check claimed to avoid); contact — "Start with the right conversation." Also `functions.php:70` meta description says "we are building in the open" — round 1 banned "we are building" hedges. These were contract-specified strings; the pixels still show them, so they are flagged. | `about.html:1`, `contact.html:1`, `functions.php:70`; ROUND-1 §8; ROUND-2 self-check (e) |
| R4-09 | P3 | Evidence pipeline | Screenshot matrix depicts the **admin-logged-in render** (WP admin bar in every capture); the console-error hook records text without URL (`console_errors` entry has no source), so the 404 needed forensic classification instead of a lookup; desktop viewport is contract-correct (1440), so this is capture hygiene only. | all 12 PNGs; `playground-e2e-receipt.json:23-25`; `tests/e2e.mjs` console handler |
| R4-10 | P3 | Chips @390px | In `work-mobile.png` the Historical chip wraps mid-date: "checked 2026-" / "09-15". A break after a hyphen is legal typography, but on a *verification-date* chip it reads as damage. | `work-mobile.png`; `class-pws-public.php:49` |
| R4-11 | P3 | `/about/` | Meta description promises "**Who runs** Patriot Web Solutions…" but the page names no human (named leadership is owner-gated per `PRELAUNCH-CHECKLIST.md:33` — correctly not invented). The buildable-now piece is the meta description; the named-steward slot stays gated. | `functions.php:74`; `about.html`; checklist line 33 |

### R4-01 root cause (verified mechanism, not conjecture)

`impact.html` renders `<div class="pws-tablewrap"><table class="pws-ledger">…` — the table **is** `.pws-ledger`. Therefore in `site.css`:

- Line 275 `.pws-exhibit table, .pws-ledger table { min-width: 0; }` — the `.pws-ledger table` selector matches **nothing** (no table inside the table). Dead rule.
- Line 279 `body { overflow-wrap: anywhere; }` (added late in round 3 to contain the unbreakable `docs/ACCEPTANCE_SPECIFICATION_GUIDE.md` token on the record page) is the actual mechanism: it reduces every table cell's min-content width to ~1 character. The table can now always compress to fit 390px, so the `.pws-tablewrap` scroll affordance **never engages**, and the browser distributes width roughly proportional to max-content — the 40-word middle column takes ~85% and the short label columns are crushed into letter stacks. This exactly matches the pixels: middle column readable, columns 1 and 3 shredded.
- The syllabus tables survive only by luck of proportion (their col 1 is "W1"), and `.pws-syllabus tbody th { white-space: nowrap }` is reset at ≤620px (line 255), so they are one long word away from the same failure. Round-1 §9 already prescribed the correct behavior — "ledger and syllabus rows collapse to label-stacked pairs" — and round 3 shipped scroll-plus-shrink instead. This finding is round 4 catching round 3's substitution.

### The single console 404 — determination

**Verdict: harmless local-environment artifact, with one cheap real improvement available.** Reasoning, honestly bounded: the harness recorded text without a URL, and I could not re-run the suite this round, so this is elimination, not observation. Every referenced asset ships and demonstrably loads: `ai-learning-workshop.png` (manifest line 190; visible in both home captures), `site.js` (menu `aria-expanded` test passed), `site.css` (styling everywhere), while `og-card.png`/`logo.png` are meta/JSON-LD references browsers don't fetch on page load, and no remote fonts or CSS URLs exist (validator line 139). The remaining candidate: **`/favicon.ico`** — no site icon is configured, no favicon file ships, no `<link rel="icon">` is emitted, and the Playground static router 404s missing static paths before WordPress's `do_favicon` fallback can answer; a single request, cached by the browser for the session, matches the single entry. On production Hostinger, WordPress itself would answer `/favicon.ico` with the default WP logo — no 404, but a generic-brand tab icon. **Actions (round 5):** (a) e2e must record `message.location().url` for console errors and fail on non-allowlisted 404s — turns this class of question into a lookup; (b) optional but recommended: wire the already-shipped 512×512 `logo.png` as the site icon during install, which kills both the Playground 404 and the default-WP-favicon brand gap in production.

---

## 3. Audience journey scorecard

Door = the home "Seven doors in." index, which maps one-to-one onto these seven audiences — the strongest single IA decision in the build.

| # | Audience | Path walked | Verdict | Concrete failure point |
|---|----------|-------------|---------|------------------------|
| 1 | Military member / veteran / spouse ("are 3 weekly hours worth it?") | home hero → `/learn/` → `/join/` | **PASS** | Strong: Mon/Wed/Fri cadence with named session structure (10/35/15), six-week syllabus with per-week artifacts, readiness standard in plain words, "not enrollment, no payment obligation," accommodation line, no-SSN form note. Failure points: header says "Join a cohort" when none exists (R4-02); if they check the record on a phone, the ledger is unreadable (R4-01). |
| 2 | Donor ("can I trust this?") | home proofstrip → `/donate/` → `/impact/` → `/about/#record` | **PASS with conditions** | Qualified EIN row, third-party records labeled external, "no ratios and no totals… evidence or not at all" — honest to a fault. Failures: the state the screenshots show contradicts itself ("form below" + no form, R4-03); the accountability ledger — the donor's destination page — fails on mobile (R4-01); no named human anywhere (owner-gated, R4-11). |
| 3 | Employer / workforce partner | home door → `/get-involved/#employers` → contact topic | **PASS** | Skills-to-job map with the honest caption ("supports applications for — not a hiring promise"), readiness standard, dedicated form topic, consequence-stating CTA. One truthfulness nick: "Our learners bring artifacts" present-tense before any cohort (R4-05). |
| 4 | Business / nonprofit / creator (paid custom work) | home door → `/solutions/` → `/contact/` | **PASS — strongest page on the site** | "A good fit / Not a fit" columns, four-step engagement, "we do not promise perfect model output or platform approval," button copy "Book a discovery conversation — no checkout, written scope first." No material failure found. |
| 5 | Developer / technical reviewer | home README exhibit → `/our-work/hive-mind-os/` | **PASS** | README status table quoted with elisions declared, revision-pinned (20a7ea2), fact block with license/language/status in the README's own words, the verify command with three honest notes, "what to look at first" pointing at real paths. Failures: cosmetic only — long path tokens wrap mid-token on mobile; home's README excerpt requires horizontal scroll at 390px (accepted; see §6). |
| 6 | Platform reviewer (OpenAI/ChatGPT etc.) | footer "Organization record" → `/about/#record` | **PASS** | One-stop dated record block: name, EIN row, location, contact, both third-party records, code link, all four policies; JSON-LD `NonprofitOrganization` with `sameAs` = exactly the three allowlisted URLs and gated fields verifiably absent; "No military, government, Google, or OpenAI endorsement is implied" in the footer of every page. Failure: meta description promises a "who" the page cannot yet name (R4-11). |
| 7 | Mentor / volunteer / community partner | home door → `/get-involved/#volunteer` | **FAIL** | The section is one paragraph with no CTA and the contact form has no volunteer topic — the journey ends in a wall (R4-04). Community-orgs and donor lines below it do have onward links; the volunteer specifically is stranded. |

Six of seven journeys pass against a try-to-reject standard; one fails outright and is a two-line fix plus one `<option>`.

---

## 4. Findings by category

**Mobile (390px).** Every page passes the containment assertion, and work/donate/hive-mind-os/join stack cleanly with readable type. The failures are quality-of-wrap, not overflow: R4-01 (ledger, destroyed), R4-10 (chip date), path tokens breaking mid-word on the record page (acceptable), and the home README `pre` as a horizontal-scroll region (accepted as the honest rendering of preformatted evidence). The global `overflow-wrap: anywhere` is a sledgehammer that traded one overflow bug for a typography regression across every table — it must be replaced by targeted rules (Fix 1).

**Accessibility.** Genuine strengths: skip link (`header.php:10`), `:focus-visible` and reduced-motion media query (validator-asserted), labeled controls with autocomplete attributes, `aria-expanded` menu toggle (e2e-tested), `role="status"/"alert"` on form notices, honeypot `aria-hidden`, table `caption`/`scope` semantics correct on desktop. Cautions: the R4-01 fix linearizes tables at ≤620px, which drops implicit table roles — for a 4-row ledger linear reading order is arguably better, but the fix must keep the visually-hidden header row technique (spec below) rather than `display:none`; contrast ratios not measured this round — run an automated pass in round 5; hero image alt text is descriptive and honest.

**Information architecture.** The seven-door index resolving to seven real journeys is the site's best structural idea, and the nav (Learn · Work · Impact · About · Partners + two actions) matches the round-1 plan. Anchors `#record`, `#employers`, `#volunteer` all exist (verified). Minor scent noise: the footer offers "Give with receipts" and "Donate" to the same URL in adjacent columns, and "Join a cohort"/"Join the interest list" name the same action differently (R4-02 resolves the harmful half of this).

**Conversion.** In-page CTAs are excellent — they state consequences ("no checkout, written scope first"; "Join the interest list") and each page ends with a dated status line instead of a hype band. The three conversion defects: the global CTA overclaim (R4-02), the volunteer dead-end (R4-04), and the donate fallback contradiction (R4-03). Note the pattern: all three are places where chrome (header/footer/PHP fallback) drifted from the content layer's discipline — the content passed through the §E scanner, the chrome didn't.

**Credibility.** The evidence chain holds end-to-end: dated chips backed by `projects.json` with build-stamped dates, external links confined to the allowlist and re-fetched at build with a shipped receipt, the README quoted rather than paraphrased and pinned to a revision, gates verifiably closed while `facts.json` is empty, and honest empty states ("nothing yet… instead of decorating"). The residual credibility risks are the truth-tense slips (R4-02, R4-05), the deferral leak through PHP (R4-03), and the human-shaped hole: no named person anywhere (correctly gated — but it remains the single highest-leverage upgrade the owner can unlock, per checklist line 33).

**Visual system.** Consistent and restrained: Georgia display over system sans, navy/cream/red held throughout, one dark exhibit per page, `text-wrap: balance` on headings, chips/fact-blocks/status-lines reused without mutation. The hero illustration is competent flat art and is labeled twice (which is once too many — R4-06); it remains the most stock-feeling element on the site, and the honest caption is what keeps it defensible. Desktop ledger and syllabus tables are typographically strong — which sharpens the contrast with their 390px collapse.

---

## 5. Exact fixes, ordered by impact

Each fix names its verification. Round 5 should implement these and nothing else without a new agreement.

### Fix 1 (R4-01) — Retire the global anywhere-wrap; linearize ledger and syllabus at ≤620px

In `site.css`:

1. **Delete** line 279 `body { overflow-wrap: anywhere; }` and the dead line 275 `.pws-exhibit table, .pws-ledger table { min-width: 0; }`. Keep `html, body { overflow-x: clip; }` as the final safety net and keep line 274's `overflow-x: auto` wrappers.
2. **Extend** line 276's targeted list to cover the token that originally forced the global rule: `code, .pws-chip, .pws-factblock dl, .pws-proofstrip a, .pws-checklist li { overflow-wrap: anywhere; }` (the record page's `docs/ACCEPTANCE_SPECIFICATION_GUIDE.md` lives in a `.pws-checklist li` — this is what round 3 missed before reaching for `body`).
3. **Add** inside the existing `@media (max-width: 620px)` block — implementing round-1 §9's label-stacked pairs:

```css
.pws-ledger, .pws-ledger tbody, .pws-ledger tr,
.pws-ledger tbody th, .pws-ledger td,
.pws-syllabus, .pws-syllabus tbody, .pws-syllabus tr,
.pws-syllabus tbody th, .pws-syllabus td { display: block; width: 100%; }
.pws-ledger thead, .pws-syllabus thead {
  position: absolute; width: 1px; height: 1px; overflow: hidden;
  clip-path: inset(50%); white-space: nowrap;
}
.pws-ledger tr, .pws-syllabus tr { padding: 1.05rem 0; border-bottom: 1px solid var(--line); }
.pws-ledger th, .pws-ledger td, .pws-syllabus th, .pws-syllabus td { padding: .18rem 0; border: 0; }
.pws-ledger td:last-child::before, .pws-syllabus td:last-child::before {
  content: "Source: "; font-size: .74rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--muted);
}
.pws-syllabus td:last-child::before { content: "Artifact: "; }
```

Row headings ("Organization record", "W1") become block labels above their prose — semantics preserved on desktop, reading order preserved on mobile, evidence link kept with its entry and labeled by generated content. The visually-hidden (not `display:none`) thead keeps column names available to assistive tech where the browser retains table semantics.
**Verify:** e2e still green including the 390px overflow assertion on `/our-work/hive-mind-os/` (the original trigger for the global rule — this is the regression to watch); fresh `impact-mobile.png` and `learn-mobile.png` show label-stacked rows with no letter stacks; desktop screenshots unchanged. Add an e2e assertion at 390px: on `/impact/`, the first `tbody th` has rendered width ≥ 200px (letter-stack canary).

### Fix 2 (R4-02) — Truthful global CTA

`header.php:20`: `Join a cohort` → **`Join the list`** (same 13-character footprint — no nav-wrap risk at mid viewports). `footer.php:5`: `Join a cohort` → **`Join the interest list`**. Both keep `/join/`.
**Verify:** e2e text assertions on header/footer link labels; grep zero remaining "Join a cohort".

### Fix 3 (R4-03) — Donate page true in both states; §E scope extended

1. `donate.html:3` bullet: "The form below runs through the site's configured donation system; this page never asks you to email card details." → **"Donations here run through the site's configured payment processor — we never ask for card details by email."** (True with or without a rendered form; drops the false spatial "below".)
2. `class-pws-public.php:38` fallback, present-state instead of deferral: **`<h3>No online donations today</h3><p>This page has no live donation form. To give or ask a question now, email support@patriotwebsolutions.org.</p>`** (keep the mailto link markup).
3. `tests/validate-release.mjs`: run the §E.3 deferral regex over `includes/*.php` string literals as well, budget 0. This closes the scan-scope gap so chrome copy is held to the same law as content.
**Verify:** validator green with the new scan; e2e (GiveWP-absent boundary) asserts the new fallback headline and the absence of "being verified" anywhere on `/donate/`.

### Fix 4 (R4-04) — Un-strand the volunteer

1. `class-pws-forms.php:64`: add `<option>Volunteer or mentor</option>` after the Employer option.
2. `get-involved.html:3`, append to the section: **`<p>Offer an hour: use the contact form's "Volunteer or mentor" topic and name the skill you'd bring.</p><div class="pws-actions"><a class="pws-button" href="/contact/">Start a volunteer conversation</a></div>`**
**Verify:** e2e asserts the new option in `form.pws-form select` and the button on `/get-involved/`.

### Fix 5 (R4-05) — Truth-tense on the employer pitch

`get-involved.html:2`: "Our learners bring artifacts, not just certificates:" → **"The program is built so learners finish with artifacts, not just certificates:"** (design claim, true today; becomes upgradeable to present tense after the first cohort — which is exactly how this site is supposed to work).
**Verify:** validator prose scans unchanged elsewhere; e2e section assertions untouched.

### Fix 6 (R4-06) — Single hero disclosure

`home.html:1`: delete the `p.pws-fine` line after `[pws_hero_image]` (keep the overlay figcaption, which travels with the shortcode and sits on the image itself). If the owner prefers the fuller consent sentence, move that wording *into* the shortcode's figcaption instead — one disclosure either way.
**Verify:** fresh home screenshots show exactly one caption.

### Fix 7 (R4-07) — Home proofstrip carries the fact, not the compliance paragraph

`home.html:2` org line: **"Public IRS-derived records list Patriot Web Solutions, Killeen, Texas, under EIN 99-1238039 (checked 2026-09-15). Organization record →"** — drop only the second sentence ("Owner confirmation is pending…"), only here. About, donate, and impact keep the full qualified row verbatim (those are the decision surfaces; the ledger row remains the canonical statement).
**Verify:** validator phrase scans unaffected; the full sentence still present ≥3 places (grep).

### Fix 8 (R4-09 + the 404) — Evidence pipeline hygiene

In `tests/e2e.mjs`: capture `msg.location().url` for console errors and fail the run on any 404 whose URL is not in a declared allowlist (expected: `/favicon.ico` until Fix 8b); log out (or use a fresh context) before the screenshot passes and assert `document.body.classList.contains('admin-bar') === false` on the first capture. **8b (optional, recommended):** installer sets the site icon from the shipped 512×512 `logo.png` during apply — removes the favicon 404 in Playground and the default-WP favicon in production. Also `functions.php:70`: "…a skills-based savings workflow we are building in the open." → **"…a skills-based savings workflow, with its current status dated on the record."**
**Verify:** new receipt has empty `console_errors` (or a documented allowlist); screenshots show no admin bar.

### Fix 9 (R4-08) — Two weakest H1s (owner's call; propose, don't insist)

- `/about/`: "Patient instruction. Useful work. Shown, not asserted." → **"The record behind the Killeen classroom."** (place + object; kills the triad/reversal).
- `/contact/`: "Start with the right conversation." → **"One form, eight topics, a reply from a person."** (numbers + concrete promise — count includes Fix 4's new topic).
These are contract-specified strings; changing them amends the round-2 contract, so they ship only with explicit acceptance.
**Verify:** e2e H1 map updated in the same commit as the copy, or not at all.

**Round-5 sequencing note:** Fix 1 is the only one with regression risk (it reverses the round-3 overflow remedy); land it first and re-run the full e2e including the hive-mind-os mobile assertion before stacking the copy fixes. Rebuild the zip, refresh both receipts and all 12 screenshots, then stop for review — same gate as this round.

---

## 6. Considered and rejected (would add slop, unsupported claims, or needless complexity)

1. **Replace the hero illustration with a real artifact screenshot (terminal/receipt bundle).** Tempting for the anti-stock instinct, but the owner accepted a labeled illustration in round 2, the disclosure chain is honest (on-image caption + LICENSES.md with retained prompt), and a terminal screenshot as the first image would misstate the audience (families, not developers). Revisit only when real, consented class photos exist (owner-gated).
2. **Soft-wrap the home README excerpt (`pre-wrap`) to kill mobile horizontal scroll.** Rejected: it would mangle the markdown table's alignment and misquote the artifact's form. A scroll region is the honest rendering of preformatted evidence; adding fade-edge scroll hints is decoration the design language doesn't need.
3. **`span.nowrap` around every date to fix R4-10.** Markup churn across content, PHP, and JSON-rendered chips to prevent one legal hyphen break. If it still rankles after Fix 1 (which changes wrapping pressure), revisit with a chip-only rule.
4. **Rename footer "Give with receipts".** The tax-receipt reading is a stretch risk, but "receipts" is the site's accountability motif, transaction receipts are real regardless of tax status, and the gated language ("tax-deductible", "501(c)(3)") is mechanically absent. Renaming would trade a distinctive, defensible label for a duller one on a hunch.
5. **Add trust badges, partner logos, star counts, or a testimonials placeholder.** All banned by §E.2 or empty-state-as-feature; the ledger is the trust mechanism.
6. **Prune the learn page for length.** Its density is the product (a family deciding on three weekly hours needs the whole picture); it is sectioned, scannable, and every number is a labeled planning range. Cutting it would re-create the thinness round 1 rejected.
7. **Schema.org enrichment (FAQ/Course markup).** No evidence need; expands the claim surface a platform reviewer could probe, against this site's minimal-claims strategy.
8. **Renaming all abstract H1s.** Only the two weakest are flagged (Fix 9); a wholesale rewrite would churn contract-agreed copy and risk over-correcting into keyword mush.

---

## 7. Adversarial self-review of this review

**Method disclosure:** the refute-first panel ran inline (single reviewer, sequential lenses) rather than as parallel subagents, because the 12 screenshots would need re-reading per agent. That is a real independence tradeoff — inline lenses share one context and one set of blind spots — disclosed rather than papered over.

**Corrections ledger (claims I formed and then overturned this round):**
1. *Suspected the desktop screenshots deviated from the contracted 1440px* → **refuted** by `e2e.mjs:7` (`viewport: { width: 1440 … }`).
2. *Suspected `/about/#record`, `#employers`, `#volunteer` anchors might not exist* (four surfaces link them) → **refuted**; all three verified in source.
3. *Initially read R4-03 as "the validator lied"* → **corrected**: the validator faithfully implements §E.3's contracted scope (`payload/content/` only); the defect is the scope plus the fallback copy, not a false PASS. The receipt's claims remain accurate as scoped.
4. *Suspected the hero image might be the 404* → **refuted**: manifest line 190 plus visible render in both home captures.
5. *Assumed `.pws-ledger table { min-width: 0 }` was the letter-stack mechanism* (my working theory entering this round) → **corrected**: that selector is dead; the mechanism is `body { overflow-wrap: anywhere }` collapsing min-content widths (§2). The fix changed shape because the diagnosis did.
6. *This document's own draft subject line quoted a stale zip hash (`3246c8fa…`) from an earlier build read* → **corrected** at delivery against `dist/release-receipt.json` (generated 2026-09-15T23:27:27Z): sha256 `164c8cd9…c684f065`, 51 files — re-read at write time, not quoted from memory. Quoting my own earlier read was exactly the anti-pattern this mode exists to catch.

**Where this review is weakest, stated plainly:** (a) it never touched the running site — hover, focus, open-menu, and error states are unreviewed, and any defect living only in those states is invisible to this round; (b) six of eighteen routes have no pixel evidence (source-reviewed only); (c) the favicon determination is elimination, not observation, and says so; (d) the reviewer of round 3 is the author of round 3 — the strongest mitigations are that the P1 was independently confirmed by Codex before I diagnosed it, that R4-03/R4-08 are findings *against my own* round-3/round-2 work, and that this round proposes and gates rather than certifies.

**What survives everything:** the release candidate's evidence chain is real, its copy discipline mostly holds, and its failures are concentrated exactly where the §E scanner couldn't see — rendered chrome and one global CSS shortcut. Fix the ledger, tell the truth in the header, open the volunteer's door, and this ships.

— End of round 4. No files other than this review document were modified. Implementation of any fix above requires round-5 acceptance.
