/**
 * =========================================================
 * assets/js/theme.js (FULL) — UPDATED
 * - Enter on search input submits form
 * - No auto-submit on checkbox/date/slider/typing/clear
 * - Accordion preserves open state if aria-expanded true
 * - MORE preserves open state if any checkbox checked inside
 * =========================================================
 */
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // Accordion toggle (only for .acc sections)
  $$(".acc").forEach((btn) => {
    const panel = btn.nextElementSibling;

    // Ensure initial UI matches aria-expanded (server sets it)
    if (panel) {
      const expanded = btn.getAttribute("aria-expanded") === "true";
      panel.style.display = expanded ? "block" : "none";
    }

    btn.addEventListener("click", () => {
      const panel = btn.nextElementSibling;
      const expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", String(!expanded));
      if (panel) panel.style.display = expanded ? "none" : "block";
    });
  });

  // Find sidebar form (if exists)
  const sidebarForm = document.querySelector(".sidebar form");

  // ECTS range live label (NO auto submit)
  const ects = $("#ects");
  const ectsVal = $("#ectsVal");
  if (ects && ectsVal) {
    const sync = () => (ectsVal.textContent = ects.value);
    ects.addEventListener("input", sync);
    sync();
  }

  // SEARCH input behaviors (Enter submit, NO clear auto submit)
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

    // ✅ Enter চাপলে submit হবে
    searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        const form = searchInput.closest("form");
        if (form) form.requestSubmit();
      }
    });

    // Clear button (NO auto submit)
    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        syncClear();
        searchInput.focus();
      });
    }
  }

  // MORE expand/collapse (keep open if any checked)
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

  /**
   * =========================================================
   * ✅ Footer University Logo Slider (Mouse drag)
   * - No arrows
   * - No auto sliding
   * - Drag with mouse, swipe works on mobile by default
   * =========================================================
   */
  const logoSlider = document.querySelector(".logo-bar .ppl-uni-logos");
  if (logoSlider) {
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    // Prevent image dragging (so mouse drag scroll feels good)
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
      const walk = (x - startX) * 1.2; // drag speed
      logoSlider.scrollLeft = scrollLeft - walk;
    });
  }

  // Chip close: only remove the tag from UI (no search change)
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".chip-close");
    if (!btn) return;

    const chip = btn.closest(".chip");
    if (chip) chip.remove();
  });

  document.addEventListener('DOMContentLoaded', function () {
  const openBtn = document.getElementById('openTeacherForm');
  const modal = document.getElementById('teacherModal');
  const closeBtn = document.getElementById('closeTeacherForm');

  if (!openBtn || !modal) return;

  // Open modal
  openBtn.addEventListener('click', function (e) {
    e.preventDefault();
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  });

  // Close modal
  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  closeBtn.addEventListener('click', closeModal);

  // Click outside content
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  // ESC key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });
});
})();
