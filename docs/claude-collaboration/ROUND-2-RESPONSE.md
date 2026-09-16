# Round 2 response — dispositions and the implementation contract

**Author:** Independent design/product strategist (Fable discipline)
**Date:** 2026-09-15
**Inputs:** ROUND-1-REVIEW.md (as corrected), the 12 Codex objections, and fresh reads this round of `class-pws-installer.php` (page-creation and rollback paths), `payload/content.json`, `package.json`, `tests/`, `scripts/`.
**Rule observed:** no site files modified in this round. This document is the build contract for round 3.

Verification note for this round: external URLs were **not** re-fetched today in this session (network fetch unavailable); the three public links below carry their 2026-09-15 round-1 verification stamps and are re-verified mechanically by the link-check script this contract adds. The IRS TEOS deep-link stability check **could not be performed** — TEOS therefore appears only as a production-checklist obligation, never as a published link, until someone verifies a stable URL.

---

## Part 1 — Disposition of the twelve objections

**1. Brand line "Verification is a military habit" — ACCEPT (retire it).**
Codex is right that the analogy itself, not just decorative use, is the risk: it flattens spouses, caregivers, and veterans with very different service relationships into one rhetorical posture. The spine is now **"Show the work."** — already pre-approved as the round-1 fallback. Military and family identity is carried only by verifiable concreteness: Killeen, the Mon/Wed/Fri hour, transitions and family constraints named in the schedule policy, and (Phase B) real people. One boundary kept: if a *named human* draws the training-standard analogy in first-person quoted copy, that is their voice and may ship; the organization's voice never uses it.

**2. 501(c)(3)/deductibility claims — ACCEPT (gate tightened).**
No categorical status or deductibility assertion ships from ProPublica alone. The site may ship now, verbatim:

> "Public IRS-derived records list Patriot Web Solutions, Killeen, Texas, under EIN 99-1238039 (record checked 2026-09-15). Owner confirmation is pending before this site publishes the organization's formal status and donation-receipt language."

with links to the ProPublica record and GuideStar profile *labeled as third-party records*. The categorical statement, JSON-LD `taxID`/`nonprofitStatus`, and receipt language remain gated behind: (a) owner confirms the EIN, (b) determination letter or IRS TEOS/Pub 78 check recorded with date, (c) qualified review of receipt wording. This is mechanically enforced (Part 2 §F: gated-phrase test keyed to `facts.json`). Round-1's D2 stands unchanged as a *defect of vagueness and burial*; the remedy is the sourced, qualified row — not a categorical claim. Nothing in either document is legal advice, and the donate page will not imply it is.

**3. Brittle vanity facts — ACCEPT.**
"~850 commits" and any live counters are dropped from all published copy. The project fact block fields are fixed as: repository URL · license · language · status **quoted from the project's own README** · what to inspect (README, `docs/`, `tests/`, `benchmarks/`) · exact `Last verified:` date · verification artifacts (the link-check receipt; a pinned short revision **only** where an annotated figure quotes specific lines, so the annotation stays true as the repo moves).

**4. Table monotony — ACCEPT.**
Eight distinct visual forms are specified in Part 2 §B, each with its own component and permitted locations. Hard caps: the full ledger table exists only on `/impact/`; the homepage contains **zero** full-width tables — its proof strip is a mixed-media band (repo card + schedule strip + place/org line), not rows of a grid. The homepage composition is specified unit-by-unit in §D so it cannot regress into either card slop or compliance-report slop.

**5. Release hashes off the homepage — ACCEPT.**
The homepage proof strip carries: the real repository, the three-hour weekly program design, the Killeen identity, and the qualified organization-record line (per objection 2). The sha256/receipt/route-map material moves entirely into the "Why we rebuilt this website in public" field note.

**6. Missing owner facts as data slots — ACCEPT.**
Mechanism (fits the turn-key release model; no new admin UI): a shipped `payload/facts.json` containing **only confirmed facts**, read by a tiny `PWS_Facts` helper. Every component that wants an owner fact has a **complete-now variant** that renders as finished prose without the fact, and an **enriched variant** used automatically when the key exists. Missing facts also live as internal obligations in `PRELAUNCH-CHECKLIST.md` — never as visitor-facing TODO prose. The deferral-phrase test (§F) makes "will be published once verified" boilerplate a build failure, so the pages *cannot* ship incomplete-sounding.

