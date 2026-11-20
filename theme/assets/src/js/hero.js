// Compass Digital — home hero (premium v2).
// The compass needle follows the pointer with a spring, like a real needle
// settling, and the "Heading 000° N" readout tracks it. Mouse/pen only: touch
// visitors see the needle at rest. With reduced motion the needle snaps to the
// pointer instead of swinging. Purely decorative; the hero works without it.

const POINTS = ["N", "NE", "E", "SE", "S", "SW", "W", "NW"];

export function initHero() {
  const hero = document.querySelector("[data-hero]");
  const needle = hero?.querySelector("[data-needle]");
  const rose = hero?.querySelector("[data-rose]");
  if (!needle || !rose) return;

  const readout = hero.querySelector("[data-bearing-readout]");
  const still = window.matchMedia("(prefers-reduced-motion: reduce)");
  const label = readout ? readout.firstChild.textContent.trim() : "Heading";

  let angle = 0;
  let velocity = 0;
  let target = 0;
  let running = false;
  let shown = -1;
  let visible = true;

  const centre = () => {
    const r = rose.getBoundingClientRect();
    return [r.left + r.width / 2, r.top + r.height / 2];
  };

  function draw() {
    needle.setAttribute("transform", `rotate(${angle.toFixed(2)} 400 400)`);
    if (!readout) return;
    const h = ((Math.round(angle) % 360) + 360) % 360;
    if (h === shown) return;
    shown = h;
    const b = document.createElement("b");
    b.textContent = `${String(h).padStart(3, "0")}° ${POINTS[Math.round(h / 45) % 8]}`;
    readout.replaceChildren(document.createTextNode(label + " "), b);
  }

  function tick() {
    // Shortest way round.
    let delta = ((target - angle + 540) % 360) - 180;
    if (still.matches) {
      angle = target;
      velocity = 0;
      delta = 0;
    } else {
      velocity = (velocity + delta * 0.045) * 0.86;
      angle += velocity;
    }
    draw();
    if (Math.abs(delta) > 0.05 || Math.abs(velocity) > 0.05) {
      requestAnimationFrame(tick);
    } else {
      running = false;
    }
  }

  const kick = () => {
    if (!running && visible) {
      running = true;
      requestAnimationFrame(tick);
    }
  };

  const onMove = (e) => {
    if (e.pointerType === "touch") return;
    const [cx, cy] = centre();
    target = (Math.atan2(e.clientX - cx, cy - e.clientY) * 180) / Math.PI; // 0 = north, clockwise
    kick();
  };

  // Only listen while the hero is on screen.
  const io = new IntersectionObserver(([entry]) => {
    visible = entry.isIntersecting;
    if (visible) document.addEventListener("pointermove", onMove, { passive: true });
    else document.removeEventListener("pointermove", onMove);
  });
  io.observe(hero);

  document.documentElement.addEventListener("mouseleave", () => {
    target = 0;
    kick();
  });

  hero.classList.add("is-live");
}
