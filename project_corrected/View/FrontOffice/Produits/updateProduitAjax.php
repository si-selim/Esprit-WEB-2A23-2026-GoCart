<?php
// updateProduitAjax.php - VERSION SANS JSON
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
if (!isOrganisateur()) { http_response_code(403); echo 'Accès refusé'; exit; }
require_once __DIR__ . '/../../Controller/ProduitController.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idProduit   = isset($_POST['ID_produit']) ? (int)$_POST['ID_produit'] : null;
    $idStand     = isset($_POST['ID_stand']) ? (int)$_POST['ID_stand'] : null;
    $nomProduit  = isset($_POST['nom_produit']) ? trim($_POST['nom_produit']) : null;
    $type        = isset($_POST['type']) ? trim($_POST['type']) : null;
    $prixProduit = isset($_POST['prix_produit']) ? (float)$_POST['prix_produit'] : null;
    $qteStock    = isset($_POST['qte_stock']) ? (int)$_POST['qte_stock'] : null;
    $enOutStock  = isset($_POST['en_out_stock']) ? $_POST['en_out_stock'] : null;

    if ($idProduit && $idStand && $nomProduit) {
        $controller = new ProduitController();
        $produit = new Produit($idProduit, $idStand, $nomProduit, $type, $prixProduit, $qteStock, $enOutStock);
        
        if ($controller->updateProduit($produit)) {
            echo "SUCCESS";
        } else {
            echo "ERROR: Mise à jour échouée";
        }
    } else {
        echo "ERROR: Champs obligatoires manquants";
    }
} else {
    echo "ERROR: Methode non autorisée";
}
?>
