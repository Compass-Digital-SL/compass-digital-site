// Compass Digital — reusable motion system.
//
// Small, dependency-free helpers shared across pages. Every effect is a
// progressive enhancement: with JS off, or under prefers-reduced-motion, the
// page is fully usable and all content is visible. We animate only transform
// and opacity, and we unobserve / disconnect once work is done so nothing runs
// offscreen.

const REDUCED = window.matchMedia("(prefers-reduced-motion: reduce)");

/**
 * True when the user asked for less motion. Checked live so a mid-session
 * system change is respected.
 */
export function prefersReducedMotion() {
  return REDUCED.matches;
}

/**
 * Reveal elements as they scroll into view. Elements carry [data-reveal];
 * an optional [data-reveal-delay] (ms) staggers siblings. We add .is-revealed
 * and stop observing — a one-shot, GPU-only transition handled in CSS.
 *
 * @param {ParentNode} [root=document]
 */
export function initScrollReveal(root = document) {
  const items = root.querySelectorAll("[data-reveal]:not(.is-revealed)");
  if (!items.length) return;

  if (prefersReducedMotion() || !("IntersectionObserver" in window)) {
    items.forEach((el) => el.classList.add("is-revealed"));
    return;
  }

  const io = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const delay = Number(el.dataset.revealDelay || 0);
        if (delay) el.style.setProperty("--reveal-delay", `${delay}ms`);
        el.classList.add("is-revealed");
        obs.unobserve(el);
      });
    },
    { rootMargin: "0px 0px -10% 0px", threshold: 0.15 }
  );

  items.forEach((el) => io.observe(el));
}

/**
 * Split a heading into per-letter spans and reveal them in a stagger. The
 * accessible text is preserved: the original text stays as the element's
 * aria-label and the generated spans are aria-hidden.
 *
 * @param {Element} el Element carrying [data-reveal-letters].
 */
export function revealLetters(el) {
  if (!el || el.dataset.lettersDone) return;
  el.dataset.lettersDone = "1";

  const text = el.textContent.replace(/\s+/g, " ").trim();
  el.setAttribute("aria-label", text);

  if (prefersReducedMotion()) {
    el.classList.add("is-revealed");
    return;
  }

  const words = Array.from(el.querySelectorAll(".hero__word"));
  const sources = words.length ? words : [el];
  let index = 0;

  sources.forEach((word) => {
    const chars = Array.from(word.textContent);
    word.textContent = "";
    word.setAttribute("aria-hidden", "true");
    chars.forEach((ch) => {
      const span = document.createElement("span");
      span.className = "hero__char";
      span.textContent = ch;
      span.style.setProperty("--char-index", String(index++));
      word.appendChild(span);
    });
  });

  // Next frame so the initial (hidden) state paints before the transition.
  requestAnimationFrame(() => el.classList.add("is-revealed"));
}

/**
 * Count a number up from 0 when it enters the viewport. The element's text is
 * the target; [data-suffix] / [data-prefix] wrap it. Final value is always set,
 * so reduced-motion and no-JS users still see the real number.
 *
 * @param {ParentNode} [root=document]
 */
export function initCounters(root = document) {
  const counters = root.querySelectorAll("[data-counter]:not(.is-counted)");
  if (!counters.length) return;

  const render = (el, value) => {
    const prefix = el.dataset.prefix || "";
    const suffix = el.dataset.suffix || "";
    el.textContent = `${prefix}${value}${suffix}`;
  };

  const run = (el) => {
    el.classList.add("is-counted");
    const target = Number(el.dataset.counter || el.textContent) || 0;
    if (prefersReducedMotion()) {
      render(el, target);
      return;
    }
    const duration = Number(el.dataset.counterDuration || 1200);
    const start = performance.now();
    const tick = (now) => {
      const p = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
      render(el, Math.round(target * eased));
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };

  if (!("IntersectionObserver" in window)) {
    counters.forEach(run);
    return;
  }

  const io = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        run(entry.target);
        obs.unobserve(entry.target);
      });
    },
    { threshold: 0.6 }
  );
  counters.forEach((el) => io.observe(el));
}

/**
 * Magnetic buttons: the element eases toward the pointer within a small radius.
 * Pointer-only (skipped on coarse/touch and under reduced motion). Resets on
 * leave; uses transform only.
 *
 * @param {ParentNode} [root=document]
 */