**7. Work section needs content now — ACCEPT.**
Full inspectable Hive Mind OS record with real links; ADW and Coupon Hive get substantial dated record pages (purpose, era, evidence state, lessons, what exists locally as a gated slot, and an explicit "what publication requires" checklist). New evidence-state chip added to the vocabulary: **"Public listing not verified — checked 2026-09-15."** No "coming soon" anywhere. Content specs in §D.

**8. Learning artifacts — ACCEPT.**
This contract includes (§D): the six-week Foundations syllabus table (drafted, marked for owner accuracy review), the repeat-until-understood readiness standard, sample portfolio contents, the skills→job-task map, the defensive-purpose statement for replay/leakage/reward-hacking/backtesting, and a session structure explicitly labeled **"our default session plan"** until a real log exists.

**9. Journeys must be visible on pages — ACCEPT.**
No audience cards. Each lens gets a named route plus a specific on-page section that exists in the build (mapping table in §D.11). The one-line "Start here" text index appears on home and in the footer.

**10. Public professionalism — ACCEPT.**
(a) Site title: the installer sets `blogname`/`blogdescription` **only if** the current values are WordPress defaults ("My WordPress Website", "Just another WordPress site", empty) — recorded in the rollback snapshot like every other option; the Playground blueprint also sets a proper `siteName` so screenshots are honest. (b) `og:image` + upgraded social metadata; JSON-LD upgraded to `NonprofitOrganization` with `address` (city/state now), `foundingDate`/`taxID`/`nonprofitStatus` gated on `facts.json`. (c) All drafting instructions stripped from privacy/terms into the checklist. (d) A banned-phrase test fails the build on any "Google-approved / OpenAI partner / compliant with Google/OpenAI" claim.

**11. Preserve the engineering — ACCEPT (verified firsthand this round).**
Confirmed in `class-pws-installer.php`: durable checkpoints written before each mutating step (`checkpoint($snapshot)`, `pending_page` recovery), page-conflict protection with explicit-approval `WP_Error` + before-state fingerprints + `wp_save_post_revision`, rollback that trashes created pages only when `hash_equals` the release fingerprint and otherwise reports `preserved_modified_page_ids`, legacy route control, sitemap isolation, and the exact-archive verify script. The nested-record installer change is specified reversibly in §C.1: manifest `parent` key, parent-first ordering, full-path lookups, child-first rollback, `post_parent` included in the fingerprinted field set so preservation logic keeps working.

**12. Visual review rounds — ACCEPT.**
The build round ends with regenerated screenshots — six key pages at 1440px and 390px — written to `dist/`, and a separate round-4 visual critique is a named gate before any production packaging. A text-only code pass cannot close this contract.

Rejected outright: nothing. Two adaptations beyond the objections' letter: (i) field-note seeds are installed as **drafts** in the `pws-field-notes` category so the route map's "assign/publish only after review" control is preserved — the owner publishes with one click; (ii) the external link-check runs as its own script (`npm run verify:links`) rather than inside the offline validator, so the deterministic build check stays network-free.

---

## Part 2 — The implementation contract (round 3 builds exactly this)

### A. Pages and files

**Modified content files** (all in `release-plugin/patriot-web-solutions/payload/content/`): `home.html`, `learn.html`, `join.html`, `our-work.html`, `solutions.html`, `impact.html`, `about.html`, `get-involved.html`, `donate.html`, `contact.html`, `stories.html`, `privacy.html`, `terms.html`, `accessibility.html`, `learner-code.html` — rewritten per §D under the §E boundaries.

**New content files:** `content/work-hive-mind-os.html`, `content/work-ai-developer-workbench.html`, `content/work-coupon-hive.html` (slugs `hive-mind-os`, `ai-developer-workbench`, `coupon-hive`, each with `"parent": "our-work"` → URLs `/our-work/<slug>/`).

**New data files:** `payload/projects.json` (catalog + link registry: name, slug, state, state_label, checked date, links[], summary) · `payload/facts.json` (confirmed owner facts only; ships near-empty).

