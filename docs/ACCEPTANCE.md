# Release acceptance record

## Artifact

- WordPress installer plugin with bundled theme and reversible content migration
- Public activation side effect: none; an administrator must run preflight and apply
- Content: 15 routes, 12 reviewed redirects, 11 intentional 410 responses, 13 preserved transactional/account routes
- Minimum tested baseline: WordPress 6.5 and PHP 8.1

## Automated evidence

- Static content, route, security-control, and policy-claim checks: pass
- Manifest file size and SHA-256 verification: pass
- ZIP top-level directory, path safety, entry count, byte count, and per-file SHA-256 verification: pass
- Fresh WordPress browser test: pass
- Desktop and 390-pixel mobile render: captured
- All 15 expected routes: HTTP 200 with expected page headings
- Reviewed redirect: HTTP 301 to the intended destination
- Retired placeholder: HTTP 410
- Form nonce and honeypot controls: present
- Existing page migration: explicit authorization, replacement, scoped rollback restoration, and reapply pass
- Legacy posts: Stories archive and post sitemap are restricted to the dedicated reviewed Field Notes category
- Browser console errors: zero in the automated route run
- Dependency audit at moderate severity threshold: zero known vulnerabilities at test time

Machine-readable receipts are in `dist/release-receipt.json` and `dist/playground-e2e-receipt.json`.

## Boundaries requiring staging evidence

The local test environment does not contain the private Hostinger database, installed production plugin/theme versions, Hostinger/LiteSpeed/CDN cache, DNS, mail transport, GiveWP, WooCommerce, payment credentials, processor webhooks, real donor/order/account records, analytics, or account-level Google/OpenAI review state. Those checks are listed in `PRELAUNCH-CHECKLIST.md` and must be completed on a current Hostinger staging copy before production apply.

No production deployment, live payment, external application, OpenAI publication, or Google Ad Grants submission is part of this artifact.
