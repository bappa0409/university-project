/**
 * =========================================================
 * assets/js/theme.js (FULL) — CLEAN (NO TEACHER MODAL)
 * - Enter on search input submits form
 * - No auto-submit on checkbox/date/slider/typing/clear
 * - ✅ Accordion robust toggle (sidebar + teacher page)
 * - MORE preserves open state if any checkbox checked inside
 * - Footer logo slider drag
 * =========================================================
 */
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* =========================
     ✅ Accordion (ROBUST)
     ========================= */
  $$(".acc").forEach((btn) => {
    const section = btn.closest(".sb-section");
    const panel = section ? section.querySelector(".acc-panel") : null;

    // initial state from aria-expanded
    if (panel) {
      const expanded = btn.getAttribute("aria-expanded") === "true";
      panel.style.display = expanded ? "block" : "none";
    }

    btn.addEventListener("click", (e) => {
      e.preventDefault();

      const section = btn.closest(".sb-section");
      const panel = section ? section.querySelector(".acc-panel") : null;

      const expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", String(!expanded));
      if (panel) panel.style.display = expanded ? "none" : "block";
    });
  });

  /* =========================
     Sidebar form (if exists)
     ========================= */
  const sidebarForm = document.querySelector(".sidebar form");

  /* =========================
     ECTS range live label
     ========================= */
  const ects = $("#ects");
  const ectsVal = $("#ectsVal");
  if (ects && ectsVal) {
    const sync = () => (ectsVal.textContent = ects.value);
    ects.addEventListener("input", sync);
    sync();
  }

  /* =========================
     Search input: Enter submit
     ========================= */
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

    // Enter => submit
    searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        const form = searchInput.closest("form");
        if (form) form.requestSubmit();
      }
    });

    // Clear (no auto submit)
    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        syncClear();
        searchInput.focus();
      });
    }
  }

  /* =========================
     MORE expand/collapse
     ========================= */
  $$("[data-more]").forEach((wrap) => {
    const items = $("[data-more-items]", wrap);
    const btn = $("[data-more-btn]", wrap);
    if (!items || !btn) return;

    const hasChecked = wrap.querySelector('input[type="checkbox"]:checked');

    if (hasChecked) {
      items.classList.add("is-open");
      btn.textContent = "LESS";
    } else {
      items.classList.remove("is-open");
      btn.textContent = "MORE";
    }

    btn.addEventListener("click", () => {
      const open = items.classList.toggle("is-open");
      btn.textContent = open ? "LESS" : "MORE";
    });
  });

  /* =========================
     Footer University Logo Slider (Mouse drag)
     ========================= */
  const logoSlider = document.querySelector(".logo-bar .ppl-uni-logos");
  if (logoSlider) {
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    logoSlider.querySelectorAll("img").forEach((img) => {
      img.setAttribute("draggable", "false");
    });

    logoSlider.addEventListener("mousedown", (e) => {
      isDown = true;
      logoSlider.classList.add("dragging");
      startX = e.pageX - logoSlider.offsetLeft;
      scrollLeft = logoSlider.scrollLeft;
    });

    logoSlider.addEventListener("mouseleave", () => {
      isDown = false;
      logoSlider.classList.remove("dragging");
    });

    logoSlider.addEventListener("mouseup", () => {
      isDown = false;
      logoSlider.classList.remove("dragging");
    });

    logoSlider.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - logoSlider.offsetLeft;
      const walk = (x - startX) * 1.2;
      logoSlider.scrollLeft = scrollLeft - walk;
    });
  }

})();
