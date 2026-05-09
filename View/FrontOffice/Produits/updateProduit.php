<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: ../Stands/listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/produitcontroller.php';

if (isset($_REQUEST['searchVal'])) {
    $searchVal = trim($_REQUEST['searchVal']);
    $controller = new ProduitController();
    // On utilise la méthode de recherche et on prend le premier résultat
    $results = $controller->searchProduits($searchVal);
    $produitTrouve = !empty($results) ? $results[0] : null;

    if ($produitTrouve) {
        $html = file_get_contents('crud-produit.php');
        
        // Injecter dynamiquement les données dans le formulaire Produit
        $html = str_replace(
            'name="idProduit" placeholder="ID_produit"', 
            'name="idProduit" placeholder="ID_produit" value="' . htmlspecialchars($produitTrouve->getIDProduit()) . '" readonly style="background:#eee; cursor:not-allowed;" title="L\'ID ne peut pas être modifié"', 
            $html
        );
        $html = str_replace(
            'name="nomProduit" placeholder="Nom_produit"', 
            'name="nomProduit" placeholder="Nom_produit" value="' . htmlspecialchars($produitTrouve->getNomProduit()) . '"', 
            $html
        );
        $html = str_replace(
            'name="type" placeholder="Type"', 
            'name="type" placeholder="Type" value="' . htmlspecialchars($produitTrouve->getType()) . '"', 
            $html
        );
        $html = str_replace(
            'name="idStand" placeholder="ID_stand"', 
            'name="idStand" placeholder="ID_stand" value="' . htmlspecialchars($produitTrouve->getIDStand()) . '"', 
            $html
        );
        $html = str_replace(
            'name="prixProduit" placeholder="Prix Produit"', 
            'name="prixProduit" placeholder="Prix Produit" value="' . htmlspecialchars($produitTrouve->getPrixProduit()) . '"', 
            $html
        );
        $html = str_replace(
            'name="quantiteStock" placeholder="Quantité en Stock"', 
            'name="quantiteStock" placeholder="Quantité en Stock" value="' . htmlspecialchars($produitTrouve->getQteStock()) . '"', 
            $html
        );

        // Gestion des boutons radio pour le stock (Compatible 0 ou Rupture)
        if ($produitTrouve->getEnOutStock() === '0' || $produitTrouve->getEnOutStock() === 'Rupture') {
            $html = str_replace('value="1" checked', 'value="1"', $html);
            $html = str_replace('value="0"', 'value="0" checked', $html);
        }

        // Changer le bouton pour passer en mode "update"
        $html = str_replace(
            'name="action" value="add" class="btn add">Ajouter</button>', 
            'name="action" value="update" class="btn add" style="background:#27ae60;">Enregistrer les modifications</button>', 
            $html
        );

        // Afficher un msg amical
        $msg = "<div style='background-color:#d1fae5; color:#065f46; font-weight:bold; padding: 15px; text-align:center; margin-bottom:20px; border-radius: 8px;'>✅ Produit trouvé : Mode Modification activé. Modifiez les champs et cliquez sur \"Enregistrer\".</div>";
        $html = str_replace('<h1>Gestion Produit</h1>', '<h1>Gestion Produit</h1>' . $msg, $html);
        
        echo $html;
        exit;
    } else {
        echo "<script>alert('❌ Produit introuvable.'); window.location.href='crud-produit.php';</script>";
        exit;
    }
} else {
    header("Location: crud-produit.php");
    exit;
}
?>
