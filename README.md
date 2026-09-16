# Patriot Web Solutions website release

This repository builds a single WordPress plugin ZIP for the existing Hostinger WordPress site. The plugin contains the custom theme, page content, safe redirects, forms, setup preflight, and scoped rollback.

The release preserves WordPress, GiveWP, WooCommerce, users, orders, donations, and existing transactional routes. Activation changes nothing public. An administrator must review preflight and apply the release on staging.

## Build

```powershell
powershell -NoProfile -File scripts/Build-Release.ps1
```

The finished upload is `dist/patriot-web-solutions-release-2.0.0.zip`. Upload it through WordPress **Plugins → Add New → Upload Plugin**. Follow `INSTALL.md` inside the archive.

## Validation

```powershell
npm test
npm run verify:package
npm run test:e2e
```

WordPress/PHP integration is exercised with the pinned WordPress Playground runner described in `tests/README.md`. Production donation, mail, account, and rollback testing requires an authorized Hostinger staging copy with sanitized/private data controls.

## Visual direction

Release 2.0 uses a type-led dark navy layout with restrained patriotic red and blue, distinct page structures, and no stock or AI-generated imagery. A real class photograph appears only when a consented owner fact is supplied. It avoids fake counters, fabricated testimonials, and unverified outcome claims.
