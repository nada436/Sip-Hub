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

// ── 4. Credential verification ────────────────────────────────
// DEMO: plain-text comparison.
// PRODUCTION: query your DB, then:
//   password_verify($password, $row['password_hash'])
if ($email === DEMO_EMAIL && $password === DEMO_PASSWORD) {

    // ✅ Login successful
    // Rotate the session ID to prevent session-fixation attacks.
    session_regenerate_id(true);

    // Store user identity in the session (persists across pages).
    $_SESSION['user_email'] = $email;
    $_SESSION['logged_in']  = true;

    // POST → Redirect → GET: prevents form re-submission on refresh.
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

// ── 5. Credentials didn't match ───────────────────────────────
$error = 'Invalid email or password. Please try again.';
