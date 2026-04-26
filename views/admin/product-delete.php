<?php
require_once '../../config/config.php';
require_once '../../db.php';

// ── 1. Auth guard ───────────────────────────────────────────
if (empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// ── 2. Validate ID ──────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid product ID.';
    header('Location: products.php');
    exit;
}

// ── 3. Fetch product ────────────────────────────────────────
$db = DATA_BASE::getInstance();
$result = $db->select('products', 'id=' . $id);
$product = $result->fetch_assoc();

if (!$product) {
    $_SESSION['flash_error'] = 'Product not found.';
    header('Location: products.php');
    exit;
}

// ── 4. CSRF token ───────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// ── 5. Handle POST confirmation ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrfToken, $postToken)) {
        $_SESSION['flash_error'] = 'Security token mismatch. Please try again.';
        header('Location: products.php');
        exit;
    }

    try {
        $db->delete('products', 'id=' . $id);
        $_SESSION['flash_success'] = '“' . htmlspecialchars($product['name']) . '” has been deleted.';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Could not delete the product. Please try again.';
    }

    header('Location: products.php');
    exit;
}

// ── 6. Build category info for display ──────────────────────
$catResult = $db->select('categories', 'id=' . (int)$product['category_id']);
$cat = $catResult->fetch_assoc();
$catName = $cat['name'] ?? 'Uncategorized';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Product — <?= htmlspecialchars($product['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/admin_products.css">
</head>
<body>
<?php include '../Navbar.php'; ?>
<?php include '../Sidebar.php'; ?>

<div class="main-content">
  <div class="mb-4">
    <h1 class="page-title">Delete Product</h1>
    <p class="page-subtitle mb-0">Review the details below before confirming removal.</p>
  </div>

  <div class="delete-card">
    <div class="delete-icon-wrap">
      <i class="bi bi-exclamation-triangle-fill"></i>
    </div>

    <h2 class="delete-title">Are you sure?</h2>
    <p class="delete-subtitle">
      This action cannot be undone. The product will be permanently removed from the catalog.
    </p>

    <div class="delete-product-preview">
      <?php if (!empty($product['img'])): ?>
        <img src="<?= htmlspecialchars($product['img']) ?>" alt="" class="delete-thumb">
      <?php else: ?>
        <div class="delete-thumb delete-thumb--empty">
          <i class="bi bi-image"></i>
        </div>
      <?php endif; ?>
      <div class="delete-product-info">
        <div class="delete-product-name"><?= htmlspecialchars($product['name']) ?></div>
        <div class="delete-product-meta">
          <span class="cat cat-other"><?= htmlspecialchars($catName) ?></span>
          <span class="delete-product-price">$<?= number_format((float)($product['price'] ?? 0), 2) ?></span>
        </div>
      </div>
    </div>

    <form method="POST" action="product-delete.php?id=<?= $id ?>" class="delete-actions">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <a href="products.php" class="btn-cancel">
        <i class="bi bi-arrow-left"></i> Cancel
      </a>
      <button type="submit" class="btn-confirm-delete">
        <i class="bi bi-trash3"></i> Yes, Delete Product
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

