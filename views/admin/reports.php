<?php
require_once '../../config/config.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
$activePage        = 'reports';
$searchPlaceholder = 'Search reports...';

require_once '../../db.php';

$db = DATA_BASE::getInstance();

/* ── Fetch orders ─────────────────────────────────────── */
$ordersResult = $db->selectAll('orders');
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}

/* ── Attach user names ────────────────────────────────── */
foreach ($orders as &$o) {
    $ur = $db->select('users', 'id=' . (int)$o['user_id']);
    $u  = $ur->fetch_assoc();
    $o['user_name']  = $u['name']  ?? 'Unknown';
    $o['user_email'] = $u['email'] ?? '';
}
unset($o);

/* ── KPIs ─────────────────────────────────────────────── */
$totalRevenue = array_sum(array_column($orders, 'total_price'));
$totalChecks  = count($orders);
$avgOrder     = $totalChecks > 0 ? $totalRevenue / $totalChecks : 0;

/* ── Revenue by day (last 7 days) ─────────────────────── */
$days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[$date] = 0;
}
foreach ($orders as $o) {
    $d = date('Y-m-d', strtotime($o['created_at']));
    if (isset($days[$d])) $days[$d] += (float)$o['total_price'];
}
$maxRevDay = max(array_values($days)) ?: 1;

/* ── Categories ───────────────────────────────────────── */
$catsResult = $db->selectAll('categories');
$categories = [];
while ($cat = $catsResult->fetch_assoc()) $categories[] = $cat;

/* ── Pagination ───────────────────────────────────────── */
$perPage     = 8;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalItems  = count($orders);
$totalPages  = max(1, (int)ceil($totalItems / $perPage));
$pageOrders  = array_slice($orders, ($currentPage - 1) * $perPage, $perPage);

