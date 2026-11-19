/**
 * Compass Digital — cookie consent + analytics loader + event tracking.
 *
 * Self-contained, no dependencies, no build step. Loads Google Analytics 4
 * only after the visitor accepts cookies AND the page is idle, so it never
 * competes with the first paint. Without consent, nothing is loaded and every
 * tracked event is a silent no-op.
 *
 * Config is injected by PHP as window.CompassConsent:
 *   { ga4, cookie, pageEvent: { name, params } | null, policyUrl }
 */
(function () {
  "use strict";

  var cfg = window.CompassConsent || {};
  var KEY = cfg.cookie || "compass_consent";
  var bar = document.getElementById("compass-consent");

  var granted = false;
  var loaded = false;
  var pageEventFired = false;
  var queue = [];

  function store(value) {
    try {
      localStorage.setItem(KEY, value);
    } catch (e) {
      /* private mode — fall back to the in-memory `granted` flag */
    }
  }

  function read() {
    try {
      return localStorage.getItem(KEY);
    } catch (e) {
      return null;
    }
  }

  /* Load GA4 once, after consent + idle. */
  function loadGA() {
    if (loaded || !cfg.ga4) return;
    loaded = true;

    window.dataLayer = window.dataLayer || [];
    window.gtag = function () {
      window.dataLayer.push(arguments);
    };
    window.gtag("js", new Date());
    window.gtag("config", cfg.ga4, { anonymize_ip: true });

    var s = document.createElement("script");
    s.async = true;
    s.src =
      "https://www.googletagmanager.com/gtag/js?id=" +
      encodeURIComponent(cfg.ga4);
    document.head.appendChild(s);

    queue.forEach(function (e) {
      window.gtag("event", e.name, e.params || {});
    });
    queue = [];
  }

  function whenIdle(fn) {
    if ("requestIdleCallback" in window) {
      window.requestIdleCallback(fn, { timeout: 4000 });
    } else if (document.readyState === "complete") {
      window.setTimeout(fn, 1200);
    } else {
      window.addEventListener("load", function () {
        window.setTimeout(fn, 1200);
      });
    }
  }

  /* Public: send an event (dropped silently until consent is granted). */
  window.compassTrack = function (name, params) {
    if (!granted || !cfg.ga4) return;
    if (loaded && window.gtag) {
      window.gtag("event", name, params || {});
    } else {
      queue.push({ name: name, params: params });
    }
  };

  function firePageEvent() {
    if (pageEventFired || !cfg.pageEvent || !cfg.pageEvent.name) return;
    pageEventFired = true;
    window.compassTrack(cfg.pageEvent.name, cfg.pageEvent.params || {});
  }

  function grant() {
    granted = true;
    whenIdle(loadGA);
    firePageEvent();
  }

  /* --- Consent bar --- */
  function showBar() {
    if (bar) bar.hidden = false;
  }
  function hideBar() {
    if (bar) bar.hidden = true;
  }

  function accept() {
    store("granted");
    hideBar();
    grant();
  }
  function decline() {
    store("denied");
    hideBar();
  }

  if (bar) {
    bar.addEventListener("click", function (e) {
      var action = e.target.getAttribute("data-consent-action");
      if (action === "accept") accept();
      else if (action === "decline") decline();
    });
  }

  /* The "Cookie preferences" link(s), e.g. in the footer, reopen the bar. */
  document.addEventListener("click", function (e) {
    var trigger = e.target.closest("[data-consent-open]");
    if (!trigger) return;
    e.preventDefault();
    showBar();
    var first = bar && bar.querySelector("[data-consent-action]");
    if (first) first.focus();
  });

  /* --- Event binding (listeners are harmless until consent is granted) --- */
  function bindEvents() {
    document.addEventListener("click", function (e) {
      var link = e.target.closest("a[href]");
      if (!link) return;
      var href = link.getAttribute("href") || "";
      if (href.indexOf("wa.me") !== -1 || href.indexOf("api.whatsapp.com") !== -1) {
        window.compassTrack("whatsapp_click", { link_url: href });
      } else if (href.indexOf("tel:") === 0) {
        window.compassTrack("phone_click", { number: href.slice(4) });
      }
    });

    var menu = document.getElementById("command-menu");
    if (menu && "MutationObserver" in window) {
      var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
          if (m.attributeName === "hidden" && !menu.hidden) {
            window.compassTrack("command_menu_used", {});
          }
        });
      });
      observer.observe(menu, { attributes: true, attributeFilter: ["hidden"] });
    }
  }

  /* --- Decide on load --- */
  var choice = read();
  if (choice === "granted") {
    grant();
  } else if (choice !== "denied") {
    showBar();
  }
  bindEvents();
})();
