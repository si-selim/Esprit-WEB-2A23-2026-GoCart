<?php
require_once __DIR__ . '/../../Controller/produitcontroller.php';

$message = "";
$produitTrouve = null;
$messageClass = "";

// Récupérer la valeur du champ
if (isset($_REQUEST['searchVal'])) {
    $searchVal = trim($_REQUEST['searchVal']);
    
    if (!empty($searchVal)) {
        $controller = new ProduitController();
        $results = $controller->searchProduits($searchVal);
        $produitTrouve = !empty($results) ? $results[0] : null;
        
        if ($produitTrouve) {
            $message = "✅ Produit trouvé !";
            $messageClass = "success-msg";
        } else {
            $message = "❌ Produit introuvable.";
            $messageClass = "error-msg";
        }
    } else {
        $message = "Veuillez entrer un ID ou nom de produit à rechercher.";
        $messageClass = "error-msg";
    }
} else {
    $message = "Aucune requête de recherche reçue.";
    $messageClass = "error-msg";
}
?>
<style>
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .modal-content {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        width: 90%;
        max-width: 650px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        position: relative;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .modal-header h3 {
        margin: 0;
        color: #102a43;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.2rem;
    }
    .close-btn {
        background: #f1f5f9;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        color: #64748b;
        font-weight: bold;
    }
    .close-btn:hover {
        background: #e2e8f0;
    }
    .search-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .search-table th {
        background: #1e2d3d;
        color: white;
        padding: 12px;
        text-align: left;
        font-size: 0.9rem;
    }
    .search-table th:first-child { border-top-left-radius: 6px; }
    .search-table th:last-child { border-top-right-radius: 6px; }
    .search-table td {
        padding: 12px;
        border-bottom: 1px solid #edf2f7;
        font-size: 0.9rem;
        color: #102a43;
    }
    .status {
        color: #27ae60;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .status.out {
        color: #e74c3c;
    }
    .error-msg {
        color: #e74c3c;
        text-align: center;
        font-weight: bold;
        font-size: 1.1em;
        margin: 40px 0;
    }
</style>
<div class="modal-overlay">
    <div class="modal-content">
        <?php if ($produitTrouve): ?>
            <div class="modal-header">
                <h3>🛒 <?= htmlspecialchars($produitTrouve->getNomProduit()) ?></h3>
                <button type="button" class="close-btn" onclick="document.getElementById('searchModalContainer').innerHTML = '';">&times;</button>
            </div>
            <table class="search-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Qté</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#<?= htmlspecialchars($produitTrouve->getIDProduit()) ?></td>
                        <td><?= htmlspecialchars($produitTrouve->getType()) ?></td>
                        <td><strong><?= htmlspecialchars($produitTrouve->getPrixProduit()) ?> TND</strong></td>
                        <td><?= htmlspecialchars($produitTrouve->getQteStock()) ?></td>
                        <td>
                            <?php if ($produitTrouve->getQteStock() > 0): ?>
                                <span class="status">✅ En stock</span>
                            <?php else: ?>
                                <span class="status out">❌ Épuisé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="modal-header">
                <h3>🔍 Résultat de recherche</h3>
                <button type="button" class="close-btn" onclick="document.getElementById('searchModalContainer').innerHTML = '';">&times;</button>
            </div>
            <div class="error-msg"><?= $message ?></div>
        <?php endif; ?>
    </div>
</div>
