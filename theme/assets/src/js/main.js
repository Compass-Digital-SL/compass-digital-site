// Compass Digital — front-end entry.
// No jQuery. Progressive enhancement only; the site works without JS.

import "../css/main.css";
import { initUI } from "./ui.js";

/** Mobile nav toggle. */
function initNavToggle() {
  const toggle = document.querySelector(".primary-nav__toggle");
  const menu = document.getElementById("primary-menu");
  if (!toggle || !menu) return;

  toggle.addEventListener("click", () => {
    const open = toggle.getAttribute("aria-expanded") === "true";
    toggle.setAttribute("aria-expanded", String(!open));
    menu.classList.toggle("is-open", !open);
  });
}

function boot() {
  initNavToggle();
  initUI();
}

if (document.readyState !== "loading") {
  boot();
} else {
  document.addEventListener("DOMContentLoaded", boot);
}
