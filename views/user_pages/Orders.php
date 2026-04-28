<?php
// views/user_pages/orders.php

require_once __DIR__ . '/../../db.php';

$db      = DATA_BASE::getInstance();
$user_id = 1; // ← swap with $_SESSION['user_id'] once login is ready

$current_page = 'orders';
$nav_links    = [
    ['href' => 'index.php',    'label' => 'Home',     'page' => 'index'],
    ['href' => 'products.php', 'label' => 'Products', 'page' => 'products'],
    ['href' => 'orders.php',   'label' => 'Orders',   'page' => 'orders'],
];
$notif_count = 0;
$cart_count  = 0;
$user_avatar = 'https://i.pravatar.cc/40?img=12';
$user_name   = 'Marina George';

// Fetch orders newest first
$orders_res = $db->selectAll('orders', "user_id=$user_id ORDER BY created_at DESC");
$orders = [];
while ($o = $orders_res->fetch_assoc()) $orders[] = $o;

// Fetch items per order
$order_details = [];
foreach ($orders as $order) {
    $oid  = (int)$order['id'];
    $ir   = $db->selectAll('order_items', "order_id=$oid");
    $items = [];
    while ($item = $ir->fetch_assoc()) {
        $prod    = $db->select('products', "id={$item['product_id']}")->fetch_assoc();
        $items[] = [
            'name'     => $prod ? $prod['name']  : 'Deleted item',
            'image'    => $prod ? $prod['image']  : '',
            'qty'      => (int)$item['quantity'],
            'price'    => (float)$item['price'],
            'subtotal' => (float)$item['price'] * (int)$item['quantity'],
        ];
    }
    $order_details[$oid] = $items;
}

$status_style = [
    'processing'       => ['pill'=>'#fff3cd','text'=>'#92690a','icon'=>'bi-hourglass-split',  'label'=>'Processing'],
    'out for delivery' => ['pill'=>'#dbeafe','text'=>'#1d4ed8','icon'=>'bi-bicycle',           'label'=>'Out for Delivery'],
    'done'             => ['pill'=>'#dcfce7','text'=>'#15803d','icon'=>'bi-check-circle-fill', 'label'=>'Done'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders — Caffeteria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="../../assets/css/User_Style.css" rel="stylesheet"/>
  <link href="../../assets/css/orders.css" rel="stylesheet"/>
</head>
<body>

<?php include __DIR__ . '/UNavbar.php'; ?>

<div class="orders-wrap">
  <div class="page-eyebrow">🧾 Order History</div>
  <h1 class="page-heading">My <em>Orders</em></h1>

  <?php if (empty($orders)): ?>
    <div class="empty-orders">
      <i class="bi bi-bag-x"></i>
      <h3>No orders yet</h3>
      <p>Once you place an order it will appear here.</p>
      <a href="index.php" class="btn-pink">Browse Menu</a>
    </div>

  <?php else: ?>
    <?php foreach ($orders as $order):
      $oid    = (int)$order['id'];
      $status = strtolower(trim($order['status']));
      $ss     = $status_style[$status]
                ?? ['pill'=>'#f0f0f0','text'=>'#555','icon'=>'bi-circle','label'=>ucwords($status)];
      $items  = $order_details[$oid] ?? [];
    ?>
      <div class="order-card">

        <div class="order-head">
          <div>
            <div class="order-meta-id">Order #<?= $oid ?></div>
            <div class="order-meta-date">
              <?= date('M j, Y — g:i A', strtotime($order['created_at'])) ?>
            </div>
          </div>

          <span class="status-pill"
                style="background:<?= $ss['pill'] ?>;color:<?= $ss['text'] ?>">
            <i class="bi <?= $ss['icon'] ?>"></i><?= $ss['label'] ?>
          </span>

          <div class="order-meta-total">EGP <?= number_format($order['total_price'], 2) ?></div>
        </div>

        <?php foreach ($items as $item): ?>
          <div class="order-item">
            <img class="oi-img"
                 src="<?= htmlspecialchars('../../' . $item['image']) ?>"
                 alt="<?= htmlspecialchars($item['name']) ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=100&q=70'"/>
            <div>
              <div class="oi-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="oi-meta">EGP <?= number_format($item['price'],2) ?> × <?= $item['qty'] ?></div>
            </div>
            <div class="oi-price">EGP <?= number_format($item['subtotal'],2) ?></div>
          </div>
        <?php endforeach; ?>

      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>