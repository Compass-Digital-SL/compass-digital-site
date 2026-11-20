// @ts-check
const { AxeBuilder } = require("@axe-core/playwright");

const AXE_TAGS = ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"];

/**
 * Wait for finite CSS animations/transitions (scroll reveals, letter
 * staggers) to finish. Without this, axe can sample text mid-fade, when its
 * blended colour sits almost on the background, and report false contrast
 * failures. Infinite animations (loops) are ignored so this always resolves.
 *
 * @param {import("@playwright/test").Page} page
 */
async function settleMotion(page) {
  await page.evaluate(async () => {
    // Let IntersectionObserver / rAF callbacks start their transitions first.
    await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));
    const finite = document
      .getAnimations()
      .filter((a) => a.effect?.getComputedTiming().endTime !== Infinity);
    await Promise.all(finite.map((a) => a.finished.catch(() => {})));
  });
}

/**
 * Run axe once motion has settled and return critical/serious violations.
 *
 * @param {import("@playwright/test").Page} page
 */
async function axeBlocking(page) {
  await settleMotion(page);
  const results = await new AxeBuilder({ page }).withTags(AXE_TAGS).analyze();
  return results.violations.filter(
    (v) => v.impact === "critical" || v.impact === "serious"
  );
}

module.exports = { AXE_TAGS, settleMotion, axeBlocking };
