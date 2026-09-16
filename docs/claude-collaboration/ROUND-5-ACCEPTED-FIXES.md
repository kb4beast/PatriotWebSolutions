# Round 5 — accepted fixes and implementation directive

Resume the same Fable 5 collaboration at maximum effort. Implement this complete, consolidated fix set in the current Patriot Web Solutions repository. Do not pause between changes and checks. Do not ask the owner questions. Do not touch production or the Hive Mind OS repository. Preserve the safe, reversible installer and all evidence gates.

## Accepted from Round 4

Implement R4 Fixes 1–8, with these refinements:

1. Replace the global `body { overflow-wrap: anywhere; }` with targeted wrapping and transform the ledger/syllabus into readable label-stacked mobile rows. Preserve table headers for assistive technology. Add a mobile canary assertion for the impact row width and confirm the long Hive Mind OS path still cannot overflow.
2. Change the global join labels to `Join the list` in the header and `Join the interest list` in the footer.
3. Make Donate truthful in both GiveWP and fallback states. Extend the deferral/banned-language validator to visitor-facing PHP strings.
4. Add the volunteer/mentor form topic and a real CTA from the volunteer section.
5. Change the employer claim to the truthful design tense: `The program is built so learners finish with artifacts, not just certificates:`.
6. Keep one hero-image disclosure, not two.
7. Shorten the home organization proof line while retaining the full qualified language on About, Donate, and Impact.
8. Capture console error URLs, fail unexplained 404s, and produce visitor screenshots without the WordPress admin bar.

For the favicon, do **not** set or overwrite WordPress's `site_icon` option. Add a theme-level favicon link using the shipped logo only when WordPress has no configured site icon. This keeps installation reversible and respects an existing site identity.

## H1 and metadata decision

Accept the goal of R4 Fix 9, with safer final copy:

- About H1: `The record behind Patriot Web Solutions.`
- Contact H1: `Learner, donor, employer, or client: start here.`

Do not promise `a reply from a person` because response staffing is not verified. Update the e2e H1 map and meta descriptions accordingly. Replace the Coupon Hive meta-description phrase `we are building in the open` with the dated-record wording proposed in Round 4. Replace About metadata that promises `Who runs` the organization while no named steward is published.

## Additional evidence corrections from Codex review

1. `get-involved.html` uses the obsolete local installation name `Fort Cavazos`. Replace it with `Fort Hood`. This was verified against current official U.S. Army material stating the name was restored June 11, 2025. Do not add a new external link.
2. Privacy currently says the site runs without optional analytics or advertising measurement. A portable package cannot prove which plugins, host services, or tools are enabled on an existing Hostinger site. State only that this release package does not add optional analytics or advertising measurement, and explain that the notice must be updated before the owner enables either.
3. Privacy says the program currently enrolls adults only, but no owner evidence establishes that policy. Remove the enrollment-age claim. Retain the rule against submitting sensitive military, medical, identity, or children's information through public forms, using accurate neutral language.
4. Accessibility says the release process includes screen-reader checks although the current receipt does not prove a screen-reader pass. Rewrite the paragraph so every claimed check is backed by this release. Add automated axe checks for every public route and retain the honest statement that automated checks do not replace testing with people. Do not claim full WCAG conformance.

## Full-route visual and accessibility gate

The owner asked that every page and child page be considered. Expand the screenshot matrix from six representative routes to all 18 public routes in `content.json`, at 1440px and 390px, for 36 visitor-only screenshots. Use stable names. Assert that the admin bar is absent from screenshots and that every route has no horizontal overflow.

Add `@axe-core/playwright` and run WCAG A/AA checks against all 18 routes in the visitor context. Fix genuine violations in release-controlled markup/CSS. If an unavoidable WordPress/Playground-only issue remains, document the exact rule, target, and reason in the receipt; do not silently suppress it.

Identify the exact prior 404 URL. The final browser receipt should contain an empty console-error list. If the favicon link does not eliminate it, diagnose and fix the actual asset request rather than allowlisting it.

## Completion gate

In one uninterrupted pass:

1. implement the accepted changes;
2. run `npm test`;
3. run the network link verifier;
4. build the release zip;
5. run the exact archive verifier;
6. run the full 18-route browser, axe, rollback, and reapply suite;
7. inspect at minimum the regenerated home, learn, work, project, impact, donate, solutions, partners, about, contact, privacy, terms, accessibility, and both historical/in-development project pages at desktop and mobile;
8. write `docs/claude-collaboration/ROUND-5-IMPLEMENTATION-REPORT.md` with changed files, test receipts, remaining owner-gated facts, and a candid self-critique.

Stop only when the package and receipts are green. Do not call the result approved; Codex will independently review the final pixels and run the release gate again.
