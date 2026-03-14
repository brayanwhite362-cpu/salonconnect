// System theme auto-detect + optional manual override in dropdown
(function () {
  const root = document.documentElement;
  const storageKey = "sc_theme"; // "system" | "light" | "dark"

  function systemTheme() {
    return window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  }

  function applyTheme(mode) {
    const theme = (mode === "system") ? systemTheme() : mode;
    root.setAttribute("data-theme", theme);
    setActive(theme, mode);
  }

  function setActive(theme, mode) {
    const sysBtn = document.querySelector('[data-theme-btn="system"]');
    const lightBtn = document.querySelector('[data-theme-btn="light"]');
    const darkBtn = document.querySelector('[data-theme-btn="dark"]');

    [sysBtn, lightBtn, darkBtn].forEach(b => b && b.classList.remove("active"));

    // show which selection is active
    if (mode === "system" && sysBtn) sysBtn.classList.add("active");
    if (mode === "light" && lightBtn) lightBtn.classList.add("active");
    if (mode === "dark" && darkBtn) darkBtn.classList.add("active");
  }

  // Initial
  const saved = localStorage.getItem(storageKey) || "system";
  applyTheme(saved);

  // React to system changes only if in system mode
  const mq = window.matchMedia("(prefers-color-scheme: dark)");
  mq.addEventListener?.("change", () => {
    const current = localStorage.getItem(storageKey) || "system";
    if (current === "system") applyTheme("system");
  });

  // Theme controls (optional)
  document.addEventListener("click", (e) => {
    const t = e.target.closest("[data-theme-btn]");
    if (!t) return;
    const mode = t.getAttribute("data-theme-btn");
    localStorage.setItem(storageKey, mode);
    applyTheme(mode);
  });

  // User menu toggle
  const trigger = document.getElementById("userMenuTrigger");
  const dropdown = document.getElementById("userMenuDropdown");
  if (trigger && dropdown) {
    trigger.addEventListener("click", () => dropdown.classList.toggle("show"));
    document.addEventListener("click", (e) => {
      if (!dropdown.contains(e.target) && !trigger.contains(e.target)) dropdown.classList.remove("show");
    });
  }
})();