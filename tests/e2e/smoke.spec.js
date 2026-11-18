// @ts-check
const { test, expect } = require("@playwright/test");
const { AxeBuilder } = require("@axe-core/playwright");

/**
 * Collect console errors and uncaught page errors while a page loads.
 * Returns an array of message strings (empty = clean).
 */
function watchForErrors(page) {
  const errors = [];
  page.on("console", (msg) => {
    if (msg.type() === "error") {
      errors.push(`console.error: ${msg.text()}`);
    }
  });
  page.on("pageerror", (err) => {
    errors.push(`pageerror: ${err.message}`);
  });
  return errors;
}

const pages = [
  { name: "home", path: "/", expectStatus: 200, h1: true },
  { name: "quote", path: "/get-a-quote/", expectStatus: 200, h1: true },
  { name: "404", path: "/this-page-does-not-exist/", expectStatus: 404, h1: false },
];

for (const p of pages) {
  test.describe(p.name, () => {
    test(`loads with no console errors (${p.name})`, async ({ page }) => {
      const errors = watchForErrors(page);
      const response = await page.goto(p.path, { waitUntil: "networkidle" });

      expect(response, "navigation produced a response").not.toBeNull();
      expect(response.status()).toBe(p.expectStatus);

      // Core landmarks render.
      await expect(page.locator("#main")).toBeAttached();
      if (p.h1) {
        await expect(page.locator("h1").first()).toBeVisible();
      }

      expect(errors, `console/page errors on ${p.name}:\n${errors.join("\n")}`).toEqual([]);
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
