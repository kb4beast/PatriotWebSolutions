# Install on Hostinger WordPress

## Before installation

1. Create a current Hostinger backup of both files and database. Confirm you can restore it.
2. Use a Hostinger staging site first. Do not use a copied staging database to overwrite production after new donations or orders have arrived.
3. Record active theme, WordPress/PHP versions, GiveWP form and payment mode, WooCommerce account/order routes, mail delivery, redirects, and any domain-verification files.
4. Update or isolate vulnerable software based on actual installed versions and authoritative advisories.

## Upload and preflight

1. In WordPress, open **Plugins → Add New → Upload Plugin**.
2. Upload `patriot-web-solutions-release-2.0.2.zip`, install it, and activate it.
3. Activation does not change the public site. Open **Tools → Patriot site release**.
4. Set the GiveWP form ID if more than one published donation form exists. Confirm the form email recipient.
5. Resolve every failed preflight. For existing-page conflicts, inspect each listed route and select the explicit replacement checkbox only after confirming the release copy is correct. The installer captures the original fields in its rollback snapshot and asks WordPress to retain a revision.

## Upgrading a staging site that already has release 1.0.0 applied

1. Open **Tools → Patriot site release → Roll back release** while the 1.0.0 plugin is still installed. This restores the prior theme and menu and trashes the unmodified 1.0.0 pages (modified pages are preserved for review).
2. Replace the plugin with the 2.0.2 upload (WordPress offers "Replace current with uploaded"). Public shortcodes, redirects, and security headers stay registered while any applied version is recorded, so the site does not degrade between upload and apply.
3. Run preflight again. The 1.0.0 theme directory is left on disk by rollback; because it is inactive and carries this theme's header, apply renames it to `patriot-web-solutions-retired-<timestamp>` and installs 2.0.2. An active or foreign directory of the same name still blocks apply, as before.
4. Any 1.0.0 pages preserved by rollback appear in preflight as existing-page conflicts; review each before selecting replacement.

## Apply and verify on staging

1. Select **Apply release** once preflight is clear. The action installs the bundled theme, creates missing pages, replaces only the explicitly approved conflicting routes, creates a dedicated menu, sets the homepage, and enables only the reviewed redirect set.
2. Clear WordPress, LiteSpeed, CDN, and browser caches.
3. Check every route listed in `ROUTE-MAP.md` on desktop and mobile. Use keyboard-only navigation and 200% zoom.
4. Submit learning and contact forms with test data. Confirm delivery, success, validation, rate-limit, and mail-failure behavior. Do not use real sensitive data.
5. Put GiveWP in its supported test/sandbox mode. Verify donation start, success, cancellation/failure, receipt, and donor dashboard behavior. Do not use a live donation for QA.
6. Verify existing WooCommerce accounts, lost-password, orders, basket, checkout, and legacy obligations before retiring any commerce route.
7. Verify WordPress sitemap, robots behavior, HTTPS, canonical URLs, metadata, structured data, error pages, and security headers.
8. Review Privacy and Terms against the actual legal entity, vendors, data flows, age policy, donation status, refunds, services, and jurisdictions. Replace the pre-publication qualification language only with reviewed facts.

## Production

Repeat preflight on the current production database. Apply only after staging acceptance and an immediately current backup. Preserve DNS/MX/TXT records and payment/webhook configuration. After apply, repeat the critical route, form, donation, account, cache, and HTTPS checks.

The release ships without a hero photograph: the home page renders type-only until the owner records a consented image URL in `payload/facts.json` (`hero_photo`, optional `hero_photo_alt`). No AI-generated imagery is included.

The package does not configure payment credentials, send a real transaction, establish tax status, obtain Google Ad Grants, verify an OpenAI organization, or publish an OpenAI product.
