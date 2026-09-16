# Round 1 review — Patriot Web Solutions redesign

**Reviewer:** Independent design/product strategist (Fable discipline, refute-first)
**Date:** 2026-09-15
**Scope:** Release `dist/patriot-web-solutions-release-1.0.0.zip` (sha256 `d70a9894…bb0e70`), all 16 content pages, theme, plugin, screenshots, planning packet at `hive-mind-os/docs/plan/patriot-web-redesign-2026-09-15/`, live Playground render at `http://127.0.0.1:9411/`, and public-web verification performed today.
**Rule observed:** no site files modified in this round.

---

## 0. Evidence ledger for this review

Every load-bearing claim below is grounded here. Items marked **not verified** were not checked and are not relied on.

| # | Claim | Evidence class | How generated (2026-09-15) |
|---|---|---|---|
| E1 | Homepage = hero + alternating navy/cream bands + three 3-card grids | Screenshot + source | `dist/home-desktop.png`, `dist/home-mobile.png`; `payload/content/home.html` |
| E2 | Portfolio is a hardcoded 3-item PHP array, text only, no links | Source + rendered page | array at `includes/class-pws-public.php:43-47`, render loop through :59; `curl http://127.0.0.1:9411/our-work/` → exactly 3 `.pws-project`, 0 `github.com` matches |
| E3 | Zero occurrences of `github`, `chatgpt.com`, `openai.com` in the entire release | Grep, 0 matches | `Grep -i 'github\|chatgpt\.com\|openai\.com' release-plugin/` |
| E4 | 14 deferral constructions ("will publish… only after… being verified…") across 11 of 16 content pages | Grep count | pattern over `payload/content/`; per-file counts retained in review notes |
| E5 | `hive-mind-os` repo is public: MIT, Python, ~850 commits, `tests/ docs/ benchmarks/ examples/ prompts/`, tagline "Don't trust your coding agent. Verify it.", self-described early prototype | Live fetch | https://github.com/kb4beast/hive-mind-os fetched today |
| E6 | Patriot Web Solutions, EIN **99-1238039**, Killeen TX, is a **501(c)(3)**, ruling March 2024, "Donations to this organization are tax deductible"; no 990 data yet (young org / 990-N) | IRS-derived public record | https://projects.propublica.org/nonprofits/organizations/991238039 fetched today; GuideStar profile at same EIN surfaced by search |
| E7 | Draft donate page refuses to state deductibility: "Donation deductibility and legal status language will be displayed only after the organization's current records are verified." | Source | `payload/content/donate.html` — a mid-item in the "Before you give" checklist, not the page's opening claim |
| E8 | No public listing found for "AI Developer Workbench", "Hive Mind OS" GPT, or "Coupon Hive" | Absence of evidence (two dated web searches) | Searches today returned no matching artifact. This proves "not found," not "does not exist" — a private/unlisted GPT would not surface |
| E9 | Playground `<title>` renders "My WordPress Website"; installer sets `show_on_front`/`page_on_front` but never `blogname`/`blogdescription` | Rendered page + grep | curl with cookie jar; `class-pws-installer.php` grep |
| E10 | JSON-LD is generic `Organization` — no address, no `nonprofitStatus`, no `taxID`, no `sameAs`, no `logo`; no `og:image` | Source | `functions.php:59-89` |
| E11 | Privacy page ships editorial instructions to visitors ("It must be checked against the live site's final analytics… before publication") | Source | `payload/content/privacy.html` lines 1, 4, 5, 7 |
| E12 | Old positioning still live in public: patriotwebsolutions.org `/offerings/`, `/shop/` indexed; old mission = "build professional websites," SEO services | Search results today | Live-site full crawl **not re-performed**; packet snapshots exist |
| E13 | Planning packet: 35-node DAG, adapt judgment, and an OPEN_OBLIGATIONS table that already names every missing owner fact | Read | `JUDGMENT.md`, `OPEN_OBLIGATIONS.md` |
| E14 | Forms are a competent custom implementation: nonce, honeypot, consent checkbox, mailto/phone fallback, no card data | Source | `includes/class-pws-forms.php` |
| E15 | Redirect/410 discipline, security headers, sitemap hygiene (users provider off, retired categories excluded) are implemented | Source | `class-pws-public.php:67-97`, `functions.php:32-57`, `ROUTE-MAP.md` |

**Not verified in this round:** `playground-e2e-receipt.json` contents, `PACKAGE_MANIFEST.json`, full text of `terms.html` / `accessibility.html`, `site.js`, admin screens, installer end-to-end flow, mobile rendering beyond the captured screenshot, a fresh full crawl of the production site. None of these change the verdict below; the terms page shares the drafting-note defect per its 2 grep hits (E4).

---

## 1. Verdict

**The current draft is a well-engineered apology for a website.** The plumbing is genuinely good — installer/rollback, redirect and 410 discipline, honest forms, security headers (E14, E15). The body is not shippable against the owner's brief. The owner's complaints ("too light, too repetitive, can't inspect the real work, doesn't persuade distinct audiences") are all **confirmed by evidence**, and there is a worse defect the owner didn't name: **the site withholds facts that are already publicly verified while promising to verify them someday.**

