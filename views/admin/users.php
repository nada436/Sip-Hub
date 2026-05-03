<?php
/**
 * users.php  –  Manage Users | Candy Cafeteria Admin
 *
 * Path: views/admin/users.php
 * DB table: users (id, name, email, password, role, created_at)
 */

// ── Bootstrap & DB ─────────────────────────────────────────────
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

$db = DATA_BASE::getInstance();

// ── Handle Actions ──────────────────────────────────────────────
$errors   = [];
$success  = '';
$formData = ['name' => '', 'email' => '', 'password' => '', 'role' => 'user'];
$editMode = false;
$editId   = null;

// ── 1. DELETE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $delId = (int)($_POST['delete_id'] ?? 0);
    if ($delId > 0) {
        $db->delete('users', "id = $delId");
        $success = 'User deleted successfully.';
    }
}

// ── 2. CREATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role']          ?? 'user';

    $formData = compact('name', 'email', 'role');

    if ($name === '')     $errors[] = 'Full name is required.';
    if ($email === '')    $errors[] = 'Email is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if (strlen($password) < 6 && $password !== '') $errors[] = 'Password must be at least 6 characters.';

    // Duplicate email check
    if (empty($errors)) {
        $esc_email = addslashes($email);
        $check = $db->select('users', "email = '$esc_email'");
        if ($check->fetch_assoc() !== null) {
            $errors[] = 'A user with this email already exists.';
        }
    }

    
    if (empty($errors)) {
        $hashed    = $password;
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
            $success  = "User <strong>" . htmlspecialchars($name) . "</strong> created successfully!";
            $formData = ['name' => '', 'email' => '', 'role' => 'user'];
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

// ── 3. UPDATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $editId   = (int)($_POST['edit_id'] ?? 0);
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role']          ?? 'user';

    $formData = compact('name', 'email', 'role');
    $editMode = true;

    if ($name === '')  $errors[] = 'Full name is required.';
    if ($email === '') $errors[] = 'Email is required.';

    if (empty($errors) && $editId > 0) {
        $esc_name  = addslashes($name);
        $esc_email = addslashes($email);
        $esc_role  = addslashes($role);
        
        $set = "name = '$esc_name', email = '$esc_email', role = '$esc_role'";
        
        // Only update password if provided
        if ($password !== '') {
            if (strlen($password) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } else {
                $hashed   = password_hash($password, PASSWORD_DEFAULT);
                $esc_pass = addslashes($hashed);
                $set .= ", password = '$esc_pass'";
            }
        }

        if (empty($errors)) {
            $db->update('users', $set, "id = $editId");
            $success  = "User <strong>" . htmlspecialchars($name) . "</strong> updated successfully!";
            $formData = ['name' => '', 'email' => '', 'role' => 'user'];
            $editMode = false;
            $editId   = null;
        }
    }
}

// ── 4. PRE-FILL FOR EDIT ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $row    = $db->select('users', "id = $editId");
    $user   = $row->fetch_assoc();
    if ($user) {
        $formData = [
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role']
        ];
        $editMode = true;
    }
}

// ── Fetch All Users ─────────────────────────────────────────────
$allUsersRes = $db->selectAll('users', "1 ORDER BY created_at DESC");
$users = [];
while ($row = $allUsersRes->fetch_assoc()) {
    $users[] = $row;
}

// ── Page variables ──────────────────────────────────────────────
$activePage = 'users';
$cssRoot    = BASE_URL . '/assets/css';
$jsRoot     = BASE_URL . '/assets/js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users — <?= APP_NAME ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= $cssRoot ?>/admin_layout.css">
  <link rel="stylesheet" href="<?= $cssRoot ?>/categories.css">
  <link rel="stylesheet" href="<?= $cssRoot ?>/users.css">
