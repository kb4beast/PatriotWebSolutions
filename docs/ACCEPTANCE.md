# Release 2.0.2 acceptance record

## Artifact

- WordPress installer plugin with bundled theme and reversible content migration
- Public activation side effect: none; an administrator must run preflight and apply
- Content: 18 routes, 12 reviewed redirects, 11 intentional 410 responses, 13 preserved transactional/account routes
- Minimum tested baseline: WordPress 6.5 and PHP 8.1

## Automated evidence

- Static content, route, security-control, and policy-claim checks: pass
- Manifest file size and SHA-256 verification: pass
- ZIP top-level directory, path safety, entry count, byte count, and per-file SHA-256 verification: pass
- Fresh WordPress browser test: pass
- Desktop and 390-pixel mobile renders: 36 screenshots captured
- All 18 expected routes: HTTP 200 with expected page headings
- 36 desktop/tablet axe checks: zero violations; desktop, tablet, and mobile overflow: pass
- About, Contact, and Donate email-link visibility at 320 pixels: pass
- Reviewed redirect: HTTP 301 to the intended destination
- Retired placeholder: HTTP 410
- Form nonce and honeypot controls: present
- Existing page migration: explicit authorization, replacement, scoped rollback restoration, and reapply pass
- Legacy posts: Stories archive and post sitemap are restricted to the dedicated reviewed Field Notes category
- Browser console errors: zero in the automated route run
- Dependency audit at moderate severity threshold: zero known vulnerabilities at test time

Machine-readable receipts are in `dist/release-receipt.json`, `dist/playground-e2e-receipt.json`, and `dist/link-check-receipt.json`.

## Hostinger staging evidence (2026-09-16)

- A Hostinger staging copy was created after a live-site files-and-database backup.
- WordPress 6.5.10, PHP 8.1.34, writable theme destination, post-name permalinks, a configured contact recipient, and published GiveWP form 2768 passed the installer preflight.
- The installer replaced the three reviewed page conflicts (Home, About, Contact) and created its rollback snapshot. Rollback restored the prior staging homepage; the final 2.0.2 release was then applied cleanly.
- The real theme switch initially retained the former navigation. Release 2.0.2 now finalizes its dedicated menu after WordPress completes the switch; the staging location shows `Patriot Web Solutions Primary` without a manual assignment.
- All 18 redesigned routes were inspected on staging. The GiveWP form iframe renders on Donate; the preserved WooCommerce account dashboard and links render in the new theme. No payment or form-message submission was made.

## Boundaries requiring staging evidence

The local test environment does not contain the private Hostinger database, installed production plugin/theme versions, Hostinger/LiteSpeed/CDN cache, DNS, mail transport, GiveWP, WooCommerce, payment credentials, processor webhooks, real donor/order/account records, analytics, or account-level Google/OpenAI review state. Those checks are listed in `PRELAUNCH-CHECKLIST.md` and must be completed on a current Hostinger staging copy before production apply.

The release artifact does not establish tax status, process a live payment, submit an external application, publish an OpenAI product, or obtain Google Ad Grants approval. Production deployment requires a current backup, Hostinger preflight, and transaction-specific verification.
