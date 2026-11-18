// @ts-check
const { test, expect } = require("@playwright/test");
const { AxeBuilder } = require("@axe-core/playwright");

/**
 * Watch a page for JS errors and failed network responses.
 *
 * `errors`  — console.error output and uncaught page errors (app bugs).
 * `failed`  — any response with status >= 400, including the main document.
 *             Browser "Failed to load resource" console noise is dropped here
 *             because the same failures are captured (with URL + status) via the
 *             response event, letting a route whitelist its own expected status
 *             (e.g. the 404 page's own 404) without masking real sub-resource 404s.
 */
function watchForErrors(page) {
  const errors = [];
  const failed = [];
  page.on("console", (msg) => {
    if (msg.type() === "error" && !msg.text().startsWith("Failed to load resource")) {
      errors.push(`console.error: ${msg.text()}`);
    }
  });
  page.on("pageerror", (err) => {
    errors.push(`pageerror: ${err.message}`);
  });
  page.on("response", (res) => {
    if (res.status() >= 400) {
      failed.push({ url: res.url(), status: res.status() });
    }
  });
  return { errors, failed };
}

const pages = [
  { name: "home", path: "/", expectStatus: 200, h1: true },
  { name: "quote", path: "/get-a-quote/", expectStatus: 200, h1: true },
  { name: "services-hub", path: "/services/", expectStatus: 200, h1: true },
  { name: "service-seo", path: "/services/seo/", expectStatus: 200, h1: true },
  { name: "industries-hub", path: "/industries/", expectStatus: 200, h1: true },
  { name: "industry-travel", path: "/industries/travel-and-tourism/", expectStatus: 200, h1: true },
  { name: "404", path: "/this-page-does-not-exist/", expectStatus: 404, h1: false },
];

for (const p of pages) {
  test.describe(p.name, () => {
    test(`loads with no console errors (${p.name})`, async ({ page }) => {
      const { errors, failed } = watchForErrors(page);
      const response = await page.goto(p.path, { waitUntil: "networkidle" });

      expect(response, "navigation produced a response").not.toBeNull();
      expect(response.status()).toBe(p.expectStatus);

      // Core landmarks render.
      await expect(page.locator("#main")).toBeAttached();
      if (p.h1) {
        await expect(page.locator("h1").first()).toBeVisible();
      }

      // The main document may carry the route's expected status (e.g. 404);
      // any other failed request is a real broken resource.
      const brokenResources = failed.filter(
        (r) => !(r.url === response.url() && r.status === p.expectStatus)
      );

      expect(errors, `console/page errors on ${p.name}:\n${errors.join("\n")}`).toEqual([]);
      expect(
        brokenResources,
        `failed resource requests on ${p.name}:\n${brokenResources
          .map((r) => `${r.status} ${r.url}`)
          .join("\n")}`
      ).toEqual([]);
    });

    test(`has no critical/serious axe violations (${p.name})`, async ({ page }) => {
      await page.goto(p.path, { waitUntil: "networkidle" });

      const results = await new AxeBuilder({ page })
        .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
        .analyze();

      const blocking = results.violations.filter(
        (v) => v.impact === "critical" || v.impact === "serious"
      );

      expect(
        blocking,
        blocking
          .map((v) => `${v.id} (${v.impact}): ${v.help}`)
          .join("\n")
      ).toEqual([]);
    });
  });
}
