<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/standcontroller.php';

$controller = new StandController();
$parcoursId = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $controller->deleteStand((int) $_GET['id']);
    $_SESSION['success_message'] = '✅ Stand supprimé avec succès !';
    
    // Si l'ID du parcours est fourni, rediriger vers detailParcours
    if ($parcoursId > 0) {
        header('Location: ../detailParcours.php?id=' . $parcoursId);
    } elseif (isset($_GET['redirect']) && $_GET['redirect'] === 'front') {
        header("Location: listStandsFront.php?msg=deleted");
    } else {
        header("Location: ../../BackOffice/dashboard.php?section=stands&msg=deleted");
    }
    exit;
} else {
    if ($parcoursId > 0) {
        header('Location: ../detailParcours.php?id=' . $parcoursId);
    } elseif (isset($_GET['redirect']) && $_GET['redirect'] === 'front') {
        header("Location: listStandsFront.php");
    } else {
        header("Location: ../../BackOffice/dashboard.php?section=stands");
    }
    exit;
}
