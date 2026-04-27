<?php
/**
 * create_category.php  –  Add New Category | Candy Cafeteria Admin
 *
 * DB table required: categories (id, name, slug, description, color, visibility)
 */

// ── Bootstrap & DB ─────────────────────────────────────────────
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../db.php';

$db = DATA_BASE::getInstance();

// ── Handle POST ─────────────────────────────────────────────────
$errors   = [];
$success  = '';
$formData = [
    'name'        => '',
    'slug'        => '',
    'description' => '',
    'color'       => 'hot-pink',
    'visibility'  => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {

    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color       = $_POST['color'] ?? 'hot-pink';
    $visibility  = isset($_POST['visibility']) ? 1 : 0;

    $formData = compact('name', 'slug', 'description', 'color', 'visibility');

  
    // ── Validation ──────────────────────────────────────────────
    if ($name === '') {
        $errors[] = 'Category name is required.';
    }
    if ($slug === '') {
        $errors[] = 'Display slug is required.';
    }

    // ── Duplicate Slug check (Optional but recommended) ──────────
    if (empty($errors)) {
        $esc_slug = addslashes($slug);
        $check = $db->select('categories', "slug = '$esc_slug'");
        if ($check && $check->fetch_assoc() !== null) {
            $errors[] = 'This slug is already in use. Please choose another.';
        }
    }

    // ── Insert using your DATA_BASE class ───────────────────────
    if (empty($errors)) {
        $esc_name = addslashes($name);
        $esc_slug = addslashes($slug);
        $esc_desc = addslashes($description);
        $esc_col  = addslashes($color);
        $vis_val  = (int)$visibility;

        // Actually insert into the database
        $newId = $db->insert(
            'categories',
            'name',
            "'$esc_name'"
        );

        if ($newId) {
            $success  = "Category <strong>" . htmlspecialchars($name) . "</strong> was created successfully!";
            // Reset form on success
            $formData = ['name' => '', 'slug' => '', 'description' => '', 'color' => 'hot-pink', 'visibility' => 1];
        } else {
            $errors[] = 'Something went wrong saving to the database. Please try again.';
        }
    }
}

// ── Page variables for components ────────────────────────────────
$activePage        = 'categories';
$searchPlaceholder = 'Search categories...';

// ── Asset paths (adjust if needed) ───────────────────────────────
$cssRoot = BASE_URL . '/assets/css';
$jsRoot  = BASE_URL . '/assets/js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Category – Candy Cafeteria</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= $cssRoot ?>/admin.css">
  <link rel="stylesheet" href="<?= $cssRoot ?>/categories.css">

  <style>
    /* ───────────────────────────────────────────
       PAGE SPECIFIC STYLES & RESPONSIVENESS
    ─────────────────────────────────────────── */
    .breadcrumb-nav {
      font-size: 0.85rem;
      color: var(--text-muted, #888);
      margin-bottom: 1rem;
      font-weight: 500;
    }
    .breadcrumb-nav span.active-crumb {
      color: var(--pink, #e91e8c);
    }
    
    textarea.candy-input {
      resize: none;
      border-radius: 1.25rem;
    }

    .selector-box {
      background: var(--pink-light, #fdfafb);
      border: 1.5px solid var(--border, #fce4ec);
      border-radius: 50px;
      padding: 0.65rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      height: 48px;
    }
    .selector-box select {
      border: none;
      background: transparent;
      outline: none;
      flex-grow: 1;
      font-weight: 500;
      color: var(--text, #333);
      appearance: none;
      cursor: pointer;
    }
    
    .candy-switch .form-check-input {
      width: 2.5em;
      height: 1.25em;
      background-color: #d1d5db;
      border-color: transparent;
      cursor: pointer;
    }
    .candy-switch .form-check-input:checked {
      background-color: var(--pink, #e91e8c);
      border-color: var(--pink, #e91e8c);
    }
    .candy-switch .form-check-input:focus {
      box-shadow: none;
    }

    .btn-submit-full {
      width: 100%;
      background: var(--pink, #e91e8c);
      color: white;
      border: none;
      padding: 0.85rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1rem;
      transition: background 0.3s;
      margin-top: 1rem;
    }
    .btn-submit-full:hover {
      background: #d8157e;
    }

    .preview-card {
      border: 2px solid var(--pink, #e91e8c);
    }

    .tip-card-blue {
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: var(--radius-lg, 20px);
      padding: 1.5rem;
      display: flex;
      gap: 1rem;
    }
    .tip-card-blue i {
      color: #0284c7;
      font-size: 1.25rem;
      margin-top: 2px;
    }
    .tip-card-blue p {
      margin: 0;
      font-size: 0.85rem;
      color: #334155;
      line-height: 1.6;
    }

    /* --- MOBILE RESPONSIVENESS --- */
    @media (max-width: 768px) {
      .page-title {
        font-size: 1.75rem !important;
      }
      .candy-card {
        padding: 1.25rem; /* Reduce padding on mobile */
      }
      .slug-prefix {
        font-size: 0.65rem; /* Shrink slug prefix text slightly */
      }
      .slug-input {
        padding-left: 5.2rem !important;
      }
      .tip-card-blue {
        padding: 1.25rem;
      }
    }
  </style>
</head>
<body>

  <?php include '../Navbar.php'; ?>
  <?php include '../Sidebar.php'; ?>

<main class="main-content">

  <div class="breadcrumb-nav">
    Admin &nbsp;>&nbsp; Categories &nbsp;>&nbsp; <span class="active-crumb">Add New Category</span>
  </div>

  <div class="mb-4">
    <h1 class="page-title" style="font-weight: 800; font-size: 2.2rem;">Create New Category</h1>
    <p class="page-subtitle text-muted">Organize your delicious sweets and treats with vibrant labels.</p>
  </div>

  <div class="row g-4 flex-column-reverse flex-lg-row">

    <div class="col-lg-8">
      <div class="candy-card shadow-sm">

        <?php if ($success): ?>
        <div class="candy-alert candy-alert-success auto-dismiss mb-4">
          <i class="bi bi-check-circle-fill"></i>
          <span><?= $success ?></span>
        </div>
        <?php endif; ?>

        <?php if ($errors): ?>
        <div class="candy-alert candy-alert-error mb-4">
          <i class="bi bi-exclamation-circle-fill"></i>
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <form method="POST" id="categoryForm" novalidate>

          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <label class="form-label" for="inp_name">Category Name</label>
              <input
                type="text" id="inp_name" name="name"
                class="candy-input w-100 <?= in_array('Category name is required.', $errors) ? 'is-invalid' : '' ?>"
                placeholder="e.g. Marshmallow Clouds"
                value="<?= htmlspecialchars($formData['name']) ?>"
              >
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="inp_slug">Display Slug</label>
              <div class="slug-wrap">
                <span class="slug-prefix">candy.com/</span>
                <input
                  type="text" id="inp_slug" name="slug"
                  class="candy-input slug-input w-100 <?= in_array('Display slug is required.', $errors) || in_array('This slug is already in use. Please choose another.', $errors) ? 'is-invalid' : '' ?>"
                  placeholder="marshmallow-"
                  value="<?= htmlspecialchars($formData['slug']) ?>"
                >
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="inp_desc">Description</label>
            <textarea
              id="inp_desc" name="description"
              class="candy-input w-100" rows="4"
              placeholder="Tell us about the treats in this category..."
            ><?= htmlspecialchars($formData['description']) ?></textarea>
          </div>

          <div class="row g-3 mb-4">
            
            <div class="col-md-4 col-sm-6">
              <label class="form-label">Accent Color</label>
              <div class="selector-box position-relative">
                <div class="color-dot" style="background: var(--pink);"></div>
                <select name="color" class="w-100" id="colorSelect">
                  <option value="hot-pink" <?= $formData['color'] === 'hot-pink' ? 'selected' : '' ?>>Hot Pink</option>
                  <option value="purple" <?= $formData['color'] === 'purple' ? 'selected' : '' ?>>Purple</option>
                  <option value="cyan" <?= $formData['color'] === 'cyan' ? 'selected' : '' ?>>Cyan</option>
                </select>
                <i class="bi bi-chevron-down text-muted" style="font-size: 0.8rem;"></i>
              </div>
            </div>

            <div class="col-md-4 col-sm-6">
              <label class="form-label">Icon Representation</label>
              <div class="selector-box" style="cursor: pointer;">
                <div style="background: #e9d5ff; color: #a855f7; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                  <i class="bi bi-icecream"></i>
                </div>
                <span style="font-weight: 500; font-size: 0.9rem; flex-grow: 1;">Choose Icon</span>
              </div>
            </div>

            <div class="col-md-4 col-12">
              <label class="form-label">Visibility</label>
              <div class="selector-box justify-content-between">
                <span style="font-weight: 500; font-size: 0.9rem;">Public</span>
                <div class="form-check form-switch candy-switch m-0 pb-1">
                  <input class="form-check-input" type="checkbox" role="switch" name="visibility" id="visibilitySwitch" <?= $formData['visibility'] ? 'checked' : '' ?>>
                </div>
              </div>
            </div>

          </div>

          <button type="submit" name="create_category" class="btn-submit-full mt-2">
            Create Category ✨
          </button>

        </form>
      </div></div><div class="col-lg-4 d-flex flex-column gap-3">

      <div class="preview-card bg-white p-4 text-center rounded-4 shadow-sm">
        <div class="preview-icon-circle shadow-sm" id="previewIconWrap" style="width: 80px; height: 80px; border-radius: 50%; background: var(--pink-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; color: var(--pink);">
          <i class="bi bi-cake2-fill"></i>
        </div>
        
        <h4 class="preview-cat-name mb-2" style="font-weight: 700; font-size: 1rem; color: var(--text);">Live Preview</h4>
        <p class="preview-cat-slug" style="font-size: 0.75rem; color: var(--text-muted);">See how your new category will look to customers.</p>

        <div class="preview-thumb" style="border-radius: 12px; overflow: hidden; background: #f5f5f5; height: 120px; margin-top: 1rem; position: relative;">
          <img src="https://images.unsplash.com/photo-1558332153-27e163b4f653?q=80&w=400&auto=format&fit=crop" alt="Placeholder" style="width: 100%; height: 100%; object-fit: cover; display: block;">
          <div class="preview-pill" id="previewPill" style="position: absolute; bottom: 10px; left: 10px; background: var(--pink); color: #fff; font-size: 0.62rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.06em;">PLACEHOLDER</div>
        </div>
        
        <div class="preview-bar-1" style="height: 10px; background: var(--pink-light); border-radius: 5px; margin-top: 0.75rem; width: 70%; margin-left: auto; margin-right: auto;"></div>
        <div class="preview-bar-2" style="height: 8px; background: var(--border); border-radius: 5px; margin-top: 0.4rem; width: 50%; margin-left: auto; margin-right: auto;"></div>
      </div>

      <div class="tip-card-blue shadow-sm">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          <p class="fw-bold mb-1" style="color: #0284c7;">Quick Tip</p>
          <p>Categories with high-quality icons and clear descriptions see <strong style="color: #0284c7;">24% higher</strong> customer engagement!</p>
        </div>
      </div>

    </div></div></main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $jsRoot ?>/admin.js"></script>
<script>
(function () {
  'use strict';

  /* ── Element refs ── */
  const nameInput = document.getElementById('inp_name');
  const previewPill = document.getElementById('previewPill');
  
  /* ── Live preview logic ── */
  function updatePreview() {
    const n = nameInput.value.trim();
    previewPill.textContent = n || 'PLACEHOLDER';
  }

  nameInput.addEventListener('input', updatePreview);

  // Initialize preview on load
  updatePreview();

})();
</script>
</body>
</html>