**New/changed plugin+theme files:** `includes/class-pws-facts.php` (new, ~40 lines) · `includes/class-pws-public.php` (catalog rewrite) · `includes/class-pws-forms.php` (one option line) · `includes/class-pws-installer.php` (parent + conditional blogname) · `payload/content.json` (3 new page entries with `parent`; nav labels "Work"/"Partners") · theme `functions.php` (identity block) · `assets/css/site.css` (components §B) · `payload/redirects.json` unchanged · `ROUTE-MAP.md` + `PRELAUNCH-CHECKLIST.md` + `DATA-FLOWS.md` updated to match.

**New field-note drafts** (installer-created, `post_status: draft`, category `pws-field-notes`; sources in `payload/notes/`): rebuild note (receives the sha256/receipt material), receipt-walkthrough note, curriculum-design note.

### B. Component inventory — eight distinct forms

| Component | CSS root | Form (must be visually distinct) | Allowed on |
|---|---|---|---|
| Ledger | `.pws-ledger` | Full-width sourced-and-dated record table | `/impact/` only |
| Proof strip | `.pws-proofstrip` | Horizontal mixed band: repo mini-card + schedule strip + place/org line; no table semantics | home only |
| Fact block | `.pws-factblock` | Bordered sidebar of labeled facts + `Last verified:` | project records, donate, about |
| Annotated exhibit | `.pws-exhibit` | Real artifact capture + numbered margin notes | home (×1), hive-mind-os record, field notes |
| Syllabus table | `.pws-syllabus` | Document-style table, caption + scope, row = week | `/learn/` (excerpt of 2–3 rows on home styled as document excerpt, not a table band) |
| Timeline | `.pws-timeline` | Ordered-list steps with duration marks | learn (session plan), solutions (phases) |
| Development log | `.pws-devlog` | Dated entries, newest first | coupon-hive record, ADW record |
| Signed note | `.pws-signed` | Byline block: prose + name/role | home (Phase B), about |
Plus small parts: `.pws-chip` (state + date, never color-only), `.pws-index` (start-here text lines). **Retired:** `pws-cards--3` for concepts; the numbered concept cards; the values grid. `pws-cards` survives only where items are genuinely parallel things (solutions fit-table may use a 2-col comparison instead).

### C. PHP changes (function-level)

**C.1 Installer — nested pages, reversibly.** In the page loop (`class-pws-installer.php` ~:132–201): resolve `$parent_id = $page_ids[$page['parent']] ?? 0` (manifest ordered parents-first; missing parent ⇒ `WP_Error`, snapshot rollback); add `post_parent => $parent_id` into `$planned` **before** `fingerprint_fields($planned)` so conflict detection, `release_state_hash`, and modified-page preservation all cover the parent linkage; lookups for child slugs use full path (`get_page_by_path('our-work/hive-mind-os')`). Rollback: iterate `created_page_ids` in reverse creation order (children trash before parents). Conditional `blogname`/`blogdescription`: set only when current value is a WP default; store prior values in the snapshot; restore on rollback. All other guarantees listed in Part 1 §11 are untouched — the diff to this file must stay reviewably small.

**C.2 `class-pws-public.php`.** `project_catalog()` reads `payload/projects.json` (same pattern as `redirects.json` at :73) and renders record rows: state chip + checked date, name linking to the record page, one-line summary, and the external link only for `public` state. Hardcoded array deleted. `hero_image()` unchanged.

**C.3 `class-pws-facts.php`.** `PWS_Facts::get(string $key): ?string` + `::has()`; reads `payload/facts.json` once per request; unknown key ⇒ null, and callers render their complete-now variant. Documented key register (initial): `org_status_confirmed`, `org_status_statement`, `receipt_language`, `street_address`, `founding_date`, `class_time_ct`, `founder_note`, `founder_name`, `adw_era`, `adw_listing_url`, `cost_structure`, `first_report_target`. Each key mirrors one PRELAUNCH-CHECKLIST obligation.

**C.4 `class-pws-forms.php`.** Add `<option>Employer / workforce partnership</option>` to the contact topic select (:64 area). No other form changes.

