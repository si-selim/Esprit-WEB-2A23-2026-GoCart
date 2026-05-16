<?php
// updateStandAjax.php - VERSION SANS JSON
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
if (!isOrganisateur()) { http_response_code(403); echo 'Accès refusé'; exit; }
require_once __DIR__ . '/../../Controller/StandController.php';

// On n'utilise plus php://input (JSON) mais $_POST (FormData)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idStand     = isset($_POST['ID_stand']) ? (int)$_POST['ID_stand'] : null;
    $idParcours  = isset($_POST['ID_parcours']) ? (int)$_POST['ID_parcours'] : null;
    $nomStand    = isset($_POST['nom_stand']) ? trim($_POST['nom_stand']) : null;
    $position    = isset($_POST['position']) ? trim($_POST['position']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    if ($idStand && $idParcours && $nomStand && $position && $description) {
        $controller = new StandController();
        $stand = new Stand($idStand, $idParcours, $nomStand, $position, $description);
        
        if ($controller->updateStand($stand, $idStand)) {
            echo "SUCCESS";
        } else {
            echo "ERROR: Mise à jour échouée";
        }
    } else {
        echo "ERROR: Champs manquants";
    }
} else {
    echo "ERROR: Methode non autorisée";
}
?>
