/**
 * =========================================================
 * assets/js/theme.js (FULL)
 * =========================================================
 */
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // Accordion toggle (only for .acc sections)
  $$(".acc").forEach((btn) => {
    btn.addEventListener("click", () => {
      const panel = btn.nextElementSibling;
      const expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", String(!expanded));
      if (panel) panel.style.display = expanded ? "none" : "block";
    });
  });

  // Find sidebar form (if exists)
  const sidebarForm = document.querySelector(".sidebar form");

  // ECTS range live label + auto submit on change
  const ects = $("#ects");
  const ectsVal = $("#ectsVal");
  if (ects && ectsVal) {
    const sync = () => (ectsVal.textContent = ects.value);
    ects.addEventListener("input", sync);
    sync();

    ects.addEventListener("change", () => {
      if (sidebarForm) sidebarForm.submit();
    });
  }

  // SEARCH: keep value from PHP, show clear, Enter submits form
  const searchInput = $("#course-search");
  if (searchInput) {
    const wrap = searchInput.closest(".input-wrap");
    const clearBtn = wrap ? wrap.querySelector(".clear") : null;

    const syncClear = () => {
      if (!wrap) return;
      if (searchInput.value.trim()) wrap.classList.add("has-value");
      else wrap.classList.remove("has-value");
    };
    syncClear();

    searchInput.addEventListener("input", syncClear);

    searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (sidebarForm) sidebarForm.submit();
      }
    });

    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        syncClear();
        searchInput.focus();
        if (sidebarForm) sidebarForm.submit();
      });
    }
  }

  // Auto-submit for all sidebar checkboxes
  $$(".sidebar input[type='checkbox']").forEach((cb) => {
    cb.addEventListener("change", () => {
      if (sidebarForm) sidebarForm.submit();
    });
  });

  // Auto-submit for date inputs
  $$(".sidebar input[type='date']").forEach((inp) => {
    inp.addEventListener("change", () => {
      if (sidebarForm) sidebarForm.submit();
    });
  });

  // MORE expand/collapse
  $$("[data-more]").forEach((wrap) => {
    const items = $("[data-more-items]", wrap);
    const btn = $("[data-more-btn]", wrap);
    if (!items || !btn) return;

    items.classList.remove("is-open");
    btn.textContent = "MORE";

    btn.addEventListener("click", () => {
      const open = items.classList.toggle("is-open");
      btn.textContent = open ? "LESS" : "MORE";
    });
  });
})();
