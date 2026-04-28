<?php
// views/user_pages/Products.php

$active_cat = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$cat_result = $db->selectAll('categories');
$categories = [];
while ($cat = $cat_result->fetch_assoc()) {
    $categories[(int)$cat['id']] = $cat['name'];
}

$result = $active_cat > 0
    ? $db->select('products', "category_id = $active_cat")
    : $db->selectAll('products');

$products = [];
while ($row = $result->fetch_assoc()) $products[] = $row;
?>

<section id="menu" class="products-section">
  <div class="products-header">
    <span class="section-tag">🛍️ Our Menu</span>
    <h2 class="section-title">Freshly Made, <span>Just for You</span></h2>
    <p class="section-sub">From bold espressos to buttery pastries — crafted with love every day.</p>
  </div>

  <!-- Category filter tabs -->
  <div class="cat-filter">
    <a href="?" class="cat-tab <?= $active_cat === 0 ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $id => $name): ?>
      <a href="?category=<?= $id ?>" class="cat-tab <?= $active_cat === $id ? 'active' : '' ?>">
        <?= htmlspecialchars($name) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($products)): ?>
    <div class="no-products">
      <i class="bi bi-cup-hot"></i>
      <p>No products found in this category.</p>
      <a href="?" class="cat-tab active" style="margin-top:.4rem">Show All</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $p): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <img src="<?= htmlspecialchars('../../' . $p['image']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 class="product-img"
                 loading="lazy"
                 onerror="this.src='https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=70'"/>
            <?php if (!empty($categories[(int)$p['category_id']])): ?>
              <span class="product-badge"><?= htmlspecialchars($categories[(int)$p['category_id']]) ?></span>
            <?php endif; ?>
          </div>

          <div class="product-body">
            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
            <p class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>
            <div class="product-footer">
              <span class="product-price">EGP <?= number_format((float)$p['price'], 2) ?></span>
              <div class="qty-row">
                <div class="qty-control">
                  <button class="qty-btn qty-minus" type="button" aria-label="Decrease">−</button>
                  <span class="qty-value">1</span>
                  <button class="qty-btn qty-plus"  type="button" aria-label="Increase">+</button>
                </div>
                <button class="add-to-cart-btn"
                        type="button"
                        data-id="<?= (int)$p['id'] ?>"
                        data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>">
                  <i class="bi bi-cart-plus"></i> Add
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<!-- JS for this section lives in assets/js/products.js, loaded by index.php -->