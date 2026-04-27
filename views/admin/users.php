<?php
/**
 * users.php  –  Add New User | Candy Cafeteria Admin
 *
 * Path: views/admin/users.php
 * DB table: users (id, name, email, password, role, created_at)
 */

// ── Bootstrap & DB ─────────────────────────────────────────────
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../db.php';

$db = DATA_BASE::getInstance();

// ── Handle POST ─────────────────────────────────────────────────
$errors   = [];
$success  = '';
$formData = [
    'name'    => '',
    'email'   => '',
    'role'    => 'user',
    'dietary' => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role']          ?? 'user';
    $dietary  = $_POST['dietary']       ?? [];   // stored in session / ignored by DB (no column)

    $formData = compact('name', 'email', 'role', 'dietary');

    // ── Validation ──────────────────────────────────────────────
    if ($name === '')
        $errors[] = 'Full name is required.';
    elseif (strlen($name) > 100)
        $errors[] = 'Name must be 100 characters or fewer.';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'A valid email address is required.';
    elseif (strlen($email) > 150)
        $errors[] = 'Email must be 150 characters or fewer.';

    if ($password === '')
        $errors[] = 'Password is required.';
    elseif (strlen($password) < 6)
        $errors[] = 'Password must be at least 6 characters.';

    if (!in_array($role, ['user', 'admin'], true))
        $errors[] = 'Invalid role selected.';

    // ── Duplicate email check ───────────────────────────────────
    if (empty($errors)) {
        $esc_email = addslashes($email);
        $check = $db->select('users', "email = '$esc_email'");
        if ($check->fetch_assoc() !== null)
            $errors[] = 'A user with this email already exists.';
    }

    // ── Insert ──────────────────────────────────────────────────
    if (empty($errors)) {
        $hashed    = password_hash($password, PASSWORD_DEFAULT);
        $esc_name  = addslashes($name);
        $esc_email = addslashes($email);
        $esc_role  = addslashes($role);
        $esc_pass  = addslashes($hashed);

        $newId = $db->insert(
            'users',
            'name, email, password, role',
            "'$esc_name', '$esc_email', '$esc_pass', '$esc_role'"
        );

        if ($newId) {
            $success  = "User <strong>" . htmlspecialchars($name) . "</strong> was created successfully!";
            $formData = ['name' => '', 'email' => '', 'role' => 'user', 'dietary' => []];
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

// ── Stats ────────────────────────────────────────────────────────
// Total users
$totalUsers = 0;
$allUsersRes = $db->selectAll('users');
while ($allUsersRes->fetch_assoc() !== null) $totalUsers++;

// Today's signups
$todaySignups = 0;
$todayRes = $db->selectAll('users', "DATE(created_at) = CURDATE()");
while ($todayRes->fetch_assoc() !== null) $todaySignups++;

// ── Page variables for components ────────────────────────────────
$activePage        = 'users';
$searchPlaceholder = 'Search users...';
$adminName         = $_SESSION['admin_name']  ?? 'Alex Candy';
$adminRole         = $_SESSION['admin_role']  ?? 'Super Admin';

// ── Asset paths (relative from views/admin/) ─────────────────────
$cssRoot = BASE_URL . '/assets/css';
$jsRoot  = BASE_URL . '/assets/js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New User – <?= APP_NAME ?></title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

   <!-- Shared admin styles -->
   <link rel="stylesheet" href="<?= $cssRoot ?>/admin_layout.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="<?= $cssRoot ?>/users.css">
</head>
<body>

<?php include __DIR__ . '/../Navbar.php'; ?>
<?php include __DIR__ . '/../Sidebar.php'; ?>

<!-- ═══════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════ -->
<main class="main-content">

  <!-- Page header -->
  <div class="mb-4">
    <h1 class="page-title">Add New User</h1>
    <p class="page-subtitle">Create a new member for the Candy Cafeteria community.</p>
  </div>

  <div class="row g-4">

    <!-- ═══ LEFT COLUMN: Form ═══ -->
    <div class="col-lg-8">
      <div class="candy-card">

        <?php if ($success): ?>
        <div class="candy-alert candy-alert-success auto-dismiss">
          <i class="bi bi-check-circle-fill"></i>
          <span><?= $success ?></span>
        </div>
        <?php endif; ?>

        <?php if ($errors): ?>
        <div class="candy-alert candy-alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <ul>
            <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <form method="POST" id="userForm" novalidate>

          <!-- Row 1: Name + Email -->
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label" for="inp_name">Full Name</label>
              <input
                type="text" id="inp_name" name="name"
                class="candy-input <?= in_array('Full name is required.', $errors) ? 'is-invalid' : '' ?>"
                placeholder="e.g. Charlie Chocolate"
                value="<?= htmlspecialchars($formData['name']) ?>"
                maxlength="100"
                autocomplete="name"
              >
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="inp_email">Email Address</label>
              <input
                type="email" id="inp_email" name="email"
                class="candy-input <?= (in_array('A valid email address is required.', $errors) || in_array('A user with this email already exists.', $errors)) ? 'is-invalid' : '' ?>"
                placeholder="charlie@candy.com"
                value="<?= htmlspecialchars($formData['email']) ?>"
                maxlength="150"
                autocomplete="email"
              >
            </div>
          </div>

          <!-- Row 2: Password -->
          <div class="mb-3">
            <label class="form-label" for="inp_password">Password</label>
            <div class="pw-wrap">
              <input
                type="password" id="inp_password" name="password"
                class="candy-input <?= (in_array('Password is required.', $errors) || in_array('Password must be at least 6 characters.', $errors)) ? 'is-invalid' : '' ?>"
                placeholder="Minimum 6 characters"
                autocomplete="new-password"
              >
              <button type="button" class="pw-toggle-btn" id="pwToggle" aria-label="Toggle password visibility">
                <i class="bi bi-eye" id="pwIcon"></i>
              </button>
            </div>
          </div>

          <!-- Row 3: Role + (display-only balance placeholder) -->
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label" for="inp_role">User Role</label>
              <select id="inp_role" name="role" class="candy-input candy-select">
                <option value="user"  <?= $formData['role'] === 'user'  ? 'selected' : '' ?>>Customer</option>
                <option value="admin" <?= $formData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Initial Balance ($)</label>
              <input
                type="number" class="candy-input"
                placeholder="0.00" step="0.01" min="0"
                title="Display only – no balance column in DB yet"
              >
              <small style="font-size:.68rem;color:var(--text-muted);">Visual reference – extend DB schema to persist.</small>
            </div>
          </div>

          <!-- Row 4: Dietary preferences (UI only — no DB column) -->
          <div class="mb-4">
            <label class="form-label">Dietary Preferences <span style="font-weight:400;color:var(--text-muted);">(optional tags)</span></label>
            <div class="dietary-group">
              <?php foreach (['Vegan', 'Sugar-Free', 'Gluten-Free', 'Nut-Free'] as $diet): ?>
              <label class="dietary-pill <?= in_array($diet, $formData['dietary']) ? 'selected' : '' ?>">
                <input type="checkbox" name="dietary[]" value="<?= $diet ?>"
                       <?= in_array($diet, $formData['dietary']) ? 'checked' : '' ?>>
                <?= $diet ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Form actions -->
          <div class="form-actions">
            <button type="button" class="btn-discard" onclick="resetUserForm()">Discard Draft</button>
            <button type="submit" name="create_user" class="btn-candy">
              <i class="bi bi-person-plus-fill"></i> Create User
            </button>
          </div>

        </form>
      </div><!-- /candy-card -->
    </div><!-- /col-lg-8 -->

    <!-- ═══ RIGHT COLUMN ═══ -->
    <div class="col-lg-4 d-flex flex-column gap-3">

      <!-- Preview Profile -->
      <div class="preview-card">
        <div class="preview-avatar-wrap" id="previewWrap">
          <i class="bi bi-camera avatar-placeholder" id="avatarPlaceholderIcon"></i>
          <img src="" alt="Preview avatar" id="previewAvatarImg">
          <div class="camera-badge" aria-hidden="true"><i class="bi bi-camera-fill"></i></div>
        </div>
        <p class="preview-name" id="previewName">Preview Profile</p>
        <p class="preview-email" id="previewEmail">User details will appear here</p>

        <div class="completeness-bar-wrap">
          <div class="completeness-label">
            <span>Profile Completeness</span>
            <span id="pctLabel">0%</span>
          </div>
          <div class="completeness-bar">
            <div class="completeness-fill" id="completenessBar" style="width:0%"></div>
          </div>
        </div>
      </div>

      <!-- Candy Tip -->
      <div class="tip-card-purple">
        <h6>✨ Candy Tip!</h6>
        <p>Users with complete profiles are <strong>3×</strong> more likely to try our weekly "Mystery Dessert" special!</p>
        <button class="tip-learn-btn">Learn more <i class="bi bi-arrow-right"></i></button>
      </div>

      <!-- Stats row -->
      <div class="row g-2">
        <div class="col-6">
          <div class="stat-card stat-purple">
            <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
            <div>
              <div class="stat-card-val"><?= number_format($totalUsers) ?></div>
              <div class="stat-card-label">Active Members</div>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-card stat-cyan">
            <div class="stat-card-icon"><i class="bi bi-stars"></i></div>
            <div>
              <div class="stat-card-val"><?= $todaySignups ?> Today</div>
              <div class="stat-card-label">New Signups</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Members teaser -->
      <div class="members-teaser">
        <div class="member-avatars">
          <img src="https://ui-avatars.com/api/?name=Bob+Shopper&background=e91e8c&color=fff&size=60" alt="Bob">
          <img src="https://ui-avatars.com/api/?name=Charlie+Buyer&background=9b59b6&color=fff&size=60" alt="Charlie">
          <img src="https://ui-avatars.com/api/?name=Alice+Admin&background=00bcd4&color=fff&size=60" alt="Alice">
          <div class="member-plus">+<?= max(0, $totalUsers - 3) ?></div>
        </div>
        <p>Your newest members are waiting for a welcome gift!</p>
      </div>

    </div><!-- /col-lg-4 -->

  </div><!-- /row -->
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $jsRoot ?>/admin_layout.js"></script>
<script>
(function () {
  'use strict';

  /* ── Element refs ── */
  const nameInput = document.getElementById('inp_name');
  const emailInput = document.getElementById('inp_email');
  const pwInput   = document.getElementById('inp_password');
  const roleSelect = document.getElementById('inp_role');
  const previewName  = document.getElementById('previewName');
  const previewEmail = document.getElementById('previewEmail');
  const previewImg   = document.getElementById('previewAvatarImg');
  const placeholder  = document.getElementById('avatarPlaceholderIcon');
  const bar  = document.getElementById('completenessBar');
  const pct  = document.getElementById('pctLabel');

  /* ── Live preview ── */
  function updatePreview() {
    const n = nameInput.value.trim();
    const e = emailInput.value.trim();

    previewName.textContent  = n || 'Preview Profile';
    previewEmail.textContent = e || 'User details will appear here';

    if (n) {
      const url = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(n) + '&background=e91e8c&color=fff&size=120';
      previewImg.src = url;
      previewImg.style.display = 'block';
      placeholder.style.display = 'none';
    } else {
      previewImg.style.display = 'none';
      placeholder.style.display = '';
    }

    updateCompleteness();
  }

  /* ── Completeness bar ── */
  function updateCompleteness() {
    let done = 0;
    const total = 4;
    if (nameInput.value.trim())  done++;
    if (emailInput.value.trim()) done++;
    if (pwInput.value.trim())    done++;
    if (roleSelect.value)        done++;

    const p = Math.round(done / total * 100);
    bar.style.width = p + '%';
    pct.textContent  = p + '%';
  }

  nameInput.addEventListener('input',  updatePreview);
  emailInput.addEventListener('input', updatePreview);
  pwInput.addEventListener('input',    updateCompleteness);
  roleSelect.addEventListener('change', updateCompleteness);

  /* ── Dietary pill toggle ── */
  document.querySelectorAll('.dietary-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      var cb = pill.querySelector('input[type="checkbox"]');
      cb.checked = !cb.checked;
      pill.classList.toggle('selected', cb.checked);
    });
  });

  /* ── Password toggle ── */
  document.getElementById('pwToggle').addEventListener('click', function () {
    var ico = document.getElementById('pwIcon');
    if (pwInput.type === 'password') {
      pwInput.type = 'text';
      ico.className = 'bi bi-eye-slash';
    } else {
      pwInput.type = 'password';
      ico.className = 'bi bi-eye';
    }
  });

  /* ── Discard form ── */
  window.resetUserForm = function () {
    document.getElementById('userForm').reset();
    document.querySelectorAll('.dietary-pill').forEach(function (p) {
      p.classList.remove('selected');
    });
    updatePreview();
  };

  /* Init on load (repopulate after validation error) */
  updatePreview();

})();
</script>
</body>
</html>