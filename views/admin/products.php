<?php
$activePage = 'products';
require_once '../../db.php';

$db = DATA_BASE::getInstance();
$result   = $db->selectAll("products");  
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$totalItems   = count($products);
$activeTreats = count(array_filter($products, fn($p) => strtolower($p['quantity']) >0 ));
$lowStock     = count(array_filter($products, fn($p) => strtolower($p['quantity']) < 30));

// Pagination 
$perPage      = 4;
$currentPage  = max(1, (int)($_GET['page'] ?? 1));
$totalPages   = max(1, (int)ceil($totalItems / $perPage));
$pageProducts = array_slice($products, ($currentPage - 1) * $perPage, $perPage);

function stockBarClass(int $pct): string {
    if ($pct >= 70) return 'stock-high';
    if ($pct >= 30) return 'stock-med';
    return 'stock-low';
}

include '../Navbar.php';
include '../Sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafeteria — Products</title>
  <link rel="stylesheet" href="../../assets/css/admin_products.css">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
</head>
<body>
<div class="main-content">
  <div class="d-flex align-items-start justify-content-between mb-4">
    <div>
      <h1 class="page-title">Manage Products</h1>
      <p class="page-subtitle mb-0">Sweeten up the menu by adding or editing cafeteria items.</p>
    </div>
    <a href="product-add.php" class="btn-add">
      <i class="bi bi-plus-circle"></i> Add New Item
    </a>
  </div>

  <!-- Stat Cards -->
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

  <!-- Products Table -->
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
            $catResult = $db->select("categories", "id=" . (int)$p['category_id']);
            $cat = $catResult->fetch_assoc();
            $catName=$cat['name'];
             $stockPct = max(0, min(100, (int)(($p['quantity'] ?? 0) / 100 * 100)));
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($p['img'])): ?>
                    <img src="<?= htmlspecialchars($p['img']) ?>"
                         alt="" class="prod-thumb">
                  <?php else: ?>
                    <div class="prod-thumb"></div>
                  <?php endif; ?>
                  <div>
                    <div class="prod-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="prod-id">#<?= htmlspecialchars($p['id']) ?></div>
                  </div>
                </div>
              </td>

              <!-- Category -->
             <td>
           <span class="cat <?= $catClass ?>">
           <?= htmlspecialchars($catName) ?>
            </span>
            </td>

              <!-- Stock bar -->
              <td>
                <div class="stock-wrap <?= stockBarClass($stockPct) ?>">
                  <div class="stock-track">
                    <div class="stock-fill" style="width:<?= $stockPct ?>%"></div>
                  </div>
                  <span class="stock-pct"><?= $stockPct ?>%</span>
                </div>
              </td>

              <!-- Price -->
              <td><span class="price-val">$<?= number_format((float)($p['price'] ?? 0), 2) ?></span></td>

              <!-- Actions -->
              <td>
                <div class="d-flex justify-content-end gap-2">
                  <a href="product-edit.php?id=<?=($p['id']) ?>"
                     class="act-btn" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="product-delete.php?id=<?= ($p['id']) ?>"
                     class="act-btn del" title="Delete"
                     >
                    <i class="bi bi-trash3"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="tbl-footer">
      <span class="showing">
        Showing <?= ($currentPage - 1) * $perPage + 1 ?> to
        <?= min($currentPage * $perPage, $totalItems) ?> of <?= $totalItems ?> items
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
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>