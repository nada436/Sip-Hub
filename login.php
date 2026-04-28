<?php
// ============================================================
// login.php  ← Entry point
// This file is intentionally thin — it just:
//   1. Loads config (session, constants)
//   2. Loads Google OAuth helper
//   3. Runs auth logic (processes form POST)
//   4. Builds the Google Sign-In URL
//   5. Renders the HTML view
// ============================================================

require_once 'config/config.php';        // Session, constants, credentials
require_once 'config/google_oauth.php';  // Google OAuth helpers
require_once 'includes/auth.php';        // Login form handler (sets $error, $success, $email)

// Redirect if already logged in (guard placed after session_start in config.php)
if (!empty($_SESSION['logged_in'])  ) {
    if($_SESSION['role'] = "admin"){
       header('Location: ' . BASE_URL . '/views/admin/products.php');
    }
    else{
        header('Location: ' . BASE_URL . '/views/user_pages/UserPage.php');
    }
   
    exit;
}  
// Build the Google Sign-In URL (passed into the view as $googleAuthURL)
$googleAuthURL = getGoogleAuthURL();

// Render the HTML view
// All variables set above ($error, $success, $email, $googleAuthURL)
// are automatically available inside the included file.
require_once 'views/login_form.php';
