<?php
// views/user_pages/Cart.php

// ── 1. Session & guard ────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    header('Location: http://localhost/Sip-Hub/login.php');
    exit;
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../config/config.php';

$db = DATA_BASE::getInstance();

$user_id = (int) $_SESSION['user_id'];

$current_page = 'cart';
$nav_links    = [
    ['href' => 'index.php',      'label' => 'Home',     'page' => 'index'],
    ['href' => 'index.php#menu', 'label' => 'Products', 'page' => 'products'],
    ['href' => 'orders.php',     'label' => 'Orders',   'page' => 'orders'],
];
$notif_count = 0;
$cart_count  = 0;

$res   = $db->selectAll('cart', "user_id = $user_id");
$items = [];
$total = 0.0;

while ($row = $res->fetch_assoc()) {
    $prod = $db->select('products', "id = {$row['product_id']}")->fetch_assoc();
    if (!$prod) continue;
    $subtotal = (float)$prod['price'] * (int)$row['quantity'];
    $total   += $subtotal;
    $cart_count++;
    $items[] = [
        'product_id' => (int)$row['product_id'],
        'name'       => $prod['name'],
        'image'      => $prod['image'],
        'price'      => (float)$prod['price'],
        'quantity'   => (int)$row['quantity'],
        'subtotal'   => $subtotal,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Cart — Caffeteria</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <!-- Shared user styles -->
  <link href="../../assets/css/User_Style.css" rel="stylesheet"/>
  <!-- Cart-specific styles -->
  <link href="../../assets/css/Cart.css" rel="stylesheet"/>
</head>
<body>

<?php include __DIR__ . '/UNavbar.php'; ?>

<div class="cart-wrap">

  <a href="index.php" class="back-link">
    <i class="bi bi-arrow-left"></i> Back to Menu
  </a>

  <p class="cart-eyebrow">🛒 Shopping Bag</p>
  <h1 class="cart-heading">Your <em>Cart</em></h1>

  <?php if (empty($items)): ?>

    <div class="empty-cart">
      <i class="bi bi-cart-x"></i>
      <h3>Nothing here yet</h3>
      <p>Add something delicious from our menu ☕</p>
      <a href="index.php#menu" class="btn-pink">Browse Menu</a>
    </div>

  <?php else: ?>

    <div class="cart-layout">

      <!-- Items list -->
      <div class="cart-items-card" id="cartItemsList">
        <?php foreach ($items as $item): ?>
          <div class="cart-item" id="item-<?= $item['product_id'] ?>">

            <img class="ci-img"
                 src="<?= htmlspecialchars('../../' . $item['image']) ?>"
                 alt="<?= htmlspecialchars($item['name']) ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=200&q=70'"/>

            <div class="ci-info">
              <div class="ci-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="ci-price">EGP <?= number_format($item['price'], 2) ?> each</div>
            </div>

            <div class="ci-right">
              <span class="ci-subtotal" id="sub-<?= $item['product_id'] ?>">
                EGP <?= number_format($item['subtotal'], 2) ?>
              </span>

              <div class="qty-stepper">
                <button type="button" class="qty-dec"
                        data-id="<?= $item['product_id'] ?>"
                        data-price="<?= $item['price'] ?>"
                        aria-label="Decrease">−</button>
                <span class="qty-num" id="qty-<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                <button type="button" class="qty-inc"
                        data-id="<?= $item['product_id'] ?>"
                        data-price="<?= $item['price'] ?>"
                        aria-label="Increase">+</button>
              </div>

              <button class="ci-remove" type="button"
                      data-id="<?= $item['product_id'] ?>" title="Remove">
                <i class="bi bi-trash3"></i>
              </button>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <div class="summary-card" id="summaryCard">
        <div class="summary-title">Order Summary</div>

        <div class="summary-row">
          <span>Items (<span id="sumQty"><?= array_sum(array_column($items, 'quantity')) ?></span>)</span>
          <span id="sumSubtotal">EGP <?= number_format($total, 2) ?></span>
        </div>

        <div class="summary-row">
          <span>Delivery</span>
          <span class="text-success fw-semibold">Free</span>
        </div>

        <div class="summary-row total">
          <span>Total</span>
          <span class="val" id="sumTotal">EGP <?= number_format($total, 2) ?></span>
        </div>

        <button class="place-order-btn" id="placeOrderBtn">
          <i class="bi bi-bag-check-fill"></i> Place Order
        </button>
      </div>

    </div>

  <?php endif; ?>
</div>

<div id="orderSuccess">
  <div class="success-box">
    <span class="ring">🎉</span>
    <h2>Order Placed!</h2>
    <p>Hang tight — we're preparing it for you.</p>
    <div class="order-num" id="successOrderId"></div>
    <div class="success-actions">
      <a href="orders.php" class="suc-btn suc-btn-primary">
        <i class="bi bi-bag-check me-1"></i> View Orders
      </a>
      <a href="index.php#menu" class="suc-btn suc-btn-outline">Keep Shopping</a>
    </div>
  </div>
</div>

<div id="cartToast">
  <i class="bi bi-check-circle-fill"></i>
  <span id="cartToastMsg">Updated!</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const ACTION_URL = 'cart_action.php';

function showToast(msg) {
  const t = document.getElementById('cartToast');
  document.getElementById('cartToastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

function updateSummary(data) {
  const qtyEl = document.getElementById('sumQty');
  const subEl = document.getElementById('sumSubtotal');
  const totEl = document.getElementById('sumTotal');
  if (qtyEl) qtyEl.textContent = data.total_qty;
  if (subEl) subEl.textContent = 'EGP ' + data.total_price.toFixed(2);
  if (totEl) totEl.textContent = 'EGP ' + data.total_price.toFixed(2);
}

function removeItemRow(productId) {
  const row = document.getElementById('item-' + productId);
  if (!row) return;
  row.style.transition = 'opacity .25s, max-height .35s';
  row.style.opacity    = '0';
  row.style.maxHeight  = row.offsetHeight + 'px';
  setTimeout(() => {
    row.style.maxHeight = '0';
    row.style.padding   = '0';
    row.style.overflow  = 'hidden';
  }, 30);
  setTimeout(() => row.remove(), 380);
}

function maybeShowEmpty(data) {
  if (!data.items || data.items.length === 0) {
    const layout = document.querySelector('.cart-layout');
    if (layout) {
      layout.innerHTML = `
        <div class="empty-cart" style="grid-column:1/-1">
          <i class="bi bi-cart-x"></i>
          <h3>Your cart is empty</h3>
          <p>Add something delicious from our menu ☕</p>
          <a href="index.php#menu" class="btn-pink">Browse Menu</a>
        </div>`;
    }
  }
}

async function cartAction(params) {
  const body = new URLSearchParams(params);
  const res  = await fetch(ACTION_URL, { method: 'POST', body });
  return res.json();
}

document.addEventListener('click', async function (e) {

  if (e.target.closest('.qty-inc')) {
    const btn    = e.target.closest('.qty-inc');
    const pid    = btn.dataset.id;
    const price  = parseFloat(btn.dataset.price);
    const qtyEl  = document.getElementById('qty-' + pid);
    const newQty = parseInt(qtyEl.textContent) + 1;
    const row    = document.getElementById('item-' + pid);

    row.classList.add('loading');
    const data = await cartAction({ action: 'update', product_id: pid, quantity: newQty });
    row.classList.remove('loading');

    if (data.ok) {
      qtyEl.textContent = newQty;
      document.getElementById('sub-' + pid).textContent = 'EGP ' + (price * newQty).toFixed(2);
      updateSummary(data);
    }
  }

  if (e.target.closest('.qty-dec')) {
    const btn    = e.target.closest('.qty-dec');
    const pid    = btn.dataset.id;
    const price  = parseFloat(btn.dataset.price);
    const qtyEl  = document.getElementById('qty-' + pid);
    const newQty = parseInt(qtyEl.textContent) - 1;
    const row    = document.getElementById('item-' + pid);

    row.classList.add('loading');

    if (newQty < 1) {
      const data = await cartAction({ action: 'remove', product_id: pid });
      row.classList.remove('loading');
      if (data.ok) { removeItemRow(pid); updateSummary(data); maybeShowEmpty(data); }
    } else {
      const data = await cartAction({ action: 'update', product_id: pid, quantity: newQty });
      row.classList.remove('loading');
      if (data.ok) {
        qtyEl.textContent = newQty;
        document.getElementById('sub-' + pid).textContent = 'EGP ' + (price * newQty).toFixed(2);
        updateSummary(data);
      }
    }
  }

  if (e.target.closest('.ci-remove')) {
    const btn  = e.target.closest('.ci-remove');
    const pid  = btn.dataset.id;
    const row  = document.getElementById('item-' + pid);

    row.classList.add('loading');
    const data = await cartAction({ action: 'remove', product_id: pid });
    row.classList.remove('loading');

    if (data.ok) {
      removeItemRow(pid);
      updateSummary(data);
      showToast('Item removed');
      maybeShowEmpty(data);
    }
  }

  if (e.target.closest('#placeOrderBtn')) {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled   = true;
    btn.innerHTML  = '<span class="spinner-border spinner-border-sm me-2"></span>Placing…';
    const data = await cartAction({ action: 'place_order' });
    btn.disabled   = false;
    btn.innerHTML  = '<i class="bi bi-bag-check-fill"></i> Place Order';

    if (data.ok) {
      document.getElementById('successOrderId').textContent =
        'Order #' + data.order_id + ' · EGP ' + parseFloat(data.total).toFixed(2);
      document.getElementById('orderSuccess').classList.add('show');
    } else {
      showToast(data.msg || 'Something went wrong');
    }
  }

  // Dismiss overlay by clicking backdrop
  if (e.target.id === 'orderSuccess') {
    e.target.classList.remove('show');
  }
});
</script>
</body>
</html>