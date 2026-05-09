<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isAdmin()) {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$ctrl = new UserController();

$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$paysFilter = trim($_GET['pays'] ?? '');

$users = $ctrl->rechercherUsers($search, $roleFilter, $paysFilter);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['ID', 'Nom complet', 'Nom utilisateur', 'Role', 'Email', 'Age', 'Poids (kg)', 'Taille (cm)', 'Pays', 'Ville', 'Telephone', 'Occupation'], ';');

foreach ($users as $u) {
    fputcsv($output, [
        $u['id_user'],
        $u['nom_complet'],
        $u['nom_user'],
        $u['role'],
        $u['email'],
        $u['age'] ?? '',
        $u['poids'] ?? '',
        $u['taille'] ?? '',
        $u['pays'] ?? '',
        $u['ville'] ?? '',
        $u['tel'] ?? '',
        $u['occupation'] ?? ''
    ], ';');
}

fclose($output);
exit;
