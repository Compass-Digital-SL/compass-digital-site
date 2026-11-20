// Compass Digital — theme (dark / light) toggle and the visitor's accent colour.
// The choices live in the visitor's own browser (localStorage). inc/theme-mode.php
// re-applies them before first paint; this module wires the header controls.
// The site is dark by default; light applies only when the visitor picks it.
// Without JS, the site stays dark and keeps the teal accent.

const THEME_KEY = "cd-theme";
const ACCENT_KEY = "cd-accent";
const ACCENT_CSS_KEY = "cd-accent-css";

// The six presets. Teal is the brand default.
const PRESETS = [
  ["Teal", "#1e8a8a"],
  ["Ocean", "#2f6fb3"],
  ["Violet", "#6e56cf"],
  ["Emerald", "#1f8a5b"],
  ["Copper", "#b5652b"],
  ["Red", "#d92d33"],
];
const DEFAULT_ACCENT = PRESETS[0][1];

// Backgrounds the generated accents are checked against (match tokens.css).
const DARK_BG = "#0a0c10";
const LIGHT_BG = "#f5f3ee";

const store = {
  get(k) {
    try {
      return localStorage.getItem(k);
    } catch {
      return null;
    }
  },
  set(k, v) {
    try {
      localStorage.setItem(k, v);
    } catch {
      /* private mode: the choice just isn't remembered */
    }
  },
  del(k) {
    try {
      localStorage.removeItem(k);
    } catch {
      /* ignore */
    }
  },
};

// ---------- colour maths (WCAG relative luminance) ----------
const hex2rgb = (h) => [1, 3, 5].map((i) => parseInt(h.slice(i, i + 2), 16));
const rgb2hex = (c) =>
  "#" + c.map((v) => Math.round(Math.max(0, Math.min(255, v))).toString(16).padStart(2, "0")).join("");
