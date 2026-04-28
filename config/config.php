<?php
// ============================================================
// config/config.php
// Central configuration file.
// Include this at the top of every PHP page.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'OfficeOasis');
define('APP_TAGLINE', 'Your daily nourishment, simplified.');
define('BASE_URL', 'http://localhost/Sip-Hub');





define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/google_callback.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);
