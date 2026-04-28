<?php
// ── Bootstrap & DB ─────────────────────────────────────────────
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
$db = DATA_BASE::getInstance();

$activePage = 'products';
$searchPlaceholder = 'Search products...';

// ── Flash messages from redirect ─────────────────────────────
$flashSuccess = $_GET['success'] ?? '';
$flashError   = $_GET['error']   ?? '';


$result = $db->selectAll('products');
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$totalItems = count($products);
$activeTreats = count(array_filter($products, fn($p) => (int)($p['quantity'] ?? 0) > 0));
$lowStock = count(array_filter($products, fn($p) => (int)($p['quantity'] ?? 0) < 10));

$perPage = 4;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalPages = max(1, (int)ceil($totalItems / $perPage));
$pageProducts = array_slice($products, ($currentPage - 1) * $perPage, $perPage);

function stockBarClass(int $pct): string {
    if ($pct >= 70) return 'stock-high';
    if ($pct >= 30) return 'stock-med';
    return 'stock-low';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Cafeteria — Products</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/admin_layout.css">
  <link rel="stylesheet" href="../../assets/css/admin_products.css">
</head>
<body>
<?php include '../Navbar.php'; ?>
<?php include '../Sidebar.php'; ?>

<div class="main-content">
  <?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show flash-banner" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashSuccess) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show flash-banner" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flashError) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
    <div>
      <h1 class="page-title">Manage Products</h1>
      <p class="page-subtitle mb-0">Sweeten up the menu by adding or editing cafeteria items.</p>
    </div>
    <a href="product_form.php" class="btn-add">
      <i class="bi bi-plus-circle"></i> Add New Item
    </a>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="stat-card sc-pink">
        <div class="stat-icon si-pink"><i class="bi bi-emoji-smile"></i></div>
        <p class="stat-label sl-pink mb-1">Active Treats</p>
        <div class="stat-value"><?= $activeTreats ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card sc-blue">
        <div class="stat-icon si-blue"><i class="bi bi-box-seam"></i></div>
        <p class="stat-label sl-blue mb-1">Low Stock</p>
        <div class="stat-value"><?= $lowStock ?></div>
      </div>
    </div>
  </div>

  <div class="products-card">
    <table class="pt">
      <thead>
        <tr>
          <th>Product</th>
          <th>Category</th>
          <th>Stock</th>
          <th>Price</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pageProducts)): ?>
          <tr>
            <td colspan="6" class="text-center py-5" style="color:var(--muted);">
              <i class="bi bi-inbox fs-2 d-block mb-2"></i>No products found.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($pageProducts as $p): ?>
            <?php
            $catResult = $db->select('categories', 'id=' . (int)$p['category_id']);
            $cat = $catResult->fetch_assoc();
            $catName = $cat['name'] ?? 'Uncategorized';
            $catSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $catName));
            $catClass = in_array($catSlug, ['pastries', 'drinks', 'sweets'], true) ? 'cat-' . $catSlug : 'cat-other';
            $stockPct = max(0, min(100, (int)($p['quantity'] ?? 0)));
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($p['image'])): ?>
                  <img src="<?php echo '../../images/' . $p['image']; ?>"  style="width: 60px; height: 60px; object-fit: cover;">

                  <?php else: ?>
                    <div class="prod-thumb"></div>
                  <?php endif; ?>
                  <div>
                    <div class="prod-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="prod-id">#<?= htmlspecialchars($p['id']) ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="cat <?= $catClass ?>"><?= htmlspecialchars($catName) ?></span>
              </td>
              <td>
                <div class="stock-wrap <?= stockBarClass($stockPct) ?>">
                  <div class="stock-track">
                    <div class="stock-fill" style="width:<?= $stockPct ?>%"></div>
                  </div>
                  <span class="stock-pct"><?= $stockPct ?>%</span>
                </div>
              </td>
              <td><span class="price-val">$<?= number_format((float)($p['price'] ?? 0), 2) ?></span></td>
              <td>
                <div class="d-flex justify-content-end gap-2">

                  <a href="product_form.php?id=<?=($p['id']) ?>"
                     class="act-btn" title="Edit">

                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="product-delete.php?id=<?= (int)$p['id'] ?>" class="act-btn del" title="Delete">
                    <i class="bi bi-trash3"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="tbl-footer">
      <span class="showing">
        Showing <?= $totalItems > 0 ? (($currentPage - 1) * $perPage + 1) : 0 ?> to
        <?= min($currentPage * $perPage, $totalItems) ?> of <?= $totalItems ?> items
      </span>
      <ul class="cpag">
        <li class="<?= $currentPage <= 1 ? 'pg-disabled' : '' ?>">
          <a href="?page=<?= $currentPage - 1 ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
          <li class="<?= $pg === $currentPage ? 'pg-active' : '' ?>">
            <?= $pg === $currentPage ? "<span>$pg</span>" : "<a href='?page=$pg'>$pg</a>" ?>
          </li>
        <?php endfor; ?>
        <li class="<?= $currentPage >= $totalPages ? 'pg-disabled' : '' ?>">
          <a href="?page=<?= $currentPage + 1 ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
      </ul>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/admin_layout.js"></script>
</body>
</html>
