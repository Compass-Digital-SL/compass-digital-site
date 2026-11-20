# Design system (v2 — premium redesign, Task 24)

Minimal, editorial, premium. A **near-black base** with a **light theme**, one **teal**
accent (the visitor can re-tint it), ivory headings in a light **Fraunces** serif, and
structure from **hairlines and space**, not boxes. Everything is built from tokens;
every text pair meets WCAG 2.2 AA in both themes.

Live reference: hidden **/styleguide/** page (noindex) renders every token and component.
Approved mockup: the Task 24 artifact (homepage), built 1:1 into the theme.

## Direction
- **Dark-first, light available.** Dark values sit on bare `:root`. Light applies when
  the system asks for light (and the visitor has not picked dark) or when the visitor
  picks light with the header toggle (`data-theme` on `<html>`). The two light blocks in
  `tokens.css` must stay identical.
- **One accent per decision.** Teal marks the primary action, the active state and the
  compass needle. Emphasised words in headings (`<em>`) are teal and upright.
- **Hairlines over boxes.** Sections are separated by 1px rules; cards are reserved for
  things you click or compare.
- **Big, light type.** Headings are Fraunces 300/400 with tight tracking; labels are
  small uppercase Inter with wide tracking and a teal dot.

## Colour

### Raw palette
| Token | Dark | Light | Use |
|---|---|---|---|
| `--bg` | `#0A0C10` | `#F5F3EE` | page |
| `--bg-2` | `#0F1217` | `#FFFFFF` | raised band, footer (light: cards) |
| `--bg-3` | `#151922` | `#ECE8E0` | cards (light: recessed) |
| `--ink` | `#F6F2EA` | `#12151B` | headings |
| `--ink-body` | `#C9CED6` | `#3A414C` | running text |
| `--ink-muted` | `#A9B0BB` | `#4A515C` | secondary text |
| `--ink-subtle` | `#8E96A3` | `#5E6673` | labels / meta only |
| `--accent` | `#309393` | `#186E6E` | accent text, rules, icons |
| `--accent-2` | `#64AEAE` | `#135C5C` | links, hover text |
| `--accent-fill` | `#1C8282` | `#186E6E` | button fill (white label) |
| `--accent-fill-2` | `#187070` | `#135A5A` | button hover |

Semantic names (`--surface`, `--surface-2`, `--surface-sunken`, `--text`,
`--text-body`, `--text-muted`, `--text-subtle`, `--link`, `--focus`, `--border`,
`--border-strong`, `--danger`, `--danger-text`) map onto these, so components never
reference raw colours. The old palette names (`--color-brass`, `--color-teal-300`, …)
remain as aliases of the new tokens so older rules still resolve.

### Visitor accent
The header "tint" dot offers six presets (Teal default, Ocean, Violet, Emerald, Copper,
Red). `theme-mode.js` generates the four accent tokens for both themes, shading each
colour until it passes AA (text ≥ 5.2:1 on the page, white label ≥ 4.6:1 on the fill).
The choice is stored in the visitor's own browser (`cd-accent`, `cd-accent-css`) and
re-applied before first paint by the inline script in `inc/theme-mode.php`.

### Contrast (WCAG 2.2 AA — measured)
| Pair | Dark | Light |
|---|---|---|
| Headings on page | 17.5:1 | 16.5:1 |
| Body text on page | 12.4:1 | 9.3:1 |
| Muted text on page / card | 9.0 / 8.0:1 | 7.2:1 |
| Subtle (labels) on page / card | 6.6 / 5.9:1 | 5.2 / 4.7:1 |
| Accent text on page / card | 5.3 / 4.8:1 | 5.4 / 4.9:1 |
| Links (`--accent-2`) on page | 7.7:1 | (= accent) 5.4:1 |
| White on button fill / hover | 4.6 / 5.9:1 | 6.0:1 |
| Error text on page | 10:1 | 5.9:1 |

## Typography
Self-hosted variable fonts: **Fraunces** (display, `wght 300..700`, no italic face) and
**Inter** (UI/body). Emphasis in headings is colour, never a synthesised slant.

```css
--fs-xs:   .75rem;                                 /* labels */
--fs-sm:   clamp(.875rem, .85rem + .1vw, .9375rem);
--fs-base: clamp(1rem, .97rem + .15vw, 1.0625rem);
--fs-lede: clamp(1.06rem, 1rem + .35vw, 1.3rem);
--fs-lg:   clamp(1.3rem, 1.1rem + .8vw, 1.8rem);   /* h3 */
--fs-xl:   clamp(2rem, 1.3rem + 2.8vw, 3.6rem);    /* h2 */
--fs-2xl:  clamp(2.5rem, 1.5rem + 4vw, 4.75rem);   /* page h1 */
--fs-hero: clamp(3.1rem, 1.4rem + 7.4vw, 8rem);    /* home h1 */
```
- Headings: Fraunces 400 (h1 300), line-height 1.06, tracking −0.025em (hero −0.045em).
- Body: Inter, line-height 1.65, colour `--text-body`; reading measure ~68ch.
- Labels/eyebrows: Inter 500, 12px, uppercase, tracking .18em, teal dot.

## Spacing, layout, radii
```
--space-1 … --space-24 (4px base);  --section: clamp(5rem, 3rem + 8vw, 9rem)
--wide-width: 1200px;  --content-width: 720px;  --gutter: clamp(1.25rem, .5rem + 2.5vw, 2rem)
--radius-sm .375rem  --radius .75rem  --radius-lg 1.25rem  --radius-pill 999px
```
Direct children of `<main>` get the centred column via a zero-specificity rule; page
wrappers that hold their own `.container` (listed in `main.css` and `critical.css`) run
full-bleed so heroes and bands can span the viewport.

## Elevation & glass
Shadows derive from `--shadow-color` (deep black on dark, soft ink on light). Glass
(`--glass-bg` = 62–72% of the page colour + 16px blur) is used only on the header island,
command menu, scroll rail and cookie card.

## Focus
`:focus-visible { outline: 2px solid var(--focus); outline-offset: 3px; }` — teal ring.
Non-interactive targets that scripts focus for screen readers (`[tabindex="-1"]`) get no
ring. Minimum target 24×24px.

## Motion
Animate only `transform` / `opacity`; everything decorative sits behind
`prefers-reduced-motion: no-preference`.
```css
--dur-fast 150ms; --dur-mid 300ms; --dur-slow 600ms;
--ease: cubic-bezier(.2,.7,.2,1);  --ease-out; --ease-in-out
```
- **Hero:** line-mask headline rise, staggered fade-ins (in `critical.css` so they start
  with the first paint and never replay), slow-drifting light field, film grain, a
  fine-line compass rose that turns slowly, and a needle that follows the pointer on a
  spring (`hero.js`, mouse/pen only; it snaps instead of swinging with reduced motion).
  A "Heading 000° N" readout tracks it (hidden on touch).
- **Header island:** transparent at the top, glass once scrolled, tucked away while
  scrolling down and back on the way up (never while a menu inside it is open).
- **Scroll progress** bar at the bottom edge (above the mobile bar on phones) and a
  right-middle **section rail** of dots built from `[data-waypoint]` sections.
- Hover: arrow nudges, row indents, card lifts, plus→minus accordion.

## Components (`components.css`)
- **Eyebrow** — `.eyebrow` / `.section-header__eyebrow`: label with a teal dot.
- **Buttons** — `.btn` pill; `.btn--primary` (accent fill, white label, soft glow),
  `.btn--ghost` (hairline); `.btn--sm`; `.btn__arrow` nudges on hover.
- **Section header** — eyebrow, large serif h2, lede beside it on wide screens.
- **Cards** — `.card` hairline panel; `.card--glass` lifts on hover; `.service-card`
  numbered link card; `.industry-chip` pill link; `.tag`, `.badge`, `.pills`.
- **Fields** — `.field` (sunken fill, teal focus, theme-aware error colours).
- **Accordion** — serif questions, circular plus/minus icon.
- **Header tools** — quiet Ctrl/⌘ K search pill (icon-only under 1240px, labelled
  "Search the site"), tint picker, theme toggle, quote button.
- **Wordmark** — `template-parts/header/branding.php`: white logo on dark, black on light
  (`assets/brand/*.webp`, from the official files); takes priority over a Customizer logo.

## Home sections
Hero → pain selector (list + sticky answer panel) → numbered services list → industry
tiles → recent work → process steps (accent line draws in) → promise headline +
testimonials + guarantees → "Where we work" (visitor's local time, regions served) →
split FAQ → large closing CTA.

---
Updated in Task 24 (premium redesign). Supersedes the Task 6 navy/brass system and the
Task 8 "Expedition" hero.
