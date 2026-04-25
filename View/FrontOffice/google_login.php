<?php
/*
 * Start of the Google OAuth 2.0 "sign in with Google" flow.
 * The user clicks a button; we redirect them to Google with a CSRF state
 * token; Google sends them back to google_callback.php after consent.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config_google.php';

$cfg = google_oauth_config();

if (empty($cfg['client_id']) || $cfg['client_id'] === 'PASTE-YOUR-CLIENT-ID.apps.googleusercontent.com') {
    http_response_code(500);
    echo 'Google OAuth non configuré. Remplissez <code>config_google.local.php</code>.';
    exit;
}

// CSRF protection: random state token we re-check in the callback
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = http_build_query([
    'client_id'     => $cfg['client_id'],
    'redirect_uri'  => $cfg['redirect_uri'],
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account',
    'state'         => $state,
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
