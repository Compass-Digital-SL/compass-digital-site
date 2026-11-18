// Compass Digital — /work/ filter chips.
// Progressive enhancement: the chips are real links that reload with a
// ?service= / ?industry= query arg and are filtered server-side, so every
// filtered URL is shareable and the page works with JS off. Here we intercept
// the clicks to filter the already-rendered cards in place and keep the URL in
// sync via the History API — no reload, no extra request.

/** Read the active filter for a group from the current URL. */
function fromUrl(group) {
  return new URLSearchParams(location.search).get(group) || "";
}

export function initWorkFilter(root = document) {
  const filter = root.querySelector("[data-work-filter]");
  const grid = root.querySelector("[data-work-grid]");
  if (!filter || !grid) return;

  const cards = Array.from(grid.querySelectorAll("[data-work-card]"));
  const empty = root.querySelector("[data-work-empty]");
  const groups = Array.from(filter.querySelectorAll("[data-filter-group]"));

  // Current selection, keyed by group (e.g. { service: "seo", industry: "" }).
  const state = {};
  groups.forEach((g) => (state[g.dataset.filterGroup] = fromUrl(g.dataset.filterGroup)));

  const cardMatches = (card) =>
    groups.every((g) => {
      const name = g.dataset.filterGroup;
      const value = state[name];
      if (!value) return true;
      const have = (card.dataset[name === "service" ? "services" : "industries"] || "")
        .split(/\s+/)
        .filter(Boolean);
      return have.includes(value);
    });

  const render = () => {
    // Reflect the active chip per group.
    groups.forEach((g) => {
      const name = g.dataset.filterGroup;
      g.querySelectorAll(".work-chip").forEach((chip) => {
        const on = (chip.dataset.filterValue || "") === state[name];
        chip.classList.toggle("is-active", on);
        if (on) chip.setAttribute("aria-current", "true");
        else chip.removeAttribute("aria-current");
      });
    });

    // Show/hide cards; `hidden` keeps them out of the a11y tree too.
    let visible = 0;
    cards.forEach((card) => {
      const show = cardMatches(card);
      card.hidden = !show;
      if (show) visible += 1;
    });
    if (empty) empty.hidden = visible !== 0;
  };

  const syncUrl = () => {
    const params = new URLSearchParams();
    Object.keys(state).forEach((k) => {
      if (state[k]) params.set(k, state[k]);
    });
    const qs = params.toString();
    history.pushState(state, "", qs ? `${location.pathname}?${qs}` : location.pathname);
  };

  filter.addEventListener("click", (e) => {
    const chip = e.target.closest(".work-chip");
    if (!chip || !filter.contains(chip)) return;
    const group = chip.closest("[data-filter-group]");
    if (!group) return;
    e.preventDefault();
    state[group.dataset.filterGroup] = chip.dataset.filterValue || "";
    render();
    syncUrl();
  });

  window.addEventListener("popstate", () => {
    groups.forEach((g) => (state[g.dataset.filterGroup] = fromUrl(g.dataset.filterGroup)));
    render();
  });

  render();
}