**Disposition: keep the engine, replace the body.** Retain the plugin infrastructure, routes, forms, and the base visual tokens. Rebuild the content model, page forms, copy, and evidence layer per sections 2–10.

### Ranked defects

**D1 — The portfolio cannot be inspected. (Fatal; the owner's core ask.)**
`/our-work/` renders three text-only cards from a hardcoded PHP array (E2). There is not one link to a repository, demo, screenshot, version, commit, or store listing anywhere in the release (E3). Meanwhile a genuinely inspectable flagship exists in public: `github.com/kb4beast/hive-mind-os` — MIT-licensed, ~850 commits, tests, benchmarks, docs (E5). The site's own headline is "Tools should show their evidence," directly above a component that shows none. The single highest-value fix in the whole project is linking the real repo with a real fact block.

**D2 — The honesty posture is miscalibrated: it withholds already-verified facts. (Fatal for the donor path.)**
Buried mid-checklist under "Before you give," the donate page says deductibility language "will be displayed only after the organization's current records are verified" (E7) — withheld *and* hidden, so a donor scanning for legitimacy finds neither a yes nor a clean no. The IRS-derived public record already verifies it: 501(c)(3), EIN 99-1238039, ruling March 2024, donations deductible (E6). To a donor, "we can't yet confirm our legal status" reads as *this may not be a real nonprofit* — the opposite of the intended honesty. The same over-withholding hides the org's founding year, GuideStar presence, and location story. Truthfulness was implemented as *refusal to state anything*, when the standard should be *state what is verified, with its source and date*.

**D3 — The dominant speech act is deferral. (The "we are building" disclaimer the owner banned.)**
14 deferral constructions across 11 of 16 pages (E4). Five of six homepage sections contain a promise to publish something later. Stories: "We will publish the first field note when…". Impact: zero numbers, all future tense — not even *live hours delivered since [date]*, which the org's own Mon/Wed/Fri schedule makes knowable. A site whose every section says "content pending verification" is indistinguishable from vaporware, however sincere.

**D4 — One template, sixteen pages. (The "repetitive" complaint, confirmed.)**
Every page is the same module chain: red uppercase kicker → serif aphorism H1 → lede → card grid → checklist → callout (E1 + all 16 files). The homepage alone stacks three separate 3-card grids. Sections are interchangeable across pages; nothing about the *form* of the learn page differs from the form of the impact page.

**D5 — Every headline is an abstract maxim. (The AI tell.)**
"Learn to use AI with skill, judgment, and confidence." "Impact should be more than a counter." "Tools should show their evidence." "Your pace belongs in the plan." "Start with the right conversation." Test: could the headline sit unchanged on any other org's site? Nearly every H1/H2 passes — which is a fail. Almost no heading contains a name, number, place, or artifact. The triad ("skill, judgment, and confidence") and the reversal aphorism are the two most recognizable LLM rhetorical fingerprints.

**D6 — There are no humans. (The owner asked for "visibly human.")**
No founder, no instructor, no student, no board member, no name anywhere except a support@ address and phone number. The only image on the site is one AI-style illustration captioned "Illustrative scene" (E1; `class-pws-public.php:61-65`). A nonprofit teaching *military families* — one of the most person-centered missions possible — presents zero people.

**D7 — Five of the seven required audiences have no destination.**
Learners get `/learn/` + `/join/`; donors get `/donate/`. Employers evaluating learner readiness, mentors/volunteers, community partners, technical reviewers, and platform (OpenAI/Google) reviewers have nothing addressed to them — `/get-involved/` is three generic cards, and there is no organization-identity block a platform reviewer could anchor on (E10).

**D8 — The learning program cannot be judged from the outside.**
`learn.html` is the best page in the draft — real module structure, honest 20–28-week core range, credible hour math. But there is still no week-by-week syllabus, no anatomy of the single hour, no sample of what a learner actually produces, no mapping from skills to job tasks, and no explanation of *how* readiness is demonstrated. An employer or skeptical learner has nothing concrete to evaluate.

**D9 — Machine-readable identity is too thin for the site's two stated compliance goals.**
JSON-LD is bare `Organization` with no address, `nonprofitStatus`, `taxID`, `sameAs`, or `logo`; no `og:image`; installer never manages `blogname`/`blogdescription` (E9, E10). For Google for Nonprofits review and OpenAI publisher/business identity, the site must be legible as *a specific legal nonprofit* to machines and reviewers, not just to sympathetic readers.

**D10 — Legal pages ship their own drafting notes.**
The privacy notice tells the public "It must be checked against the live site's final analytics… before publication" (E11). Terms shares the pattern (E4). A visitor — or an OpenAI/Google reviewer following the required privacy-policy link — reads a document that announces it is unfinished.

**D11 — Legacy commercial surface contradicts the new identity.**
The old `/shop/` and services positioning are still live and indexed (E12), and the release intentionally preserves shop/checkout routes pending review (ROUTE-MAP). Reasonable engineering, but until resolved the *public* identity is "web-design vendor with a shop," which is a live risk for Ad Grants review and confuses every one of the seven lenses.

---

## 2. Replacement positioning and narrative spine

### Positioning statement

> **Patriot Web Solutions is a 501(c)(3) nonprofit in Killeen, Texas that teaches military members, veterans, and their families to build and evaluate AI systems — and publishes its own work the way it teaches: with receipts.**

Why this wins: it is specific (place, legal status, audience), it is *already true and verifiable today* (E5, E6), and it converts the org's real oddity — an obsession with verification, embodied by a public repo whose literal tagline is "Don't trust your coding agent. Verify it." — into the brand instead of a disclaimer.

### Narrative spine: "Verification is a military habit."

Pre-flight checks, after-action reviews, maintenance logs, standards you demonstrate rather than claim — the military already trains the exact discipline that trustworthy AI work needs. The org teaches that discipline applied to AI, practices it in public (open repo, evidence ledger, dated records), and sells it (custom builds delivered with tests and documented limits).

This one spine serves every lens without cosplay:
- **Learner:** "You already know how to train to a standard. We map that habit onto AI skills employers need."
- **Donor:** "You fund instruction; we log it and show receipts, starting with our IRS record."
- **Employer:** "Graduates demonstrate readiness; here is the demonstration standard and a portfolio."
- **Client:** "We ship tools with evaluations and documented limits, not demos and hope."
- **Developer/platform reviewer:** "Here is the repo. Here is the org record. Check us."
- **Mentor/partner:** "Help people practice a discipline you respect."

Anti-cosplay rule: the spine may only appear where it attaches to an actual practice (a checklist, a log, a review record, a demonstration standard). Never as flags, eagles, stencil fonts, or rank imagery. If round-2 copy ever uses the spine decoratively, cut it. Fallback spine if the community reads it as forced: **"Show your work."**

### Voice

First-person plural, named humans, plain declaratives, Central-Texas specificity. Not "a patient, practical learning community" but: "Class meets Monday, Wednesday, and Friday for one hour. If a topic didn't land, we teach it again. Nobody gets dropped." Hedges are allowed exactly once per page, as a dated status line linking to the evidence ledger — never as inline boilerplate.

---

## 3. Information architecture and navigation model

Keep existing slugs (the release's redirect map is already reviewed; relabel in nav rather than re-slugging). Add four pages; delete none.

```
/                     Home — editorial front page
/learn/               The program (curriculum tables, session anatomy, job mapping)
/join/                Interest list (unchanged form, better framing)
/our-work/            Work — evidence-state index of projects        [nav label: "Work"]
  /our-work/hive-mind-os/            Project record (public, inspectable)   [NEW]
  /our-work/ai-developer-workbench/  Project record (historical, honest)    [NEW]
  /our-work/coupon-hive/             Project record (in development, log)   [NEW]
/solutions/           Custom builds (engagement model, fit/no-fit)
/impact/              Records & impact — THE evidence ledger lives here, once
/donate/              Support — deductibility, EIN, funding model
/about/               People, story, and the organization record block
/get-involved/        Partners — employers, mentors, community orgs  [nav label: "Partners"]
/stories/             Field notes — seeded with 3 real entries at launch
/contact/             Contact (role-routed)
/privacy/ /terms/ /accessibility/ /learner-code/   Trust cluster (finalized, cross-linked)
```

**Engineering note (caught by panel):** the current installer creates only flat pages — `wp_insert_post()` with no `post_parent` anywhere (`class-pws-installer.php`). The three nested project records therefore require a small installer change (a `parent` field in the manifest, resolved to the parent page ID, insertion-ordered). This stays in Phase A because it is a code change in this repo, but it is an installer change, not a content-only drop — plan and test it as such. Fallback if the change is unwanted: flat slugs `/project-hive-mind-os/` etc., at the cost of uglier URLs.

**Primary nav (5 + utilities):** Learn · Work · Solutions · Impact · About — with **Join** (outline) and **Donate** (solid red) as the persistent utility pair, exactly as the header already does. "Partners," "Field notes," "Contact," and the trust cluster live in the footer directory and in-page routes. Project records get breadcrumbs.

**Audience routing model:** do *not* turn the homepage into an audience card grid. Routing happens through a one-line-per-lens **"Start here" index** — plain text links, set like a table of contents — appearing once low on the homepage and repeated in the footer:

> Reading this as a **donor**? Start with [our records](/impact/) and [IRS status](/donate/).
> **Hiring** or building a workforce pipeline? See [what graduates demonstrate](/get-involved/).
> **Technical reviewer?** The repo is public: [hive-mind-os](https://github.com/kb4beast/hive-mind-os).
> …(7 lines total, one per lens)

---

## 4. Audience journeys and calls to action

| Lens | Entry | Proof they need | Path | Primary CTA (says what happens) |
|---|---|---|---|---|
| Military member / veteran / spouse | Home lead story, search, word of mouth | "People like me can do this at my pace; it leads to job-relevant evidence" | Home → Learn (session anatomy + job map) → Join | "Join the interest list — we reply when a cohort opens" · secondary: "Read a sample session log" |
| Donor | Home ledger excerpt, GuideStar | Legal status, what money funds, honesty about unknowns | Home → Impact (ledger) → Donate | "Give — donations are tax-deductible (EIN 99-1238039)" · secondary: "Read what we won't claim yet" |
| Employer / workforce partner | Partners page, LinkedIn later | What a graduate can demonstrably do | Partners → Learn §job-map → sample portfolio artifact | "Request a conversation about hiring-pipeline fit" |
| Business / nonprofit / creator (custom AI) | Home work exhibit, referral | Real shipped work, boundaries, process | Home → Work → project record → Solutions | "Book a discovery conversation" (states no checkout, written scope first) |
| Developer / technical reviewer | Repo → site, or Work page | Code, tests, limitations, versions | Work → hive-mind-os record → GitHub | "Inspect the repository" (external, labeled) |
| OpenAI / Google / platform reviewer | Domain root, /about/, policy links | Legal name, EIN, address, contact, finished policies, consistent identity | About §organization record + trust cluster | None — legibility is the conversion |
| Mentor / volunteer / community partner | Partners page | Defined roles, screening, supervision, time cost | Partners §volunteer → Contact (role-routed) | "Tell us your background — screening comes before any learner contact" |

Every CTA names its consequence; no bare "Learn more."

---

## 5. Page-by-page redesign

Each page gets a distinct **form**, its own proof objects, and one conversion goal. Modules marked ⛔ are owner-gated (section 10, Phase B/C); everything else is buildable now.

### Home — form: editorial front page (like a small journal, not a SaaS landing)
Purpose: establish "real org, real work, real discipline" in one screenful per audience; route.
1. **Lead unit:** kicker states audience + place ("Killeen, Texas · For military members, veterans, and their families"). H1 as one declarative with place and cadence, e.g. "We teach military families in Killeen to build and test AI tools — one live hour, three days a week." (My first draft of this example was itself a triad; see corrections ledger.) Sub: two plain sentences incl. Mon/Wed/Fri and no-flunk-out. Media: real photo ⛔ or the current honest illustration *with* its caption — acceptable interim.
2. **Ledger excerpt** (3–4 rows from /impact/): 501(c)(3) since March 2024 → IRS record link · hive-mind-os public → repo link · Class rhythm Mon/Wed/Fri → /learn/ · Rebuilt site, reviewed release → field note. Each row: fact, source link, "checked 2026-09-15."
3. **One annotated exhibit** (not a card grid): a single real artifact — e.g., a captioned excerpt of a hive-mind-os receipt or an annotated prompt-versus-eval comparison — with 3 margin notes explaining what the reader is looking at.
4. **Program excerpt:** first three rows of the actual curriculum table, then "Full path →."
5. **Signed note** ⛔: 120 words from the founder, first person, name and role beneath. This is the single highest-leverage humanization unit on the site.
6. **Start-here index** (§3) + footer.
Conversion: Join (primary), Donate (secondary). Kill: all three current 3-card grids; the "Impact should be more than a counter" section (its promise moves into ledger row form).

### Learn — form: syllabus document
1. Intro: who it's for, the weekly rhythm, the no-flunk-out rule stated as policy, not slogan.
2. **Inside one hour** — timeline of a real session ⛔ (until confirmed, label "our default session plan"): recap/teach-back → guided build → demonstrate & log. Include one **sample session log** excerpt ⛔.
3. **Curriculum tables** — per module (Foundations, Prompting & verification, Assistants/skills/plugins, Career & portfolio): week-by-week topic rows, the artifact each week produces, hours. Draftable now; owner reviews accuracy.
4. **Readiness demonstration** defined: "Explain it, do it, handle a variation — privately, untimed, repeated until solid." What passes, what triggers re-teach.
5. **What graduates carry:** portfolio contents list (task briefs, checked workflows, a documented skill with test cases, a truthful case study).
6. **Skills → job-task map** (table): e.g., prompt/verification → operations, admin, research support; evaluation/replay → QA and testing support; APIs/agents → junior automation work. Every row phrased "supports applications for," never "qualifies you for."
7. **Advanced paths** (keep current three) + **responsibility statement:** leakage, contamination, reward hacking, replay, and backtesting are taught as *evaluation literacy* — how to detect and prevent, bound by the learner code.
8. Status line + Join CTA.
Conversion: interest-list signup with a topic selected.

### /our-work/ (label: Work) — form: evidence-state index
Not cards — **record rows** grouped by evidence state: *Public & inspectable* · *Historical — record pending* · *In development*. Each row: name, one-line problem, state chip with date, link to full record. Intro explains the states in two sentences. The "complete project record" checklist stays, moved to the bottom as the standard each record follows.

### /our-work/hive-mind-os/ — form: project record (the template)
- **Fact block** (sidebar): Status: public, early prototype · License: MIT · Language: Python · Repo: github.com/kb4beast/hive-mind-os · Scale: ~850 commits · Contents: tests, benchmarks, docs, examples · Last verified: 2026-09-15.
- **What it is / why it exists** (3 short paragraphs, mission-linked: this is the verification discipline we teach).
- **What to look at first:** README, `docs/`, `benchmarks/` — one sentence each on what a reviewer will find.
- **Honest limits, quoted from the project itself:** "early prototype… production use not yet supported." Quoting your own README's caution is credibility, not weakness.
- **Annotated figure:** one real receipt-bundle excerpt with margin notes.
- CTA: "Inspect the repository" + "Discuss a build like this."

### /our-work/ai-developer-workbench/ — form: historical record (the honest-absence template)
- Fact block: Status: historical — no public listing found as of 2026-09-15 · Era: [year ⛔] · Evidence held privately: [screenshots/config ⛔].
- What it was, what it taught the org, and **"What would make this public again"** — a 3-item checklist (locate original listing/source ⛔, re-verify behavior, meet current publishing rules). A missing artifact presented as a dated record with a re-verification plan feels intentional; a card saying "public record pending" feels like vapor.

### /our-work/coupon-hive/ — form: development log
Fact block (status: in development, next milestone, last updated date) + short dated log entries. No promises, just the most recent真 entry. First entry can be written today from real design intent.

### Solutions — form: engagement prospectus
Keep the four-audience grid? **No** — replace with prose intro + **fit table**: "Good fit" (bounded workflows, internal knowledge tools, eval harnesses, documented skills/plugins/MCP tools…) vs "Not a fit" (bet-the-company automation, unsupervised agents over consequential actions, anything we can't test honestly). Keep the 4-phase engagement list (it's good). Add **deliverables ledger**: every engagement ships with tests/eval results, operating doc, limits doc, support terms. Add mission line ⛔ if true: fees fund instruction. CTA: discovery conversation.

### Impact — form: the evidence ledger (site's single source of truth)
1. **Verified today** table: legal status row (IRS record, GuideStar, EIN, ruling year, links), public code row (repo + stats + date), program rhythm row, release/receipt row (site rebuilt with reviewed release, sha256, date). Every row sourced and dated.
2. **Counting from [start date] ⛔:** live hours delivered, sessions held — the org can start logging *now*; publish the counter only with its start date and definition.
3. **Reporting rules** — keep the existing five verbatim; they are the best copy on the current site.
4. **What we will not claim yet:** employment outcomes, testimonials, historical totals — each with what evidence would unlock it. One section, once, instead of 14 scattered hedges.
5. **First public report** commitment with a target date ⛔.

### Donate — form: case + form
1. Open with verified facts: "Patriot Web Solutions is a 501(c)(3) (EIN 99-1238039, ruling year 2024). Donations are tax-deductible." Links: IRS/ProPublica record, GuideStar. ⛔ *one-glance owner confirmation that this EIN is theirs before publish; then Phase C adds IRS TEOS check + receipt language review.*
2. **What gifts fund** — the real cost structure of 3 live hours/week (instructor time, tools/API credits for learner practice, accessible materials, safe test environments). Real numbers ⛔; until then, categories only, no invented ratios.
3. The existing "Before you give" trust list, minus the deductibility-withholding line.
4. `[pws_donation]` embed (keep; fallback notice is fine).
5. Status line → ledger.
Conversion: completed gift; secondary "read the records first" (donors who check give bigger).

### About — form: story + record
1. **The story, with dates and people ⛔:** founded [year], by [name], why Killeen, why the pivot from websites to AI. Written as narrative paragraphs, not values cards.
2. **People ⛔:** name, role, one honest sentence, real photo or none — no headshot placeholders ever.
3. **Organization record block** (buildable now): legal name, EIN, 501(c)(3) status + ruling year, address ⛔ (city/state now), contact, policy links, GuideStar/GitHub `sameAs` links. This block is what an OpenAI or Google reviewer anchors on.
4. Practices (replaces the "values" card set): one short prose paragraph naming concrete practices — re-teaching until it lands, last-verified dates on project pages, consent before any story, every lesson tied to a real task. The four virtue nouns (Patience/Proof/Respect/Usefulness) are retired; presenting them as a labeled set is the concept-grid pattern §8 bans.

### Get-involved (label: Partners) — form: three offers, three forms of proof
1. **Employers & workforce:** embeds an excerpt of the skills→job-task map (canonical version on /learn/) plus the readiness-demonstration standard, then three concrete partnership shapes: review a graduate portfolio, host a mock interview, send us your real task list to teach against. Mechanic: CTA routes to /contact/ with a new "Employer / workforce partnership" option added to the topic select (one-line change in `class-pws-forms.php:64`). Without that routing line this journey dead-ends — caught by panel.
2. **Mentors & volunteers:** defined roles, screening/supervision policy stated plainly, time cost honesty (e.g., one hour/week alongside our schedule).
3. **Community organizations:** co-hosting, referrals, equipment.
No card grid; three titled prose sections with distinct CTAs.

### Stories (Field notes) — launch with 3 real entries, buildable now
1. "Why we rebuilt this website in public" — with real receipts (release sha256, route map, what got 410'd and why).
2. "What a hive-mind-os receipt actually proves" — annotated walkthrough (tool content, not navel-gazing).
3. "Designing a no-flunk-out curriculum for 3 hours a week" — the honest math behind 20–28 weeks.
Archive stays gated by `pws-field-notes` category (good mechanism; keep).

### Join / Contact — keep forms (E14). Join adds the expectations summary from learner-code. Contact adds role routing (topic select already exists) and drops "response hours will be published after staffing is confirmed" for a status line.

### Privacy / Terms — strip every drafting instruction (D10) ⛔ *final text depends on live config facts (analytics, processor)*; buildable-now version: rewrite in final voice with explicit "as of" date and the specific known stack, leaving no visitor-facing TODOs.

### Accessibility — form: commitment record
Purpose: make the accessibility promise checkable. Modules: target standard (WCAG 2.2 AA) stated as of a date; a short table of what has actually been tested (keyboard, focus, contrast, screen reader) with test dates; known gaps listed plainly; how to report a barrier and what happens next. Proof objects: the tested-criteria table; the skip link and focus styles already shipping. Conversion: a barrier report reaching a human.

### Learner code — form: signed community agreement
Purpose: show prospective learners and partners what the room feels like; it is the most human page in the current draft and the substance stays. Modules: a two-sentence "why this exists" opening (protecting the ability to ask questions and get it wrong); the existing seven commitments verbatim; who it applies to and how concerns are handled (⛔ exact procedures pre-cohort). Proof objects: the code itself, plus a link from /join/ so applicants read it before signing up. Conversion: none — its job is to make joining feel safe; measured by /join/ referrals from this page.

### 404 — add the footer directory and a link to the start-here index.

---

## 6. Portfolio and product evidence model

**Evidence states** (chips with dates, used on Work index + records):
- `Public & inspectable — verified <date>` requires a working public URL fetched on that date.
- `Historical — record pending` requires: what it was, era, where private evidence lives, and a re-verification checklist.
- `In development — last updated <date>` requires a dated log entry; goes stale visibly if not updated.
- `Planned` gets one line on the index, never its own page.

**Verified links available today (use these; nothing else):**
| Artifact | URL | State (2026-09-15) |
|---|---|---|
| Hive Mind OS repository | https://github.com/kb4beast/hive-mind-os | Public; MIT; Python; ~850 commits; tests/benchmarks/docs present (E5) |
| IRS nonprofit record | https://projects.propublica.org/nonprofits/organizations/991238039 | 501(c)(3), EIN 99-1238039, deductible (E6) |
| GuideStar profile | https://www.guidestar.org/profile/99-1238039 | Exists at same EIN (E6) — owner should claim/update the profile ⛔ |

**Found nothing public for** AI Developer Workbench, a Hive Mind OS GPT, or Coupon Hive (E8) — so the site links none of them. A "Patriot GPT" surfaced in search results is **someone else's product**; never reference it. Do not construct `chatgpt.com/g/…` URLs from memory; the ADW record page ships in its honest-absence form until the owner exports a real listing URL from the builder account ⛔.

**Verification maintenance:** add a link-check test to the repo's existing `tests/` + `scripts/` so every external URL in `payload/content/` is fetched in CI; every fact block carries `Last verified:`; a failed check flips the chip to "link failing — under review" rather than 404ing readers.

---

## 7. The learning program, presented so it can be judged

The current ranges (20–28 core weeks, 60–84 live hours; advanced 30–48 hrs/track; everything-path 54–74 weeks) are credible and survive review — **keep them**. What's missing is inspectable texture; section 5's Learn page provides the containers. The five artifacts that make the program judgeable, in priority order:

1. **One real syllabus table** (Foundations, weeks 1–6, topic + weekly artifact per row) — draftable now, owner-verified.
2. **One sample session log** ⛔ — even from a pilot session with the founder and one learner; redact names. This single artifact outweighs every paragraph of pedagogy prose.
3. **The readiness-demonstration standard** — half a page, buildable now.
4. **One complete learner-style case study** — the founder can produce one honestly *as the first learner* ("I built the interest-form workflow; here's the brief, the checks, the failure I kept").
5. **The skills → job-task map** — buildable now from public job-posting language; reviewed for overclaim ("supports applications for…").

Advanced topics (APIs, agents, MCP/tools, evaluation, replay, leakage, reward hacking, backtesting) are presented as **evaluation literacy with a defensive frame**: you learn reward hacking the way a maintenance chief learns failure modes — to catch it, not to do it — bound to the learner code's "use evaluation methods to find weaknesses, not to manipulate scores." That framing is already half-present (learner-code.html) and just needs to be surfaced on /learn/.

---

## 8. Visual and editorial design system (the anti-slop contract)

**Keep** (verified in `site.css`, screenshots): navy/paper/red/gold palette; Georgia display over Inter text; 4px radius; hairline-grid cards; the restrained flag-stripe header rule; visible focus styles; the skip link. This is already a defensible base — the slop is in *composition and copy*, not tokens.

**Add components** (each earns a distinct page form): ledger table · fact block (sidebar of labeled facts + verified date) · annotated figure (artifact + numbered margin notes) · syllabus table · timeline (session anatomy, engagement phases) · status chip with date · signed note (byline block) · development-log entry · start-here index. Retire: `pws-cards--3` as a default; the number-badge concept cards.

**Editorial rules — enforceable, testable:**
1. Every H1/H2 contains at least one concrete noun, number, name, or place. Ban abstract triads and "X. Not Y." reversals. Test: if the heading could ship on another org's site unchanged, rewrite it.
2. Maximum one card grid per page, and only for genuinely parallel *things* (three projects), never concepts (patience/proof/respect).
3. Every section contains ≥1 verifiable object — a link, date, artifact, or named person — or the section is cut.
4. One dated status line per page, linking to /impact/; zero inline "will be published once verified" boilerplate. (Current count to beat: 14 across 11 pages, E4.)
5. Numbers appear only with definition + date. Unknowns are stated once, as dated fact ("We have not yet published outcome data; first report targeted for ⛔"), not sprinkled.
6. Imagery: real photographs with consent, or honest artifact captures (terminal, receipt, code, whiteboard, the actual town), or nothing. No generated faces; no stock veterans; no flags-as-decoration; the existing illustration is acceptable only with its honesty caption and only until a real photo exists.
7. Buttons state consequences ("Join the interest list — we reply when a cohort opens"), never bare imperatives ("Learn more," "Get started").
8. Read-aloud pass on every page: any sentence you wouldn't say to a person in Killeen gets rewritten.

---

## 9. Mobile, accessibility, performance, SEO, and platform identity

- **Mobile:** the current mobile render is one undifferentiated column of bands (E1). New table/ledger components need explicit small-screen patterns: ledger and syllabus rows collapse to label-stacked pairs (CSS grid → block with `dt/dd`-style labels), fact blocks move above prose, annotated figures place notes below the figure. Longer concrete headlines mean tightening the H1 clamp (current 6.5rem max is aphorism-sized).
- **Accessibility:** keep skip link/focus/consent patterns (already good). Add: `caption` + `scope` on all new tables; status chips never color-only; timelines as ordered lists; WCAG 2.2 AA target stated on /accessibility/ with tested-criteria list; NVDA + keyboard pass in acceptance evidence alongside the existing screenshots.
- **Performance:** no framework needed — extend the single-CSS approach; explicit `width/height` on every figure; `loading="lazy"` below the fold; system serif stack already avoids font payloads; add a real `og:image` (E10) at fixed size.
- **SEO:** per-page titles exist (`title-tag`); meta descriptions exist (`functions.php:63-74`) — rewrite them to the new copy. Fix identity: set `blogname`/`blogdescription` via installer or PRELAUNCH checklist (E9). Upgrade JSON-LD to `NonprofitOrganization` with `address` ⛔, `nonprofitStatus: Nonprofit501c3`, `taxID: 99-1238039` ⛔(after owner confirm), `foundingDate`, `logo`, `sameAs: [GuideStar, GitHub]`. Keep the existing 301/410 and sitemap hygiene (E15) — it is genuinely good.
- **Google for Nonprofits / Ad Grants:** the 501(c)(3) prerequisite is satisfiable (E6). Site-quality risks to clear: thin repeated pages (fixed by this redesign), and the **live commercial surface** — `/shop/`, product routes, old service positioning (E12, D11). Recommendation: owner decides retire-vs-keep for commerce routes before applying; if kept for legacy transactions, exclude from sitemaps/nav and noindex ⛔. This review does **not** certify Ad Grants compliance — account-level review is Phase C, and the packet's OPEN_OBLIGATIONS row stands.
- **OpenAI publisher / business identity:** what the *website* can contribute: a stable /about/ organization-record block (legal name, EIN, location, contact), finished privacy policy at a stable URL (D10 fix), domain kept consistent with the builder-profile domain the owner verifies in their OpenAI account ⛔, and zero unverifiable claims on identity pages. Actual builder-profile domain verification, org verification, and GPT publication are account actions the site cannot perform (packet OPEN_OBLIGATIONS, E13). Do not print "OpenAI-compliant" anywhere on the site.

---

## 10. Prioritized implementation plan

**Phase A — buildable now, this repo, no owner input** (order = impact):
1. Hive Mind OS project record page + real repo link + fact block; Work index converted to evidence-state rows. Includes the installer `post_parent` change for nested records (§3 engineering note) with an install/uninstall test. (Kills D1.)
2. Impact page → evidence ledger with the four verified-today rows; delete the 14 scattered hedges, one status line per page. (Kills D2/D3 structurally.)
3. Homepage rebuild per §5 (ledger excerpt, exhibit, program excerpt, start-here index; grids removed). (Kills D4/D5 on the front door.)
4. Learn page: syllabus table draft, readiness standard, job-task map, responsibility statement. (D8.)
5. ADW + Coupon Hive record pages in honest-absence / dev-log forms. (D1 remainder.)
6. Copy pass site-wide under §8 rules; retitle every aphorism heading. (D5.)
7. Seed 3 field notes. (D3, D6 partially — the rebuild note has a human author.)
8. JSON-LD upgrade (minus taxID/address), og:image, blogname/tagline handling, link-check test in `tests/`. (D9.)
9. Partners page restructure; Solutions fit-table + deliverables ledger. (D7.)
10. Strip drafting notes from privacy/terms into an internal checklist file; interim finalized text. (D10 visitor-facing part.)

**Phase B — owner facts required (site blocks reserved, shipped as they arrive):** confirm EIN is theirs (10 seconds — unblocks deductibility line + taxID) → founder signed note, names/photos/consent → founding story dates → exact class time/timezone → sample session log → ADW era + any original listing URL from the ChatGPT builder account → funding cost numbers → street address for schema → first-report target date → shop retire/keep decision.

**Phase C — external account checks (cannot be done from this repo; already itemized in packet OPEN_OBLIGATIONS, E13 — do not duplicate that work):** IRS TEOS pull + qualified review of receipt/deductibility language; GiveWP/processor configuration on production; Google for Nonprofits enrollment + Ad Grants review; OpenAI builder-profile domain verification; Hostinger staging rehearsal of the release; GuideStar profile claim.

Dependency note: nothing in Phase A waits on B or C. Phase A alone resolves the owner's stated rejection ("too light, repetitive, can't inspect the work"); B makes it human; C makes it certified.

---

## 11. Self-challenge — attacking this proposal

Ran the refute-first pass on my own draft; corrections were made in place and are logged here.

**Challenge 1 — "Your evidence ledger is just new slop."** Tables-with-dates could become as monotonous as card grids. *Held with constraint:* the full ledger exists exactly once (/impact/), excerpted once (home); everywhere else facts live inline in prose or fact blocks. §8 rule 2/3 applies to ledgers too.

**Challenge 2 — "You're overclaiming the 501(c)(3), the exact sin you condemn."** ProPublica reflects IRS bulk data; name+city match Killeen; but I have not seen the owner's determination letter, and BMF snapshots lag. *Survives with a gate:* the claim ships only after a one-glance owner confirmation (Phase B item 1), with IRS TEOS verification in Phase C. Auto-revocation risk is structurally low (ruling 2024; three years of non-filing required). The current draft's *refusal* to state it remains wrong either way — the record is public.

**Challenge 3 — "The flagship repo is a 0-star self-described prototype; showcasing it hurts credibility."** *Partially conceded, reframed:* for the technical and platform lenses, working code with tests and honest limits beats polish; the record page quotes the repo's own caution rather than hiding it. Mitigations: last-verified stamps + CI link check in case the repo moves or goes private; the record never claims adoption, only inspectability.

**Challenge 4 — "A site writing field notes about its own website is navel-gazing."** *Conceded, capped:* exactly one rebuild note; the other two seeds are tool content and program content.

**Challenge 5 — "Your session anatomy is invented — fabrication with better manners."** *Conceded fully:* every invented-texture item (session plan, session log, cost structure, founding dates) is marked ⛔ owner-gated in §5/§7/§10 and ships labeled "default plan" or not at all until confirmed. The syllabus tables are drafts *for owner review*, and say so.

**Challenge 6 — "Seven audiences on a tiny org's site = fragmentation."** *Held:* the plan adds only four pages (three project records + none new for audiences; Partners reuses /get-involved/). Lenses are served by proof objects and the start-here index, not page count.

**Challenge 7 — "'Verification is a military habit' is cosplay with extra steps."** *Held with tripwire:* the spine only appears attached to a practice (§2 anti-cosplay rule), and the fallback ("Show your work") is pre-approved. If the owner's community reads it as forced in round 2 testing, switch without ceremony.

**Challenge 8 — "You were told to refute the draft — did you steelman it anywhere?"** Yes, and it changed the verdict: the plugin engineering, redirect/410 hygiene, forms, security headers, sitemap hygiene, the learn-page hour math, the impact reporting *rules*, and the learner code are all kept in the redesign. The draft's failure is rhetorical and evidential, not infrastructural — hence "keep the engine, replace the body," not "start over."

**Corrections ledger (changes made during this review):**
- Reversed my initial "rename `/our-work/` → `/work/`" — re-slugging a just-reviewed redirect map is churn; relabel in nav instead (§3).
- Softened my initial "installer fails to set blogname = release defect" — production keeps its existing option; the real gap is the unmanaged tagline/og identity (E9 → §9).
- Downgraded "publish the court packet as a transparency artifact" to "one curated field note" — the packet contains internal process detail the owner hasn't cleared for publication.
- Withdrew an early idea to link a "Patriot GPT" store listing found in search — it is an unrelated third party's product (E8). This is exactly how fabricated portfolio links happen; recorded here as a caught near-miss.

**Panel round (independent adversarial reviewer, 2026-09-15) — six refutations landed; all fixed in place:**
- *Blocking:* nested `/our-work/<project>/` pages were presented as content-only "buildable now," but the installer creates flat pages exclusively (`wp_insert_post` with no `post_parent` anywhere). Fixed: §3 engineering note + Phase A item 1 now scope the installer change and test; flat-slug fallback documented.
- *Material:* E2's line citation blurred the data array (`:43-47`) with its render loop (`:through 59`). Fixed in the ledger.
- *Material:* my proposed replacement H1 example was itself a triad — the exact pattern §8 bans. Replaced with a single declarative carrying place and cadence. The About "values" rewrite also still presented four virtue nouns as a labeled set; retired.
- *Material:* `learner-code` (and `accessibility`) lacked the full page treatment (form / proof objects / conversion) that §5 promises for every route. Both now have one.
- *Minor:* E7/D2 implied the deductibility-withholding sentence was prominent; it is buried mid-checklist. Reworded — accurately, this is worse (withheld *and* hidden).
- *Minor:* the §4 employer journey had no §5 implementation — the job-map excerpt and a contact-topic routing option are now specified in the Partners treatment.
The panel also re-verified and confirmed: E3, E4 (14 hits / 11 of 16 files), E9, E10, E11, and all spot-checked quotes. Reversals recorded rather than cleaned; that the panel caught the author's own slop-relapse is the process working.

**What would change this review's verdict:** a public ADW listing URL from the owner's account (upgrades D1 remedy), evidence the EIN is not theirs (rewrites §5 Donate and §10), or owner rejection of the verification spine (fallback engages, §2).
