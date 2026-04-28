<?php
// ============================================================
// includes/auth.php
// Handles the login form submission.
// Included by login.php BEFORE any HTML is output.
// ============================================================

// ── Output variables (read by the view) ──────────────────────
$error   = '';   // Non-empty = show error banner
$success = '';   // Non-empty = show success banner
$email   = '';   // Pre-fill email field after a failed attempt

// ── Only run when the form is submitted via POST ──────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;   // Nothing to do on a normal page load (GET)
}

// ── 1. Collect & sanitise inputs ─────────────────────────────
// trim()           → strip leading/trailing whitespace
// htmlspecialchars → turn < > & " into safe HTML (blocks XSS)
// ?? ''            → null-coalescing: use '' if key doesn't exist in $_POST
$email    = trim(htmlspecialchars($_POST['email']    ?? ''));
$password = trim($_POST['password'] ?? '');   // Never echo the raw password back

// ── 2. Basic presence check ───────────────────────────────────
if (empty($email) || empty($password)) {
    $error = 'Please fill in all fields.';
    return;
}

// ── 3. Email format check ─────────────────────────────────────
// filter_var with FILTER_VALIDATE_EMAIL uses RFC 5322 rules.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
    return;
}


// ── 4. Credential verification (DB CHECK) ───────────────────────
require_once __DIR__ . '/../db.php';

$db = DATA_BASE::getInstance();
$userResult = $db->select('users', "email = '" . addslashes($email) . "'");
$user = $userResult->fetch_assoc();

// Check if user exists
if ($user) {

    if ($password=== $user['password']) {
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['logged_in']  = true;

        if($user['role']=='admin'){
          header('Location: ' . BASE_URL . '/views/admin/products.php');
        }
       else{
           header('Location: ' . BASE_URL . '/views/user_pages/UserPage.php');
       }
        exit;

    } else {
        $error = "Invalid email or password.";
    }

} else {
    $error = "Invalid email or password.";
}