</head>
<body>

  <?php include __DIR__ . '/../Navbar.php'; ?>
  <?php include __DIR__ . '/../Sidebar.php'; ?>

  
  <form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="delete_id" id="deleteIdInput">
    <input type="hidden" name="delete_user" value="1">
  </form>

  <main class="main-content">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-strip">
      <a href="#">Admin</a>
      <i class="bi bi-chevron-right sep"></i>
      <a href="#">Users</a>
      <i class="bi bi-chevron-right sep"></i>
      <span class="cur"><?= $editMode ? 'Edit User' : 'Add New' ?></span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
      <div class="page-header-left">
        <h1><?= $editMode ? ' Edit User' : ' Manage Users' ?></h1>
        <p><?= $editMode ? 'Update user profile and permissions.' : 'Create and manage your community members.' ?></p>
      </div>
      <div class="count-pill">
        <i class="bi bi-people-fill me-1"></i><?= count($users) ?> <?= count($users) === 1 ? 'user' : 'users' ?>
      </div>
    </div>

    <!-- Two-column grid -->
    <div class="page-grid">

      <!-- ── LEFT: Form Card ───────────────────────────── -->
      <div class="cc-card">
        <div class="form-card-header <?= $editMode ? 'mode-edit' : '' ?>">
          <div class="form-icon <?= $editMode ? 'edit' : 'create' ?>">
            <i class="bi <?= $editMode ? 'bi-pencil-fill' : 'bi-person-plus-fill' ?>"></i>
          </div>
          <div>
            <h3><?= $editMode ? 'Edit User' : 'New User' ?></h3>
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

            <!-- Full Name -->
            <div class="field-group">
              <label class="field-label" for="inp_name">Full Name</label>
              <div class="field-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="inp_name" name="name" class="field-input" 
                       placeholder="e.g. Charlie Chocolate" value="<?= htmlspecialchars($formData['name']) ?>" required>
              </div>
            </div>

            <!-- Email -->
            <div class="field-group">
              <label class="field-label" for="inp_email">Email Address</label>
              <div class="field-wrap">
                <i class="bi bi-envelope field-icon"></i>
                <input type="email" id="inp_email" name="email" class="field-input" 
                       placeholder="charlie@candy.com" value="<?= htmlspecialchars($formData['email']) ?>" required>
              </div>
            </div>

            <!-- Password -->
            <div class="field-group">
              <label class="field-label" for="inp_pass">Password <?= $editMode ? '<small>(leave blank to keep current)</small>' : '' ?></label>
              <div class="field-wrap">
                <i class="bi bi-shield-lock field-icon"></i>
                <input type="password" id="inp_pass" name="password" class="field-input" 
                       placeholder="<?= $editMode ? 'New password' : 'Minimum 6 characters' ?>">
              </div>
            </div>

            <!-- Role -->
            <div class="field-group">
              <label class="field-label" for="inp_role">User Role</label>
              <div class="field-wrap">
                <i class="bi bi-shield-check field-icon"></i>
                <select id="inp_role" name="role" class="field-input">
                  <option value="user" <?= $formData['role'] === 'user' ? 'selected' : '' ?>>Customer</option>
                  <option value="admin" <?= $formData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
              </div>
            </div>

            <?php if ($editMode): ?>
              <button type="submit" name="update_user" class="btn-primary-full">
                <i class="bi bi-check2"></i> Save Changes
              </button>
              <a href="users.php" class="btn-cancel">
                <i class="bi bi-x"></i> Cancel
              </a>
            <?php else: ?>
              <button type="submit" name="create_user" class="btn-primary-full">
                <i class="bi bi-plus-circle"></i> Create User
              </button>
            <?php endif; ?>
          </form>
        </div>
      </div><!-- /form card -->

      <!-- ── RIGHT: Table Card ─────────────────────────── -->
      <div class="cc-card">
        <div class="table-card-header">
          <h3>
            <i class="bi bi-people-fill" style="color:var(--pink);"></i>
            All Users
          </h3>
        </div>

        <div class="cat-table-wrap">
          <?php if (empty($users)): ?>
            <div class="empty-state">
              <div class="empty-state-icon"><i class="bi bi-people"></i></div>
              <h4>No users found</h4>
              <p>Create your first user using the form on the left.</p>
            </div>
          <?php else: ?>
          <table class="cat-tbl">
            <thead>
              <tr>
                <th style="width:52px;">ID</th>
                <th>User Details</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td><span class="id-chip"><?= (int)$u['id'] ?></span></td>
                <td>
                  <div class="user-info-cell">
                    <div class="user-name"><?= htmlspecialchars($u['name']) ?></div>
                    <div class="user-email"><?= htmlspecialchars($u['email']) ?></div>
                  </div>
                </td>
                <td>
                  <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>" style="font-weight:500; font-size:0.7rem; text-transform:uppercase;">
                    <?= htmlspecialchars($u['role']) ?>
                  </span>
                </td>
                <td>
                  <div class="row-actions">
                    <a href="?edit_id=<?= (int)$u['id'] ?>" class="btn-icon btn-icon-edit" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                   <form method="POST" style="display:inline;">
  <input type="hidden" name="delete_id" value="<?= (int)$u['id'] ?>">

  <button type="submit"
          name="delete_user"
          class="btn-icon btn-icon-delete"
         
          title="Delete">
    <i class="bi bi-trash3"></i>
  </button>
</form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div><!-- /table card -->

    </div><!-- /page-grid -->
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $jsRoot ?>/admin_layout.js"></script>
  
</body>
</html>