const lum = (c) => {
  const [r, g, b] = c.map((v) => {
    v /= 255;
    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};
const ratio = (a, b) => {
  const x = lum(a);
  const y = lum(b);
  return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05);
};
const mix = (c, t, k) => c.map((v, i) => v + (t[i] - v) * k);
const WHITE = [255, 255, 255];
const BLACK = [0, 0, 0];
/** Shade `c` toward `to` until it reaches `target` contrast against `bg`. */
const fit = (c, bg, to, target) => {
  let k = 0;
  let out = c;
  while (ratio(out, bg) < target && k < 1) {
    k += 0.02;
    out = mix(c, to, k);
  }
  return out;
};

/**
 * Build the CSS override for an accent. Every generated colour is shaded until it
 * passes WCAG AA (text 4.5:1 with margin, white button labels 4.6:1), so any
 * preset stays readable in both themes.
 */
export function accentCss(hex) {
  const c = hex2rgb(hex);
  const dark = hex2rgb(DARK_BG);
  const light = hex2rgb(LIGHT_BG);
  const fill = fit(c, WHITE, BLACK, 4.6);
  const darkText = fit(c, dark, WHITE, 5.2);
  const lightText = fit(c, light, BLACK, 5.2);
  const lightFill = fit(lightText, WHITE, BLACK, 4.6);
  const glow = (k) => `color-mix(in srgb, ${hex} ${k}%, transparent)`;
  const d =
    `--accent:${rgb2hex(darkText)};--accent-2:${rgb2hex(mix(darkText, WHITE, 0.25))};` +
    `--accent-fill:${rgb2hex(fill)};--accent-fill-2:${rgb2hex(mix(fill, BLACK, 0.14))};--glow-b:${glow(16)};`;
  const l =
    `--accent:${rgb2hex(lightText)};--accent-2:${rgb2hex(mix(lightText, BLACK, 0.15))};` +
    `--accent-fill:${rgb2hex(lightFill)};--accent-fill-2:${rgb2hex(mix(lightFill, BLACK, 0.14))};--glow-b:${glow(10)};`;
  return (
    `:root{${d}}` +
    `:root[data-theme="light"]{${l}}:root[data-theme="dark"]{${d}}`
  );
}

function applyAccent(hex) {
  let tag = document.getElementById("compass-accent");
  if (hex === DEFAULT_ACCENT) {
    tag?.remove();
    store.del(ACCENT_KEY);
    store.del(ACCENT_CSS_KEY);
    return;
  }
  const css = accentCss(hex);
  if (!tag) {
    tag = document.createElement("style");
    tag.id = "compass-accent";
    document.head.appendChild(tag);
  }
  tag.textContent = css;
  store.set(ACCENT_KEY, hex);
  store.set(ACCENT_CSS_KEY, css);
}

// ---------- theme toggle ----------
function initThemeToggle() {
  const btn = document.querySelector("[data-theme-toggle]");
  if (!btn) return;
  const root = document.documentElement;
  // Dark is the default; light is active only when explicitly chosen.
  const current = () => root.dataset.theme || "dark";
  const label = () => {
    const next = current() === "dark" ? "light" : "dark";
    btn.setAttribute("aria-label", next === "light" ? "Switch to light theme" : "Switch to dark theme");
  };
  label();
  btn.addEventListener("click", () => {
    const next = current() === "dark" ? "light" : "dark";
    root.dataset.theme = next;
    store.set(THEME_KEY, next);
    label();
  });
}

// ---------- accent picker ----------
function initTint() {
  const wrap = document.querySelector("[data-tint]");
  if (!wrap) return;
  const btn = wrap.querySelector("[data-tint-toggle]");
  const pop = wrap.querySelector("[data-tint-pop]");
  const saved = store.get(ACCENT_KEY);
  let active = PRESETS.some((p) => p[1] === saved) ? saved : DEFAULT_ACCENT;

  PRESETS.forEach(([name, hex]) => {
    const b = document.createElement("button");
    b.type = "button";
    b.className = "tint__swatch";
    b.dataset.hex = hex;
    b.style.setProperty("--swatch", hex);
    b.setAttribute("aria-label", name);
    b.title = name;
    b.setAttribute("aria-pressed", String(hex === active));
    b.addEventListener("click", () => {
      active = hex;
      applyAccent(hex);
      pop.querySelectorAll("button").forEach((s) => s.setAttribute("aria-pressed", String(s === b)));
      close();
      btn.focus();
    });
    pop.appendChild(b);
  });

  const close = () => {
    pop.hidden = true;
    btn.setAttribute("aria-expanded", "false");
  };
  btn.addEventListener("click", (e) => {
    e.stopPropagation();
    pop.hidden = !pop.hidden;
    btn.setAttribute("aria-expanded", String(!pop.hidden));
    if (!pop.hidden) pop.querySelector('[aria-pressed="true"]')?.focus();
  });
  pop.addEventListener("keydown", (e) => {
    const items = [...pop.querySelectorAll("button")];
    const i = items.indexOf(document.activeElement);
    const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
    if (step && i >= 0) {
      e.preventDefault();
      items[(i + step + items.length) % items.length].focus();
    }
  });
  document.addEventListener("click", (e) => {
    if (!pop.hidden && !wrap.contains(e.target)) close();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !pop.hidden) {
      close();
      btn.focus();
    }
  });
  // Re-generate a saved custom accent if the stored CSS went missing.
  if (active !== DEFAULT_ACCENT && !document.getElementById("compass-accent")) applyAccent(active);
}

/** "⌘ K" on Apple devices, "Ctrl K" elsewhere. */
function initShortcutLabel() {
  const platform = navigator.userAgentData?.platform || navigator.platform || "";
  const mac = /Mac|iPhone|iPad/.test(platform);
  document.querySelectorAll("[data-shortcut-label]").forEach((el) => {
    el.textContent = mac ? "⌘ K" : "Ctrl K";
  });
}

export function initThemeMode() {
  initThemeToggle();
  initTint();
  initShortcutLabel();
}
