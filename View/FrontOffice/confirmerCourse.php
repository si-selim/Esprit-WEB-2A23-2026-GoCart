<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../config.php';

if (!isOrganisateur() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: participantsOrganisateur.php');
    exit;
}

$id_inscription = $_POST['id_inscription'] ?? 0;
$id_user = $_POST['id_user'] ?? 0;
$difficulte = strtolower($_POST['difficulte'] ?? '');
$action_type = $_POST['action_type'] ?? 'arrivee';

$points = 50; // par defaut
if ($difficulte === 'moyen') {
    $points = 100;
} elseif ($difficulte === 'difficile') {
    $points = 150;
} elseif ($difficulte === 'facile') {
    $points = 50;
}

$statut_final = 'terminé';
if ($action_type === 'depart') {
    $points = max(10, (int)round($points / 3)); // Un peu d'XP pour l'essai
    $statut_final = 'abandon';
}

try {
    $pdo = config::getConnexion();
    $pdo->beginTransaction();

    // Vérifier que l'inscription n'est pas déjà terminée
    $stmtCheck = $pdo->prepare("SELECT statut_course FROM inscription WHERE id_inscription = ?");
    $stmtCheck->execute([$id_inscription]);
    $ins = $stmtCheck->fetch();

    if ($ins && !in_array(($ins['statut_course'] ?? ''), ['terminé', 'abandon'])) {
        // Mettre à jour l'inscription
        $stmtUpdIns = $pdo->prepare("UPDATE inscription SET statut_course = ? WHERE id_inscription = ?");
        $stmtUpdIns->execute([$statut_final, $id_inscription]);

        // Ajouter l'XP et valider la participation pour les objectifs
        $stmtUpdUser = $pdo->prepare("UPDATE user SET xp = COALESCE(xp, 0) + ?, nbre_inscription = COALESCE(nbre_inscription, 0) + 1 WHERE id_user = ?");
        $stmtUpdUser->execute([$points, $id_user]);

        $pdo->commit();
    } else {
        $pdo->rollBack();
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

header('Location: participantsOrganisateur.php');
exit;
