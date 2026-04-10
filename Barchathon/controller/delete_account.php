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

$id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT profile_picture FROM `user` WHERE id_user = ?");
$stmt->execute([$id]);
$pic = $stmt->fetchColumn();

if ($pic && file_exists(__DIR__ . '/../uploads/' . $pic)) {
    unlink(__DIR__ . '/../uploads/' . $pic);
}

$stmt = $pdo->prepare("DELETE FROM `user` WHERE id_user = ?");
$stmt->execute([$id]);

session_destroy();
header('Location: login.php?success=' . urlencode('Votre compte a ete supprime.'));
exit;
