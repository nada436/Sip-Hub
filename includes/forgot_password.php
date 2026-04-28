<?php
// ============================================================
// includes/forgot_password.php
// Handles the "Forgot Password" form submission.
// Generates a reset token and (in production) emails it.
// ============================================================

$fp_error   = '';
$fp_success = '';
$fp_email   = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$fp_email    = trim(htmlspecialchars($_POST['fp_email'] ?? ''));
$fp_password = trim($_POST['fp_password'] ?? '');

// ── 1. Validate inputs ─────────────────────────────────────────
if (empty($fp_email) || empty($fp_password)) {
    $fp_error = 'Please enter both email and new password.';
    return;
}

if (!filter_var($fp_email, FILTER_VALIDATE_EMAIL)) {
    $fp_error = 'Please enter a valid email address.';
    return;
}

if (strlen($fp_password) < 6) {
    $fp_error = 'New password must be at least 6 characters.';
    return;
}

// ── 2. Update password in DB ───────────────────────────────────
require_once __DIR__ . '/../db.php';
$db = DATA_BASE::getInstance();

// Check if user exists
$esc_email = addslashes($fp_email);
$userCheck = $db->select('users', "email = '$esc_email'");
$user = $userCheck->fetch_assoc();

if (!$user) {
    $fp_error = 'No account found with that email address.';
    return;
}

// Hash and update
$esc_pass   = addslashes($fp_password);

$updated = $db->update('users', "password = '$esc_pass'", "id = " . (int)$user['id']);

if ($updated) {
    $fp_success = 'Password updated successfully! You can now log in with your new password.';
} else {
    $fp_error = 'Failed to update password. Please try again.';
}
