// Compass Digital — hero ("The Expedition") behaviour.
//
// Loaded on demand (see main.js) only when the expedition hero is on the page,
// so inner pages never run or parse it. The needle follows the pointer; on
// touch it drifts on its own, and — only after an explicit tap — tilts with
// device motion. The bearing the needle points at glows. Everything degrades
// to a calm static compass under prefers-reduced-motion.

import {
  prefersReducedMotion,
  initScrollReveal,
  revealLetters,
  initMagnetic,
  initCursorGlow,
  initParallax,
} from "./motion.js";

const TAU = Math.PI * 2;

/**
 * Shortest signed difference between two angles (deg), in (-180, 180].
 */
function angleDelta(a, b) {
  let d = (a - b) % 360;
  if (d > 180) d -= 360;
  if (d < -180) d += 360;
  return d;
}

/**
 * Drive the needle: track a target angle (deg from north), ease toward it each
 * frame, and light the nearest bearing. Returns a controller with setTarget()
 * and stop().
 */
function createNeedle(needle, bearings) {
  let current = 0;
  let target = 0;
  let raf = 0;
  let running = false;

  const bearingAngles = bearings.map((el) => Number(el.dataset.angle || 0));
  let activeIndex = -1;

  const setActive = () => {
    // Nearest bearing within a 30° arc glows; none if the needle is between.
    let best = -1;
    let bestDist = 31;
    bearingAngles.forEach((angle, i) => {
      const dist = Math.abs(angleDelta(current, angle));
      if (dist < bestDist) {
        bestDist = dist;
        best = i;
      }
    });
    if (best === activeIndex) return;
    if (activeIndex > -1) bearings[activeIndex].classList.remove("is-active");
    if (best > -1) bearings[best].classList.add("is-active");
    activeIndex = best;
  };

  const frame = () => {
    const delta = angleDelta(target, current);
    current += delta * 0.12; // critically-ish damped lerp
    needle.style.transform = `rotate(${current.toFixed(2)}deg)`;
    setActive();
    if (Math.abs(delta) > 0.05) {
      raf = requestAnimationFrame(frame);
    } else {
      running = false;
    }
  };

  const kick = () => {
    if (running) return;
    running = true;
    raf = requestAnimationFrame(frame);
  };

  return {
    setTarget(deg) {
      target = deg;
      kick();
    },
    get current() {
      return current;
    },
    stop() {
      cancelAnimationFrame(raf);
      running = false;
    },
  };
}

/**
 * Point the needle at the pointer, measured from the compass centre.
 */
function initPointerTracking(compass, needle) {
  const onMove = (e) => {
    const rect = compass.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;
    const angle = (Math.atan2(e.clientY - cy, e.clientX - cx) * 180) / Math.PI;
    // atan2 is measured from east; the needle's 0 is north, so add 90.
    needle.setTarget(angle + 90);
  };
  window.addEventListener("pointermove", onMove, { passive: true });
}

/**
 * Gentle autonomous drift for touch devices — a slow wander so the compass
 * feels alive without input.
 */
function initDrift(needle) {
  let t = Math.random() * TAU;
  const step = () => {
    t += 0.01;
    // Lissajous-ish wander across ~140° of travel.
    const deg = Math.sin(t) * 70 + Math.sin(t * 0.5) * 20;
    needle.setTarget(deg);
    needle._drift = requestAnimationFrame(step);
  };
  needle._drift = requestAnimationFrame(step);
  return () => cancelAnimationFrame(needle._drift);
}

/**
 * Device-motion tilt, strictly opt-in. On iOS 13+ orientation needs a user
 * gesture to request permission; elsewhere we just attach. The toggle button
 * is only shown when the API exists and the pointer is coarse (touch).
 */
function initMotionToggle(toggle, needle, stopDrift) {
  if (!toggle || !("DeviceOrientationEvent" in window)) return;
  const coarse = window.matchMedia("(pointer: coarse)").matches;
  if (!coarse) return;

  toggle.hidden = false;

  const attach = () => {
    stopDrift();
    window.addEventListener(
      "deviceorientation",
      (e) => {
        if (e.gamma == null || e.beta == null) return;
        // gamma: left/right tilt (-90..90), beta: front/back (-180..180).
        const angle = Math.atan2(e.gamma, e.beta) * (180 / Math.PI);
        needle.setTarget(angle);
      },
      { passive: true }
    );
    toggle.setAttribute("hidden", "");
  };

  toggle.addEventListener("click", () => {
    const req = window.DeviceOrientationEvent.requestPermission;
    if (typeof req === "function") {
      req()
        .then((state) => {
          if (state === "granted") attach();
        })
        .catch(() => {});
    } else {
      attach();
    }
  });
}

/**
 * Smooth-scroll a bearing/explore click to its target section, with a focus
 * handoff so keyboard users land on the destination. Falls back to the native
 * jump when the target does not exist yet (Task 10 sections).
 */
function initBearingScroll(root) {
  const links = root.querySelectorAll(".bearing__link, [data-hero-explore]");
  links.forEach((link) => {
    link.addEventListener("click", (e) => {
      const id = (link.getAttribute("href") || "").replace(/^#/, "");
      if (!id) return;
      const target = document.getElementById(id);
      if (!target) return; // let the browser try; section arrives in Task 10
      e.preventDefault();
      const behavior = prefersReducedMotion() ? "auto" : "smooth";
      target.scrollIntoView({ behavior, block: "start" });
      target.setAttribute("tabindex", "-1");
      target.focus({ preventScroll: true });
    });
  });
}

/**
 * Boot the hero. Idempotent-ish: guarded by a dataset flag.
 */
export function initHero() {
  const hero = document.querySelector("[data-hero]");
  if (!hero || hero.dataset.heroReady) return;
  hero.dataset.heroReady = "1";

  // Shared motion effects within the hero.
  initScrollReveal(hero);
  const title = hero.querySelector("[data-reveal-letters]");
  if (title) revealLetters(title);
  initMagnetic(hero);
  initParallax(hero);

  const glow = document.createElement("div");
  glow.className = "cursor-glow";
  glow.setAttribute("aria-hidden", "true");
  hero.appendChild(glow);
  initCursorGlow(glow);

  initBearingScroll(hero);

  const compass = hero.querySelector("[data-compass]");
  const needleEl = hero.querySelector("[data-needle]");
  const bearings = Array.from(hero.querySelectorAll("[data-bearing]"));
  if (!compass || !needleEl) return;

  const needle = createNeedle(needleEl, bearings);
  needle.setTarget(0);

  if (prefersReducedMotion()) {
    // Static: point north, no tracking, no drift.
    return;
  }

  const finePointer = window.matchMedia("(pointer: fine)").matches;
  if (finePointer) {
    initPointerTracking(compass, needle);
  } else {
    const stopDrift = initDrift(needle);
    const toggle = hero.querySelector("[data-motion-toggle]");
    initMotionToggle(toggle, needle, stopDrift);
  }
}
