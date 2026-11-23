// Where we work — the dynamic location map.
//
// Lazy chunk: main.js imports this only when the #worldwide section nears the
// viewport, so nothing map-related (incl. the bundled outlines) is fetched up
// front and Lighthouse is unaffected.
//
// Position, in order (see the task brief):
//   1. Browser time zone -> country + city, from the bundled tz-data table.
//      No network call, no permission prompt.
//   2. Generic / unmapped zone -> no confident place; show the world and invite
//      the opt-in link below.
//   3. Opt-in "use my exact location" link -> navigator.geolocation on click
//      only. Kept in memory, never stored or sent anywhere.
//
// Colours come from the --accent CSS variables via CSS classes, so the accent
// picker updates the map live with no JS hook. Mercator projection; the camera
// is one <g> we transform. prefers-reduced-motion disables the zoom + pulse.

import { COUNTRIES } from "./geo-50m.js";
import { TZ } from "./tz-data.js";

const VBW = 1000; // viewBox width (the "screen")
const VBH = 680; // viewBox height — matches the CSS map aspect ratio
const PW = 1000; // projected world width
const DEG = Math.PI / 180;
const FIT = 0.8; // country fills ~80% of the view
const GLOW = 26; // desired on-screen glow blur (px), kept constant via 1/zoom
const ZOOM_MS = 900;

const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/** Mercator projection into world units. Latitude is clamped to avoid infinity. */
function project(lon, lat) {
  const x = ((lon + 180) / 360) * PW;
  const r = Math.max(-85, Math.min(85, lat)) * DEG;
  const y = (PW / (2 * Math.PI)) * (Math.PI - Math.log(Math.tan(Math.PI / 4 + r / 2)));
  return [x, y];
}

/** Camera transform fitting bbox [w,s,e,n] to ~FIT of the view. */
function fit(bbox, pad = FIT) {
  const [w, s, e, n] = bbox;
  const [x0, y0] = project(w, n); // top-left (north-west)
  const [x1, y1] = project(e, s); // bottom-right (south-east)
  const bw = Math.max(1e-3, x1 - x0);
  const bh = Math.max(1e-3, y1 - y0);
  const scale = pad * Math.min(VBW / bw, VBH / bh);
  const cx = (x0 + x1) / 2;
  const cy = (y0 + y1) / 2;
  return { scale, tx: VBW / 2 - scale * cx, ty: VBH / 2 - scale * cy };
}

/** Build the "d" attribute for a country's outer rings. */
function pathFor(rings) {
  let d = "";
  for (const ring of rings) {
    d += "M";
    for (let i = 0; i < ring.length; i++) {
      const [lon, lat] = ring[i];
      const [x, y] = project(lon, lat);
      d += (i ? "L" : "") + x.toFixed(1) + " " + y.toFixed(1);
    }
    d += "Z";
  }
  return d;
}

const svgNS = "http://www.w3.org/2000/svg";
const el = (name, attrs) => {
  const node = document.createElementNS(svgNS, name);
  for (const k in attrs) node.setAttribute(k, attrs[k]);
  return node;
};

