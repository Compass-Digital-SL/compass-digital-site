# Design system

> Stub. Filled in during Task 6 (Design system) and Task 8 (Hero and motion).

## Principles
- Clarity first: one clear message and one clear action per section.
- Built from tokens: colour, type, spacing, radius and motion all come from CSS custom properties.
- Fast by default: system-friendly fonts or a single self-hosted variable font, no layout shift.
- Accessible: WCAG 2.2 AA contrast, visible focus, 24×24px minimum targets.

## Colour tokens (from docs/BRAND.md)
```css
--color-navy:  #0F2A43;
--color-brass: #C9A24B;
--color-ivory: #F6F2EA;
--color-teal:  #2F7F86;
--color-ink:   #1B1F24;
```
Semantic tokens (surface, text, accent, focus, border) to be defined in Task 6.

## Typography
TBD (Task 6). Fluid type scale with `clamp()`.

## Spacing and layout
TBD (Task 6). 4px base scale, fluid container widths.

## Motion
- All motion is optional: wrap in `@media (prefers-reduced-motion: no-preference)`.
- Animate only `transform` and `opacity`.
- No motion may affect LCP or cause layout shift.
- Details in Task 8.

## Components
TBD. Built as PHP template parts with matching CSS modules.
