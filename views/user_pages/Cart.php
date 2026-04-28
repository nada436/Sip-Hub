<?php
// views/user_pages/cart.php

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../config/config.php';

$db      = DATA_BASE::getInstance();
$user_id = 1; // ← swap with $_SESSION['user_id'] once login is ready

$current_page = 'cart';
$nav_links    = [
    ['href' => 'index.php',    'label' => 'Home',     'page' => 'index'],
    ['href' => 'index.php#menu', 'label' => 'Products', 'page' => 'products'],
    ['href' => 'orders.php',   'label' => 'Orders',   'page' => 'orders'],
];
$notif_count = 0;
$cart_count  = 0;
$user_avatar = 'https://i.pravatar.cc/40?img=12';
$user_name   = 'Marina George';

// Fetch cart items
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <link href="../../assets/css/User_Style.css" rel="stylesheet"/>
  <style>
    /* ── Cart Page Styles ── */
    .cart-wrap {
      max-width: 960px;
      margin: 0 auto;
      padding: 2.5rem 1.25rem 5rem;
    }

    .cart-eyebrow {
      font-size: .78rem;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--pink, #e91e8c);
      margin-bottom: .4rem;
    }

    .cart-heading {
      font-family: 'DM Sans', sans-serif;
      font-size: clamp(1.8rem, 4vw, 2.6rem);
      font-weight: 700;
      color: var(--dark, #1a1a2e);
      margin-bottom: 2rem;
    }

    .cart-heading em {
      font-style: normal;
      color: var(--pink, #e91e8c);
    }

    /* ── Two-column layout ── */
    .cart-layout {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 1.75rem;
      align-items: start;
    }

    @media (max-width: 768px) {
      .cart-layout { grid-template-columns: 1fr; }
    }

    /* ── Item list ── */
    .cart-items-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 24px rgba(0,0,0,.07);
      overflow: hidden;
    }

    .cart-item {
      display: grid;
      grid-template-columns: 80px 1fr auto;
      align-items: center;
      gap: 1rem;
      padding: 1.1rem 1.4rem;
      transition: background .18s;
    }

    .cart-item:not(:last-child) {
      border-bottom: 1px solid #f3f3f3;
    }

    .cart-item:hover { background: #fafafa; }

    .ci-img {
      width: 80px;
      height: 80px;
      border-radius: 14px;
      object-fit: cover;
      flex-shrink: 0;
    }

    .ci-info .ci-name {
      font-weight: 700;
      font-size: .98rem;
      color: var(--dark, #1a1a2e);
      margin-bottom: .15rem;
    }

    .ci-info .ci-price {
      font-size: .84rem;
      color: #888;
    }

    .ci-right {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: .5rem;
    }

    .ci-subtotal {
      font-weight: 700;
      font-size: 1rem;
      color: var(--pink, #e91e8c);
      white-space: nowrap;
    }

    /* Qty stepper */
    .qty-stepper {
      display: flex;
      align-items: center;
      border: 1.5px solid #ececec;
      border-radius: 50px;
      overflow: hidden;
      height: 34px;
    }

    .qty-stepper button {
      background: none;
      border: none;
      width: 34px;
      height: 34px;
      font-size: 1.1rem;
      cursor: pointer;
      color: var(--dark, #1a1a2e);
      transition: background .15s, color .15s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .qty-stepper button:hover {
      background: var(--pink, #e91e8c);
      color: #fff;
    }

    .qty-stepper .qty-num {
      min-width: 30px;
      text-align: center;
      font-weight: 700;
      font-size: .9rem;
      color: var(--dark, #1a1a2e);
    }

    .ci-remove {
      background: none;
      border: none;
      color: #ccc;
      font-size: 1rem;
      cursor: pointer;
      padding: 0;
      transition: color .18s;
      line-height: 1;
    }

    .ci-remove:hover { color: #e53935; }

    /* ── Summary card ── */
    .summary-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 24px rgba(0,0,0,.07);
      padding: 1.6rem 1.5rem;
      position: sticky;
      top: 90px;
    }

    .summary-title {
      font-weight: 700;
      font-size: 1.15rem;
      color: var(--dark, #1a1a2e);
      margin-bottom: 1.2rem;
      padding-bottom: .75rem;
      border-bottom: 1px solid #f0f0f0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      font-size: .9rem;
      color: #666;
      margin-bottom: .6rem;
    }

    .summary-row.total {
      font-weight: 700;
      font-size: 1.08rem;
      color: var(--dark, #1a1a2e);
      border-top: 1px solid #f0f0f0;
      padding-top: .85rem;
      margin-top: .4rem;
    }

    .summary-row.total .val { color: var(--pink, #e91e8c); }

    .place-order-btn {
      width: 100%;
      background: var(--pink, #e91e8c);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: .85rem 1rem;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 1.2rem;
      transition: background .2s, transform .15s, box-shadow .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
    }

    .place-order-btn:hover:not(:disabled) {
      background: #c9186f;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(233,30,140,.3);
    }

    .place-order-btn:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      color: var(--pink, #e91e8c);
      font-weight: 600;
      font-size: .9rem;
      text-decoration: none;
      margin-bottom: 1.4rem;
      transition: gap .2s;
    }

    .back-link:hover { gap: .55rem; color: #c9186f; }

    /* ── Empty state ── */
    .empty-cart {
      text-align: center;
      padding: 5rem 1rem;
    }

    .empty-cart i {
      font-size: 4rem;
      color: #ddd;
      display: block;
      margin-bottom: 1rem;
    }

    .empty-cart h3 {
      font-weight: 700;
      font-size: 1.4rem;
      color: var(--dark, #1a1a2e);
      margin-bottom: .5rem;
    }

    .empty-cart p { color: #999; margin-bottom: 1.5rem; }

    .btn-pink {
      display: inline-block;
      background: var(--pink, #e91e8c);
      color: #fff;
      border-radius: 50px;
      padding: .65rem 1.6rem;
      font-weight: 700;
      text-decoration: none;
      transition: background .2s;
    }

    .btn-pink:hover { background: #c9186f; color: #fff; }

    /* ── Success overlay ── */
    #orderSuccess {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }

    #orderSuccess.show { display: flex; }

    .success-box {
      background: #fff;
      border-radius: 24px;
      padding: 2.5rem 2rem;
      text-align: center;
      max-width: 380px;
      width: 90%;
      animation: popIn .35s cubic-bezier(.34,1.56,.64,1);
    }

    @keyframes popIn {
      from { transform: scale(.8); opacity: 0; }
      to   { transform: scale(1);  opacity: 1; }
    }

    .success-box .ring {
      font-size: 3.5rem;
      display: block;
      margin-bottom: .75rem;
    }

    .success-box h2 {
      font-weight: 800;
      font-size: 1.5rem;
      color: var(--dark, #1a1a2e);
      margin-bottom: .4rem;
    }

    .success-box p { color: #888; font-size: .92rem; margin-bottom: 1.4rem; }

    .success-box .order-num {
      font-size: .82rem;
      font-weight: 700;
      color: var(--pink, #e91e8c);
      letter-spacing: .05em;
      margin-bottom: 1.4rem;
    }

    .success-actions { display: flex; gap: .75rem; flex-wrap: wrap; justify-content: center; }

    .suc-btn {
      border-radius: 50px;
      padding: .6rem 1.4rem;
      font-weight: 700;
      font-size: .88rem;
      cursor: pointer;
      text-decoration: none;
      transition: .2s;
    }

    .suc-btn-primary {
      background: var(--pink, #e91e8c);
      color: #fff;
      border: none;
    }

    .suc-btn-primary:hover { background: #c9186f; color: #fff; }

    .suc-btn-outline {
      background: transparent;
      color: var(--dark, #1a1a2e);
      border: 2px solid #e0e0e0;
    }

    .suc-btn-outline:hover { border-color: #bbb; }

    /* Toast */
    #cartToast {
      position: fixed;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%) translateY(100px);
      background: #1a1a2e;
      color: #fff;
      border-radius: 50px;
      padding: .65rem 1.4rem;
      font-size: .88rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: .5rem;
      z-index: 8000;
      transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s;
      opacity: 0;
      pointer-events: none;
    }

    #cartToast.show {
      transform: translateX(-50%) translateY(0);
      opacity: 1;
    }

    /* Loading overlay on item */
    .cart-item.loading { opacity: .5; pointer-events: none; }

    /* Skeleton / spinner on full reload */
    .spinner-row {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem;
      color: #ccc;
      font-size: 1.5rem;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/UNavbar.php'; ?>

<div class="cart-wrap">

  <a href="index.php" class="back-link">
    <i class="bi bi-arrow-left"></i> Back to Menu
  </a>

  <div class="cart-eyebrow">🛒 Shopping Bag</div>
  <h1 class="cart-heading">Your <em>Cart</em></h1>

  <?php if (empty($items)): ?>
    <!-- ── Empty state ── -->
    <div class="empty-cart">
      <i class="bi bi-cart-x"></i>
      <h3>Nothing here yet</h3>
      <p>Add something delicious from our menu ☕</p>
      <a href="index.php#menu" class="btn-pink">Browse Menu</a>
    </div>

  <?php else: ?>
    <div class="cart-layout">

      <!-- ── Left: items ── -->
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
                <button type="button"
                        class="qty-dec"
                        data-id="<?= $item['product_id'] ?>"
                        data-price="<?= $item['price'] ?>"
                        aria-label="Decrease">−</button>
                <span class="qty-num" id="qty-<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                <button type="button"
                        class="qty-inc"
                        data-id="<?= $item['product_id'] ?>"
                        data-price="<?= $item['price'] ?>"
                        aria-label="Increase">+</button>
              </div>
              <button class="ci-remove"
                      type="button"
                      data-id="<?= $item['product_id'] ?>"
                      title="Remove">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- ── Right: summary ── -->
      <div class="summary-card" id="summaryCard">
        <div class="summary-title">Order Summary</div>

        <div class="summary-row">
          <span>Items (<span id="sumQty"><?= array_sum(array_column($items, 'quantity')) ?></span>)</span>
          <span id="sumSubtotal">EGP <?= number_format($total, 2) ?></span>
        </div>
        <div class="summary-row">
          <span>Delivery</span>
          <span style="color:#27ae60;font-weight:600">Free</span>
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

<!-- ── Order success overlay ── -->
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

<!-- Toast -->
<div id="cartToast">
  <i class="bi bi-check-circle-fill"></i>
  <span id="cartToastMsg">Updated!</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ACTION_URL = 'cart_action.php';

/* ── Toast ── */
function showToast(msg) {
  const t = document.getElementById('cartToast');
  document.getElementById('cartToastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

/* ── Update summary numbers ── */
function updateSummary(data) {
  const qtyEl  = document.getElementById('sumQty');
  const subEl  = document.getElementById('sumSubtotal');
  const totEl  = document.getElementById('sumTotal');
  if (qtyEl)  qtyEl.textContent  = data.total_qty;
  if (subEl)  subEl.textContent  = 'EGP ' + data.total_price.toFixed(2);
  if (totEl)  totEl.textContent  = 'EGP ' + data.total_price.toFixed(2);
}

/* ── Remove item row from DOM ── */
function removeItemRow(productId) {
  const row = document.getElementById('item-' + productId);
  if (row) {
    row.style.transition = 'opacity .25s, max-height .35s';
    row.style.opacity    = '0';
    row.style.maxHeight  = row.offsetHeight + 'px';
    setTimeout(() => {
      row.style.maxHeight  = '0';
      row.style.padding    = '0';
      row.style.overflow   = 'hidden';
    }, 30);
    setTimeout(() => row.remove(), 380);
  }
}

/* ── Show empty state when no items remain ── */
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

/* ── API call helper ── */
async function cartAction(params) {
  const body = new URLSearchParams(params);
  const res  = await fetch(ACTION_URL, { method: 'POST', body });
  return res.json();
}

/* ── Quantity change ── */
document.addEventListener('click', async function(e) {

  /* INC */
  if (e.target.closest('.qty-inc')) {
    const btn = e.target.closest('.qty-inc');
    const pid = btn.dataset.id;
    const price = parseFloat(btn.dataset.price);
    const qtyEl = document.getElementById('qty-' + pid);
    const newQty = parseInt(qtyEl.textContent) + 1;

    const row = document.getElementById('item-' + pid);
    row.classList.add('loading');
    const data = await cartAction({ action: 'update', product_id: pid, quantity: newQty });
    row.classList.remove('loading');

    if (data.ok) {
      qtyEl.textContent = newQty;
      document.getElementById('sub-' + pid).textContent =
        'EGP ' + (price * newQty).toFixed(2);
      updateSummary(data);
    }
  }

  /* DEC */
  if (e.target.closest('.qty-dec')) {
    const btn = e.target.closest('.qty-dec');
    const pid = btn.dataset.id;
    const price = parseFloat(btn.dataset.price);
    const qtyEl = document.getElementById('qty-' + pid);
    const newQty = parseInt(qtyEl.textContent) - 1;

    const row = document.getElementById('item-' + pid);
    row.classList.add('loading');

    if (newQty < 1) {
      const data = await cartAction({ action: 'remove', product_id: pid });
      row.classList.remove('loading');
      if (data.ok) {
        removeItemRow(pid);
        updateSummary(data);
        maybeShowEmpty(data);
      }
    } else {
      const data = await cartAction({ action: 'update', product_id: pid, quantity: newQty });
      row.classList.remove('loading');
      if (data.ok) {
        qtyEl.textContent = newQty;
        document.getElementById('sub-' + pid).textContent =
          'EGP ' + (price * newQty).toFixed(2);
        updateSummary(data);
      }
    }
  }

  /* REMOVE */
  if (e.target.closest('.ci-remove')) {
    const btn = e.target.closest('.ci-remove');
    const pid = btn.dataset.id;
    const row = document.getElementById('item-' + pid);
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

  /* PLACE ORDER */
  if (e.target.closest('#placeOrderBtn')) {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Placing…';

    const data = await cartAction({ action: 'place_order' });
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-bag-check-fill"></i> Place Order';

    if (data.ok) {
      document.getElementById('successOrderId').textContent =
        'Order #' + data.order_id + ' · EGP ' + parseFloat(data.total).toFixed(2);
      document.getElementById('orderSuccess').classList.add('show');
    } else {
      showToast(data.msg || 'Something went wrong');
    }
  }

  /* Close success overlay on backdrop click */
  if (e.target.id === 'orderSuccess') {
    e.target.classList.remove('show');
  }
});
</script>
</body>
</html>