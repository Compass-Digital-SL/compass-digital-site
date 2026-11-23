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
// fetched on first open. The launcher is always visible — opening it and sending
// a message is the visitor's own choice, and it sets no cookies (the GA4 consent
// bar is separate), so it does not depend on the cookie choice.
function initChat() {
  const cfg = window.CompassChat;
  const launcher = document.querySelector("[data-compass-chat]");
  if (!cfg || !launcher) return;

  launcher.hidden = false;
  launcher.setAttribute("aria-expanded", "false");

  // The launcher is pinned to the bottom-right corner, where it would sit on top
  // of two other fixed elements: the cookie consent bar (while it's on screen)
  // and the desktop WhatsApp chip. Lift the launcher clear of whichever is there
  // by exposing the needed offset as a CSS variable it adds to its own `bottom`.
  // A visible element reports a real height; a hidden/`display:none` one reports
  // 0, so the lift is always just enough and drops back to 0 when both are gone.
  const consentBar = document.getElementById("compass-consent");
  const waChip = document.querySelector(".contact-chip");
  const obstacles = [consentBar, waChip].filter(Boolean);
  if (obstacles.length) {
    const clearance = (el) => {
      if (!el || el.hidden) return 0;
      const r = el.getBoundingClientRect();
      return r.height ? Math.ceil(r.height) + 12 : 0;
    };
    const syncLift = () => {
      const lift = Math.max(0, ...obstacles.map(clearance));
      launcher.style.setProperty("--compass-launcher-lift", lift + "px");
    };
    syncLift();
    if ("ResizeObserver" in window) {
      const ro = new ResizeObserver(syncLift);
      obstacles.forEach((el) => ro.observe(el));
    }
    if ("MutationObserver" in window && consentBar) {
      new MutationObserver(syncLift).observe(consentBar, {
        attributes: true,
        attributeFilter: ["hidden"],
      });
    }
    window.addEventListener("resize", syncLift, { passive: true });
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

// Dynamic "Where we work" map. The heavy chunk (outlines + logic) is fetched
// only when the section nears the viewport, so it never costs the initial load.
function initLocalMap() {
  const host = document.querySelector("[data-local-map]");
  if (!host) return;
  const load = () => import("./map/local-map.js").then((m) => m.initLocalMap());
  if (!("IntersectionObserver" in window)) {
    load();
    return;
  }
  const io = new IntersectionObserver(
    (entries, obs) => {
      if (entries.some((e) => e.isIntersecting)) {
        obs.disconnect();
        load();
      }
    },
    { rootMargin: "250px" }
  );
  io.observe(host);
}

function boot() {
  initThemeMode();
  initNavigation();
  initUI();
  initMotion();
  initLocalMap();
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
