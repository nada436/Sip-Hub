<?php
/**
 * create_category.php  –  Manage Categories | Candy Cafeteria Admin
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../db.php';

$db = DATA_BASE::getInstance();

$errors   = [];
$success  = '';
$formData = ['name' => ''];
$editMode = false;
$editId   = null;

// ── DELETE ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $delId = (int)($_POST['delete_id'] ?? 0);
    if ($delId > 0) {
        $db->delete('categories', "id = $delId");
        $success = 'Category deleted successfully.';
    }
}

// ── CREATE ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $name     = trim($_POST['name'] ?? '');
    $formData = compact('name');
    if ($name === '') {
        $errors[] = 'Category name is required.';
    }
    if (empty($errors)) {
        $newId = $db->insert('categories', 'name', "'$name'");
        if ($newId) {
            $success  = "Category <strong>" . htmlspecialchars($name) . "</strong> created!";
            $formData = ['name' => ''];
        } else {
            $errors[] = 'Database error. Please try again.';
        }
    }
}

// ── UPDATE ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $editId   = (int)($_POST['edit_id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $formData = compact('name');
    $editMode = true;
    if ($name === '') {
        $errors[] = 'Category name is required.';
    }
    if (empty($errors) && $editId > 0) {
        $safeName = addslashes($name);
        $db->update('categories', "name = '$safeName'", "id = $editId");
        $success  = "Category updated to <strong>" . htmlspecialchars($name) . "</strong>!";
        $formData = ['name' => ''];
        $editMode = false;
        $editId   = null;
    }
}

// ── PRE-FILL FORM FOR EDIT ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $row    = $db->select('categories', "id = $editId");
    $cat    = $row->fetch_assoc();
    if ($cat) {
        $formData = ['name' => $cat['name']];
        $editMode = true;
    }
}

// ── FETCH ALL ────────────────────────────────────────────────────
$result     = $db->selectAll('categories');
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$cssRoot = BASE_URL . '/assets/css';
$jsRoot  = BASE_URL . '/assets/js';
$activePage = 'live-categories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories </title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssRoot ?>/admin_layout.css">
  <link rel="stylesheet" href="<?= $cssRoot ?>/categories.css">

  <style>
    
  </style>
</head>
<body>

  <?php include '../Navbar.php'; ?>
  <?php include '../Sidebar.php'; ?>

<!-- ── Delete Confirm Modal ──────────────────────────────────── -->
<div class="modal-backdrop" id="deleteModal">
  <div class="modal-box">
    <div class="modal-icon-wrap"><i class="bi bi-trash3-fill"></i></div>
    <h4>Delete Category?</h4>
    <p>You're about to delete <span class="modal-name-highlight" id="modalCatName"></span>. This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
      <button class="btn-modal-confirm" onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>
<form id="deleteForm" method="POST" style="display:none;">
  <input type="hidden" name="delete_id" id="deleteIdInput">
  <input type="hidden" name="delete_category" value="1">
</form>

<main class="main-content">

  <!-- Breadcrumb -->
  <nav class="breadcrumb-strip">
    <a href="#">Admin</a>
    <i class="bi bi-chevron-right sep"></i>
    <a href="#">Categories</a>
    <i class="bi bi-chevron-right sep"></i>
    <span class="cur"><?= $editMode ? 'Edit Category' : 'Add New' ?></span>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h1><?= $editMode ? '✏️ Edit Category' : ' Manage Categories' ?></h1>
      <p><?= $editMode ? 'Update the category name below.' : 'Create and organise your candy collections.' ?></p>
    </div>
    <div class="count-pill">
      <i class="bi bi-tags-fill me-1"></i><?= count($categories) ?> <?= count($categories) === 1 ? 'category' : 'categories' ?>
    </div>
  </div>

  <!-- Two-column grid -->
  <div class="page-grid">

    <!-- ── LEFT: Form Card ───────────────────────────── -->
    <div class="cc-card">
      <div class="form-card-header <?= $editMode ? 'mode-edit' : '' ?>">
        <div class="form-icon <?= $editMode ? 'edit' : 'create' ?>">
          <i class="bi <?= $editMode ? 'bi-pencil-fill' : 'bi-plus-lg' ?>"></i>
        </div>
        <div>
          <h3><?= $editMode ? 'Edit Category' : 'New Category' ?></h3>
          <span><?= $editMode ? 'Modifying #' . $editId : 'Fill in the details below' ?></span>
        </div>
      </div>

      <div class="form-card-body">

        <?php if ($success): ?>
        <div class="cc-alert cc-alert-success">
          <i class="bi bi-check-circle-fill"></i>
          <span><?= $success ?></span>
        </div>
        <?php endif; ?>

        <?php if ($errors): ?>
        <div class="cc-alert cc-alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <div>
            <?php foreach ($errors as $err): ?>
            <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <?php if ($editMode && $editId): ?>
            <input type="hidden" name="edit_id" value="<?= $editId ?>">
          <?php endif; ?>

          <div class="field-group ">
            <label class="field-label" for="inp_name">Category Name</label>
            <div class="field-wrap">
              <i class="bi bi-tag field-icon"></i>
              <input
                type="text"
                id="inp_name"
                name="name"
                class="field-input <?= !empty($errors) ? 'has-error' : '' ?>"
                placeholder="e.g. Marshmallow Clouds"
                value="<?= htmlspecialchars($formData['name']) ?>"
                autocomplete="off"
                <?= $editMode ? 'autofocus' : '' ?>
              >
            </div>
          </div>

          <?php if ($editMode): ?>
            <button type="submit" name="update_category" class="btn-primary-full mode-create">
              <i class="bi bi-check2"></i> Save Changes
            </button>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn-cancel">
              <i class="bi bi-x"></i> Cancel
            </a>
          <?php else: ?>
            <button type="submit" name="create_category" class="btn-primary-full mode-create">
              <i class="bi bi-plus-circle"></i> Create Category
            </button>
          <?php endif; ?>
        </form>

      </div>
    </div><!-- /form card -->

    <!-- ── RIGHT: Table Card ─────────────────────────── -->
    <div class="cc-card">
      <div class="table-card-header">
        <h3>
          <i class="bi bi-grid-3x3-gap-fill" style="color:var(--pink);"></i>
          All Categories
        </h3>
       
      </div>

      <div class="cat-table-wrap">
        <?php if (empty($categories)): ?>
          <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-tags"></i></div>
            <h4>No categories yet</h4>
            <p>Create your first category using the form on the left.</p>
          </div>
        <?php else: ?>
        <table class="cat-tbl" id="catTable">
          <thead>
            <tr>
              <th style="width:52px;">ID</th>
              <th>Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="catTbody">
            <?php foreach ($categories as $cat): ?>
            <tr 
                data-name="<?= htmlspecialchars(strtolower($cat['name'])) ?>">
              <td><span class="id-chip"><?= (int)$cat['id'] ?></span></td>
              <td class="cat-name-cell">
                <?= htmlspecialchars($cat['name']) ?>
                <?php if ($editMode && (int)$editId === (int)$cat['id']): ?>
                  <span class="editing-tag"><i class="bi bi-pencil-fill"></i> editing</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="row-actions">
                  <a href="?edit_id=<?= (int)$cat['id'] ?>" class="btn-icon btn-icon-edit" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                <form method="POST" style="display:inline;">
             <input type="hidden" name="delete_id" value="<?= (int)$cat['id'] ?>">
            <button type="submit"
             name="delete_category"
             class="btn-icon btn-icon-delete">
    <i class="bi bi-trash3"></i>
  </button>
</form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <tr class="no-results-row" id="noResults" style="display:none;">
              <td colspan="3">No categories match your search.</td>
            </tr>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div><!-- /table card -->

  </div><!-- /page-grid -->
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>