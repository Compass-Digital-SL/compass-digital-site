// @ts-check
const { test, expect } = require("@playwright/test");
const { axeBlocking } = require("./helpers");

/**
 * Accessibility + responsive QA (Task 19).
 *
 * Runs on every project in playwright.config.js (Chromium, Firefox, WebKit).
 * Covers what axe cannot: responsive axe at phone/tablet widths, reflow with
 * no horizontal scroll at 320px, zoom to 200%, visible keyboard focus, touch
 * target sizes, the skip link, and reduced-motion behaviour.
 */

const WIDTHS = { mobile: 360, tablet: 768, desktop: 1440 };

// Representative set spanning every template type.
const routes = [
  { name: "home", path: "/" },
  { name: "services-hub", path: "/services/" },
  { name: "service-seo", path: "/services/seo/" },
  { name: "industries-hub", path: "/industries/" },
  { name: "industry-travel", path: "/industries/travel-and-tourism/" },
  { name: "work-archive", path: "/work/" },
  { name: "about", path: "/about/" },
  { name: "process", path: "/process/" },
  { name: "faq", path: "/faq/" },
  { name: "insights", path: "/insights/" },
  { name: "insight-post", path: "/insights/website-cost-sri-lanka/" },
  { name: "quote", path: "/get-a-quote/" },
  { name: "contact", path: "/contact/" },
  { name: "thank-you", path: "/thank-you/" },
];

// --- Responsive axe: phone and tablet widths (desktop is covered by smoke) ---
for (const [label, width] of [
  ["mobile", WIDTHS.mobile],
  ["tablet", WIDTHS.tablet],
]) {
  test.describe(`axe @ ${label} (${width}px)`, () => {
    for (const r of routes) {
      test(`${r.name} has no critical/serious violations`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        await page.goto(r.path, { waitUntil: "networkidle" });
        const blocking = await axeBlocking(page);
        expect(
          blocking,
          blocking.map((v) => `${v.id} (${v.impact}): ${v.help}`).join("\n")
        ).toEqual([]);
      });
    }
  });
}

// --- Reflow: no horizontal scrollbar at 320px (WCAG 1.4.10) ---
test.describe("reflow @ 320px", () => {
  for (const r of routes) {
    test(`${r.name} has no horizontal overflow`, async ({ page }) => {
      await page.setViewportSize({ width: 320, height: 800 });
      await page.goto(r.path, { waitUntil: "networkidle" });
      const overflow = await page.evaluate(() => {
        const d = document.documentElement;
        return d.scrollWidth - d.clientWidth;
      });
      // Allow 1px for sub-pixel rounding.
      expect(overflow, `horizontal overflow on ${r.name}`).toBeLessThanOrEqual(1);
    });
  }
});

// --- Zoom to 200%: content stays usable, no 2D scrolling trap ---
test.describe("zoom 200%", () => {
  for (const r of ["/", "/services/seo/", "/get-a-quote/"]) {
    test(`${r} reflows at 200% zoom`, async ({ page }) => {
      // 1280 logical / 2 ≈ a 640px-wide reading column, the reflow target.
      await page.setViewportSize({ width: 640, height: 900 });
      await page.goto(r, { waitUntil: "networkidle" });
      const overflow = await page.evaluate(() => {
        const d = document.documentElement;
        return d.scrollWidth - d.clientWidth;
      });
      expect(overflow).toBeLessThanOrEqual(1);
      await expect(page.locator("h1").first()).toBeVisible();
    });
  }
});

