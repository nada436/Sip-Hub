<?php
$activePage = 'products';
$searchPlaceholder = 'Search products...';
require_once '../../db.php';
require_once '../../config/config.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
$db = DATA_BASE::getInstance();
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$product = null;
$isEdit = false;
$errors = [];
$success = '';

if ($id) {
    $result = $db->select("products", "id=$id");
    $product = $result->fetch_assoc();
    if (!$product) {
        header("Location: products.php?error=Product+not+found");
        exit;
    }
    $isEdit = true;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action      = $_POST['action']      ?? '';
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price']       ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $quantity    = intval($_POST['quantity']    ?? 0);

    // ── Validation ───────────────────────────────────────────────
    if ($name === '')        $errors[] = "Product name is required.";
    if ($category_id === '') $errors[] = "Category is required.";
    if ($price === '' || !is_numeric($price) || $price < 0)
                             $errors[] = "A valid price is required.";

    // ── Image upload ─────────────────────────────────────────────
    $imageName = $_POST['existing_image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $file    = $_FILES['image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image type. Allowed: JPG, PNG, WEBP, GIF.";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "Image is too large. Maximum size is 5 MB.";
        } else {
            $newImageName = $file['name'];
            $uploadDir    ="../../images/";
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newImageName)) {
    // Delete old image file if replacing
    if ($isEdit && !empty($imageName)) {
        $oldFile = $uploadDir. $imageName;
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }
    $imageName = $newImageName;
            } else {
                $errors[] = "Failed to save the uploaded image.";
            }
        }
    }

    // ── Save if no errors ─────────────────────────────────────────
    if (empty($errors)) {
        $nameSafe  = addslashes($name);
        $descSafe  = addslashes($description);
        $imgSafe   = addslashes($imageName);

        // ── SCENARIO 1: INSERT ────────────────────────────────────
        if ($action === 'insert') {
            $columns = "name, description, price, image, category_id, quantity";
            $values  = "'$nameSafe', '$descSafe', '$price', '$imgSafe', '$category_id', '$quantity'";
            $newId   = $db->insert("products", $columns, $values);

            if ($newId) {
                header("Location: products.php?success=Product+created+successfully");
                exit;
            } else {
                $errors[] = "Database error: could not create product.";
            }

        // ── SCENARIO 2: UPDATE ────────────────────────────────────
        } elseif ($action === 'update') {
            $editId = intval($_POST['id']);
            $set    = "name='$nameSafe',
                       description='$descSafe',
                       price='$price',
                       image='$imgSafe',
                       category_id='$category_id',
                       quantity='$quantity'";

            $ok = $db->update("products", $set, "id=$editId");

            if ($ok) {
                header("Location: products.php?success=Product+updated+successfully");
                exit;
            } else {
                $errors[] = "Database error: could not update product.";
            }
        }
    }
    $product = [
        'id'          => $_POST['id']          ?? '',
        'name'        => $name,
        'description' => $description,
        'price'       => $price,
        'category_id' => $category_id,
        'quantity'    => $quantity,
        'image'       => $imageName,
    ];
}

// ── Asset paths (relative from views/admin/) ─────────────────────
$cssRoot = BASE_URL . '/assets/css';
$jsRoot  = BASE_URL . '/assets/js';

