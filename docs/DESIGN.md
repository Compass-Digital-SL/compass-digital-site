# Design system

Premium, calm, editorial. A **dark base** (deep navy to ink), **brass** as the single
accent, **teal** for secondary information, **ivory** text. Restrained glass. No neon,
no heavy gradients. Everything is built from tokens; contrast meets WCAG 2.2 AA.

Live reference: hidden **/styleguide/** page (noindex) renders every token and component.

## Direction
- The site is **dark-first**. Navy (`#0F2A43`) is the page base, deepening toward ink
  (`#1B1F24`) for recessed surfaces. Ivory is text, not background.
- Brass is the **only** accent. It marks the primary action, active state and compass
  motif — never decoration for its own sake. One brass thing per decision.
- Teal carries secondary meaning (links on dark use a brightened teal; see contrast).
- Light (ivory) surfaces stay available as a token for the rare light section, but the
  default page, header and footer are dark.

## Colour

### Brand palette (docs/BRAND.md)
```css
--color-navy:  #0F2A43;  /* page base, dark surface */
--color-ink:   #1B1F24;  /* deepest surface, recessed */
--color-brass: #C9A24B;  /* single accent */
--color-ivory: #F6F2EA;  /* text on dark, light surface */
--color-teal:  #2F7F86;  /* secondary (borders, large text, light-ctx links) */
```

### Derived tokens
```css
--color-navy-700: #0B2033;  /* darker navy — header / card base */
--color-teal-300: #5FB5BD;  /* brightened teal — links/text on dark (AA) */
--text-muted:     #B9C2CC;  /* muted ivory text on dark */
```

### Semantic tokens (dark base)
```css
--surface:        var(--color-navy);    /* page */
--surface-2:      var(--color-navy-700);/* raised panel / header */
--surface-sunken: var(--color-ink);     /* recessed */
--surface-light:  var(--color-ivory);   /* light section */
--text:           var(--color-ivory);
--text-muted:     #B9C2CC;
--text-on-light:  var(--color-ink);
--accent:         var(--color-brass);
--accent-text:    var(--color-navy);    /* text on a brass fill */
--secondary:      var(--color-teal-300);
--link:           var(--color-teal-300);
--focus:          var(--color-brass);   /* focus ring */
--border:         color-mix(in srgb, var(--color-ivory) 14%, transparent);
--border-strong:  color-mix(in srgb, var(--color-ivory) 24%, transparent);
```

### Glass (restrained)
Used **only** on the header, hover cards and the command menu. **Never more than 3 glass
elements in one viewport** (backdrop-filter is expensive). Blur capped at 12px.
```css
--glass-bg:     color-mix(in srgb, #ffffff 5%, transparent);   /* 4–6% white fill */
--glass-border: color-mix(in srgb, #ffffff 10%, transparent);  /* 1px @ 10% white */
--glass-blur:   12px;
```

### Contrast (WCAG 2.2 AA — measured)
AA needs 4.5:1 for normal text, 3:1 for large text (≥24px, or ≥18.66px bold) and UI.

| Foreground          | Background | Ratio  | Verdict                       |
|---------------------|------------|--------|-------------------------------|
| Ivory text          | Navy       | 13.1:1 | Pass (all text)               |
| Ivory text          | Ink        | 14.8:1 | Pass (all text)               |
| Muted `#B9C2CC`     | Navy       | 8.1:1  | Pass (all text)               |
| Brass `#C9A24B`     | Navy       | 6.1:1  | Pass (all text)               |
| Brass               | Ink        | 6.9:1  | Pass (all text)               |
| Teal-300 `#5FB5BD`  | Navy       | 6.2:1  | Pass (links/text on dark)     |
| Base teal `#2F7F86` | Navy       | 3.1:1  | **Large text / borders only** |
| Navy text           | Brass fill | 6.1:1  | Pass — button label           |
| Ink text            | Ivory      | 14.8:1 | Pass (light section)          |

Rule: **base teal is never body or link text on dark** — use `--color-teal-300`.
Brass as text is allowed on dark (passes) but reserved for accents.

**On light surfaces** brass fails as small text (~2.2:1 on ivory), so the light-section
eyebrow uses dark-brass `#7A5E1F` (5.5:1) and light-context links use dark-teal `#1F6168`.

## Typography
Variable fonts, self-hosted (Task 3). Display = **Fraunces** (serif), UI/body = **Inter**.

Fluid scale (`clamp()`), matches `theme.json`:
```css
--fs-sm:   clamp(0.875rem, 0.85rem + 0.1vw,  0.9375rem);
--fs-base: clamp(1rem,     0.97rem + 0.15vw, 1.0625rem);
--fs-lg:   clamp(1.25rem,  1.15rem + 0.5vw,  1.5rem);
--fs-xl:   clamp(1.75rem,  1.5rem + 1.25vw,  2.5rem);
--fs-2xl:  clamp(2.25rem,  1.8rem + 2.25vw,  3.75rem);
```
- Headings: Fraunces, weight 600, line-height 1.15, slightly tightened tracking.
- Body: Inter, line-height 1.6. Reading measure capped at ~68ch.
- `--lh-tight: 1.15; --lh-base: 1.6;`

## Spacing (4px base)
```
--space-1:.25rem  --space-2:.5rem  --space-3:.75rem  --space-4:1rem
--space-6:1.5rem  --space-8:2rem   --space-12:3rem   --space-16:4rem  --space-24:6rem
```

## Layout
```css
--content-width: 720px;   /* reading measure */
--wide-width:    1120px;  /* section max */
--gutter: clamp(1rem, 0.5rem + 2.5vw, 2rem);
```
- `.container` centres content at `--wide-width`; `.container--narrow` at `--content-width`.
- `.grid` is responsive auto-fit (`minmax(min(100%, 16rem), 1fr)`), gap `--space-6`.

## Radii
```css
--radius-sm: .375rem;  --radius: .625rem;  --radius-lg: 1rem;  --radius-pill: 999px;
```

## Elevation
Shadows are soft and cool (shadow colour derived from ink, not pure black).
```css
--shadow-1: 0 1px 2px rgba(5,12,20,.4);
--shadow-2: 0 8px 24px -8px rgba(5,12,20,.55);
--shadow-3: 0 20px 48px -12px rgba(5,12,20,.6);
```

## Focus ring
Brass, always visible, offset so it clears the element.
```css
--focus: var(--color-brass);
:focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }
```
Minimum target size 24×24px (WCAG 2.2 AA, 2.5.8).

## Motion
Three durations, one easing family. Animate only `transform` and `opacity`.
Everything inside `@media (prefers-reduced-motion: no-preference)`.
```css
--dur-fast: 150ms;   /* hovers, small state */
--dur-mid:  300ms;   /* reveals, menus */
--dur-slow: 600ms;   /* large entrances (Task 8) */
--ease:        cubic-bezier(.4, 0, .2, 1);     /* standard */
--ease-out:    cubic-bezier(0, 0, .2, 1);      /* decelerate */
--ease-in-out: cubic-bezier(.65, 0, .35, 1);   /* emphasis */
```

## Components
Plain CSS classes (`assets/src/css/components.css`), used by PHP template parts.
Behaviour (accordion, tabs) lives in `assets/src/js/ui.js` — no jQuery, keyboard
accessible, progressive enhancement (works without JS).

- **Buttons** — `.btn` + `.btn--primary` (brass fill, navy label), `.btn--ghost`
  (transparent, ivory label, border).
- **Links** — `.link` teal-300, underline on hover/focus, brass on active.
- **Cards** — `.card` (surface-2, border, shadow-2); `.card--glass` (hover card, glass).
- **Tags** — `.tag` small pill, teal border, muted text.
- **Badges** — `.badge` status chip (`--brass` / `--teal` / neutral variants).
- **Form fields** — `.field` label + input/textarea/select, ink fill, brass focus ring,
  `.field__error` for messages.
- **Accordion** — `.accordion` native `<button aria-expanded>` + region, animated height.
- **Tabs** — `.tabs` WAI-ARIA roving-tabindex tablist + panels.
- **Section header** — `.section-header` (eyebrow + h2 + lede), consistent rhythm.
- **Container / grid** — layout primitives above.

## Styleguide
`/styleguide/` (template `page-styleguide.php`): renders all tokens and components.
`noindex,nofollow`, excluded from nav and sitemap, never linked publicly.

---
Updated in Task 6. Hero / entrance motion detailed in Task 8.
