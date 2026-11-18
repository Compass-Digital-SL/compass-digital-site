# Compass Digital — Claude working rules

Marketing site for Compass Digital (Sri Lanka): websites, SEO and POS systems.
WordPress classic PHP theme, built with Vite. Visitors request a quote; there is no checkout.

## Key docs
- `docs/PROGRESS.md` — task list, decisions, what is next. Read first, every task.
- `docs/BRAND.md` — name, palette, voice, services, industries.
- `docs/DESIGN.md` — design system, tokens, motion rules.
- `docs/SEO.md` — SEO strategy, keywords, technical SEO rules.

## Standing rules
1. At the start of each task read only `docs/PROGRESS.md` and the docs the task names.
   Never scan the whole repo.
2. Do one task only, then stop.
3. Branch per task (`task/NN-name`), small conventional commits, PR to `main`.
4. WordPress classic PHP theme + Vite build. No page builders. No ACF.
   No jQuery on the front end. Do not add a plugin for anything under 50 lines of code.
5. Performance budget (mobile):
   - Lighthouse 95+
   - LCP < 1.8s, CLS < 0.05, INP < 200ms
   - Homepage JS < 90KB gzipped, CSS < 40KB gzipped
6. Accessibility: WCAG 2.2 AA, keyboard friendly, honour `prefers-reduced-motion`.
7. For tasks labelled **L**: give a plan in 10 bullets or fewer and wait for "go".
8. Never read or print `.env` files or secrets. Ask instead.
9. Before finishing: run lint, build and tests, update `docs/PROGRESS.md`
   (done, decisions, next), commit.
10. Final message: 10 lines or fewer — what changed, the URL to check,
    anything the owner must do, and a reminder to type `/clear`.

## Conventions
- Commits: Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `perf:`, `test:`, `ci:`).
- Branches: `task/NN-short-name` (e.g. `task/03-theme-build`).
- Line endings LF, UTF-8, 2-space indent (PHP: 4 spaces) — see `.editorconfig`.
- Copy follows the voice in `docs/BRAND.md`: plain, confident, warm, no jargon, no hype.
- Colours come from design tokens only; never hard-code hex values in components.
