// Compass Digital — front-end entry.
// No jQuery. Progressive enhancement only; the site works without JS.

import "../css/main.css";
import { initUI } from "./ui.js";
import { initNavigation } from "./command-menu.js";

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

function boot() {
  initNavigation();
  initUI();
  initMotion();
  initWork();
  initInsights();
}

if (document.readyState !== "loading") {
  boot();
} else {
  document.addEventListener("DOMContentLoaded", boot);
}
