# Prelaunch facts and owner checks

Complete these checks on Hostinger staging before production. They depend on current private records or account access and are deliberately not guessed in this package.

## Owner-fact register (`payload/facts.json`)

Visitor-facing copy renders complete without any of these keys; each key is an internal obligation, and adding it enriches the site. Never add a key before the underlying fact is verified.

| Key | Unlocks | Verification required first |
| --- | --- | --- |
| `org_status_confirmed` (`"true"`) | JSON-LD `taxID`/`nonprofitStatus`/`foundingDate` gate | Owner confirms EIN **and** determination letter or IRS TEOS/Pub 78 check recorded with date |
| `org_status_statement` | Categorical status sentence replacing the qualified EIN row | Same gate, plus qualified review of wording |
| `org_tax_id` / `org_nonprofit_status` / `founding_date` | Structured-data fields | Same gate |
| `receipt_language` | Donation receipt/deductibility language on `/donate/` | Qualified review of receipt wording |
| `street_address` | Street line in JSON-LD address | Owner confirms publishable address |
| `class_time_ct` | Exact Central Time meeting times | Cohort schedule fixed |
| `founder_note` / `founder_name` | Optional signed quotation (the `[pws_founder_note]` shortcode; the founder section itself is already published) | Founder writes and approves it |
| `adw_era` / `adw_listing_url` | ADW record enrichment; listing URL flips its chip to public if it passes the link check | Located from the builder account and re-verified |
| `cost_structure` | Mission-cost line on `/solutions/` | Cost evidence retained |
| `first_report_target` | First-report date on `/impact/` | Reporting period committed |
| `hero_photo` (image URL) / `hero_photo_alt` | Real class photograph in the home hero (type-only until set) | Written consent from every person shown; image uploaded to the media library |
| `founder_name` | Signature line under the founder note | Founder approves |

## Drafting reviews moved out of visitor copy

- Reconcile the privacy notice against the live site's final analytics, donation, account, email, and hosting configuration; the final provider list, roles, international transfers, and retention settings must match the live configuration.
- Have the terms of use reviewed against the organization's verified legal identity and actual operations; add governing-law, dispute, refund, tax, and formal legal-entity language only after that review.
- Review and publish the three seeded field-note drafts (or leave them as drafts); they ship unpublished.
- Owner accuracy review of the six-week foundations syllabus before enrollment opens.

## Third-party listings read on 2026-09-16

- ProPublica Nonprofit Explorer (IRS-derived) lists Patriot Web Solutions, Killeen, TX, EIN 99-1238039, "Designated as a 501(c)(3)", "Tax exemption issued: March 2024", donations tax deductible. The site attributes these statements to the listing and does not restate them in its own voice until `org_status_confirmed` is recorded.
- Candid (formerly GuideStar; app.candid.org profile 15321808) lists the same name, EIN, location (ruling year 2024, NTEE P20) and a street address. The street address is not published by the site; add `street_address` only if the owner wants it public.
- Results are recorded in `payload/link-check-receipt.json`.

## Organization and program facts

- Confirm the exact legal entity name, jurisdiction, nonprofit/tax-exempt status, EIN display decision, donation deductibility language, and required state solicitation notices with qualified counsel or the organization's records.
- The president and founder, Brian Espinosa, is named on the home and About pages and in the organization's structured data at the owner's direction (software engineer, family man, forward AI thinker — no other biographical claims). Publish any further leadership, board, photographs, or contact details only after identity, role, biography, image rights, and contact details are approved.
- Set the next cohort's exact start date, Central Time meeting times, remote/in-person format, eligibility, capacity, price (if any), age policy, accessibility accommodations, recording policy, equipment expectations, and response time.
- Add measured impact only from retained records. Record the metric definition, period, source, denominator, and responsible owner. Obtain written consent before publishing a learner story, photo, or quotation.

## Product evidence

- Add public, stable links and evidence for Hive Mind OS, AI Developer Workbench, Coupon Hive, and later tools only after each owner approves the description, status, license, privacy behavior, support boundary, and source location.
- Do not describe an internal prototype as a published GPT, app, plugin, skill, customer deployment, or proven outcome without a verifiable public record.

## WordPress and Hostinger

- Create and restore-test a current files-and-database backup. Use a Hostinger staging copy for acceptance.
- Inventory all installed themes/plugins, versions, active licenses, custom snippets, redirects, cron jobs, mail configuration, DNS records, verification files, caching layers, analytics, forms, users, orders, donations, refunds, subscriptions, and account routes.
- Review every existing-page conflict shown by the installer before selecting replacement. Export any content that needs separate archival treatment.
- Confirm HTTPS, canonical host, sitemap, robots behavior, 404/410 responses, metadata, structured data, cookie behavior, and mobile/keyboard/zoom accessibility after clearing Hostinger and CDN caches.

## Forms, donations, and accounts

- Configure an authenticated mail provider supported by the live WordPress environment. Test delivery, failure handling, spam controls, reply routing, retention, and deletion requests without submitting sensitive data.
- Configure the intended GiveWP form and run its supported sandbox/test flow for success, cancel, failure, receipt, refund, recurring behavior (if used), and donor dashboard access. Confirm processor webhooks and privacy disclosures.
- Verify WooCommerce and any legacy account, order, basket, checkout, password-reset, download, refund, and subscription obligations before retiring or redirecting a route.

## Google and OpenAI readiness

- Google Ad Grants: verify current program eligibility, active nonprofit validation, conversion tracking, landing-page quality, geographic targeting, account structure, and current policy requirements in the organization's Google account. Approval cannot be guaranteed by site code.
- OpenAI: verify the current organization/business verification, builder profile, domain verification, privacy URL, terms URL, support contact, data handling, authentication, action/API behavior, brand use, and publishing requirements for the specific product. Do not claim OpenAI endorsement or partnership.
- Recheck both providers' current official requirements immediately before application or publication; account-level reviews and policies can change.

Production approval should record who completed each check, when, the evidence location, and the rollback owner.
