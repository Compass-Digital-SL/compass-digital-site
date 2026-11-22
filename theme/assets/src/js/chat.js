// Compass Digital — AI chat assistant (lazy chunk).
//
// Imported dynamically by main.js on the first launcher click, so neither this
// module nor its CSS is in the initial page load. Vanilla, no dependencies.
// The panel is keyboard accessible and focus-trapped, honours
// prefers-reduced-motion (handled in CSS), and talks only to the same-origin
// REST proxy (the API key stays on the server).

import "../css/chat.css";

const FOCUSABLE =
  'a[href], button:not([disabled]), textarea, input, [tabindex]:not([tabindex="-1"])';

/**
 * Create the chat controller. Builds the panel (closed) and returns handles.
 *
 * @param {HTMLElement} launcher The launcher button already in the DOM.
 * @param {object} cfg window.CompassChat.
 * @returns {{open: Function, close: Function, toggle: Function}}
 */
export function createChat(launcher, cfg) {
  const history = []; // [{ role: 'user'|'assistant', text }]
  let open = false;
  let pending = false;
  let lastFocus = null;

  const panel = document.createElement("div");
  panel.className = "compass-chat";
  panel.id = "compass-chat-panel";
  panel.setAttribute("role", "dialog");
  panel.setAttribute("aria-modal", "false");
  panel.setAttribute("aria-label", cfg.name + " chat");
  panel.hidden = true;

  panel.innerHTML = `
    <div class="compass-chat__head">
      <span class="compass-chat__avatar" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
      </span>
      <span class="compass-chat__title">
        <span class="compass-chat__name"></span>
        <span class="compass-chat__status">${esc(cfg.company)}</span>
      </span>
      <button type="button" class="compass-chat__close" data-chat-close aria-label="Close chat">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="compass-chat__log" data-chat-log role="log" aria-live="polite" aria-atomic="false"></div>
    <form class="compass-chat__form" data-chat-form>
      <label class="compass-chat__sr" for="compass-chat-input">Type your message</label>
      <textarea id="compass-chat-input" class="compass-chat__input" data-chat-input rows="1"
        placeholder="Type your message…" maxlength="${Number(cfg.maxLen) || 1000}" autocomplete="off"></textarea>
      <button type="submit" class="compass-chat__send" data-chat-send aria-label="Send message">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
      </button>
    </form>
    <p class="compass-chat__foot">Answers may be imperfect. <a href="${esc(cfg.quoteUrl)}">Request a quote</a> for a real estimate.</p>
  `;

  panel.querySelector(".compass-chat__name").textContent = cfg.name;

  const log = panel.querySelector("[data-chat-log]");
  const form = panel.querySelector("[data-chat-form]");
  const input = panel.querySelector("[data-chat-input]");
  const sendBtn = panel.querySelector("[data-chat-send]");

  document.body.appendChild(panel);

  // --- Rendering helpers ---------------------------------------------------

  // Untrusted text (visitor input, model output): escaped then auto-linked.
  function bubble(role, text) {
    return bubbleHTML(role, linkify(text));
  }

  // Trusted, developer-authored HTML (our own error copy only).
  function bubbleHTML(role, html) {
    const el = document.createElement("div");
    el.className =
      "compass-chat__msg compass-chat__msg--" +
      (role === "user" ? "user" : role === "error" ? "error" : "bot");
    el.innerHTML = html;
    log.appendChild(el);
    log.scrollTop = log.scrollHeight;
    return el;
  }

  function showTyping() {
    const el = document.createElement("div");
    el.className = "compass-chat__msg compass-chat__msg--bot";
    el.setAttribute("data-chat-typing", "");
    el.innerHTML =
      '<span class="compass-chat__typing" aria-label="Typing"><span></span><span></span><span></span></span>';
    log.appendChild(el);
    log.scrollTop = log.scrollHeight;
    return el;
  }

  // --- Networking ----------------------------------------------------------

  // The nonce printed in the page can be stale when the HTML came from a page
  // cache, so fetch a fresh one once per page view (falls back to the printed one).
  let nonce = null;
  async function getNonce(force = false) {
    if (nonce && !force) return nonce;
    if (cfg.nonceUrl) {
      try {
        const r = await fetch(cfg.nonceUrl, { credentials: "same-origin", cache: "no-store" });
        const j = await r.json();
        if (j && j.success && j.data && j.data.nonce) nonce = j.data.nonce;
      } catch {
        /* fall back below */
      }
    }
    return nonce || cfg.nonce;
  }

  function post(text, n) {
    return fetch(cfg.endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": n },
      body: JSON.stringify({ message: text, history: history.slice(0, -1) }),
    });
  }

  async function send(text) {
    bubble("user", text);
    history.push({ role: "user", text });
    input.value = "";
    autoGrow();
    setPending(true);
    const typing = showTyping();

    try {
      let res = await post(text, await getNonce());
      // 403 = nonce rejected (e.g. expired mid-visit): refresh once and retry.
      if (res.status === 403) res = await post(text, await getNonce(true));
      const data = await res.json().catch(() => ({}));
      typing.remove();
      const reply =
        (data && data.reply) ||
        "Sorry — something went wrong. Please try the quote form.";
      if (res.ok && data && data.state === "ok") {
        bubble("bot", reply);
        // Keep the server's signature with the turn; it must be echoed back on
        // the next request or the server drops this turn as unverified context.
        history.push({ role: "assistant", text: reply, sig: data.sig });
      } else {
        bubble("error", reply);
      }
    } catch {
      typing.remove();
      bubbleHTML(
        "error",
        "I can’t reach the server right now. Please " +
          '<a href="' +
          esc(cfg.quoteUrl) +
          '">request a quote</a>' +
          (cfg.whatsapp
            ? ' or message us on <a href="' + esc(cfg.whatsapp) + '">WhatsApp</a>.'
            : ".")
      );
    } finally {
      setPending(false);
      if (open) input.focus();
    }
  }

  function setPending(state) {
    pending = state;
    sendBtn.disabled = state;
    input.disabled = state;
  }

  // --- Open / close + focus trap ------------------------------------------

  function doOpen() {
    if (open) return;
    open = true;
    lastFocus = document.activeElement;
    panel.hidden = false;
    launcher.setAttribute("aria-expanded", "true");
    if (!log.childElementCount) {
      bubble("bot", cfg.greeting);
    }
    // Focus the input after the panel is shown.
    window.requestAnimationFrame(() => input.focus());
    document.addEventListener("keydown", onKeydown, true);
  }

  function doClose() {
    if (!open) return;
    open = false;
    panel.hidden = true;
    launcher.setAttribute("aria-expanded", "false");
    document.removeEventListener("keydown", onKeydown, true);
    if (lastFocus && typeof lastFocus.focus === "function") {
      lastFocus.focus();
    } else {
      launcher.focus();
    }
  }

  function toggle() {
    if (open) doClose();
    else doOpen();
  }

  function onKeydown(e) {
    if (e.key === "Escape") {
      e.preventDefault();
      doClose();
      return;
    }
    if (e.key !== "Tab") return;
    const nodes = Array.prototype.filter.call(
      panel.querySelectorAll(FOCUSABLE),
      (n) => !n.disabled && n.offsetParent !== null
    );
    if (!nodes.length) return;
    const first = nodes[0];
    const last = nodes[nodes.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  // --- Composer wiring -----------------------------------------------------

  function autoGrow() {
    input.style.height = "auto";
    input.style.height = Math.min(input.scrollHeight, 96) + "px";
  }

  input.addEventListener("input", autoGrow);

  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (pending) return;
    const text = input.value.trim();
    if (!text) return;
    send(text);
  });

  panel
    .querySelector("[data-chat-close]")
    .addEventListener("click", doClose);

  return { open: doOpen, close: doClose, toggle };
}

/* --- small utilities ------------------------------------------------------ */

function esc(s) {
  return String(s == null ? "" : s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

// Escape, then turn bare URLs into safe links (http/https only).
function linkify(text) {
  const safe = esc(text);
  return safe.replace(/(https?:\/\/[^\s<]+[^\s<.,!?;:)\]])/g, (url) => {
    return (
      '<a href="' +
      url +
      '" target="_blank" rel="noopener nofollow">' +
      url +
      "</a>"
    );
  });
}
