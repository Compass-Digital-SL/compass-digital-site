// Compass Digital — front-end entry.
// No jQuery. Progressive enhancement only; the site works without JS.

import "../css/main.css";
import { initUI } from "./ui.js";
import { initNavigation } from "./command-menu.js";

function boot() {
  initNavigation();
  initUI();
}

if (document.readyState !== "loading") {
  boot();
} else {
  document.addEventListener("DOMContentLoaded", boot);
}
