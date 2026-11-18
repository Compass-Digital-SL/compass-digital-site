// Compass Digital — Insights enhancements.
// Progressive enhancement only; every piece works with JS off.
//  - Category chips filter the already-rendered cards in place (shareable URLs).
//  - The table of contents highlights the section in view (scroll-spy).
//  - The "Copy link" share button copies the URL to the clipboard.

/** Category filter chips on the Insights index. */
export function initInsightsFilter(root = document) {
  const filter = root.querySelector("[data-insights-filter]");
  const grid = root.querySelector("[data-post-grid]");
  if (!filter || !grid) return;

  const cards = Array.from(grid.querySelectorAll("[data-post-card]"));
  const empty = root.querySelector("[data-post-empty]");
  const chips = Array.from(filter.querySelectorAll(".post-chip"));

  const fromUrl = () => new URLSearchParams(location.search).get("topic") || "";
  let active = fromUrl();

  const render = () => {
    chips.forEach((chip) => {
      const on = (chip.dataset.filterValue || "") === active;
      chip.classList.toggle("is-active", on);
      if (on) chip.setAttribute("aria-current", "true");
      else chip.removeAttribute("aria-current");
    });

    let visible = 0;
    cards.forEach((card) => {
      const cats = (card.dataset.categories || "").split(/\s+/).filter(Boolean);
      const show = !active || cats.includes(active);
      card.hidden = !show;
      if (show) visible += 1;
    });
    if (empty) empty.hidden = visible !== 0;
  };

  filter.addEventListener("click", (e) => {
    const chip = e.target.closest(".post-chip");
    if (!chip || !filter.contains(chip)) return;
    e.preventDefault();
    active = chip.dataset.filterValue || "";
    const params = new URLSearchParams();
    if (active) params.set("topic", active);
    const qs = params.toString();
    history.pushState({ topic: active }, "", qs ? `${location.pathname}?${qs}` : location.pathname);
    render();
  });

  window.addEventListener("popstate", () => {
    active = fromUrl();
    render();
  });

  render();
}

/** Scroll-spy: mark the TOC link for the heading currently in view. */
export function initToc(root = document) {
  const toc = root.querySelector("[data-insight-toc]");
  if (!toc || !("IntersectionObserver" in window)) return;

  const links = new Map(
    Array.from(toc.querySelectorAll("[data-toc-link]")).map((a) => [a.dataset.tocLink, a])
  );
  if (!links.size) return;

  const headings = Array.from(links.keys())
    .map((id) => document.getElementById(id))
    .filter(Boolean);

  let current = null;
  const setCurrent = (id) => {
    if (id === current) return;
    current = id;
    links.forEach((a, key) => a.classList.toggle("is-current", key === id));
  };

  const observer = new IntersectionObserver(
    (entries) => {
      const visible = entries
        .filter((e) => e.isIntersecting)
        .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
      if (visible[0]) setCurrent(visible[0].target.id);
    },
    { rootMargin: "-20% 0px -70% 0px", threshold: 0 }
  );

  headings.forEach((h) => observer.observe(h));
}

/** "Copy link" share button → clipboard, with a brief confirmation. */
export function initCopyLink(root = document) {
  const btn = root.querySelector("[data-copy-link]");
  if (!btn || !navigator.clipboard) return;

  const original = btn.querySelector("[aria-hidden]");
  const copiedLabel = btn.dataset.copiedLabel || "Copied";

  btn.addEventListener("click", (e) => {
    e.preventDefault();
    navigator.clipboard.writeText(btn.getAttribute("href")).then(() => {
      btn.classList.add("is-copied");
      if (original) {
        const prev = original.textContent;
        original.textContent = copiedLabel;
        setTimeout(() => {
          original.textContent = prev;
          btn.classList.remove("is-copied");
        }, 2000);
      }
    });
  });
}

export function initInsights(root = document) {
  initInsightsFilter(root);
  initToc(root);
  initCopyLink(root);
}
