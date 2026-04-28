<?php
// views/user_pages/UNavbar.php
// Expects from parent: $nav_links, $cart_count, $notif_count, $user_avatar, $user_name, $current_page
?>
<nav class="navbar navbar-expand-lg sticky-top px-3">

  <a class="brand me-4" href="index.php">Caffeteria</a>

  <button class="navbar-toggler ms-auto me-1 d-lg-none"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#mainNav"
          aria-controls="mainNav"
          aria-expanded="false"
          aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="mainNav">

    <ul class="navbar-nav me-auto">
      <?php foreach ($nav_links as $link): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === $link['page']) ? 'active' : '' ?>"
             href="<?= htmlspecialchars($link['href']) ?>">
            <?= htmlspecialchars($link['label']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
      <a href="cart.php" class="icon-btn" title="Cart">
        <i class="bi bi-cart3"></i>
        <?php if ($cart_count > 0): ?>
          <span class="dot"><?= (int)$cart_count ?></span>
        <?php endif; ?>
      </a>

      <div class="dropdown">
        <img src="<?= htmlspecialchars($user_avatar) ?>"
             alt="<?= htmlspecialchars($user_name) ?>"
             class="avatar"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="false" />
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <span class="dropdown-header fw-bold" style="color:var(--pink)">
              Hi, <?= htmlspecialchars($user_name) ?> 👋
            </span>
          </li>
          <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li><a class="dropdown-item" href="orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>

    </div>
  </div>
</nav>