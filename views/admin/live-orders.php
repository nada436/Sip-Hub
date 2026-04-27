  <?php
  require_once '../../config/config.php';

  $activePage        = 'live-orders';
  $searchPlaceholder = 'Search orders...';

  require_once '../../db.php';

  $db = DATA_BASE::getInstance();

  /* ── Fetch all orders ─────────────────────────────────── */
  $ordersResult = $db->selectAll('orders');
  $orders = [];
  while ($row = $ordersResult->fetch_assoc()) {
      $orders[] = $row;
  }

  /* ── Attach user name + items ─────────────────────────── */
  foreach ($orders as &$order) {
      // User
      $ur = $db->select('users', 'id=' . (int)$order['user_id']);
      $u  = $ur->fetch_assoc();
      $order['user_name']  = $u['name']  ?? 'Unknown';
      $order['user_email'] = $u['email'] ?? '';

      // Items
      $ir = $db->select('order_items', 'order_id=' . (int)$order['id']);
      $order['items'] = [];
      while ($item = $ir->fetch_assoc()) {
          $pr   = $db->select('products', 'id=' . (int)$item['product_id']);
          $prod = $pr->fetch_assoc();
          $item['product_name'] = $prod['name'] ?? 'Item';
          $order['items'][]     = $item;
      }
  }
  unset($order);

  /* ── Bucket by status ─────────────────────────────────── */
  $incoming = array_values(array_filter($orders, fn($o) => strtolower($o['status']) === 'processing'));
  $kitchen  = array_values(array_filter($orders, fn($o) => strtolower($o['status']) === 'out for delivery'));
  $pickup   = array_values(array_filter($orders, fn($o) => strtolower($o['status']) === 'done'));

  /* ── Quick stats ──────────────────────────────────────── */
  $totalOrders = count($orders);
  $totalRev    = array_sum(array_column($orders, 'total_price'));
  $avgOrder    = $totalOrders > 0 ? $totalRev / $totalOrders : 0;

  /* ── Helper ───────────────────────────────────────────── */
  function human_time_diff_lo(string $datetime): string {
      $diff = time() - strtotime($datetime);
      if ($diff < 60)    return $diff . ' secs ago';
      if ($diff < 3600)  return floor($diff / 60)   . ' mins ago';
      if ($diff < 86400) return floor($diff / 3600)  . ' hrs ago';
      return floor($diff / 86400) . ' days ago';
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafeteria – Live Orders</title>

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
    <link rel="stylesheet" href="../../assets/css/admin_live_orders.css">
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
        <h1 class="page-title">Live Orders</h1>
        <p class="page-subtitle mb-0">Managing the sweet rush of today</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-funnel me-1"></i>Filter View
        </button>
        <a href="order-create.php" class="btn btn-sm rounded-pill px-3 text-white" style="background:var(--pink);">
          <i class="bi bi-plus-circle me-1"></i>Create Order
        </a>
      </div>
    </div>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4">
        <div class="stat-card sc-pink">
          <div class="stat-icon si-pink"><i class="bi bi-bag-check"></i></div>
          <div>
            <p class="stat-label sl-pink mb-1">Incoming</p>
            <div class="stat-value"><?= count($incoming) ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="stat-card sc-purple">
          <div class="stat-icon si-purple"><i class="bi bi-truck"></i></div>
          <div>
            <p class="stat-label sl-purple mb-1">Delivery</p>
            <div class="stat-value"><?= count($kitchen) ?></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="stat-card sc-green">
          <div class="stat-icon si-green"><i class="bi bi-check-circle"></i></div>
          <div>
            <p class="stat-label sl-green mb-1">Done</p>
            <div class="stat-value"><?= count($pickup) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Kanban ── -->
    <div class="kanban mb-4">

      <!-- INCOMING -->
      <div class="k-col">
        <div class="k-col-header">
          <div class="k-col-title"><span class="k-dot dot-pink"></span> Incoming</div>
          <span class="k-badge kb-pink"><?= count($incoming) ?> Orders</span>
        </div>

        <?php if (empty($incoming)): ?>
          <div class="empty-state"><i class="bi bi-inbox"></i>No incoming orders</div>
        <?php else: foreach ($incoming as $o):
          $initials = strtoupper(substr($o['user_name'], 0, 2));
          $ago      = human_time_diff_lo($o['created_at']);
        ?>
          <div class="order-card">
            <div class="oc-top">
              <span class="oc-id oc-id-pink">#ORD-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span>
              <span class="oc-time"><?= $ago ?></span>
            </div>
            <div class="oc-name"><?= htmlspecialchars($o['user_name']) ?>'s Order</div>
            <div class="oc-items">
              <?php foreach ($o['items'] as $it): ?>
                <span class="oc-item-tag"><?= (int)$it['quantity'] ?>× <?= htmlspecialchars($it['product_name']) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="oc-footer">
              <div class="oc-user">
                <div class="oc-avatar"><?= $initials ?></div>
                <span class="oc-username"><?= htmlspecialchars($o['user_name']) ?></span>
              </div>
              <span class="oc-price">$<?= number_format($o['total_price'], 2) ?></span>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- OUT FOR DELIVERY -->
      <div class="k-col">
        <div class="k-col-header">
          <div class="k-col-title"><span class="k-dot dot-purple"></span> Out for Delivery</div>
          <span class="k-badge kb-purple"><?= count($kitchen) ?> Orders</span>
        </div>

        <?php if (empty($kitchen)): ?>
          <div class="empty-state"><i class="bi bi-truck"></i>Nothing in transit</div>
        <?php else: foreach ($kitchen as $o):
          $initials = strtoupper(substr($o['user_name'], 0, 2));
          $ago      = human_time_diff_lo($o['created_at']);
        ?>
          <div class="order-card kitchen-card">
            <div class="oc-top">
              <span class="oc-id oc-id-purple">#ORD-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span>
              <span class="oc-time"><i class="bi bi-clock me-1"></i><?= $ago ?></span>
            </div>
            <div class="oc-name"><?= htmlspecialchars($o['user_name']) ?>'s Order</div>
            <div class="oc-items">
              <?php foreach ($o['items'] as $it): ?>
                <span class="oc-item-tag"><?= (int)$it['quantity'] ?>× <?= htmlspecialchars($it['product_name']) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="oc-footer">
              <div class="oc-user">
                <div class="oc-avatar" style="background:linear-gradient(135deg,var(--purple),#a78bfa);"><?= $initials ?></div>
                <span class="oc-username"><?= htmlspecialchars($o['user_name']) ?></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="oc-price">$<?= number_format($o['total_price'], 2) ?></span>
                <a href="order-complete.php?id=<?= (int)$o['id'] ?>" class="btn-action btn-complete">Complete</a>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- DONE / PICKUP -->
      <div class="k-col">
        <div class="k-col-header">
          <div class="k-col-title"><span class="k-dot dot-green"></span> Pickup / Done</div>
          <span class="k-badge kb-green"><?= count($pickup) ?> Ready</span>
        </div>

        <?php if (empty($pickup)): ?>
          <div class="empty-state"><i class="bi bi-bag-check"></i>No completed orders yet</div>
        <?php else: foreach ($pickup as $o):
          $initials = strtoupper(substr($o['user_name'], 0, 2));
        ?>
          <div class="order-card pickup-card">
            <div class="oc-top">
              <span class="oc-id oc-id-green">#ORD-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span>
              <span class="k-badge kb-green">READY</span>
            </div>
            <div class="oc-name"><?= htmlspecialchars($o['user_name']) ?>'s Order</div>
            <div class="oc-items">
              <?php foreach ($o['items'] as $it): ?>
                <span class="oc-item-tag"><?= (int)$it['quantity'] ?>× <?= htmlspecialchars($it['product_name']) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="oc-footer">
              <div class="oc-user">
                <div class="oc-avatar" style="background:linear-gradient(135deg,var(--green),#34d399);"><?= $initials ?></div>
                <span class="oc-username"><?= htmlspecialchars($o['user_name']) ?></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="oc-price">$<?= number_format($o['total_price'], 2) ?></span>
                <button class="btn-action btn-notify">Notify</button>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

    </div><!-- /kanban -->

    <!-- ── Bottom widgets ── -->
    <div class="row g-3">
      <!-- Rush alert -->
      <div class="col-12 col-md-5">
        <div class="rush-card">
          <div class="rush-title">Rush Hour Alert! 🍬</div>
          <div class="rush-sub">Stay on top of incoming orders and keep things flowing.</div>
          <div class="d-flex gap-3">
            <div class="rush-stat flex-fill">
              <div class="rs-label">Total Orders</div>
              <div class="rs-val"><?= $totalOrders ?></div>
            </div>
            <div class="rush-stat flex-fill">
              <div class="rs-label">Avg. Order</div>
              <div class="rs-val">$<?= number_format($avgOrder, 2) ?></div>
            </div>
            <div class="rush-stat flex-fill">
              <div class="rs-label">Revenue</div>
              <div class="rs-val">$<?= number_format($totalRev, 0) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Happiness -->
      <div class="col-12 col-sm-6 col-md-4">
        <div class="widget-card">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-star-fill" style="color:var(--orange);font-size:1.1rem;"></i>
            <span class="widget-label mb-0">Customer Happiness</span>
          </div>
          <div class="hap-score mt-2">4.9</div>
          <div class="hap-sub">Customer Happiness Today</div>
          <div class="avatar-row">
            <?php for ($i = 0; $i < 4; $i++): ?>
              <img src="https://ui-avatars.com/api/?name=U<?= $i ?>&background=e91e8c&color=fff&size=56"
                  alt="" class="rounded-circle" width="28" height="28">
            <?php endfor; ?>
            <div class="avatar-more">+12</div>
          </div>
        </div>
      </div>

      <!-- Daily goal -->
      <div class="col-12 col-sm-6 col-md-3">
        <div class="widget-card">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-trophy-fill" style="color:var(--pink);font-size:1.1rem;"></i>
            <span class="widget-label mb-0">Daily Goal</span>
          </div>
          <?php $goalPct = min(100, round(($totalOrders / 20) * 100)); ?>
          <div class="mt-3">
            <div class="goal-bar-track">
              <div class="goal-bar-fill" style="width:<?= $goalPct ?>%;"></div>
            </div>
            <div class="goal-sub"><?= $totalOrders ?> / 20 Orders Processed</div>
          </div>
          <div style="font-size:1.4rem;font-weight:700;margin-top:.75rem;color:var(--text);">
            <?= $goalPct ?>%
          </div>
        </div>
      </div>
    </div><!-- /bottom widgets -->

  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/admin_layout.js"></script>
  </body>
  </html>