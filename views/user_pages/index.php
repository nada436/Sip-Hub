<?php
// views/user_pages/index.php

require_once __DIR__ . '/../../db.php';
$db = DATA_BASE::getInstance();

$current_page = 'index';

$nav_links = [
    ['href' => 'index.php',        'label' => 'Home',     'page' => 'index'],
    ['href' => 'index.php#menu',   'label' => 'Products', 'page' => 'products'],
    ['href' => 'orders.php',       'label' => 'Orders',   'page' => 'orders'],
];

$notif_count = 0;
$cart_count  = 0;
$user_avatar = 'https://i.pravatar.cc/40?img=12';
$user_name   = 'Marina George';

$slides = [
    [
        'image'    => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=1400&q=80',
        'tag'      => 'Hot & Fresh',
        'title'    => 'Crafted Coffee',
        'sub'      => 'Rich espresso, velvety foam — your perfect morning cup.',
        'btn'      => 'Order Now',
        'gradient' => 'linear-gradient(120deg,rgba(233,30,140,.45) 0%,rgba(255,180,220,.25) 100%)',
    ],
    [
        'image'    => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=1400&q=80',
        'tag'      => 'Calm & Soothing',
        'title'    => 'Finest Tea',
        'sub'      => 'Handpicked leaves brewed to cozy, aromatic perfection.',
        'btn'      => 'Explore Teas',
        'gradient' => 'linear-gradient(120deg,rgba(180,20,110,.40) 0%,rgba(255,150,210,.22) 100%)',
    ],
    [
        'image'    => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=1400&q=80',
        'tag'      => 'Cool & Bold',
        'title'    => 'Iced Coffee',
        'sub'      => 'Cold-brewed strength over ice — refreshingly irresistible.',
        'btn'      => 'Try It Cold',
        'gradient' => 'linear-gradient(120deg,rgba(233,30,140,.42) 0%,rgba(120,10,80,.30) 100%)',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Caffeteria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <link href="../../assets/css/User_Style.css" rel="stylesheet"/>
  <link href="../../assets/css/cart.css" rel="stylesheet"/>
</head>
<body>

<?php include __DIR__ . '/UNavbar.php'; ?>

<div class="hero-wrap">
  <?php include __DIR__ . '/Hero.php'; ?>
</div>

<?php include __DIR__ . '/Products.php'; ?>

<!-- ══════════ CART DRAWER ══════════ -->
<div id="cartOverlay"></div>

<div id="cartDrawer" role="dialog" aria-label="Your Cart">
  <div class="drawer-handle"></div>

  <div class="drawer-header">
    <div class="drawer-title">
      Your <span>Cart</span>
      <span id="drawerBadge"></span>
    </div>
    <button class="drawer-close" id="closeCartBtn" aria-label="Close cart">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="drawer-body" id="drawerBody">
    <div class="drawer-empty" id="drawerEmpty">
      <i class="bi bi-cart-x"></i>
      <p>Your cart is empty</p>
      <small>Add something delicious! ☕</small>
    </div>
    <div id="drawerItems"></div>
  </div>

  <div class="drawer-footer" id="drawerFooter" style="display:none">
    <div class="drawer-total-row">
      <span class="drawer-total-label">Total</span>
      <span class="drawer-total-price" id="drawerTotal">EGP 0.00</span>
    </div>
    <button class="place-order-btn" id="placeOrderBtn">
      <i class="bi bi-bag-check-fill"></i> Place Order
    </button>
  </div>
</div>

<!-- ══════════ ORDER SUCCESS ══════════ -->
<div id="orderSuccess">
  <div class="success-ring">🎉</div>
  <div class="success-title">Order Placed!</div>
  <div class="success-sub">Hang tight — we're preparing it for you.</div>
  <div class="success-id" id="successOrderId"></div>
  <div class="success-actions">
    <a href="orders.php" class="suc-btn suc-btn-primary">
      <i class="bi bi-bag-check me-1"></i> View My Orders
    </a>
    <button class="suc-btn suc-btn-outline" id="successContinueBtn">Keep Shopping</button>
  </div>
</div>

<!-- Toast notification -->
<div id="cartToast">
  <i class="bi bi-cart-check-fill"></i>
  <span id="cartToastMsg">Added to cart!</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- cart.js must load before products.js (exposes openCartDrawer, updateCartBadge, showToast) -->
<script src="../../assets/js/cart.js"></script>
<script src="../../assets/js/products.js"></script>
</body>
</html>