**C.5 Theme `functions.php`.** JSON-LD → `@type: NonprofitOrganization`, add `address` (Killeen, TX now; street via facts), `sameAs` [GitHub repo, GuideStar, ProPublica], `logo`; `foundingDate`/`taxID`/`nonprofitStatus` emitted **only when** `org_status_confirmed`. Add `og:image` (new static asset at fixed 1200×630), `og:title`, `twitter:card`. Rewrite the per-page meta descriptions to the new copy. Fallback menu gains Work children handling (depth stays 1; records reached from Work page, so no nav change needed beyond labels).

### D. Content requirements (the copy contract)

Numbers below are requirements, not suggestions; §E boundaries apply to every page. Full drafted artifacts (syllabus, job map, standards) ship in the build exactly as specified here, subject only to owner accuracy corrections.

**D.1 Home** — six units, in order: (1) lead editorial unit — kicker `Killeen, Texas · For military members, veterans, and their families`; H1 single declarative with place+cadence (contract example: "We teach military families in Killeen to build and test AI tools — one live hour, three days a week."); two-sentence sub naming Mon/Wed/Fri and the no-flunk-out policy; current illustration with honesty caption until a real photo exists (slot `hero_photo`); (2) proof strip — repo mini-card ("Our flagship project is public — read the code": link), schedule strip (reusing the existing band's Mon/Learn Wed/Practice Fri/Build), org line (the §1.2 qualified statement, one line, linking `/about/#record`); (3) one annotated exhibit — a hive-mind-os receipt excerpt with 3 margin notes; (4) program excerpt — syllabus weeks 1–3 styled as a document excerpt + "Full path →"; (5) signed-note slot (`founder_note`; complete-now variant: omitted entirely — no placeholder); (6) start-here index, 7 text lines. **Banned on home:** any 3-card concept grid, any full-width table, any sha256, any counter.

**D.2 Learn** — intro (who/rhythm/policy as fact, not slogan); **Inside one hour**, labeled *"Our default session plan — we publish a real session log once cohorts run"*: recap & questions (10) → guided build (35) → demonstrate & log (15); **Foundations syllabus (6 weeks)** — the shipped draft: W1 What AI can and can't do — artifact: a task attempted with and without AI, with notes · W2 Privacy and safe inputs — artifact: personal red-lines checklist · W3 Asking better: briefs not prompts — artifact: reusable task brief · W4 Checking answers: sources and failure patterns — artifact: source-check worksheet applied to a real answer · W5 Repeatable workflows — artifact: a documented everyday workflow · W6 Readiness week: demonstrate, repeat, or extend — artifact: private demonstration record; table caption marks it *reviewed by the program owner before enrollment opens*; **readiness standard** verbatim: "Explain it in your own words. Do it on a fresh example. Handle one variation. Privately, untimed, repeated until solid — a miss means we re-teach, not that you failed."; **portfolio contents** list (task briefs, checked workflows, one documented skill with test cases, one truthful case study); **skills→job-task map** table with rows: prompting & verification → operations, admin, research support · evaluation & replay → QA and testing support · APIs & workflows → junior automation work · documentation & briefs → knowledge/office roles — every row phrased "supports applications for"; **advanced paths** (keep the three) + defensive-purpose statement: replay, leakage, contamination, reward hacking, and backtesting are taught *so learners can detect and prevent them*, bound to the learner code's evaluation clause; status line + join CTA.

**D.3 Work index** — intro (2 sentences on evidence states); record rows from `projects.json`: Hive Mind OS `Public & inspectable — verified 2026-09-15` · AI Developer Workbench `Historical — public listing not verified, checked 2026-09-15` · Coupon Hive `In development — last updated <build date>`; the complete-record checklist moves to the foot as "the standard each record follows."

**D.4 Hive Mind OS record** — fact block (URL, MIT, Python, README status quote "early prototype… production use not yet supported", inspect list README/`docs/`/`tests/`/`benchmarks/`, last-verified date, link-check artifact reference); three short paragraphs what/why/mission-link; "what to look at first" with one sentence per target; one annotated receipt exhibit (pinned short revision noted beside the figure); CTAs "Inspect the repository" (labeled external) + "Discuss a build like this."

**D.5 ADW record** — fact block (state chip; era slot `adw_era`; "evidence held privately" slot; nothing invented); narrative: what it was, what it taught the org (3 dated lesson bullets are acceptable *as statements of practice*, not outcomes); **What publication requires:** locate original listing/source from the builder account (owner) → re-verify behavior against current platform rules → publish with limits documented. If `adw_listing_url` ever lands in facts.json *and passes the link check*, the chip flips to public.

**D.6 Coupon Hive record** — fact block + dev log with one real dated entry written at build time (design intent, current milestone, next verifiable artifact). No availability language.

**D.7 Solutions** — prose intro; **fit table** (good fit: bounded workflows, internal knowledge tools, eval harnesses, documented skills/plugins/MCP tools · not a fit: unsupervised agents over consequential actions, bet-the-company automation, anything we cannot test honestly); 4-phase engagement timeline (keep existing copy, render as `.pws-timeline`); deliverables paragraph (every engagement ships with evaluation results, an operating document, a limits document, and written support terms); mission-connection line only via slot (`cost_structure`); CTA "Book a discovery conversation — no checkout, written scope first."

**D.8 Impact** — the ledger (rows: org record [qualified §1.2 row] · public code [repo, checked date] · program design [3 hrs/week, no-flunk-out, source: /learn/] · site rebuild [link to rebuild field note]); "counting from" block only via slot; the existing five reporting rules verbatim (best copy on the site); **What we will not claim yet** (employment outcomes, testimonials, historical totals — each with its unlock evidence); first-report target via slot.

**D.9 Donate** — opens with the §1.2 qualified org statement + third-party record links (labeled as such); what gifts fund as categories (live instruction, learner tool/API access, accessible materials, safe test environments) with **no ratios or totals**; trust list minus the withholding line; `[pws_donation]` embed; status line. Receipt/deductibility language appears only from `receipt_language` after the gate.

**D.10 About** — story slots (complete-now variant: the truthful two-paragraph pivot narrative already in draft, minus "we are documenting before publishing claims" prose); people section renders only real entries (zero placeholder headshots; section absent until Phase B); **organization record block** with anchor `#record`: legal name as registered, the qualified EIN row, city/state, contact, policy links, `sameAs` links — this is the platform-reviewer anchor; practices paragraph (round-1 corrected form — no virtue-noun set).

**D.11 Partners (get-involved), Join, Contact, Stories, trust cluster, learner code, 404** — as specified in ROUND-1-REVIEW §5 including the panel fixes (employer section embeds the job-map excerpt + readiness standard and routes to the new contact topic; learner-code and accessibility have full treatments). Journey→page mapping that must exist in the build: learner→home lead+D.2 · donor→proof-strip org line+D.8+D.9 · employer→Partners employer section · client→D.3/D.4+D.7 · technical reviewer→D.4 · platform reviewer→D.10 `#record`+finished policies · mentor/community→Partners sections 2–3. The start-here index links exactly these anchors.

### E. Evidence boundaries (hard rules for round 3)

1. **Allowed external links, total:** `github.com/kb4beast/hive-mind-os` · `projects.propublica.org/nonprofits/organizations/991238039` · `guidestar.org/profile/99-1238039` (verified 2026-09-15; re-verified by `verify:links` at build). Adding any other external URL requires a fresh dated fetch recorded in the build receipt.
2. **Banned regardless of source:** constructed `chatgpt.com/g/…` URLs; any GPT-store availability claim; testimonials; participant counts, outcome numbers, or historical totals; "tax-deductible"/"501(c)(3)" as categorical claims (gated phrases); "Google-approved", "OpenAI partner(ed)", "compliant with Google/OpenAI" in any form; commit counts or star counts in published copy.
3. **Deferral budget:** the round-1 deferral grep pattern must return **0 matches** in `payload/content/` outside `impact.html`, and ≤2 inside it (the "will not claim yet" section). Per-page status lines use the form "Status: … — see [the record](/impact/)" with a date, never future-tense promise phrasing.
4. **Every date is real:** `Last verified` / `checked` dates must equal the date the check actually ran (build script injects them into `projects.json`; hand-written dates in content match the build receipt).
5. **Facts render complete-now:** no visitor-facing sentence may depend on an absent `facts.json` key; reviewers check each slot's absent-state rendering.

### F. Test and tooling changes

- `tests/validate-release.mjs` (offline, deterministic — stays network-free): + manifest `parent` integrity (parent exists, ordered before child, no slug collisions with `redirects.json` paths); + `projects.json`/`facts.json` schema checks; + **deferral-budget scan** (§E.3 pattern, fails over budget); + **banned/gated-phrase scan** (§E.2; gated phrases pass only if `facts.json.org_status_confirmed` is true); + content-file existence for the three new records and notes.
- **New** `tests/verify-links.mjs` (`npm run verify:links`, network): fetches every external URL in `payload/content/` + `projects.json`, writes `dist/link-check-receipt.json` with per-URL status and timestamp; run at build and before any production packaging, not inside the offline validator.
- `tests/run-playground-e2e.mjs`: + blueprint sets a real `siteName` (kills "My WordPress Website" in screenshots); + assertions: `/our-work/hive-mind-os/` resolves 200 with the repo link present; contact form shows the employer topic; `/our-work/` shows three state chips with dates; JSON-LD parses and `taxID` absent while ungated; + **screenshot matrix**: home, learn, our-work, hive-mind-os record, impact, donate at 1440px and 390px → `dist/*.png`.
- Installer tests via e2e: apply → verify nested URL → simulate child edit → rollback → assert modified child preserved and parents restored (exercises the fingerprint path with `post_parent`).
- `scripts/Verify-Release.ps1` archive gate unchanged; `Build-Release.ps1` adds date injection for `projects.json` and includes new payload files in the manifest.

### G. Ordered build plan (round 3)

| # | Step | Gate to pass |
|---|---|---|
| 1 | Installer changes (parent, conditional blogname) + validator parent checks | `npm test` green; diff to installer reviewed as small |
| 2 | Data layer: `projects.json`, `facts.json`, `PWS_Facts`, catalog rewrite, forms option | validator schema checks green |
| 3 | Theme: §B components in `site.css`, `functions.php` identity block, og:image asset | offline validator green |
| 4 | Content: 15 rewrites + 3 records + 3 note drafts, per §D under §E | deferral + phrase scans green (they will fail honest-looking drafts — that is the point) |
| 5 | Docs sync: ROUTE-MAP, PRELAUNCH-CHECKLIST (all ⛔ slots), DATA-FLOWS, LICENSES if og:image needs it | manifest verify green |
| 6 | `npm run verify:links` + full e2e + screenshot matrix | link receipt clean; e2e assertions green; 12 screenshots in `dist/` |
| 7 | Build release zip + receipt | `verify:package` green |
| 8 | **STOP — round 4 visual critique** of the screenshot matrix (desktop and mobile, all six pages) before any production/packaging conversation | separate review round; no self-certification |

Owner-gated register (unchanged from round 1 Phase B/C, now keyed to `facts.json`): EIN confirmation → determination letter/TEOS check → receipt language review → founder note/people/photos/consent → class time → ADW era + listing URL from the builder account → cost structure → first-report date → street address → shop retire/keep → Google/OpenAI account actions → Hostinger staging rehearsal.

---

### Self-check on this response

Attacked before shipping: (a) *Does the qualified org row still overclaim?* It states only that public records list the name/EIN and that confirmation is pending — every word is evidenced; survives. (b) *Does `facts.json` recreate the TODO problem in data form?* No — TODOs become internal obligations; the visitor always sees complete prose; enforced by §E.5 review and the deferral scan. (c) *Is the installer change still the riskiest item?* Yes — hence step 1, smallest reviewable diff, fingerprint-covered `post_parent`, child-first rollback, and a dedicated e2e path. (d) *Did I sneak any unverified link in?* The only three external URLs are round-1-verified and re-checked mechanically; TEOS is deliberately absent. (e) *Slop-relapse scan of my own proposed copy:* the contract H1 and readiness standard contain place, cadence, and consequence — no triads, no reversals; the banned-phrase list now also binds me.
