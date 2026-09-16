# Route map

## New or replaced pages

`/`, `/learn/`, `/join/`, `/our-work/`, `/solutions/`, `/impact/`, `/about/`, `/get-involved/`, `/donate/`, `/contact/`, `/stories/`, `/privacy/`, `/terms/`, `/accessibility/`, `/learner-code/`

Project records nested under Work (manifest `parent` key; parents are created first, children roll back first): `/our-work/hive-mind-os/`, `/our-work/ai-developer-workbench/`, `/our-work/coupon-hive/`.

## Primary menu

Created by the installer from `payload/content.json` → `primary_navigation`: Learn, Work, Solutions, Impact, About, Partners. Header actions link to `/join/` and `/donate/`; Contact, Field notes, and the policy pages live in the footer. Route set is unchanged from 1.0.0.

## Exact permanent redirects

- `/offerings/` → `/solutions/`
- `/donation-page/`, `/donations/`, `/donations/donation-form/` → `/donate/`
- `/privacy-policy-for-patriot-web-solutions/` → `/privacy/`
- `/terms-conditions/` → `/terms/`
- malformed contact routes → `/contact/`
- `/category/blog/` and `/2024/04/` → `/stories/`
- organization author archive → `/about/`

## Intentional 410 responses

`/post-1/`, `/post-2/`, `/post-3/`, the six repetitive legacy website/SEO articles listed in `payload/redirects.json`, `/category/general/`, `/2023/07/`. The database records are retained; these public URLs return 410 because no individually equivalent successor was verified.

## Field Notes publication control

The Stories archive and post sitemap include only posts assigned to the **Field Notes — reviewed** category (`pws-field-notes`). Existing posts remain in WordPress but are not surfaced by the new archive. Assign the category only after the author, sources, permissions, project status, and claims have been reviewed.

The release installs three seed notes as **drafts** in that category (`why-we-rebuilt-this-website-in-public`, `how-to-read-our-release-receipt`, `designing-the-foundations-syllabus`). They are invisible to visitors until the owner reviews and publishes each one; if a post with the same slug already exists, the installer skips it and never overwrites.

The core user sitemap is disabled to avoid advertising account/author identifiers. The retired `general` and `blog` categories are excluded from the category sitemap; other taxonomies, including product taxonomies, are unchanged.

## Preserved pending authenticated transaction review

Basket, checkout, shop, My Account/password reset, donor dashboard, donation confirmation/failure, GiveWP form/callbacks, both product routes, and both product-category routes. These are not redirected by the release.

The machine-readable map is `payload/redirects.json`. Query-string API/payment states are outside the generic map.