export function initMagnetic(root = document) {
  if (prefersReducedMotion() || !window.matchMedia("(pointer: fine)").matches) {
    return;
  }
  const els = root.querySelectorAll("[data-magnetic]");
  els.forEach((el) => {
    const strength = Number(el.dataset.magnetic || 0.3);
    const onMove = (e) => {
      const rect = el.getBoundingClientRect();
      const x = e.clientX - (rect.left + rect.width / 2);
      const y = e.clientY - (rect.top + rect.height / 2);
      el.style.transform = `translate3d(${x * strength}px, ${y * strength}px, 0)`;
    };
    const reset = () => {
      el.style.transform = "";
    };
    el.addEventListener("pointermove", onMove);
    el.addEventListener("pointerleave", reset);
  });
}

/**
 * Subtle cursor glow: a single element tracks the pointer via a CSS variable.
 * Pointer-only and reduced-motion aware. One rAF-batched write per frame.
 *
 * @param {Element} el Glow element (position: fixed, pointer-events: none).
 */
export function initCursorGlow(el) {
  if (!el || prefersReducedMotion() || !window.matchMedia("(pointer: fine)").matches) {
    return;
  }
  let x = 0;
  let y = 0;
  let queued = false;
  const paint = () => {
    queued = false;
    el.style.setProperty("--glow-x", `${x}px`);
    el.style.setProperty("--glow-y", `${y}px`);
  };
  window.addEventListener(
    "pointermove",
    (e) => {
      x = e.clientX;
      y = e.clientY;
      el.classList.add("is-active");
      if (!queued) {
        queued = true;
        requestAnimationFrame(paint);
      }
    },
    { passive: true }
  );
}

/**
 * Parallax for a handful of layers ([data-parallax] = speed factor). Driven by
 * a single rAF loop that only runs while any layer is on screen; pointer and
 * reduced-motion safe. Max three layers by design (perf budget).
 *
 * @param {ParentNode} [root=document]
 * @returns {() => void} teardown
 */
export function initParallax(root = document) {
  const layers = Array.from(root.querySelectorAll("[data-parallax]")).slice(0, 3);
  if (!layers.length || prefersReducedMotion()) return () => {};

  let visible = false;
  let ticking = false;

  const update = () => {
    ticking = false;
    const vh = window.innerHeight;
    layers.forEach((layer) => {
      const rect = layer.getBoundingClientRect();
      const mid = rect.top + rect.height / 2;
      const offset = (mid - vh / 2) * Number(layer.dataset.parallax || 0.1);
      layer.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
    });
  };

  const onScroll = () => {
    if (!visible || ticking) return;
    ticking = true;
    requestAnimationFrame(update);
  };

  const io =
    "IntersectionObserver" in window
      ? new IntersectionObserver(
          (entries) => {
            visible = entries.some((e) => e.isIntersecting);
            if (visible) onScroll();
          },
          { rootMargin: "20% 0px" }
        )
      : null;

  if (io) layers.forEach((l) => io.observe(l));
  else visible = true;

  window.addEventListener("scroll", onScroll, { passive: true });
  update();

  return () => {
    window.removeEventListener("scroll", onScroll);
    if (io) io.disconnect();
  };
}

/**
 * Rotate a decorative compass needle anti-clockwise in proportion to how far
 * the page is scrolled. The needle carries [data-compass-needle]; rotation is
 * written to a CSS custom property so the compositor does the work (transform
 * only). One rAF-batched write per scroll frame. Under reduced motion the
 * needle is left at rest — the whole effect is purely decorative.
 *
 * @param {Element} el Watermark root ([data-compass]).
 * @returns {() => void} teardown
 */
export function initScrollCompass(el) {
  if (!el || prefersReducedMotion()) return () => {};

  const needle = el.querySelector("[data-compass-needle]") || el;
  // Total travel across the full scrollable range. Negative = anti-clockwise.
  const turn = Number(el.dataset.compassTurn || 540);
  let ticking = false;

  const update = () => {
    ticking = false;
    const doc = document.documentElement;
    const scrollable = Math.max(1, doc.scrollHeight - window.innerHeight);
    const progress = Math.min(Math.max(window.scrollY / scrollable, 0), 1);
    needle.style.setProperty("--compass-rot", `${(-progress * turn).toFixed(2)}deg`);
  };

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(update);
  };

  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll, { passive: true });
  update();

  return () => {
    window.removeEventListener("scroll", onScroll);
    window.removeEventListener("resize", onScroll);
  };
}
