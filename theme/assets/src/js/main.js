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

function boot() {
  initNavigation();
  initUI();
  initMotion();
}

if (document.readyState !== "loading") {
  boot();
} else {
  document.addEventListener("DOMContentLoaded", boot);
}
