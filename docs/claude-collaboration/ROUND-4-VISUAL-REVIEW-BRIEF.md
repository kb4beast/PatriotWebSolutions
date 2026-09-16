# Fable 5 round 4: rendered-site adversarial review

Resume the same design collaboration at maximum effort. Do not modify files in this round. Review the finished Round 3 release candidate as an independent design critic, product strategist, accessibility reviewer, conversion reviewer, and skeptical representative of each audience named in the owner's original request.

## Binding source material

- Owner's exact original prompt: `C:/Users/beesp/.codex/worktrees/0afa/hive-mind-os/docs/plan/patriot-web-redesign-2026-09-15/USER_REQUEST.md`
- Round 1 review: `docs/claude-collaboration/ROUND-1-REVIEW.md`
- Round 2 agreed contract: `docs/claude-collaboration/ROUND-2-RESPONSE.md`
- Current repository diff and all release source files
- All 12 current screenshots in `dist/`: home, learn, work, hive-mind-os, impact, and donate at desktop and 390px mobile
- Browser receipt: `dist/playground-e2e-receipt.json`
- Release and link receipts: `dist/release-receipt.json`, `dist/link-check-receipt.json`
- Local rendered site: `http://127.0.0.1:9411/`

## Review standard

Try to reject the release. Inspect the actual pixels and content, not only the implementation contract. Look for generic AI-generated composition or copy, template repetition, shallow content, weak information scent, missing audience proof, visual imbalance, bad mobile transformations, unreadable text, excessive page length, empty states presented as features, broken wrapping, weak calls to action, false confidence, or evidence that cannot support its claim.

Walk the site through these lenses:

1. military member, veteran, or spouse deciding whether three weekly hours are worth committing;
2. donor deciding whether to trust and support the organization;
3. employer or workforce partner judging whether learners will be useful and ready;
4. business, nonprofit, creator, or product team considering paid custom AI work;
5. developer or technical reviewer looking for working repositories, tests, limitations, and methods;
6. OpenAI/ChatGPT or another platform reviewing a legitimate publisher/business identity;
7. mentor, volunteer, or community partner deciding how to help.

## Known issue from Codex's independent pixel review

At 390px, the impact ledger's three-column table is technically contained but is not usable: short first-column and source labels break into near-vertical letter stacks. Treat this as a confirmed defect and propose a specific mobile presentation that preserves semantics and evidence relationships.

Also determine whether the single 404 console entry is a harmless local-environment artifact or a release defect that should be corrected.

## Required output

Write `docs/claude-collaboration/ROUND-4-VISUAL-REVIEW.md` with:

1. a decisive ship / revise / reject verdict;
2. ranked defects tied to exact page, viewport, and visible evidence;
3. a compact audience-journey scorecard with concrete failure points;
4. mobile, accessibility, information architecture, conversion, credibility, and visual-system findings;
5. exact fixes, ordered by impact, with copy/CSS/template direction precise enough to implement;
6. a list of proposed changes you considered but rejected because they would add AI slop, unsupported claims, or needless complexity;
7. an adversarial self-review of your own recommendations.

No production action. No invented facts or links. No self-approval. The next round will implement only the accepted fixes.
