<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: ../Stands/listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/produitcontroller.php';

$controller = new ProduitController();

$standId = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : 0;
$parcoursId = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;
$redirectUrl = 'produit.php?stand_id=' . $standId;
if ($parcoursId) {
    $redirectUrl .= '&parcours_id=' . $parcoursId;
}

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    if ($controller->deleteProduit((int)$_GET['id'])) {
        header("Location: {$redirectUrl}&success=deleted");
    } else {
        header("Location: {$redirectUrl}&error=delete_failed");
    }
} else {
    header("Location: {$redirectUrl}");
}
exit;
?>
