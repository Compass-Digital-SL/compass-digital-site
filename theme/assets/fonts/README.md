# Fonts

Two self-hosted variable fonts, Latin subset, `woff2`:

| File | Family | Role | Axes |
|------|--------|------|------|
| `inter-latin-var.woff2` | Inter | sans — body / UI | `wght 100..900` |
| `fraunces-latin-var.woff2` | Fraunces | serif display — headings | `wght 300..700` (opsz flattened) |

These are committed to the repo (tracked, not built by Vite) so their URLs stay
stable and match the `<link rel="preload">` hrefs in `inc/fonts.php` and the
`@font-face` rules in `fonts.css`.

## Regenerate the subsets

Source: Google Fonts (OFL). Use `fonttools` to subset to Latin + punctuation and
keep the weight axis as a variable font:

```bash
pip install fonttools brotli
# Inter (download Inter-VariableFont_opsz,wght.ttf from fonts.google.com)
pyftsubset Inter.ttf \
  --output-file=inter-latin-var.woff2 --flavor=woff2 \
  --layout-features='*' \
  --unicodes="U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD"
# Fraunces — pin the opsz axis, keep wght variable
pyftsubset Fraunces.ttf \
  --output-file=fraunces-latin-var.woff2 --flavor=woff2 \
  --layout-features='*' --instance='opsz=72' \
  --unicodes="U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD"
```

If you change the subset, keep the `unicode-range` in `fonts.css` in sync.
