<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_user'])) {
    header('Location: ../BackOffice/dashboard.php?tab=utilisateurs');
    exit;
}

$ctrl = new UserController();
$ctrl->supprimerUser((int)$_POST['id_user']);

header('Location: ../BackOffice/dashboard.php?tab=utilisateurs&success=' . urlencode('Utilisateur supprime.'));
exit;
