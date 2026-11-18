// Compass Digital — navigation behaviour.
// No jQuery. Everything here is progressive enhancement: the header links,
// command-menu links, mobile sheet and footer all work with this script absent.

/** Elements that can receive focus inside a container, for the focus trap. */
const FOCUSABLE =
  'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Trap Tab focus within `el` and route Escape to `onClose`.
 * Returns a detach function.
 */
function trapFocus(el, onClose) {
  function onKeydown(e) {
    if (e.key === "Escape") {
      e.preventDefault();
      onClose();
      return;
    }
    if (e.key !== "Tab") return;
    const items = Array.from(el.querySelectorAll(FOCUSABLE)).filter(
      (n) => n.offsetParent !== null
    );
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }
  el.addEventListener("keydown", onKeydown);
  return () => el.removeEventListener("keydown", onKeydown);
}

/** A generic open/close dialog controller used by the command menu and sheets. */
function createOverlay(el, { onOpen } = {}) {
  let lastFocus = null;
  let detach = null;
  const main = document.getElementById("main");

  function open() {
    if (!el.hidden) return;
    lastFocus = document.activeElement;
    el.hidden = false;
    document.documentElement.classList.add("has-overlay");
    if (main) main.setAttribute("inert", "");
    detach = trapFocus(el, close);
    if (onOpen) onOpen(el);
  }

  function close() {
    if (el.hidden) return;
    el.hidden = true;
    document.documentElement.classList.remove("has-overlay");
    if (main) main.removeAttribute("inert");
    if (detach) detach();
    if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
  }

  el.querySelectorAll("[data-command-close], [data-sheet-close]").forEach((b) =>
    b.addEventListener("click", close)
  );

  return { open, close, el };
}

/** The command menu: open on K / button, type-to-filter, arrow keys. */
function initCommandMenu() {
  const menu = document.getElementById("command-menu");
  if (!menu) return;

  const input = menu.querySelector(".command-menu__input");
  const options = Array.from(menu.querySelectorAll("[data-command-option]"));
  const groups = Array.from(menu.querySelectorAll("[data-command-group]"));
  const empty = menu.querySelector(".command-menu__empty");

  const overlay = createOverlay(menu, {
    onOpen() {
      filter("");
      if (input) {
        input.value = "";
        input.focus();
      }
    },
  });

  function visibleOptions() {
    return options.filter((o) => !o.parentElement.hidden);
  }

  function filter(q) {
    const needle = q.trim().toLowerCase();
    options.forEach((o) => {
      const match = o.dataset.label.toLowerCase().includes(needle);
      o.parentElement.hidden = !match;
    });
    groups.forEach((g) => {
      const anyVisible = g.querySelectorAll("li:not([hidden])").length > 0;
      g.hidden = !anyVisible;
    });
    const vis = visibleOptions();
    if (empty) empty.hidden = vis.length > 0;
    vis.forEach((o, i) => (o.tabIndex = i === 0 ? 0 : -1));
  }

  function moveActive(dir) {
    const vis = visibleOptions();
    if (!vis.length) return;
    const idx = vis.indexOf(document.activeElement);
    let next = idx + dir;
    if (next < 0) next = vis.length - 1;
    if (next >= vis.length) next = 0;
    vis[next].focus();
  }

  if (input) {
    input.addEventListener("input", () => filter(input.value));
    input.addEventListener("keydown", (e) => {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        const vis = visibleOptions();
        if (vis.length) vis[0].focus();
      } else if (e.key === "Enter") {
        const vis = visibleOptions();
        if (vis.length) {
          e.preventDefault();
          vis[0].click();
        }
      }
    });
  }

  menu.querySelector(".command-menu__results")?.addEventListener("keydown", (e) => {
    if (e.key === "ArrowDown") {
      e.preventDefault();
      moveActive(1);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      if (document.activeElement === visibleOptions()[0]) input?.focus();
      else moveActive(-1);
    }
  });

  // Triggers: the "Navigate" button, and the K shortcut.
  document.querySelectorAll("[data-command-open]").forEach((b) =>
    b.addEventListener("click", (e) => {
      e.preventDefault();
      overlay.open();
    })
  );

  document.addEventListener("keydown", (e) => {
    const tag = (e.target.tagName || "").toLowerCase();
    const typing = tag === "input" || tag === "textarea" || e.target.isContentEditable;
    if (typing || e.metaKey || e.ctrlKey || e.altKey) return;
    if (e.key === "k" || e.key === "K") {
      e.preventDefault();
      overlay.open();
    }
  });
}