// --- Skip link: first focusable, hidden until focused, moves focus to main ---
// Uses .focus() rather than a Tab press: WebKit does not Tab to links unless
// full keyboard access is on, but the link is still focusable and usable.
test("skip link reaches main content", async ({ page }) => {
  await page.goto("/", { waitUntil: "networkidle" });
  const skip = page.locator("a.skip-link");

  // It is the first focusable element in the DOM.
  const firstFocusable = await page.evaluate(() => {
    const el = document.querySelector(
      'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    return el?.classList.contains("skip-link") ?? false;
  });
  expect(firstFocusable, "skip link is the first focusable element").toBeTruthy();

  // Visually hidden off-screen until focused, then on-screen (after the
  // reveal transition — poll so WebKit's animation has time to settle).
  const hiddenTop = await skip.evaluate((el) => el.getBoundingClientRect().top);
  expect(hiddenTop, "skip link starts off-screen").toBeLessThan(0);
  await skip.focus();
  await expect(skip).toBeFocused();
  await expect
    .poll(async () => skip.evaluate((el) => el.getBoundingClientRect().top), {
      message: "skip link becomes visible on focus",
    })
    .toBeGreaterThanOrEqual(0);

  // Activating it sends focus into the main landmark.
  await page.keyboard.press("Enter");
  const movedToMain = await page.evaluate(() => {
    const main = document.getElementById("main");
    return main === document.activeElement || main?.contains(document.activeElement);
  });
  expect(movedToMain).toBeTruthy();
});

// --- Visible keyboard focus: every header nav link shows an outline ---
test("header nav links have a visible focus ring", async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto("/", { waitUntil: "networkidle" });
  const links = page.locator(".site-header a[href]");
  const count = await links.count();
  expect(count).toBeGreaterThan(0);
  for (let i = 0; i < count; i++) {
    const link = links.nth(i);
    if (!(await link.isVisible())) continue;
    await link.focus();
    const ring = await link.evaluate((el) => {
      const s = getComputedStyle(el);
      const w = parseFloat(s.outlineWidth) || 0;
      return { outline: w, shadow: s.boxShadow };
    });
    expect(
      ring.outline >= 1 || (ring.shadow && ring.shadow !== "none"),
      `no visible focus indicator on header link #${i}`
    ).toBeTruthy();
  }
});

// --- Command menu: opening it shows a visible focus ring on the search row ---
test("command menu search has a visible focus indicator", async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto("/", { waitUntil: "networkidle" });
  // Opened from its header trigger (the keyboard shortcut is an unmodified "k").
  await page.locator("[data-command-open]").first().click();
  const input = page.locator(".command-menu__input");
  await expect(input).toBeFocused();
  const ring = await page.evaluate(() => {
    const row = document.querySelector(".command-menu__search");
    if (!row) return 0;
    return parseFloat(getComputedStyle(row).outlineWidth) || 0;
  });
  expect(ring, "no focus-within ring on command search").toBeGreaterThanOrEqual(1);
});

// --- Touch targets: mobile bottom-bar buttons are at least 44px ---
test("mobile bar targets are at least 44px", async ({ page }) => {
  await page.setViewportSize({ width: 360, height: 800 });
  await page.goto("/", { waitUntil: "networkidle" });
  const btns = page.locator(".mobile-bar__btn");
  const count = await btns.count();
  expect(count).toBeGreaterThan(0);
  for (let i = 0; i < count; i++) {
    const box = await btns.nth(i).boundingBox();
    expect(box, `mobile bar button #${i} has no box`).not.toBeNull();
    expect(box.height, `mobile bar button #${i} height`).toBeGreaterThanOrEqual(44);
    expect(box.width, `mobile bar button #${i} width`).toBeGreaterThanOrEqual(44);
  }
});

// --- Reduced motion: home loads static, with no animation and no errors ---
test.describe("prefers-reduced-motion", () => {
  test.use({ reducedMotion: "reduce" });
  test("home renders statically with no console errors", async ({ page }) => {
    const errors = [];
    page.on("pageerror", (e) => errors.push(e.message));
    page.on("console", (m) => {
      if (m.type() === "error" && !m.text().startsWith("Failed to load resource")) {
        errors.push(m.text());
      }
    });
    await page.goto("/", { waitUntil: "networkidle" });
    await expect(page.locator("h1").first()).toBeVisible();
    expect(errors, errors.join("\n")).toEqual([]);
  });
});