/* ── Helper ───────────────────────────────────────────── */
function statusBadge(string $status): string {
    return match (strtolower($status)) {
        'done'             => '<span class="badge-status bs-done">Completed</span>',
        'processing'       => '<span class="badge-status bs-proc">Processing</span>',
        'out for delivery' => '<span class="badge-status bs-deliv">Delivery</span>',
        default            => '<span class="badge-status bs-proc">' . htmlspecialchars($status) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafeteria – Reports &amp; Checks</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="../../assets/css/admin_layout.css">
  <link rel="stylesheet" href="../../assets/css/admin_reports.css">
</head>
<body>
<?php include '../Navbar.php'; ?>
<?php include '../Sidebar.php'; ?>

<!-- ══════════════════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════════════════ -->
<main class="main-content">

  <!-- Page header -->
  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
    <div>
      <h1 class="page-title">Reports &amp; Checks</h1>
      <p class="page-subtitle mb-0">Sweet insights and financial overviews for your cafeteria.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button class="btn-month">
        <i class="bi bi-calendar3"></i><?= date('F Y') ?>
      </button>
      <button class="btn-export">
        <i class="bi bi-download"></i>Export PDF
      </button>
    </div>
  </div>

  <!-- ── KPI cards ── -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="kpi-card">
        <i class="bi bi-cash-stack kpi-ghost"></i>
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value pink">$<?= number_format($totalRevenue, 2) ?></div>
        <div class="kpi-sub up"><i class="bi bi-arrow-up-right me-1"></i>Live from database</div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="kpi-card">
        <i class="bi bi-receipt kpi-ghost"></i>
        <div class="kpi-label">Total Checks</div>
        <div class="kpi-value purple"><?= number_format($totalChecks) ?></div>
        <div class="kpi-sub muted">
          <?= count(array_filter($orders, fn($o) => strtolower($o['status']) === 'done')) ?> completed
          &middot;
          <?= count(array_filter($orders, fn($o) => strtolower($o['status']) === 'processing')) ?> processing
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="kpi-card gradient-cyan">
        <i class="bi bi-graph-up kpi-ghost"></i>
        <div class="kpi-label">Avg. Order</div>
        <div class="kpi-value white">$<?= number_format($avgOrder, 2) ?></div>
        <div class="kpi-sub"><i class="bi bi-clock me-1"></i>Peak at 12:30 PM</div>
      </div>
    </div>
  </div>

  <!-- ── Chart + Filters ── -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
      <div class="chart-card">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <div class="chart-title">Revenue Performance</div>
          <div class="chart-legend">
            <span class="legend-dot" style="background:var(--pink);"></span>
            <span class="legend-dot" style="background:var(--blue);"></span>
            <span class="legend-dot" style="background:var(--cyan);"></span>
          </div>
        </div>
        <div class="bar-chart">
          <?php foreach ($days as $date => $rev): ?>
            <?php $pct = $maxRevDay > 0 ? ($rev / $maxRevDay) * 100 : 0; ?>
            <div class="bar-wrap">
              <div
                class="bar"
                style="height:<?= max(4, $pct) ?>%;"
                data-val="<?= $rev > 0 ? '$' . number_format($rev, 0) : '' ?>"
              ></div>
              <div class="bar-label"><?= date('M d', strtotime($date)) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="filters-card">
        <div class="filter-title">
          <i class="bi bi-funnel-fill" style="color:var(--pink);"></i> Filters
        </div>

        <!-- Category pills -->
        <div class="filter-section-label">Category</div>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php foreach ($categories as $cat): ?>
            <button class="cat-pill"><?= htmlspecialchars($cat['name']) ?></button>
          <?php endforeach; ?>
        </div>

        <!-- Status pills -->
        <div class="filter-section-label">Status</div>
        <div class="d-flex flex-wrap gap-2">
          <button class="cat-pill active">All</button>
          <button class="cat-pill">Completed</button>
          <button class="cat-pill">Processing</button>
          <button class="cat-pill">Delivery</button>
        </div>

        <button class="btn-reset">Reset All</button>
      </div>
    </div>
  </div>

  <!-- ── Recent Checks table ── -->
  <div class="checks-card">
    <div class="checks-header">
      <div>
        <div class="checks-header-title">Recent Checks</div>
        <div class="checks-header-sub"><?= $totalItems ?> orders total</div>
      </div>
      <a href="#" class="checks-view-all">View All <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="table-responsive">
      <table class="checks-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Amount</th>
            <th>Date</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pageOrders)): ?>
            <tr>
              <td colspan="6" class="text-center py-5" style="color:var(--muted);">
                <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;opacity:.5;"></i>
                No orders found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pageOrders as $i => $o):
              $avClass  = 'av-' . ($i % 6);
              $initials = strtoupper(substr($o['user_name'], 0, 2));
            ?>
              <tr>
                <td><span class="order-id">#ORD-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                <td>
                  <div class="user-cell">
                    <div class="u-avatar <?= $avClass ?>"><?= $initials ?></div>
                    <div>
                      <div class="u-name"><?= htmlspecialchars($o['user_name']) ?></div>
                      <div class="u-email"><?= htmlspecialchars($o['user_email']) ?></div>
                    </div>
                  </div>
                </td>
                <td><?= statusBadge($o['status']) ?></td>
                <td><span class="price-val">$<?= number_format((float)$o['total_price'], 2) ?></span></td>
                <td><span class="date-val"><?= date('M d, Y', strtotime($o['created_at'])) ?></span></td>
                <td>
                  <div class="d-flex justify-content-end gap-1">
                    <a href="order-view.php?id=<?= (int)$o['id'] ?>" class="act-btn" title="View">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="order-edit.php?id=<?= (int)$o['id'] ?>" class="act-btn" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="tbl-footer">
      <span class="showing">
        Showing
        <?= $totalItems > 0 ? (($currentPage - 1) * $perPage + 1) : 0 ?>–<?= min($currentPage * $perPage, $totalItems) ?>
        of <?= $totalItems ?> orders
      </span>
      <ul class="cpag">
        <li class="<?= $currentPage <= 1 ? 'pg-disabled' : '' ?>">
          <a href="?page=<?= $currentPage - 1 ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
          <li class="<?= $pg === $currentPage ? 'pg-active' : '' ?>">
            <?= $pg === $currentPage
                ? "<span>$pg</span>"
                : "<a href='?page=$pg'>$pg</a>" ?>
          </li>
        <?php endfor; ?>
        <li class="<?= $currentPage >= $totalPages ? 'pg-disabled' : '' ?>">
          <a href="?page=<?= $currentPage + 1 ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
      </ul>
    </div>
  </div><!-- /checks-card -->

</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/admin_layout.js"></script>
<script>
  /* Filter pill toggle */
  document.querySelectorAll('.cat-pill').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const group = this.closest('.d-flex');
      group.querySelectorAll('.cat-pill').forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
    });
  });
</script>
</body>
</html>