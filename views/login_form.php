<?php
// ============================================================
// views/login_form.php
// Pure HTML view – renders the login form.
// All variables ($error, $success, $email, $googleAuthURL)
// are set by login.php before this file is included.
// NO business logic here.
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> – Login</title>
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

    <!-- ── Login card ────────────────────────────────────── -->
    <div class="card">
        <h2 class="card-title">Welcome Back</h2>
        <p class="card-subtitle">Please enter your credentials to access your account</p>

        <!-- Error banner (shown only when $error is non-empty) -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Success banner -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Login form
             action=""     → posts back to login.php (same file)
             method="post" → data sent in request body
             novalidate    → we handle validation in PHP   -->
        <form action="" method="post" novalidate>

            <!-- Email field -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <!-- value echoes the typed email so it stays filled after an error -->
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="name@company.com"
                        value="<?= htmlspecialchars($email) ?>"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <!-- Password field -->
            <div class="form-group">
                <div class="form-label-row">
                    <label for="password">Password</label>
                    <!-- Real link to the forgot-password page -->
                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <!-- id="pwdInput" → toggled by login.js -->
                    <input
                        type="password"
                        id="pwdInput"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="eye-btn" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember this device</label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary">
                Login
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>

        <!-- ── OAuth section ─────────────────────────── -->
        <div class="divider">
            <hr><span>OR CONTINUE WITH</span><hr>
        </div>

        <div class="oauth-row">
            <!--
                Real Google Sign-In link.
                $googleAuthURL is built by getGoogleAuthURL() in config/google_oauth.php
                and passed in from login.php.
            -->
            <a href="<?= htmlspecialchars($googleAuthURL) ?>" class="btn-oauth">
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#4285F4" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.1 29.3 35 24 35c-6.1 0-11-4.9-11-11s4.9-11 11-11c2.8 0 5.3 1 7.2 2.7l5.7-5.7C33.5 7.1 29 5 24 5 12.9 5 4 13.9 4 25s8.9 20 20 20 20-8.9 20-20c0-1.5-.2-3-.4-4.5z"/>
                    <path fill="#34A853" d="M6.3 14.7l6.6 4.8C14.6 16 19 13 24 13c2.8 0 5.3 1 7.2 2.7l5.7-5.7C33.5 7.1 29 5 24 5c-7.7 0-14.3 4.5-17.7 9.7z"/>
                    <path fill="#FBBC05" d="M24 45c5.2 0 9.9-1.8 13.4-4.7l-6.2-5.1C29.3 36.6 26.8 37.5 24 37.5c-5.2 0-9.7-3-11.3-7.3l-6.6 4.8C9.7 40.6 16.3 45 24 45z"/>
                    <path fill="#EA4335" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.2 5.7l6.2 5.1C37.1 38 44 32 44 24c0-1.5-.2-3-.4-4.5z"/>
                </svg>
                Google
            </a>

            <!-- Microsoft (placeholder – same pattern as Google) -->
            <a href="#" class="btn-oauth" onclick="alert('Microsoft OAuth coming soon'); return false;">
                <svg width="18" height="18" viewBox="0 0 21 21">
                    <rect x="1"  y="1"  width="9" height="9" fill="#f25022"/>
                    <rect x="11" y="1"  width="9" height="9" fill="#7fba00"/>
                    <rect x="1"  y="11" width="9" height="9" fill="#00a4ef"/>
                    <rect x="11" y="11" width="9" height="9" fill="#ffb900"/>
                </svg>
                Microsoft
            </a>
        </div>
    </div>

    <!-- ── Footer ─────────────────────────────────────────── -->
    <div class="footer">
        <p>Don't have an account? <a href="mailto:admin@company.com">Contact your Administrator</a></p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <span>•</span>
            <a href="#">Terms of Service</a>
            <span>•</span>
            <a href="#">Help Center</a>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
