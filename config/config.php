<?php
// ============================================================
// config/config.php
// Central configuration file.
// Include this at the top of every PHP page.
// ============================================================

// ── Session ──────────────────────────────────────────────────
// Start session only if one isn't already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── App constants ─────────────────────────────────────────────
define('APP_NAME',    'OfficeOasis');
define('APP_TAGLINE', 'Your daily nourishment, simplified.');
define('BASE_URL',    'http://localhost/siphub');   // Change to your domain

// ── Demo credentials ──────────────────────────────────────────
// PRODUCTION: replace with a real database lookup.
// Passwords should be stored with password_hash() and checked
// with password_verify().
define('DEMO_EMAIL',    'admin@company.com');
define('DEMO_PASSWORD', 'password123');

// ── Google OAuth credentials ──────────────────────────────────
// Get these from https://console.cloud.google.com/
// Create a project → APIs & Services → Credentials → OAuth 2.0 Client ID
define('GOOGLE_CLIENT_ID',     'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI',  BASE_URL . '/google_callback.php');

// ── Error reporting (turn off in production) ──────────────────
ini_set('display_errors', 1);
error_reporting(E_ALL);
