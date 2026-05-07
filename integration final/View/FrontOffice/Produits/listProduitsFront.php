<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/produitcontroller.php';

$produitController = new ProduitController();
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ID_produit';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$listProduits = $produitController->listProduits($sort, $order);

$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
$currentPage = 'stands';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Produits | BarchaThon</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:36px 0 0; }
        .table-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(15, 118, 110, 0.1);
            margin-top: 30px;
            overflow-x: auto;
        }
        .table-title {
            color: #0f766e;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 10px;
        }
        .top-bar h1 { font-size: 1.8rem; font-weight: 900; color: #102a43; }
        .btn-add {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; background: linear-gradient(135deg,#0f766e,#14b8a6);
            color: white; border-radius: 14px; font-weight: 700; text-decoration: none;
            font-size: 0.95rem; transition: transform .15s, box-shadow .15s;
            box-shadow: 0 5px 16px rgba(15,118,110,.22);
        }
        .btn-add:hover { transform: translateY(-2px); }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        th {
            text-align: left;
            padding: 15px 20px;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        th a { color: inherit; text-decoration: none; display: flex; align-items: center; gap: 5px; }
        tr.product-row {
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        tr.product-row:hover {
            transform: translateY(-2px);
            background: #f1f5f9;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        td { padding: 20px; color: #1e293b; font-weight: 500; }
        td:first-child { border-radius: 12px 0 0 12px; }
        td:last-child { border-radius: 0 12px 12px 0; }
        .badge-id { background: #10b981; color: white; padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 0.8rem; }
        .parcours-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .stock-badge { padding: 6px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; }
        .in-stock { background: #dcfce7; color: #166534; }
        .out-stock { background: #fee2e2; color: #991b1b; }
        .price-tag { color: #0f766e; font-weight: 800; font-size: 1.1rem; }
        .empty-msg { text-align: center; padding: 50px; color: #64748b; font-style: italic; }
        html[data-theme="dark"] .table-container { background:#1e293b; box-shadow:0 15px 35px rgba(0,0,0,.3); }
        html[data-theme="dark"] .table-title { color:#14b8a6; }
        html[data-theme="dark"] .top-bar h1 { color:#e2e8f0; }
        html[data-theme="dark"] tr.product-row { background:#162032; }
        html[data-theme="dark"] tr.product-row:hover { background:rgba(20,184,166,0.08); }
        html[data-theme="dark"] td { color:#e2e8f0; }
        html[data-theme="dark"] th { color:#94a3b8; }
        html[data-theme="dark"] .price-tag { color:#14b8a6; }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <div class="top-bar">
        <h1>📦 Espace Produits</h1>
        <?php if (isOrganisateur()): ?>
            <a href="crud-produit.php" class="btn-add">➕ Ajouter un produit</a>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <h2 class="table-title">📦 Tous les produits enregistrés</h2>
        
        <table>
            <thead>
                <tr>
                    <th><a href="?sort=ID_produit&order=<?= ($sort=='ID_produit' && $order=='ASC') ? 'DESC' : 'ASC' ?>">ID ⇅</a></th>
                    <th><a href="?sort=nom_produit&order=<?= ($sort=='nom_produit' && $order=='ASC') ? 'DESC' : 'ASC' ?>">Nom ⇅</a></th>
                    <th><a href="?sort=type&order=<?= ($sort=='type' && $order=='ASC') ? 'DESC' : 'ASC' ?>">Type ⇅</a></th>
                    <th><a href="?sort=ID_stand&order=<?= ($sort=='ID_stand' && $order=='ASC') ? 'DESC' : 'ASC' ?>">Stand ⇅</a></th>
                    <th><a href="?sort=prix_produit&order=<?= ($sort=='prix_produit' && $order=='ASC') ? 'DESC' : 'ASC' ?>">Prix ⇅</a></th>
                    <th><a href="?sort=qte_stock&order=<?= ($sort=='qte_stock' && $order=='ASC') ? 'DESC' : 'ASC' ?>">Quantité ⇅</a></th>
                    <th>Stock</th>

                </tr>
            </thead>
            <tbody>
                <?php if (empty($listProduits)): ?>
                    <tr>
                        <td colspan="7" class="empty-msg">Aucun produit n'a encore été enregistré.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listProduits as $prod): ?>
                        <tr class="product-row">
                            <td><span class="badge-id">#<?= htmlspecialchars($prod['ID_produit']); ?></span></td>
                            <td><?= htmlspecialchars($prod['nom_produit']); ?></td>
                            <td><?= htmlspecialchars($prod['type']); ?></td>
                            <td><span class="parcours-badge">Stand #<?= htmlspecialchars($prod['ID_stand']); ?></span></td>
                            <td><span class="price-tag"><?= number_format($prod['prix_produit'], 2); ?> TND</span></td>
                            <td><?= htmlspecialchars($prod['qte_stock']); ?></td>
                            <td>
                                <?php 
                                $stockStr = $prod['en_out_stock'];
                                $isInStock = (stripos($stockStr, 'dispo') !== false || $stockStr === '1' || $stockStr === 1);
                                ?>
                                <span class="stock-badge <?= $isInStock ? 'in-stock' : 'out-stock' ?>">
                                    <?= $isInStock ? '✅ En Stock' : '❌ Rupture' ?>
                                </span>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
