// Compass Digital — UI behaviour (accordion + tabs).
// No jQuery. Progressive enhancement: markup works without JS; this adds
// keyboard-accessible interactivity following WAI-ARIA patterns.

/** Accordion: <button aria-expanded> controls a panel referenced by aria-controls. */
export function initAccordions(root = document) {
  root.querySelectorAll(".accordion__trigger").forEach((trigger) => {
    const panel = document.getElementById(trigger.getAttribute("aria-controls"));
    if (!panel) return;
    const expanded = trigger.getAttribute("aria-expanded") === "true";
    panel.hidden = !expanded;

    trigger.addEventListener("click", () => {
      const open = trigger.getAttribute("aria-expanded") === "true";
      trigger.setAttribute("aria-expanded", String(!open));
      panel.hidden = open;
    });
  });
}

/** Tabs: roving-tabindex tablist with arrow-key navigation. */
export function initTabs(root = document) {
  root.querySelectorAll('[role="tablist"]').forEach((list) => {
    const tabs = Array.from(list.querySelectorAll('[role="tab"]'));
    if (!tabs.length) return;

    const select = (tab) => {
      tabs.forEach((t) => {
        const selected = t === tab;
        t.setAttribute("aria-selected", String(selected));
        t.tabIndex = selected ? 0 : -1;
        const panel = document.getElementById(t.getAttribute("aria-controls"));
        if (panel) panel.hidden = !selected;
      });
    };

    tabs.forEach((tab, i) => {
      tab.addEventListener("click", () => select(tab));
      tab.addEventListener("keydown", (e) => {
        let next = null;
        if (e.key === "ArrowRight") next = tabs[(i + 1) % tabs.length];
        else if (e.key === "ArrowLeft") next = tabs[(i - 1 + tabs.length) % tabs.length];
        else if (e.key === "Home") next = tabs[0];
        else if (e.key === "End") next = tabs[tabs.length - 1];
        if (next) {
          e.preventDefault();
          select(next);
          next.focus();
        }
      });
    });
  });
}

/**
 * Pain-point selector (home page). Without JS every tailored panel is rendered
 * and visible — the cards and the hero bearings are plain in-page anchors, so
 * the section works as anchored sections. With JS we enhance it into a
 * swap-in-place selector: one panel shows at a time and the choice follows card
 * clicks, the hero bearings, and the URL hash (back/forward, deep links).
 */
export function initPainSelector(root = document) {
  const selector = root.querySelector("[data-pain]");
  if (!selector) return;

  const panels = Array.from(selector.querySelectorAll("[data-pain-panel]"));
  const cards = Array.from(selector.querySelectorAll("[data-pain-link]"));
  if (!panels.length) return;

  const byId = new Map(panels.map((p) => [p.id, p]));
  selector.classList.add("is-enhanced");

  const activate = (id, { focus = false } = {}) => {
    const panel = byId.get(id) || panels[0];
    panels.forEach((p) => {
      const on = p === panel;
      p.classList.toggle("is-active", on);
      p.hidden = !on;
    });
    cards.forEach((c) => {
      const on = (c.getAttribute("href") || "").replace(/^#/, "") === panel.id;
      c.setAttribute("aria-current", on ? "true" : "false");
    });
    if (focus) panel.setAttribute("tabindex", "-1");
  };

  const idFromHash = () => (location.hash || "").replace(/^#/, "");

  // Any in-page anchor pointing at a panel switches it: the pain cards and the
  // hero bearings. We run before hero.js (its listeners attach after its async
  // import) and we don't preventDefault, so its smooth scroll still lands.
  document.addEventListener("click", (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    const id = (link.getAttribute("href") || "").replace(/^#/, "");
    if (byId.has(id)) activate(id);
  });

  window.addEventListener("hashchange", () => {
    const id = idFromHash();
    if (byId.has(id)) activate(id);
  });

  const initial = idFromHash();
  activate(byId.has(initial) ? initial : panels[0].id);
}

/** "Where we work": show the visitor's own local time and time zone. */
export function initLocalTime(root = document) {
  const time = root.querySelector("[data-local-time]");
  if (!time) return;
  const zoneEl = root.querySelector("[data-local-zone]");
  let zone = "";
  try {
    zone = Intl.DateTimeFormat().resolvedOptions().timeZone || "";
  } catch {
    /* older browsers: keep the plain label */
  }
  const draw = () => {
    time.textContent = new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  };
  draw();
  window.setInterval(draw, 30000);
  if (zone && zoneEl) zoneEl.textContent = `${zone.replace(/_/g, " ")}. We plan calls around it.`;
}

export function initUI(root = document) {
  initAccordions(root);
  initTabs(root);
  initPainSelector(root);
  initLocalTime(root);
}
