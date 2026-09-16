# Scoped rollback

Use **Tools → Patriot site release → Roll back release**. The rollback restores the prior theme, front-page settings, posts page, and menu locations. Replaced pages return to their captured title, content, excerpt, status, and menu order only when a fingerprint of all five release fields is unchanged. Pages created by this release move to Trash under the same condition. Modified release pages stay published for review rather than being overwritten or deleted.

The rollback intentionally preserves users, donations, orders, GiveWP/WooCommerce settings, inquiries, and all other database records. Deactivate the installer plugin only after verifying the restored site.

Rollback leaves the release theme files on disk. If this apply retired an earlier inactive release directory (renamed to `patriot-web-solutions-retired-<timestamp>`), the rename is reported in the rollback result and can be reversed by renaming the directory back once the current release theme is removed; theme files are never deleted by the installer.

Use a full Hostinger files-and-database restore only for disaster recovery. Before restoring an older database, reconcile every donation, order, account change, and form submission received after the backup. Blindly restoring a stale database can lose real transactions.
