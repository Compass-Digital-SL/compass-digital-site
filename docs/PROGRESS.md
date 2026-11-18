# Progress

## Tasks
- [x] 1 Scaffolding
- [ ] 2 Local WP env
- [ ] 3 Theme and build
- [ ] 4 CI
- [ ] 5 SEO strategy
- [ ] 6 Design system
- [ ] 7 Navigation
- [ ] 8 Hero and motion
- [ ] 9 Post types and settings
- [ ] 10 Home page
- [ ] 11 Service pages
- [ ] 12 Industry pages
- [ ] 13 Work portfolio
- [ ] 14 About/Process/FAQ/Insights
- [ ] 15 Quote flow
- [ ] 16 Technical SEO
- [ ] 17 Local SEO and analytics
- [ ] 18 Performance
- [ ] 19 Accessibility and QA
- [ ] 20 Security and legal
- [ ] 21 CD pipeline
- [ ] 22 Go-live
- [ ] 23 Post-launch

## Log

### Task 1 — Scaffolding (2026-11-18)
**Done**
- Added CLAUDE.md (standing rules), docs/BRAND.md, docs/DESIGN.md (stub), docs/SEO.md (stub), docs/PROGRESS.md.
- Added .gitignore, .editorconfig, README.md, .github/ (PR template, issue template).
- First commit on `main`.

**Decisions**
- Default branch is `main`; every later task uses `task/NN-name` and a PR.
- `assets-to-use/` (raw logo PNGs + notes) is git-ignored as source material. Optimised copies
  (SVG/WebP, sized) get added to the theme's `assets/` in Task 3/6; originals stay in design storage.
- No lint/build/tests exist yet; they arrive in Task 3 (build) and Task 4 (CI).

**Next**
- Task 2: Local WP env.
