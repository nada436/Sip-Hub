<?php
/**
 * Sidebar.php  –  Candy Cafeteria Admin Sidebar
 *
 * Usage: include right after Navbar.php on every admin page.
 * Requires $activePage to be defined before including this file.
 *
 * Example:
 *   $activePage = 'products';
 *   include '../Sidebar.php';
 */

$activePage = $activePage ?? '';

$menuItems = [
  /* ── MAIN ── */
  ['section' => 'Main'],


  ['id' => 'live-orders','icon' => 'bi-lightning-charge','label' => 'Current Orders'],

  /* ── MANAGEMENT ── */
  ['section' => 'Management'],

  ['id' => 'products',   'icon' => 'bi-box-seam',   'label' => 'Products'],
  ['id' => 'live-categories', 'icon' => 'bi-tag',        'label' => 'Categories'],
  ['id' => 'users',      'icon' => 'bi-people',     'label' => 'Users'],

  /* ── ANALYTICS ── */
  ['section' => 'Analytics'],
  ['id' => 'reports',    'icon' => 'bi-bar-chart-line', 'label' => 'Reports & Checks'],
];

// Resolve logged-in admin name (fallback to session or default)
$adminName  = $_SESSION['name'] ;
$adminEmail =$_SESSION['email'];
$adminRole  =$_SESSION['role'] ;
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($adminName) . "&background=e91e8c&color=fff&size=80";
?>

<!-- Sidebar backdrop (mobile overlay) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="candy-sidebar" id="candySidebar" aria-label="Admin navigation">

  <!-- Header badge -->
  <div class="sidebar-header">
    <div class="s-panel-badge">
      <div class="badge-icon"><i class="bi bi-grid-fill"></i></div>
      <div>
        <p class="s-panel-title">Admin Panel</p>
        <p class="s-panel-sub">Cafeteria Management</p>
      </div>
    </div>
  </div>
  <!-- Navigation -->
  <nav class="sidebar-nav">
    <?php foreach ($menuItems as $item): ?>

      <?php if (isset($item['section'])): ?>
        <span class="nav-section-label"><?= htmlspecialchars($item['section']) ?></span>

      <?php else:
        $isActive = $activePage === $item['id'];
      ?>
        <a
          href="<?= htmlspecialchars($item['id']) ?>.php"
          class="s-link <?= $isActive ? 'active' : '' ?>"
          <?= $isActive ? 'aria-current="page"' : '' ?>
          title="<?= htmlspecialchars($item['label']) ?>"
        >
          <span class="s-icon"><i class="bi <?= $item['icon'] ?>"></i></span>
          <span><?= htmlspecialchars($item['label']) ?></span>
        </a>
      <?php endif; ?>

    <?php endforeach; ?>
  </nav>

  <!-- Footer: admin profile -->
  <div class="sidebar-footer">
    <div class="d-flex align-items-center gap-3">
      <img
        src="<?= htmlspecialchars($avatarUrl) ?>"
        alt="<?= htmlspecialchars($adminName) ?>"
        class="rounded-circle"
        width="40" height="40"
        style="border: 2px solid var(--border); flex-shrink:0;"
      >
      <div style="min-width:0;">
        <p class="s-admin-name text-truncate"><?= htmlspecialchars($adminName) ?></p>
        <p class="s-admin-role text-truncate"><?= htmlspecialchars($adminRole) ?></p>
        <a href="../../logout.php" class="s-admin-logout">
          <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
      </div>
    </div>
  </div>

</aside>