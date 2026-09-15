# Data-flow inventory

| Flow | Data | Recipient/storage | Release behavior |
| --- | --- | --- | --- |
| Learning interest | Name, email, optional phone, interests, optional connection, message, consent | Sent through configured WordPress mail to configured recipient; not stored by this plugin | Nonce, honeypot, hourly IP-derived transient rate limit, server validation |
| General contact | Name, email, optional phone, topic, message, consent | Sent through configured WordPress mail to configured recipient; not stored by this plugin | Same controls as interest form |
| Donation | Determined by installed GiveWP and its configured processor | Existing GiveWP/processor data stores | Existing published form is embedded; no payment settings or records changed |
| Accounts/orders | Determined by existing WordPress/WooCommerce setup | Existing WordPress/WooCommerce data stores | Preserved; no migration or redirect of transactional routes without review |
| Analytics/ads | None added by this release | None | Configure only after vendor, consent, privacy, and conversion requirements are approved |
| Installer | Page IDs, release version, prior theme/front-page/menu settings | WordPress options and post metadata | Supports idempotence and scoped rollback; contains no credentials |

The final privacy notice must be reconciled with the actual live plugins, hosting logs, backups, email system, processor, analytics, consent tooling, retention, and applicable rights.