$categories = $db->selectAll("categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isEdit ? 'Edit Product' : 'Add Product' ?> — <?= APP_NAME ?></title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">

  <!-- Shared admin styles -->
  <link rel="stylesheet" href="<?= $cssRoot ?>/admin_layout.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="<?= $cssRoot ?>/admin_products.css">
<style>
  .alert-error {
    background: #fff0f3;
    border: 1.5px solid #ffb3c1;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 22px;
    color: #c0392b;
    font-size: .87rem;
  }
  .alert-error ul { margin: 6px 0 0 18px; padding: 0; }
  .alert-error li { margin-bottom: 3px; }
</style>
</head>
<body>

<?php
include '../Navbar.php';
include '../Sidebar.php';
?>

<main class="main-content">

  <!-- Page header -->
  <div class="page-header-row">
    <div>
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;color:var(--pink);text-transform:uppercase;margin-bottom:6px">
        Products / <?= $isEdit ? 'Edit Product' : 'Add New Product' ?>
      </div>
      <h1 class="page-title">
        <?php if ($isEdit): ?>
          Edit <span style="color:var(--pink);font-family:var(--font-brand);font-weight:400">Treat</span>
        <?php else: ?>
          Create a New <span style="color:var(--pink);font-family:var(--font-brand);font-weight:400">Treat</span>
        <?php endif; ?>
      </h1>
      <p class="page-subtitle">
        <?= $isEdit
            ? 'Update the details for this product.'
            : 'Fill in the details to add a new delight to the cafeteria menu.' ?>
      </p>
    </div>
    <?php if ($isEdit): ?>
    <div class="edit-mode-badge">
      <i class="bi bi-pencil-square"></i>
      Editing: <strong>#<?= htmlspecialchars($id) ?></strong>
    </div>
    <?php endif; ?>
  </div>

  <!-- Validation errors -->
  <?php if (!empty($errors)): ?>
  <div class="alert-error">
    <strong><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following:</strong>
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Form card — POST to itself -->
  <form class="form-card"
        action="product_form.php<?= $isEdit ? '?id=' . intval($id) : '' ?>"
        method="POST"
        enctype="multipart/form-data">

    <!-- Hidden control fields -->
    <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'insert' ?>">
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= intval($id) ?>">
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
    <?php endif; ?>

    <!-- Section 1: Essential Info -->
    <div class="form-section">
      <div class="section-label"><i class="bi bi-info-circle-fill"></i> Essential Information</div>

      <div class="form-group">
        <label class="form-label" for="prodName">Product Name <span class="req">*</span></label>
        <input class="candy-input" id="prodName" name="name" type="text"
               placeholder="e.g. Rainbow Glazed Donut"
               value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label" for="prodCat">Category <span class="req">*</span></label>
          <select class="candy-select" id="prodCat" name="category_id" required>
            <option value="">Select a category</option>
            <?php
            while ($cat = $categories->fetch_assoc()):
                $selected = (isset($product['category_id']) && $product['category_id'] == $cat['id'])
                            ? 'selected' : '';
            ?>
              <option value="<?= $cat['id'] ?>" <?= $selected ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0">
          <label class="form-label" for="prodPrice">Price ($) <span class="req">*</span></label>
          <input class="candy-input" id="prodPrice" name="price" type="number"
                 min="0" step="0.01" placeholder="0.00"
                 value="<?= htmlspecialchars($product['price'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-group" style="margin-top:20px">
        <label class="form-label" for="prodDesc">Description</label>
        <textarea class="candy-textarea" id="prodDesc" name="description"
                  placeholder="Describe the flavors, ingredients, and vibes…"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- Section 2: Image -->
    <div class="form-section">
      <div class="section-label"><i class="bi bi-image-fill"></i> Product Image</div>

      <?php if (!empty($product['image'])): ?>
      <div style="margin-bottom:14px">
        <p style="font-size:.8rem;color:var(--muted);margin-bottom:6px">
          <?= $isEdit ? 'Current image (upload below to replace):' : 'Uploaded image:' ?>
        </p>
        <img src="../../images/<?= htmlspecialchars($product['image']) ?>"
             alt="Product image"
             style="max-height:130px;border-radius:10px;border:2px solid var(--border)">
      </div>
      <?php endif; ?>

      <div class="img-upload-area" id="uploadArea">
        <input type="file" id="imgInput" name="image" accept="image/*">
        <div id="uploadPlaceholder">
          <div class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
          <div class="upload-text">Drop an image here or click to browse</div>
          <div class="upload-sub">PNG, JPG, WEBP — max 5 MB</div>
        </div>
      </div>
    </div>

    <!-- Section 3: Inventory -->
    <div class="form-section">
      <div class="section-label"><i class="bi bi-boxes"></i> Inventory</div>

      <div class="form-row">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label" for="prodStock">Stock Count (quantity)</label>
          <input class="candy-input" id="prodStock" name="quantity" type="number"
                 min="0" placeholder="0"
                 value="<?= htmlspecialchars($product['quantity'] ?? 0) ?>">
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="form-footer">
      <div class="ft-hint">
        <i class="bi bi-shield-check" style="color:var(--pink)"></i>
        Fields marked <span style="color:var(--pink);margin:0 3px">*</span> are required
      </div>
      <div class="ft-actions">
        <a href="products.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Cancel</a>
        <button class="btn-save-page" type="submit">
          <i class="bi bi-bag-heart-fill"></i>
          <?= $isEdit ? 'Update Product' : 'Save &amp; Create' ?>
        </button>
      </div>
    </div>

  </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $jsRoot ?>/admin_layout.js"></script>
</body>
</html>