// Compass Digital — front-end entry.
// No jQuery. Progressive enhancement only; the site works without JS.

import "../css/main.css";
import { initUI } from "./ui.js";
import { initNavigation } from "./command-menu.js";
import { initThemeMode } from "./theme-mode.js";

// Lazy-load motion so its bundle is fetched only on pages that use it, and the
// heavier hero module only on the home page.
function initMotion() {
  if (document.querySelector("[data-hero]")) {
    import("./hero.js").then((m) => m.initHero());
    // The home page also has below-hero sections (Task 10) that use scroll
    // reveal and number counters. hero.js only drives motion inside the hero,
    // so init the page-level effects for everything after it.
    import("./motion.js").then((m) => {
      m.initScrollReveal();
      m.initCounters();
    });
    return;
  }
  const needsMotion = document.querySelector(
    "[data-reveal],[data-counter],[data-magnetic],[data-parallax]"
  );
  if (needsMotion) {
    import("./motion.js").then((m) => {
      m.initScrollReveal();
      m.initCounters();
      m.initMagnetic();
      m.initParallax();
    });
  }
}

// Decorative scroll-driven compass watermark (every page except home).
function initCompass() {
  const el = document.querySelector("[data-compass]");
  if (!el) return;
  import("./motion.js").then((m) => m.initScrollCompass(el));
}

function initWork() {
  if (!document.querySelector("[data-work-filter]")) return;
  import("./work-filter.js").then((m) => m.initWorkFilter());
}

function initInsights() {
  if (
    !document.querySelector("[data-insights-filter],[data-insight-toc],[data-copy-link]")
  )
    return;
  import("./insights.js").then((m) => m.initInsights());
}

function initQuote() {
  if (!document.querySelector("[data-quote-form]")) return;
  import("./quote.js").then((m) => m.initQuote());
}

// AI chat assistant. Only the launcher ships on load; the panel is a lazy chunk
// fetched on first open. The launcher appears only after cookie consent.
function initChat() {
  const cfg = window.CompassChat;
  const launcher = document.querySelector("[data-compass-chat]");
  if (!cfg || !launcher) return;

  function reveal() {
    launcher.hidden = false;
    launcher.setAttribute("aria-expanded", "false");
  }

  let consented = false;
  try {
    consented = localStorage.getItem(cfg.consent) === "granted";
  } catch {
    consented = false;
  }
  if (consented) {
    reveal();
  } else {
    window.addEventListener("compass:consent-granted", reveal, { once: true });
  }

  let controller = null;
  launcher.addEventListener("click", async () => {
    if (!controller) {
      launcher.setAttribute("aria-busy", "true");
      try {
        const m = await import("./chat.js");
        controller = m.createChat(launcher, cfg);
      } finally {
        launcher.removeAttribute("aria-busy");
      }
    }
    if (controller) controller.toggle();
  });
}

function boot() {
  initThemeMode();
  initNavigation();
  initUI();
  initMotion();
  initCompass();
  initWork();
  initInsights();
  initQuote();
  initChat();
}

if (document.readyState !== "loading") {
  boot();
} else {
  document.addEventListener("DOMContentLoaded", boot);
}
