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

$fp_email = trim(htmlspecialchars($_POST['fp_email'] ?? ''));

// ── 1. Validate email ─────────────────────────────────────────
if (empty($fp_email)) {
    $fp_error = 'Please enter your email address.';
    return;
}

if (!filter_var($fp_email, FILTER_VALIDATE_EMAIL)) {
    $fp_error = 'Please enter a valid email address.';
    return;
}

// ── 2. Generate a secure reset token ─────────────────────────
// bin2hex(random_bytes(32)) gives a 64-character cryptographically
// secure random string — safe to use in a URL.
$resetToken   = bin2hex(random_bytes(32));
$resetExpires = time() + 3600;   // Token valid for 1 hour

// ── 3. PRODUCTION: save token to DB & send email ──────────────
// Example DB query (PDO):
//   $stmt = $pdo->prepare(
//     "UPDATE users SET reset_token=?, reset_expires=? WHERE email=?"
//   );
//   $stmt->execute([$resetToken, $resetExpires, $fp_email]);
//
// Then send with PHPMailer or mail():
//   $resetLink = BASE_URL . "/reset_password.php?token=$resetToken";
//   mail($fp_email, 'Reset your password', "Click here: $resetLink");

// ── 4. Always show the same success message ───────────────────
// Never confirm whether the email exists — prevents user enumeration attacks.
$fp_success = 'If that email is registered, you will receive a reset link shortly.';

// Store token in session for demo purposes only
$_SESSION['reset_token']   = $resetToken;
$_SESSION['reset_email']   = $fp_email;
$_SESSION['reset_expires'] = $resetExpires;
