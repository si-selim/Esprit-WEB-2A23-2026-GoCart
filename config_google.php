<?php
/*
 * Google OAuth 2.0 configuration for BarchaThon.
 *
 * --- SETUP (what YOU need to do) ---
 * 1. Go to https://console.cloud.google.com/
 * 2. Create a new project (e.g. "BarchaThon").
 * 3. In the project, open "APIs & Services" -> "OAuth consent screen":
 *      - User type: External
 *      - App name: BarchaThon
 *      - User support email + developer email: your Gmail
 *      - Add scopes: .../auth/userinfo.email, .../auth/userinfo.profile, openid
 *      - Add your Gmail as a test user
 * 4. Open "APIs & Services" -> "Credentials" -> "Create credentials" -> "OAuth client ID":
 *      - Application type: Web application
 *      - Name: BarchaThon Web
 *      - Authorized JavaScript origins:  http://localhost
 *      - Authorized redirect URIs:       http://localhost/Barchathon/View/FrontOffice/google_callback.php
 * 5. Copy the Client ID and Client secret into config_google.local.php (see template).
 *
 * This file loads the .local file if present (so your secret never lands in git).
 */

$GOOGLE_CLIENT_ID     = '';
$GOOGLE_CLIENT_SECRET = '';
$GOOGLE_REDIRECT_URI  = 'http://localhost/Barchathon/View/FrontOffice/google_callback.php';

if (file_exists(__DIR__ . '/config_google.local.php')) {
    require __DIR__ . '/config_google.local.php';
}

function google_oauth_config() {
    global $GOOGLE_CLIENT_ID, $GOOGLE_CLIENT_SECRET, $GOOGLE_REDIRECT_URI;
    return [
        'client_id'     => $GOOGLE_CLIENT_ID,
        'client_secret' => $GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => $GOOGLE_REDIRECT_URI,
    ];
}
