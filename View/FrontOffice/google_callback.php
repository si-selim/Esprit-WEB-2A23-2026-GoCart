<?php
/*
 * Google OAuth 2.0 callback:
 * 1. Verify CSRF state.
 * 2. Exchange the one-time ?code for an access token.
 * 3. Fetch the Google user profile (email, name, picture, sub=Google ID).
 * 4. Find existing account (by Google ID, else by email) or create new one.
 * 5. Populate $_SESSION['user'] and redirect.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config_google.php';
require_once __DIR__ . '/../../Controller/UserController.php';

function google_fail($msg) {
    $_SESSION['google_login_error'] = $msg;
    header('Location: login.php?err=google');
    exit;
}

$cfg = google_oauth_config();

// 1. Verify state (CSRF)
$state = $_GET['state'] ?? '';
$expected = $_SESSION['google_oauth_state'] ?? '';
unset($_SESSION['google_oauth_state']);
if (!$state || !$expected || !hash_equals($expected, $state)) {
    google_fail('Jeton de sécurité Google invalide. Réessayez.');
}

if (!empty($_GET['error'])) {
    google_fail('Google a refusé la connexion : ' . htmlspecialchars($_GET['error']));
}

$code = $_GET['code'] ?? '';
if (!$code) google_fail('Code Google manquant.');

// 2. Exchange code -> access_token
$tokenRes = null;
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $code,
        'client_id'     => $cfg['client_id'],
        'client_secret' => $cfg['client_secret'],
        'redirect_uri'  => $cfg['redirect_uri'],
        'grant_type'    => 'authorization_code',
    ]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 15,
]);
$tokenRaw = curl_exec($ch);
$tokenHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($tokenHttp !== 200 || !$tokenRaw) google_fail('Échec de l\'échange de token (HTTP ' . $tokenHttp . ').');

$tokenRes = json_decode($tokenRaw, true);
$accessToken = $tokenRes['access_token'] ?? '';
if (!$accessToken) google_fail('Token d\'accès Google absent.');

// 3. Fetch user profile
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_TIMEOUT        => 15,
]);
$profileRaw = curl_exec($ch);
$profileHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($profileHttp !== 200 || !$profileRaw) google_fail('Échec de la récupération du profil Google.');

$profile = json_decode($profileRaw, true);
$googleId = $profile['sub']           ?? '';
$email    = $profile['email']         ?? '';
$verified = $profile['email_verified'] ?? false;
$name     = $profile['name']          ?? '';
$picture  = $profile['picture']       ?? null;

if (!$googleId || !$email) google_fail('Profil Google incomplet.');
if (!$verified) google_fail('Adresse email Google non vérifiée.');

// 4. Link or create user
$ctrl = new UserController();
$user = $ctrl->findByGoogleId($googleId);

if (!$user) {
    $byEmail = $ctrl->findByEmail($email);
    if ($byEmail) {
        // Existing account -> attach Google ID
        $ctrl->linkGoogleId($byEmail['id_user'], $googleId);
        $user = $ctrl->findByGoogleId($googleId);
    } else {
        // Brand new user
        $newId = $ctrl->createGoogleUser($email, $name, $googleId, $picture);
        $user  = $ctrl->findByGoogleId($googleId);
        if (!$user) google_fail('Création du compte impossible.');
    }
}

if (($user['status'] ?? 'active') === 'banned') {
    google_fail('Votre compte a été bloqué.');
}

// 5. Log in
$_SESSION['user'] = [
    'id'              => $user['id_user'],
    'id_user'         => $user['id_user'],
    'username'        => $user['nom_user'],
    'nom'             => $user['nom_complet'],
    'role'            => $user['role'],
    'email'           => $user['email'],
    'profile_picture' => $user['profile_picture'],
];

if ($user['role'] === 'admin') {
    header('Location: ../BackOffice/dashboard.php');
} else {
    header('Location: accueil.php');
}
exit;
