# Claude redesign integration — release 2.0.0

The owner supplied two downloaded 2.0.0 ZIPs. The standalone installer was an earlier build and still carried a phone number that the later owner conversation removed. The later wrapper contained a nested installer with the corrected Candid URL and phone removal; its 51 payload digests matched its manifest. Only that later installer was imported into this repository. The wrapper's private hosting handoff was not published.

The imported source was rebuilt using this repository's release scripts and WordPress Playground harness. Integration fixes:

- Let long email addresses wrap inside cards and footer columns, with a 320px visibility check.
- Raise dark-theme red text contrast after axe found failures on form actions and project fact cards.
- Use a present-state notice when no live donation form is configured.
- Update versioned build paths, source checks, and the Candid evidence-link allowlist.

Acceptance evidence is in `docs/ACCEPTANCE.md` and `dist/`. The package's staging and production instructions are in `release-plugin/patriot-web-solutions/INSTALL.md`.

## Hostinger staging correction — release 2.0.2

The first real theme switch kept the old WordPress primary menu. The installer now records the intended theme menu location and completes it after WordPress finishes switching themes. A second staging check found the preserved WooCommerce account page functional but visually unfinished, so the bundled theme now gives it a page title, compact account navigation, and a readable content panel. The footer email address breaks at `@` when its column is narrow. Staging rollback and clean 2.0.2 apply verified these changes.
