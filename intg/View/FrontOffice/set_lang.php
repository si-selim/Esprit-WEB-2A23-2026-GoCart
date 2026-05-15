<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$lang = $_GET['lang'] ?? 'fr';
if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}
$ref = $_SERVER['HTTP_REFERER'] ?? 'accueil.php';
header('Location: ' . $ref);
exit;