export function initLocalMap(root = document) {
  const host = root.querySelector("[data-local-map]");
  if (!host || host.dataset.ready) return;
  host.dataset.ready = "1";

  // Resolve the visitor's zone -> place.
  let zone = "";
  try {
    zone = Intl.DateTimeFormat().resolvedOptions().timeZone || "";
  } catch {
    /* older browsers: fall back to the world view */
  }
  const hit = TZ[zone];
  let place = hit
    ? { country: hit[0], city: hit[1], lat: hit[2], lon: hit[3], approx: !!hit[4], source: "your time zone" }
    : null;

  // --- build the SVG ---
  const svg = el("svg", {
    viewBox: `0 0 ${VBW} ${VBH}`,
    preserveAspectRatio: "xMidYMid meet",
    role: "img",
    class: "local-map__svg",
  });
  const cam = el("g", { class: "local-map__cam" }); // the camera we transform
  svg.appendChild(cam);

  // Base layer: every country, dimmed. Active country gets its own classes.
  const activeName = place && place.country;
  let activePath = null;
  let glowPath = null;
  for (const c of COUNTRIES) {
    const d = pathFor(c.r);
    if (c.n === activeName) {
      glowPath = el("path", { d, class: "local-map__glow" });
      cam.appendChild(glowPath);
      activePath = el("path", { d, class: "local-map__country local-map__country--active", "vector-effect": "non-scaling-stroke" });
      cam.appendChild(activePath);
    } else {
      cam.appendChild(el("path", { d, class: "local-map__country", "vector-effect": "non-scaling-stroke" }));
    }
  }

  // Pin overlay (HTML, so it keeps a constant size and crisp label).
  const pin = document.createElement("span");
  pin.className = "local-map__pin";
  pin.hidden = true;
  const pinLabel = document.createElement("span");
  pinLabel.className = "local-map__pin-label";
  pin.appendChild(pinLabel);

  const figure = document.createElement("div");
  figure.className = "local-map__figure";
  figure.appendChild(svg);
  figure.appendChild(pin);
  host.appendChild(figure);
  host.removeAttribute("aria-busy");
  const metaEl = root.querySelector("[data-map-meta]");
  if (metaEl) metaEl.hidden = false;

  // Caption slots (place chip, coords, source, opt-in link) live in the PHP.
  const chipEl = root.querySelector("[data-map-place]");
  const coordsEl = root.querySelector("[data-map-coords]");
  const sourceEl = root.querySelector("[data-map-source]");
  const locate = root.querySelector("[data-map-locate]");
  const msgEl = root.querySelector("[data-map-msg]");

  // Current camera, so the pin can be re-placed after each move.
  let cam3 = { scale: 1, tx: 0, ty: 0 };

  function applyCamera(target, animate) {
    const go = () => {
      cam.style.transform = `translate(${target.tx}px, ${target.ty}px) scale(${target.scale})`;
      // Keep the glow blur visually constant as the camera scales.
      if (glowPath) glowPath.style.setProperty("--map-glow", GLOW / target.scale + "px");
      cam3 = target;
    };
    if (animate && !reduceMotion) {
      cam.style.transition = `transform ${ZOOM_MS}ms var(--ease-out, cubic-bezier(.22,1,.36,1))`;
      requestAnimationFrame(() => requestAnimationFrame(go));
    } else {
      cam.style.transition = "none";
      go();
    }
  }

  function placePin(lon, lat, label) {
    const [x, y] = project(lon, lat);
    const sx = cam3.tx + cam3.scale * x;
    const sy = cam3.ty + cam3.scale * y;
    pin.style.left = (sx / VBW) * 100 + "%";
    pin.style.top = (sy / VBH) * 100 + "%";
    pinLabel.textContent = label;
    pin.hidden = false;
  }

  function fmtCoords(lat, lon) {
    const ns = lat >= 0 ? "N" : "S";
    const ew = lon >= 0 ? "E" : "W";
    return `${Math.abs(lat).toFixed(4)}° ${ns}, ${Math.abs(lon).toFixed(4)}° ${ew}`;
  }

  function render(p, animate) {
    const label = p.city;
    svg.setAttribute("aria-label", `Map showing ${p.city}, ${p.country}`);
    const target = fit(p.bbox || bboxFor(p.country) || worldBox(), FIT);
    applyCamera(target, animate);
    const showPin = () => placePin(p.lon, p.lat, label);
    if (animate && !reduceMotion) setTimeout(showPin, ZOOM_MS - 120);
    else showPin();
    if (chipEl) chipEl.textContent = p.approx ? `${p.city}, ${p.country} (approx.)` : `${p.city}, ${p.country}`;
    if (coordsEl) coordsEl.textContent = fmtCoords(p.lat, p.lon);
    if (sourceEl) sourceEl.textContent = p.source;
  }

  function bboxFor(country) {
    const c = COUNTRIES.find((x) => x.n === country);
    return c ? c.b : null;
  }
  function worldBox() {
    return [-170, -58, 190, 78];
  }

  if (place) {
    place.bbox = bboxFor(place.country);
    // Start from the world, then animate the zoom in.
    applyCamera(fit(worldBox(), 1), false);
    render(place, true);
  } else {
    // Unmapped / generic zone: show the world and nudge the opt-in link.
    svg.setAttribute("aria-label", "World map. Use the link below to show your location.");
    applyCamera(fit(worldBox(), 0.95), false);
    if (chipEl) chipEl.textContent = "Somewhere on Earth";
    if (coordsEl) coordsEl.textContent = "—";
    if (sourceEl) sourceEl.textContent = "an unknown time zone";
  }

  // --- opt-in exact location ---
  if (locate) {
    if (!("geolocation" in navigator)) {
      locate.hidden = true;
    } else {
      locate.addEventListener("click", (e) => {
        e.preventDefault();
        if (msgEl) msgEl.textContent = "";
        locate.setAttribute("aria-busy", "true");
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            locate.removeAttribute("aria-busy");
            const { latitude: lat, longitude: lon } = pos.coords;
            // Tight box around the point; kept in memory only, never stored/sent.
            const d = 1.1;
            render(
              {
                country: (place && place.country) || "your area",
                city: "You are here",
                lat,
                lon,
                approx: false,
                source: "your device",
                bbox: [lon - d, lat - d * 0.7, lon + d, lat + d * 0.7],
              },
              true
            );
            locate.hidden = true;
          },
          (err) => {
            locate.removeAttribute("aria-busy");
            if (msgEl) {
              msgEl.textContent =
                err.code === err.PERMISSION_DENIED
                  ? "No problem — we’ll keep the approximate spot."
                  : "Couldn’t get an exact fix just now.";
            }
          },
          { enableHighAccuracy: false, timeout: 8000, maximumAge: 600000 }
        );
      });
    }
  }
}
