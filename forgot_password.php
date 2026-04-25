<?php
// ============================================================
// forgot_password.php
// Standalone page for the "Forgot Password" flow.
// Same pattern as login.php: config → logic → view.
// ============================================================

require_once 'config/config.php';
require_once 'includes/forgot_password.php';  // Sets $fp_error, $fp_success, $fp_email
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> – Forgot Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <!-- ── Brand header ──────────────────────────────────── -->
    <div class="brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                <line x1="6" y1="1" x2="6" y2="4"/>
                <line x1="10" y1="1" x2="10" y2="4"/>
                <line x1="14" y1="1" x2="14" y2="4"/>
            </svg>
        </div>
        <h1><?= APP_NAME ?></h1>
        <p><?= APP_TAGLINE ?></p>
    </div>

    <!-- ── Forgot password card ──────────────────────────── -->
    <div class="card">

        <!-- Back arrow link to login.php -->
        <a href="login.php" class="back-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Login
        </a>

        <h2 class="card-title">Reset Password</h2>
        <p class="card-subtitle">Enter your work email and we'll send you a reset link.</p>

        <!-- Error banner -->
        <?php if (!empty($fp_error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($fp_error) ?></div>
        <?php endif; ?>

        <!-- Success banner – shown after form is submitted -->
        <?php if (!empty($fp_success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($fp_success) ?></div>
        <?php endif; ?>

        <!--
            Only show the form if we haven't sent the email yet.
            Once $fp_success is set, we hide the form to avoid double-submits.
        -->
        <?php if (empty($fp_success)): ?>
        <form action="" method="post" novalidate>

            <!-- Email field -->
            <div class="form-group">
                <label for="fp_email">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <!--
                        name="fp_email" → read as $_POST['fp_email']
                        in includes/forgot_password.php
                    -->
                    <input
                        type="email"
                        id="fp_email"
                        name="fp_email"
                        placeholder="name@company.com"
                        value="<?= htmlspecialchars($fp_email) ?>"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn-primary">
                Send Reset Link
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>
        <?php endif; ?>

    </div>

    <!-- ── Footer ─────────────────────────────────────────── -->
    <div class="footer">
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <span>•</span>
            <a href="#">Terms of Service</a>
            <span>•</span>
            <a href="#">Help Center</a>
        </div>
    </div>

</body>
</html>
