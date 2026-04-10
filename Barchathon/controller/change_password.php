<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';

if ($current === '' || $new === '') {
    header('Location: profile.php?error=' . urlencode('Veuillez remplir les deux champs.'));
    exit;
}

if (strlen($new) < 6) {
    header('Location: profile.php?error=' . urlencode('Le nouveau mot de passe doit contenir au moins 6 caracteres.'));
    exit;
}

$stmt = $pdo->prepare("SELECT mot_de_passe FROM `user` WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$hash = $stmt->fetchColumn();

if (!password_verify($current, $hash)) {
    header('Location: profile.php?error=' . urlencode('Mot de passe actuel incorrect.'));
    exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE `user` SET mot_de_passe = ? WHERE id_user = ?");
$stmt->execute([$newHash, $_SESSION['user_id']]);

header('Location: profile.php?success=' . urlencode('Mot de passe modifie avec succes.'));
exit;
