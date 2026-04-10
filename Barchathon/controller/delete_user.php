<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_user'])) {
    header('Location: backoffice_User.php');
    exit;
}

$id = (int)$_POST['id_user'];

$stmt = $pdo->prepare("SELECT profile_picture FROM `user` WHERE id_user = ?");
$stmt->execute([$id]);
$pic = $stmt->fetchColumn();

if ($pic && file_exists(__DIR__ . '/../uploads/' . $pic)) {
    unlink(__DIR__ . '/../uploads/' . $pic);
}

$stmt = $pdo->prepare("DELETE FROM `user` WHERE id_user = ?");
$stmt->execute([$id]);

header('Location: backoffice_User.php?success=' . urlencode('Utilisateur supprime.'));
exit;
