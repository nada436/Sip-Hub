/**
 * admin_layout.js
 * Handles sidebar open/close on mobile + tablet.
 * Include at the bottom of every admin page (before </body>).
 */
(function () {
  "use strict";

  const toggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("candySidebar");
  const backdrop = document.getElementById("sidebarBackdrop");

  if (!toggle || !sidebar || !backdrop) return;

  function openSidebar() {
    sidebar.classList.add("open");
    backdrop.classList.add("show");
    toggle.innerHTML = '<i class="bi bi-x-lg"></i>';
    document.body.style.overflow = "hidden";
  }

  function closeSidebar() {
    sidebar.classList.remove("open");
    backdrop.classList.remove("show");
    toggle.innerHTML = '<i class="bi bi-list"></i>';
    document.body.style.overflow = "";
  }

  toggle.addEventListener("click", function () {
    sidebar.classList.contains("open") ? closeSidebar() : openSidebar();
  });

  backdrop.addEventListener("click", closeSidebar);

  // Close on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeSidebar();
  });

  // Close sidebar when a nav link is clicked (mobile UX)
  sidebar.querySelectorAll(".s-link").forEach(function (link) {
    link.addEventListener("click", function () {
      if (window.innerWidth < 768) closeSidebar();
    });
  });
})();
