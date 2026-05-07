<?php
// deleteStandAjax.php - Suppression sans rechargement
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
if (!isOrganisateur()) { http_response_code(403); echo 'Accès refusé'; exit; }
require_once __DIR__ . '/../../Controller/StandController.php';

if (isset($_POST['id']) && ctype_digit($_POST['id'])) {
    $id = (int)$_POST['id'];
    $db = config::getConnexion();
    
    try {
        $db->beginTransaction();

        // 1. Supprimer d'abord les produits liés à ce stand (Contrainte de clé étrangère)
        $sqlProd = "DELETE FROM produit WHERE ID_stand = :id";
        $queryProd = $db->prepare($sqlProd);
        $queryProd->bindValue(':id', $id, PDO::PARAM_INT);
        $queryProd->execute();

        // 2. Supprimer le stand
        $sqlStand = "DELETE FROM stand WHERE ID_stand = :id";
        $queryStand = $db->prepare($sqlStand);
        $queryStand->bindValue(':id', $id, PDO::PARAM_INT);
        $queryStand->execute();

        $db->commit();
        echo "SUCCESS";
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo "ERROR: " . $e->getMessage();
    }
} else {
    echo "ERROR: ID manquant ou invalide.";
}
?>
