document.addEventListener("DOMContentLoaded", function () {
  const els = document.querySelectorAll(".ppl-date");

  els.forEach((el) => {
    flatpickr(el, {
      dateFormat: "Y-m-d",
      allowInput: true,
      disableMobile: true, 
    });
  });
});
