<?php
// ============================================================
// includes/auth.php
// ============================================================

$error   = '';
$success = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

// ── 1. Collect inputs ─────────────────────────────────────────
$email    = trim(htmlspecialchars($_POST['email']    ?? ''));
$password = trim($_POST['password'] ?? '');

// ── 2. Presence check ────────────────────────────────────────
if (empty($email) || empty($password)) {
    $error = 'Please fill in all fields.';
    return;
}

// ── 3. Email format check ─────────────────────────────────────
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
    return;
}

// ── 4. Fetch user from DB ─────────────────────────────────────
require_once __DIR__ . '/../db.php';

$db         = DATA_BASE::getInstance();
$userResult = $db->select('users', "email = '" . addslashes($email) . "'");
$user       = $userResult->fetch_assoc();

if (!$user) {
    $error = 'Invalid email or password.';
    return;
}

// ── 5. Password check (plain text — matches current DB) ───────
if ($password !== $user['password']) {
    $error = 'Invalid email or password.';
    return;
}

// ── 6. Build session ──────────────────────────────────────────
session_regenerate_id(true);

$_SESSION['logged_in'] = true;
$_SESSION['user_id']   = $user['id'];
$_SESSION['name']      = $user['name'];   // ✅ "Marina George" — used by UNavbar
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];

// ── 7. Redirect based on role ─────────────────────────────────
if ($user['role'] === 'admin') {
    header('Location: http://localhost/Sip-Hub/views/admin/products.php');
} else {
    header('Location: http://localhost/Sip-Hub/views/user_pages/index.php');
}
exit;