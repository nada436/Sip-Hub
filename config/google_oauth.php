<?php
// ============================================================
// config/google_oauth.php
// Handles Google Sign-In via OAuth 2.0 (without Composer).
//
// HOW GOOGLE OAUTH WORKS (3 steps):
//   1. We redirect the user to Google's login page.
//   2. Google redirects back to google_callback.php with a ?code=
//   3. We exchange that code for an access token, then fetch the user's profile.
// ============================================================

require_once __DIR__ . '/config.php';

// ── Step 1: Build the Google Authorization URL ────────────────
// Call this function to get the URL you redirect the user to.
function getGoogleAuthURL(): string
{
    // A random token stored in session to prevent CSRF attacks.
    // We verify it matches when Google redirects back.
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));

    $params = http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        // Scopes = what data we ask Google permission to read
        'scope'         => 'openid email profile',
        'state'         => $_SESSION['oauth_state'],
        'access_type'   => 'online',
    ]);

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
}

// ── Step 2: Exchange authorization code for access token ──────
// Google sends ?code=XXXX to our callback URL.
// We POST that code to Google to get a real access token.
function exchangeCodeForToken(string $code): ?array
{
    $postData = http_build_query([
        'code'          => $code,
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]);

    // Use PHP's built-in stream context (no cURL or Composer needed)
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData,
        ]
    ]);

    $response = file_get_contents('https://oauth2.googleapis.com/token', false, $context);

    if (!$response) return null;

    return json_decode($response, true);  // Returns ['access_token' => ..., 'id_token' => ...]
}

// ── Step 3: Fetch the user's Google profile ───────────────────
// Using the access_token from step 2, ask Google for the user's info.
function getGoogleUserInfo(string $accessToken): ?array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $accessToken,
        ]
    ]);

    $response = file_get_contents(
        'https://www.googleapis.com/oauth2/v3/userinfo',
        false,
        $context
    );

    if (!$response) return null;

    // Returns: ['sub'=>'...', 'email'=>'...', 'name'=>'...', 'picture'=>'...']
    return json_decode($response, true);
}