/** Mobile bottom-sheet triggers (data-sheet-open points to a sheet id). */
function initSheets() {
  document.querySelectorAll(".sheet").forEach((el) => {
    const overlay = createOverlay(el, {
      onOpen() {
        el.querySelector(FOCUSABLE)?.focus();
      },
    });
    el._overlay = overlay;
  });

  document.querySelectorAll("[data-sheet-open]").forEach((trigger) => {
    const sheet = document.getElementById(trigger.dataset.sheetOpen);
    if (!sheet || !sheet._overlay) return;
    trigger.addEventListener("click", (e) => {
      e.preventDefault();
      sheet._overlay.open();
    });
  });
}

/** Header shrinks once the page is scrolled. */
function initHeaderShrink() {
  const header = document.querySelector("[data-site-header]");
  if (!header) return;
  let ticking = false;
  function update() {
    header.classList.toggle("is-scrolled", window.scrollY > 24);
    ticking = false;
  }
  window.addEventListener(
    "scroll",
    () => {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    },
    { passive: true }
  );
  update();
}

/** Upgrade header dropdowns: click toggles aria-expanded; outside click closes. */
function initDropdowns() {
  const items = Array.from(document.querySelectorAll(".primary-nav__item.has-dropdown"));
  if (!items.length) return;

  function closeAll(except) {
    items.forEach((item) => {
      if (item === except) return;
      item.classList.remove("is-open");
      item.querySelector(".primary-nav__link")?.setAttribute("aria-expanded", "false");
    });
  }

  items.forEach((item) => {
    const link = item.querySelector(".primary-nav__link");
    if (!link) return;
    link.addEventListener("click", (e) => {
      // Pointer users toggle the panel; the href still works via keyboard Enter.
      if (e.detail === 0) return; // keyboard activation: follow the link
      e.preventDefault();
      const open = item.classList.toggle("is-open");
      link.setAttribute("aria-expanded", String(open));
      closeAll(item);
    });
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".primary-nav__item.has-dropdown")) closeAll(null);
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAll(null);
  });
}

/** Build the scroll rail from on-page [data-waypoint] sections. */
function initScrollRail() {
  const rail = document.querySelector("[data-scroll-rail]");
  if (!rail) return;
  const sections = Array.from(document.querySelectorAll("#main [data-waypoint][id]"));
  if (sections.length < 2) return;

  const list = rail.querySelector(".scroll-rail__list");
  const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const links = sections.map((section) => {
    const li = document.createElement("li");
    const a = document.createElement("a");
    a.className = "scroll-rail__tick";
    a.href = "#" + section.id;
    const label = section.getAttribute("data-label") || section.id;
    a.setAttribute("aria-label", label);
    a.innerHTML = '<span class="scroll-rail__dot" aria-hidden="true"></span>' +
      '<span class="scroll-rail__text">' + label + "</span>";
    a.addEventListener("click", (e) => {
      e.preventDefault();
      section.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
      section.setAttribute("tabindex", "-1");
      section.focus({ preventScroll: true });
    });
    li.appendChild(a);
    list.appendChild(li);
    return a;
  });

  rail.hidden = false;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const i = sections.indexOf(entry.target);
        links.forEach((l, j) => {
          l.classList.toggle("is-active", j === i);
          if (j === i) l.setAttribute("aria-current", "true");
          else l.removeAttribute("aria-current");
        });
      });
    },
    { rootMargin: "-45% 0px -45% 0px", threshold: 0 }
  );
  sections.forEach((s) => observer.observe(s));
}

export function initNavigation() {
  initCommandMenu();
  initSheets();
  initHeaderShrink();
  initDropdowns();
  initScrollRail();
}
