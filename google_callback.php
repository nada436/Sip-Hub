<?php
// ============================================================
// google_callback.php
// Google redirects the user HERE after they approve sign-in.
// URL will look like: /google_callback.php?code=XXX&state=YYY
//
// This file:
//   1. Validates the state token (CSRF check)
//   2. Exchanges the ?code for an access token
//   3. Fetches the user's Google profile
//   4. Logs them in (sets $_SESSION)
//   5. Redirects to the dashboard
// ============================================================

require_once 'config/config.php';
require_once 'config/google_oauth.php';

// ── Helper: redirect to login with an error message ──────────
function failWithError(string $msg): void {
    $_SESSION['oauth_error'] = $msg;
    header('Location: login.php');
    exit;
}

// ── 1. Check for an error response from Google ───────────────
// Google adds ?error=access_denied if the user clicks "Cancel"
if (!empty($_GET['error'])) {
    failWithError('Google sign-in was cancelled or failed.');
}

// ── 2. Verify state token (CSRF protection) ──────────────────
// We stored a random string in $_SESSION['oauth_state'] when
// building the auth URL. Google echoes it back as ?state=
// If they don't match, something suspicious happened → abort.
$returnedState = $_GET['state'] ?? '';
$storedState   = $_SESSION['oauth_state'] ?? '';

if (empty($returnedState) || $returnedState !== $storedState) {
    failWithError('Security check failed. Please try again.');
}

// State is used; clear it so it can't be replayed
unset($_SESSION['oauth_state']);

// ── 3. Exchange the authorization code for an access token ────
$code = $_GET['code'] ?? '';
if (empty($code)) {
    failWithError('No authorization code received from Google.');
}

$tokenData = exchangeCodeForToken($code);

if (empty($tokenData['access_token'])) {
    failWithError('Failed to get access token from Google.');
}

// ── 4. Fetch the user's Google profile ───────────────────────
$userInfo = getGoogleUserInfo($tokenData['access_token']);

if (empty($userInfo['email'])) {
    failWithError('Could not retrieve user information from Google.');
}

// ── 5. Log the user in ────────────────────────────────────────
// PRODUCTION TIP: check if this Google email exists in your DB.
// If not, create a new user record. Then store their internal user ID.
session_regenerate_id(true);

$_SESSION['user_email']   = $userInfo['email'];
$_SESSION['user_name']    = $userInfo['name']    ?? '';
$_SESSION['user_picture'] = $userInfo['picture'] ?? '';
$_SESSION['logged_in']    = true;
$_SESSION['auth_method']  = 'google';

// ── 6. Redirect to dashboard ──────────────────────────────────
header('Location: ' . BASE_URL . '/dashboard.php');
exit;
