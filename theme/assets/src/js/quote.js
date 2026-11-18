// Quote / Contact form — progressive 3-step wizard.
// The form is a complete, working long form without JS. This only layers a
// step-by-step view and inline validation on top. No dependencies.

export function initQuote() {
  const form = document.querySelector("[data-quote-form]");
  if (!form) return;

  const steps = Array.from(form.querySelectorAll("[data-quote-step]"));
  if (steps.length < 2) return;

  const progress = form.querySelector("[data-quote-progress]");
  const back = form.querySelector("[data-quote-back]");
  const next = form.querySelector("[data-quote-next]");
  const submit = form.querySelector("[data-quote-submit]");
  const summary = form.querySelector("[data-quote-errors]");

  // If the server bounced the form with errors, keep the whole form visible so
  // every error is reachable; just focus the summary.
  if (summary) {
    summary.focus();
    return;
  }

  let current = 0;

  // Enter stepped mode.
  form.classList.add("is-stepped");
  if (progress) progress.hidden = false;
  if (back) back.hidden = false;
  if (next) next.hidden = false;

  const progressSteps = progress
    ? Array.from(progress.querySelectorAll(".quote-progress__step"))
    : [];

  function show(index) {
    current = Math.max(0, Math.min(index, steps.length - 1));

    steps.forEach((step, i) => {
      step.hidden = i !== current;
    });

    progressSteps.forEach((el, i) => {
      el.classList.toggle("is-current", i === current);
      el.classList.toggle("is-done", i < current);
    });

    const isFirst = current === 0;
    const isLast = current === steps.length - 1;
    if (back) back.hidden = isFirst;
    if (next) next.hidden = isLast;
    if (submit) submit.hidden = !isLast;

    // Move focus to the step so screen readers land on the new content.
    const legend = steps[current].querySelector(".quote-step__legend");
    if (legend) {
      legend.setAttribute("tabindex", "-1");
      legend.focus();
    }
  }

  // Validate the fields inside the current step before advancing.
  function stepValid() {
    const step = steps[current];

    // Step 1: require at least one service.
    const checks = step.querySelectorAll('input[name="services[]"]');
    if (checks.length) {
      const anyChecked = Array.from(checks).some((c) => c.checked);
      if (!anyChecked) {
        checks[0].setCustomValidity(
          "Please pick at least one thing you need."
        );
        checks[0].reportValidity();
        checks[0].addEventListener(
          "change",
          () => checks[0].setCustomValidity(""),
          { once: true }
        );
        return false;
      }
    }

    // Native constraint validation for everything else in this step.
    const controls = step.querySelectorAll("input, textarea, select");
    for (const control of controls) {
      if (!control.checkValidity()) {
        control.reportValidity();
        return false;
      }
    }
    return true;
  }

  if (next) {
    next.addEventListener("click", () => {
      if (stepValid()) show(current + 1);
    });
  }
  if (back) {
    back.addEventListener("click", () => show(current - 1));
  }

  show(0);
}
