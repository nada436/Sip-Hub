<?php
/**
 * Navbar.php  –  Candy Cafeteria Admin Navbar
 *
 * Usage: include at the TOP of every admin page, after <body>.
 * The hamburger button toggles the sidebar on mobile — it requires
 * the JS snippet in AdminLayout.js (or inline at bottom of page).
 *
 * Optional variable you can define before including this file:
 *   $searchPlaceholder  (string)  default: "Search..."
 */

$searchPlaceholder = $searchPlaceholder ?? 'Search...';
?>
<nav class="navbar candy-navbar fixed-top px-0">
  <div class="container-fluid px-4 gap-3">

    <!-- Hamburger (visible on mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
      <i class="bi bi-list"></i>
    </button>

    <!-- Brand -->
    <a class="candy-brand" href="dashboard.php">
      <span style="font-weight:600;font-family:'Poppins',sans-serif;font-size:1rem;vertical-align:middle;"> Caffeteria</span>
    </a>

  

    <!-- Right controls -->
    <div class="d-flex align-items-center gap-2">
      <img
      src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=e91e8c&color=fff&size=80"
        alt="Admin avatar"
        class="nav-avatar"
        title="Admin"
      >
    </div>
  </div>
</nav>
  