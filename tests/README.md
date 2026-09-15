# Validation

`node tests/validate-release.mjs` verifies content coverage, required program language, safe route handling, form/installer controls, responsive/accessibility CSS, absence of remote style assets, risky PHP functions, and release-manifest hashes.

For clean WordPress integration, use WordPress Playground with Node's system CA enabled. The tested flow installs the exact release ZIP on WordPress 6.5 / PHP 8.1, activates the plugin, opens its authenticated setup page, resolves preflight, applies the release, and checks public routes and rollback. This is local compatibility evidence; Hostinger staging remains required for GiveWP, payment, mail, private accounts, caches, and restore.

