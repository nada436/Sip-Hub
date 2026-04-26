<?php
// views/user_pages/Products.php
// Expects: $db (DATA_BASE instance)

// ── Active category filter ────────────────────────────────────
$active_cat = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// ── Fetch all categories for the filter bar ───────────────────
$cat_result = $db->selectAll('categories');
$categories = [];
while ($cat = $cat_result->fetch_assoc()) {
    $categories[$cat['id']] = $cat['name'];
}

// ── Fetch products (filtered or all) ─────────────────────────
if ($active_cat > 0) {
    $result = $db->select('products', "category_id = $active_cat");
} else {
    $result = $db->selectAll('products');
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<section class="products-section">

  <div class="products-header">
    <span class="section-tag">🛍️ Our Menu</span>
    <h2 class="section-title">Freshly Made, <span>Just for You</span></h2>
    <p class="section-sub">From bold espressos to buttery pastries — crafted with love every day.</p>
  </div>

  <!-- ── Category filter tabs ── -->
  <div class="cat-filter">
    <a href="?" class="cat-tab <?= ($active_cat === 0) ? 'active' : '' ?>">
      All
    </a>
    <?php foreach ($categories as $id => $name): ?>
      <a href="?category=<?= $id ?>"
         class="cat-tab <?= ($active_cat === $id) ? 'active' : '' ?>">
        <?= htmlspecialchars($name) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Product grid ── -->
  <?php if (empty($products)): ?>
    <div class="no-products">
      <i class="bi bi-cup-hot"></i>
      <p>No products found in this category.</p>
      <a href="?" class="cat-tab active" style="margin-top:.5rem">Show All</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">

          <div class="product-img-wrap">
            <img
              src="<?= htmlspecialchars('../../' . $product['image']) ?>"
              alt="<?= htmlspecialchars($product['name']) ?>"
              class="product-img"
              onerror="this.src='https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=70'"
            />
            <?php if (!empty($categories[$product['category_id']])): ?>
              <span class="product-badge"><?= htmlspecialchars($categories[$product['category_id']]) ?></span>
            <?php endif; ?>
          </div>

          <div class="product-body">
            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>

            <div class="product-footer">
              <span class="product-price">EGP <?= number_format($product['price'], 2) ?></span>

              <div class="qty-row">
                <div class="qty-control">
                  <button class="qty-btn qty-minus" type="button">−</button>
                  <span class="qty-value">1</span>
                  <button class="qty-btn qty-plus" type="button">+</button>
                </div>
                <button
                  class="add-to-cart-btn"
                  data-id="<?= (int)$product['id'] ?>"
                  title="Add to cart"
                >
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

<script>
document.querySelectorAll('.product-card').forEach(card => {
  const minus   = card.querySelector('.qty-minus');
  const plus    = card.querySelector('.qty-plus');
  const display = card.querySelector('.qty-value');

  plus.addEventListener('click', () => {
    display.textContent = parseInt(display.textContent) + 1;
  });

  minus.addEventListener('click', () => {
    const current = parseInt(display.textContent);
    if (current > 1) display.textContent = current - 1;
  });
});
</script>