<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/standcontroller.php';

$controller = new StandController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $idStand     = isset($_POST['ID_stand']) && !empty($_POST['ID_stand']) ? (int) $_POST['ID_stand'] : null;
    $idParcours  = isset($_POST['ID_parcours']) ? (int) $_POST['ID_parcours'] : null;
    $nomStand    = isset($_POST['nom_stand']) ? trim($_POST['nom_stand']) : null;
    $position    = isset($_POST['position']) ? trim($_POST['position']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    if ($idParcours && $nomStand && $position && $description) {

        // Crée l'objet Stand
        $stand = new Stand(
            $idStand,
            $idParcours,
            $nomStand,
            $position,
            $description
        );

        // Vérifier si l'ID existe déjà pour faire UPDATE au lieu de INSERT
        $existingStand = null;
        if ($idStand) {
            $existingStand = $controller->getStandByValue((string)$idStand);
        }

        if ($existingStand) {
            // C'est un UPDATE
            if ($controller->updateStand($stand, $idStand)) {
                $_SESSION['success_message'] = '✅ Stand modifié avec succès !';
                header('Location: ../detailParcours.php?id=' . $idParcours);
                exit;
            } else {
                $_SESSION['error_message'] = '❌ Erreur lors de la modification du stand.';
                header('Location: ../detailParcours.php?id=' . $idParcours);
                exit;
            }
        } else {
            // C'est un INSERT
            if ($controller->addStand($stand)) {
                $_SESSION['success_message'] = '✅ Stand ajouté avec succès !';
                header('Location: ../detailParcours.php?id=' . $idParcours);
                exit;
            } else {
                $_SESSION['error_message'] = '❌ Erreur lors de l\'ajout du stand.';
                header('Location: ../detailParcours.php?id=' . $idParcours);
                exit;
            }
        }

    } else {
        // Rediriger vers le formulaire précédent avec l'ID du parcours
        if ($idParcours > 0) {
            header('Location: crud-stand.php?parcours_id=' . $idParcours);
        } else {
            header('Location: listStandsFront.php');
        }
        exit;
    }
}
?>
