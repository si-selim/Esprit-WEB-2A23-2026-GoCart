<?php
session_start();
require_once __DIR__ . '/../../../Controller/StandController.php';
require_once __DIR__ . '/../../../Controller/ProduitController.php';
require_once __DIR__ . '/../partials/session.php'; // Add session handling if needed

// Instantiate controllers
$standController = new StandController();
$produitController = new ProduitController();

// Handle Stand Sorting
$standSort = isset($_GET['standSort']) ? $_GET['standSort'] : (isset($_SESSION['standSort']) ? $_SESSION['standSort'] : 'ID_stand');
$standOrder = isset($_GET['standOrder']) ? $_GET['standOrder'] : (isset($_SESSION['standOrder']) ? $_SESSION['standOrder'] : 'ASC');
$_SESSION['standSort'] = $standSort;
$_SESSION['standOrder'] = $standOrder;

// Handle Produit Sorting
$produitSort = isset($_GET['produitSort']) ? $_GET['produitSort'] : (isset($_SESSION['produitSort']) ? $_SESSION['produitSort'] : 'ID_produit');
$produitOrder = isset($_GET['produitOrder']) ? $_GET['produitOrder'] : (isset($_SESSION['produitOrder']) ? $_SESSION['produitOrder'] : 'ASC');
$_SESSION['produitSort'] = $produitSort;
$_SESSION['produitOrder'] = $produitOrder;

$listStands = $standController->listStands($standSort, $standOrder);
$listProduits = $produitController->listProduits($produitSort, $produitOrder);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Trail de Zaghouan</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="dashboard">
        <aside class="admin-sidebar">
            <div class="admin-profile">
                <div class="sidebar-logo">
                    <img src="../logo.png" alt="Logo" class="sidebar-logo-img">
                </div>
                <div class="admin-info">
                    <div class="admin-name">Admin </div>
                    <div class="admin-role">Administrateur - admin</div>
                </div>
            </div>
            <nav class="nav-menu">
                <a class="nav-item active" href="../../FrontOffice/Stands/stand.php" data-menu="catalogue">📋 Catalogue</a>
                <a class="nav-item" href="#" data-menu="ajouter">➕ Ajouter un marathon</a>
                <a class="nav-item" href="#" data-menu="statistiques">📊 Statistiques</a>
                <a class="nav-item" href="#" data-menu="utilisateurs">👥 Utilisateurs</a>
                <a class="nav-item" href="#" data-menu="marathons">🏁 Marathons</a>
                <a class="nav-item" href="#" data-menu="parcours">🗺️ Parcours</a>
                <a class="nav-item" href="#" data-menu="stands">🏪 Stands</a>
                <a class="nav-item" href="#" data-menu="produits">📦 Produits</a>
                <a class="nav-item" href="#" data-menu="reclamations">💬 Réclamations</a>
                <a class="nav-item logout" href="#" data-menu="logout">🚪 Se déconnecter</a>
            </nav>
            <div class="sync-footer">
                🔄 4/4HZ / ADAPTIVE SYNC
            </div>
        </aside>

        <main class="main-content">
            <section class="page-section" id="catalogue">
                <div class="card">
                    <div class="card-header">
                        <h2>Catalogue</h2>
                    </div>
                    
                </div>
            </section>

            <section class="page-section hidden" id="ajouter">
                <div class="card">
                    <div class="card-header">
                        <h2>Ajouter un nouveau marathon</h2>
                    </div>
                    <div class="card-content">
                        <p>Cette section sera utilisée pour ajouter un marathon, avec son nom, sa date, son parcours et sa description.</p>
                    </div>
                </div>
            </section>

            <section class="page-section hidden" id="statistiques">
                <div class="card">
                    <div class="card-header">
                        <h2>📊 Rapports et Analyses</h2>
                    </div>
                    <div class="card-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px;">
                        <div class="stat-card-trigger" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 30px; border-radius: 12px; text-align: center; transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'" onclick="showStatsSection('standsStats')">
                            <div style="font-size: 40px; margin-bottom: 15px;">🏪</div>
                            <h3 style="color: #006056; margin-bottom: 10px;">Activité des Stands</h3>
                            <p style="color: #64748b; font-size: 14px;">Visualisez quel stand est le plus actif et combien de produits chaque stand possède.</p>
                            <span style="display: inline-block; margin-top: 15px; color: #0984e3; font-weight: bold;">Voir le graphique →</span>
                        </div>
                        
                        <div class="stat-card-trigger" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 30px; border-radius: 12px; text-align: center; transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'" onclick="showStatsSection('produitsStats')">
                            <div style="font-size: 40px; margin-bottom: 15px;">📦</div>
                            <h3 style="color: #006056; margin-bottom: 10px;">État des Stocks</h3>
                            <p style="color: #64748b; font-size: 14px;">Analysez la répartition de vos produits entre "En Stock" et "En Rupture".</p>
                            <span style="display: inline-block; margin-top: 15px; color: #0984e3; font-weight: bold;">Voir le graphique →</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- STANDS STATS SECTION -->
            <section class="page-section hidden" id="standsStats">
                <style>
                    .infographic-container-internal { width: 100%; text-align: center; background: #9cd1d1; padding: 40px; border-radius: 20px; margin-bottom: 20px; }
                    .infographic-container-internal h1 { font-size: 2.5rem; color: #3e7070; letter-spacing: 5px; font-weight: 800; margin-bottom: 30px; }
                    .spotlight-card { background: linear-gradient(135deg, #ffd700 0%, #f39c12 100%); padding: 20px; border-radius: 25px; display: inline-block; margin-bottom: 30px; border: 4px solid white; box-shadow: 0 10px 30px rgba(243, 156, 18, 0.3); }
                    .stats-layout-internal { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
                    .distribution-box-internal { background: white; border-radius: 30px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); display: flex; flex-direction: column; align-items: center; }
                    .columns-box-internal { background: rgba(255, 255, 255, 0.2); border-radius: 30px; padding: 20px; display: flex; justify-content: space-around; align-items: flex-end; min-height: 400px; gap: 10px; border: 1px solid rgba(255,255,255,0.3); }
                    .chart-wrapper-internal { width: 100%; height: 280px; position: relative; }
                    .col-internal { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; }
                    .sleeve-internal { background: rgba(255, 255, 255, 0.4); width: 40px; height: 250px; border-radius: 40px; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; }
                    .bar-internal { width: 100%; height: 0%; border-radius: 40px; transition: height 1s ease-out; }
                    .icon-cap-internal { margin-top: 10px; background: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
                    .name-internal { font-size: 0.7rem; color: #3e7070; font-weight: 700; margin-top: 5px; }
                </style>
                <div class="infographic-container-internal">
                    <h1>INFOGRAPHIC STANDS</h1>
                    <div id="leader-spotlight-int" style="display:none;">
                        <div class="spotlight-card">
                            <div style="font-size: 2rem;">🏆</div>
                            <h2 id="top-stand-name-int" style="color:white; margin:5px 0;">Chargement...</h2>
                            <div style="color:white; font-size:0.9rem; font-weight:600;"><span id="top-stand-count-int">0</span> Produits</div>
                        </div>
                    </div>
                    <div class="stats-layout-internal">
                        <div class="distribution-box-internal">
                            <h3 style="color:#3e7070; font-size:0.9rem; font-weight:800; margin-bottom:15px;">RÉPARTITION</h3>
                            <div class="chart-wrapper-internal">
                                <canvas id="distributionChartInt"></canvas>
                            </div>
                        </div>
                        <div class="columns-box-internal" id="columnsContainerInt"></div>
                    </div>
                    <button onclick="showSection('statistiques')" class="btn-action edit" style="margin-top:20px;">⬅ Retour</button>
                </div>
            </section>

            <!-- PRODUITS STATS SECTION -->
            <section class="page-section hidden" id="produitsStats">
                <style>
                    .infographic-card-prod { background: #9cd1d1; padding: 40px; border-radius: 20px; text-align: center; }
                    .chart-container-prod { display: flex; justify-content: space-around; align-items: flex-end; height: 350px; gap: 30px; margin: 30px 0; padding: 20px; border-top: 1px solid rgba(0,0,0,0.1); }
                    .column-prod { width: 120px; display: flex; flex-direction: column; align-items: center; height: 100%; }
                    .value-label-prod { font-size: 1.5rem; font-weight: 800; color: #3e7070; margin-bottom: 10px; }
                    .sleeve-prod { background: rgba(255,255,255,0.4); width: 100%; height: 100%; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; }
                    .bar-prod { width: 100%; height: 0%; transition: height 1s ease-out; }
                </style>
                <div class="infographic-card-prod">
                    <h1 style="font-size: 2.5rem; color: #3e7070; letter-spacing: 5px; font-weight: 800; margin-bottom: 30px;">INFOGRAPHIC STOCKS</h1>
                    <div class="chart-container-prod">
                        <div class="column-prod">
                            <div class="value-label-prod" id="val-dispo-int">0</div>
                            <div class="sleeve-prod"><div class="bar-prod" id="bar-dispo-int" style="background:#27ae60;"></div></div>
                            <div style="font-size:2rem; margin-top:10px;">📦</div>
                            <div style="font-weight:800; color:#3e7070;">EN STOCK</div>
                        </div>
                        <div class="column-prod">
                            <div class="value-label-prod" id="val-rupture-int">0</div>
                            <div class="sleeve-prod"><div class="bar-prod" id="bar-rupture-int" style="background:#e74c3c;"></div></div>
                            <div style="font-size:2rem; margin-top:10px;">⚠️</div>
                            <div style="font-weight:800; color:#3e7070;">RUPTURE</div>
                        </div>
                    </div>
                    <button onclick="showSection('statistiques')" class="btn-action edit">⬅ Retour</button>
                </div>
            </section>

            <section class="page-section hidden" id="utilisateurs">
                <div class="card">
                    <div class="card-header">
                        <h2>Utilisateurs</h2>
                    </div>
                    <div class="card-content">
                        <p>Section de gestion des utilisateurs : suivi des comptes et des rôles.</p>
                    </div>
                </div>
            </section>

            <section class="page-section hidden" id="marathons">
                <div class="card">
                    <div class="card-header">
                        <h2>Marathons</h2>
                    </div>
                    <div class="card-content">
                        <p>Affiche les marathons programmés, leurs distances et leurs détails.</p>
                    </div>
                </div>
            </section>

            <section class="page-section hidden" id="parcours">
                <div class="card">
                    <div class="card-header">
                        <h2>Parcours</h2>
                    </div>
                    <div class="card-content">
                        <p>Affiche les parcours associés aux marathons, avec leurs points de départ et d’arrivée.</p>
                    </div>
                </div>
            </section>

            <section class="page-section hidden" id="stands-section">
                <div class="event-header">
                    <div class="event-title">
                        <h1>Présentation des stands</h1>
                    </div>
                    <div class="event-meta">
                        <span>🏁 Stands disponibles</span>
                        <span>📏 Informations générales</span>
                        <span>⚡ Mise à jour en temps réel</span>
                        <span>📍 Tous les lieux</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <h2 style="margin:0;">Liste des Stands</h2>
                            <div class="search-container" style="display: flex; align-items: center; background: #f1f5f9; border-radius: 50px; padding: 4px 15px; border: 1px solid #e2e8f0; width: 350px;">
                                <i class="fas fa-search" style="color: #64748b;"></i>
                                <input type="text" id="standSearchInput" placeholder="Chercher par nom ou ID..." style="border: none; background: transparent; padding: 8px; outline: none; width: 100%; font-size: 0.9rem;">
                                <button onclick="triggerSearch()" style="background: #10b981; border: none; color: white; padding: 6px 15px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 0.8rem; margin-right: -11px;">Go</button>
                            </div>
                            <button onclick="searchByLocation()" class="btn-location" style="background: white; border: 1px solid #10b981; color: #10b981; padding: 8px 15px; border-radius: 50px; cursor: pointer; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                                📍 Autour de moi
                            </button>
                        </div>
                        <button onclick="exportTableToPDF('stands-table', 'liste_stands.pdf', 'Rapport des Stands')" class="btn-export">
                            <span style="font-size: 16px;">📄</span> Exporter PDF
                        </button>
                    </div>
                    <div id="searchModalContainer"></div>
                    <div class="table-wrapper">
                        <table id="stands-table">
                            <thead>
                                <tr>
                                    <th><a href="?standSort=ID_parcours&standOrder=<?= ($standSort == 'ID_parcours' && $standOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=stands" style="color:inherit; text-decoration:none;">Marathon ⇅</a></th>
                                    <th><a href="?standSort=ID_stand&standOrder=<?= ($standSort == 'ID_stand' && $standOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=stands" style="color:inherit; text-decoration:none;">ID stand ⇅</a></th>
                                    <th><a href="?standSort=nom_stand&standOrder=<?= ($standSort == 'nom_stand' && $standOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=stands" style="color:inherit; text-decoration:none;">Nom ⇅</a></th>
                                    <th><a href="?standSort=position&standOrder=<?= ($standSort == 'position' && $standOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=stands" style="color:inherit; text-decoration:none;">Position ⇅</a></th>
                                    <th><a href="?standSort=description&standOrder=<?= ($standSort == 'description' && $standOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=stands" style="color:inherit; text-decoration:none;">Description ⇅</a></th>
                                    <th>Produits disponibles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($listStands)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; font-weight: bold; color: #666;">
                                            Aucun stand n'est actuellement enregistré dans la base de données.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($listStands as $stand): ?>
                                    <tr id="row-<?= htmlspecialchars($stand['ID_stand']); ?>" data-id="<?= htmlspecialchars($stand['ID_stand']); ?>">
                                        <td class="editable" data-field="ID_parcours"><?= htmlspecialchars($stand['ID_parcours']); ?></td>
                                        <td><span class="badge-id">#<?= htmlspecialchars($stand['ID_stand']); ?></span></td>
                                        <td class="editable" data-field="nom_stand"><?= htmlspecialchars($stand['nom_stand']); ?></td>
                                        <td class="editable" data-field="position"><?= htmlspecialchars($stand['position']); ?></td>
                                        <td class="editable" data-field="description"><?= htmlspecialchars($stand['description']); ?></td>
                                        <td><span class="produit-count"><?= $produitController->countProduitsByStand((int)$stand['ID_stand']); ?></span></td>
                                        <td class="actions-cell">
                                            <button onclick="toggleEdit(this, <?= $stand['ID_stand']; ?>)" class="btn-action edit">Modifier</button>
                                            <button onclick="deleteStand(<?= $stand['ID_stand']; ?>)" class="btn-action delete">Supprimer</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            
            <section class="page-section hidden" id="produits-section">
                <div class="event-header">
                    <div class="event-title">
                        <h1>Gestion des produits</h1>
                    </div>
                    <div class="event-meta">
                        <span>📦 Produits en stock</span>
                        <span>💰 Gestion des prix</span>
                        <span>⚡ Mise à jour en temps réel</span>
                        <span>🏪 Tous les stands</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>Catalogue des Produits</h2>
                        <button onclick="exportTableToPDF('products-table', 'liste_produits.pdf', 'Catalogue des Produits')" class="btn-export">
                            <span style="font-size: 16px;">📄</span> Exporter PDF
                        </button>
                    </div>
                    <div class="table-wrapper">
                        <table id="products-table">
                            <thead>
                                <tr>
                                    <th><a href="?produitSort=ID_produit&produitOrder=<?= ($produitSort == 'ID_produit' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">ID ⇅</a></th>
                                    <th><a href="?produitSort=nom_produit&produitOrder=<?= ($produitSort == 'nom_produit' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">Nom ⇅</a></th>
                                    <th><a href="?produitSort=type&produitOrder=<?= ($produitSort == 'type' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">Type ⇅</a></th>
                                    <th><a href="?produitSort=ID_stand&produitOrder=<?= ($produitSort == 'ID_stand' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">Stand ⇅</a></th>
                                    <th><a href="?produitSort=prix_produit&produitOrder=<?= ($produitSort == 'prix_produit' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">Prix ⇅</a></th>
                                    <th><a href="?produitSort=qte_stock&produitOrder=<?= ($produitSort == 'qte_stock' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">Quantité ⇅</a></th>
                                    <th><a href="?produitSort=en_out_stock&produitOrder=<?= ($produitSort == 'en_out_stock' && $produitOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&section=produits" style="color:inherit; text-decoration:none;">Stock ⇅</a></th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($listProduits)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 20px; font-weight: bold; color: #666;">
                                            Aucun produit enregistré dans la base de données.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($listProduits as $prod): ?>
                                    <tr id="row-prod-<?= $prod->getIDProduit(); ?>" data-id="<?= $prod->getIDProduit(); ?>">
                                        <td><span class="badge-id">#<?= $prod->getIDProduit(); ?></span></td>
                                        <td class="editable-prod" data-field="nom_produit"><?= htmlspecialchars($prod->getNomProduit()); ?></td>
                                        <td class="editable-prod" data-field="type"><?= htmlspecialchars($prod->getType()); ?></td>
                                        <td class="editable-prod" data-field="ID_stand"><?= htmlspecialchars($prod->getIDStand()); ?></td>
                                        <td class="editable-prod" data-field="prix_produit"><?= htmlspecialchars($prod->getPrixProduit()); ?></td>
                                        <td class="editable-prod" data-field="qte_stock"><?= htmlspecialchars($prod->getQteStock()); ?></td>
                                        <td class="editable-prod" data-field="en_out_stock"><?= (stripos($prod->getEnOutStock(), 'dispo') !== false || $prod->getEnOutStock() === '1') ? '1' : '0'; ?></td>
                                        <td class="actions-cell">
                                            <button onclick="toggleEditProd(this, <?= $prod->getIDProduit(); ?>)" class="btn-action edit">Modifier</button>
                                            <a href="../../FrontOffice/Produits/deleteProduit.php?id=<?= $prod->getIDProduit(); ?>" class="btn-action delete" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="page-section hidden" id="reclamations-section">
                <div class="card">
                    <div class="card-header reclamations-header">
                        <h2>Réclamations</h2>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Marathon</th>
                                    <th>Stand</th>
                                    <th>Participant</th>
                                    <th>Status</th>
                                    <th>Messages</th>
                                    <th>Dernier message</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="empty-message">
                                        <p>Aucune réclamation ou discussion pour le moment.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <!-- jsPDF and AutoTable libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <script>
        // PDF Export function
        function exportTableToPDF(tableId, filename, title) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add title
            doc.setFontSize(18);
            doc.text(title, 14, 22);
            doc.setFontSize(11);
            doc.setTextColor(100);
            doc.text("Généré le: " + new Date().toLocaleString(), 14, 30);

            // Extract table data manually to avoid HTML garbage in headers
            const table = document.getElementById(tableId);
            const headers = [];
            const rows = [];

            // Get headers (only text, excluding the last one 'Actions')
            const ths = table.querySelectorAll('thead th');
            for (let i = 0; i < ths.length - 1; i++) {
                // Remove the arrows (⇅) and take clean text
                headers.push(ths[i].innerText.replace(' ⇅', '').trim());
            }

            // Get rows (excluding the last cell 'Actions')
            const trs = table.querySelectorAll('tbody tr');
            trs.forEach(tr => {
                const row = [];
                const tds = tr.querySelectorAll('td');
                if (tds.length > 1) { // Skip empty/colspan rows
                    for (let i = 0; i < tds.length - 1; i++) {
                        row.push(tds[i].innerText.trim());
                    }
                    rows.push(row);
                }
            });

            // Export table
            doc.autoTable({
                head: [headers],
                body: rows,
                startY: 35,
                theme: 'striped',
                headStyles: { fillColor: [0, 96, 86], textColor: [255, 255, 255] },
                margin: { top: 35 }
            });

            doc.save(filename);
        }

        const navItems = document.querySelectorAll('.nav-item');
        const mainContent = document.querySelector('.main-content');
        const sections = {
            catalogue: document.getElementById('catalogue'),
            ajouter: document.getElementById('ajouter'),
            statistiques: document.getElementById('statistiques'),
            utilisateurs: document.getElementById('utilisateurs'),
            marathons: document.getElementById('marathons'),
            parcours: document.getElementById('parcours'),
            stands: document.getElementById('stands-section'),
            produits: document.getElementById('produits-section'),
            reclamations: document.getElementById('reclamations-section'),
            standsStats: document.getElementById('standsStats'),
            produitsStats: document.getElementById('produitsStats')
        };

        function hideAllSections() {
            Object.values(sections).forEach(section => {
                if (section) {
                    section.classList.add('hidden');
                }
            });
        }

        navItems.forEach(item => {
            item.addEventListener('click', event => {
                const href = item.getAttribute('href');
                if (href && href !== '#') {
                    return;
                }
                event.preventDefault();
                const target = item.getAttribute('data-menu');
                showSection(target);
            });
        });

        // ==========================
        // AUTO OPEN SECTION FROM URL
        // ==========================
        const urlParams = new URLSearchParams(window.location.search);
        const sectionParam = urlParams.get('section');
        
        if (sectionParam && sections[sectionParam]) {
            hideAllSections();
            sections[sectionParam].classList.remove('hidden');
            // Update active state in sidebar
            navItems.forEach(nav => {
                if (nav.getAttribute('data-menu') === sectionParam) {
                    nav.classList.add('active');
                } else {
                    nav.classList.remove('active');
                }
            });
        }

        // ==========================
        // INLINE EDIT LOGIC (STANDS)
        // ==========================
        let isEditing = false; // Prevents editing multiple rows at once
        
        function toggleEdit(btn, id) {
            if (isEditing) {
                alert("Veuillez enregistrer la ligne en cours d'édition avant d'en modifier une autre.");
                return;
            }

            const row = document.getElementById('row-' + id);
            const editables = row.querySelectorAll('.editable');
            
            editables.forEach(td => {
                const text = td.innerText;
                const fieldName = td.getAttribute('data-field');
                // Create an input for each editable field
                let inputType = (fieldName === 'ID_parcours') ? 'number' : 'text';
                td.innerHTML = `<input type="${inputType}" class="inline-input" value="${text}" style="width:100%; padding:4px;" />`;
            });
            
            isEditing = true;
            
            // Change "Modifier" to "Enregistrer"
            btn.innerHTML = "Enregistrer";
            btn.style.background = "#27ae60"; // Green color
            btn.style.color = "#ffffff";
            btn.style.border = "none";
            btn.setAttribute('onclick', `saveStand(this, ${id})`);
        }

        function saveStand(btn, id) {
            const row = document.getElementById('row-' + id);
            const editables = row.querySelectorAll('.editable');
            
            // Use FormData (No JSON)
            const formData = new FormData();
            formData.append('ID_stand', id);
            editables.forEach(td => {
                const input = td.querySelector('input');
                const fieldName = td.getAttribute('data-field');
                formData.append(fieldName, input.value);
            });
            
            fetch('../../FrontOffice/Stands/updateStandAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if(result === 'SUCCESS') {
                    editables.forEach(td => {
                        const input = td.querySelector('input');
                        td.innerHTML = input.value;
                    });
                    isEditing = false;
                    btn.innerHTML = "Modifier";
                    btn.style.background = "#f0f2f5";
                    btn.style.color = "#3a4a5c";
                    btn.style.border = "1px solid #d5dae0";
                    btn.setAttribute('onclick', `toggleEdit(this, ${id})`);
                } else {
                    alert('Erreur: ' + result);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Une erreur est survenue.");
            });
        }

        // ==========================
        // INLINE EDIT LOGIC (PRODUCTS)
        // ==========================
        function toggleEditProd(btn, id) {
            if (isEditing) {
                alert("Veuillez enregistrer l'élément en cours d'édition avant d'en modifier un autre.");
                return;
            }

            const row = document.getElementById('row-prod-' + id);
            const editables = row.querySelectorAll('.editable-prod');
            
            editables.forEach(td => {
                const text = td.innerText;
                const fieldName = td.getAttribute('data-field');
                let inputType = (fieldName === 'prix_produit' || fieldName === 'qte_stock' || fieldName === 'ID_stand') ? 'number' : 'text';
                td.innerHTML = `<input type="${inputType}" class="inline-input" value="${text}" style="width:100%; padding:4px;" />`;
            });
            
            isEditing = true;
            btn.innerHTML = "Enregistrer";
            btn.style.background = "#27ae60";
            btn.style.color = "#ffffff";
            btn.style.border = "none";
            btn.setAttribute('onclick', `saveProduit(this, ${id})`);
        }

        function saveProduit(btn, id) {
            const row = document.getElementById('row-prod-' + id);
            const editables = row.querySelectorAll('.editable-prod');
            
            const formData = new FormData();
            formData.append('ID_produit', id);
            editables.forEach(td => {
                const input = td.querySelector('input');
                const fieldName = td.getAttribute('data-field');
                formData.append(fieldName, input.value);
            });
            
            fetch('../../FrontOffice/Produits/updateProduitAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if(result === 'SUCCESS') {
                    editables.forEach(td => {
                        const input = td.querySelector('input');
                        td.innerHTML = input.value;
                    });
                    isEditing = false;
                    btn.innerHTML = "Modifier";
                    btn.style.background = "#f0f2f5";
                    btn.style.color = "#3a4a5c";
                    btn.style.border = "1px solid #d5dae0";
                    btn.setAttribute('onclick', `toggleEditProd(this, ${id})`);
                } else {
                    alert('Erreur: ' + result);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Une erreur est survenue.");
            });
        }

        function deleteStand(id) {
            if (!confirm("Voulez-vous vraiment supprimer ce stand ?")) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('../../FrontOffice/Stands/deleteStandAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'SUCCESS') {
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                } else {
                    alert("Erreur : " + result);
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                alert("Une erreur est survenue lors de la suppression.");
            });
        }

        // ==========================
        // DYNAMIC SEARCH & GEOLOCATION
        // ==========================
        function triggerSearch() {
            const val = document.getElementById('standSearchInput').value;
            if (!val) {
                alert("Veuillez entrer un nom ou ID de stand.");
                return;
            }
            fetchSearch(`searchVal=${encodeURIComponent(val)}`);
        }

        function searchByLocation() {
            const btn = document.querySelector('.btn-location');
            const originalText = btn.innerHTML;
            
            if (!navigator.geolocation) {
                alert("La géolocalisation n'est pas supportée par votre navigateur.");
                return;
            }

            btn.innerHTML = "⌛ Détection...";
            btn.style.opacity = "0.7";

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    btn.innerHTML = originalText;
                    btn.style.opacity = "1";
                    fetchSearch(`lat=${lat}&lon=${lon}`);
                },
                (error) => {
                    btn.innerHTML = originalText;
                    btn.style.opacity = "1";
                    alert("Erreur de géolocalisation : " + error.message);
                }
            );
        }

        function showSection(target) {
            hideAllSections();
            if (target && sections[target]) {
                sections[target].classList.remove('hidden');
                mainContent.classList.remove('hidden');
                
                // Update nav items
                navItems.forEach(nav => {
                    if (nav.getAttribute('data-menu') === target) {
                        nav.classList.add('active');
                    } else {
                        nav.classList.remove('active');
                    }
                });
            } else {
                mainContent.classList.add('hidden');
            }
        }

        function showStatsSection(type) {
            showSection(type);
            if (type === 'standsStats') {
                updateStandsChart();
            } else if (type === 'produitsStats') {
                updateProduitsChart();
            }
        }

        // ==========================
        // STATS RENDERING LOGIC
        // ==========================
        let standsChartInstance = null;

        function updateStandsChart() {
            fetch('../../FrontOffice/Stands/getStatsStands.php')
                .then(r => r.text())
                .then(text => {
                    if (text.startsWith('SUCCESS')) {
                        const data = text.split('|')[1].split(';').filter(x => x).map(item => {
                            const [name, count] = item.split(':');
                            return { name, total: parseInt(count) };
                        });
                        renderStandsInfographic(data);
                    }
                });
        }

        function renderStandsInfographic(data) {
            const sorted = [...data].sort((a,b) => b.total - a.total);
            const leader = sorted[0];
            if(leader && leader.total > 0) {
                document.getElementById('leader-spotlight-int').style.display = 'block';
                document.getElementById('top-stand-name-int').innerText = leader.name;
                document.getElementById('top-stand-count-int').innerText = leader.total;
            }

            const ctx = document.getElementById('distributionChartInt').getContext('2d');
            if(standsChartInstance) standsChartInstance.destroy();
            standsChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.name),
                    datasets: [{
                        data: data.map(d => d.total),
                        backgroundColor: ['#3498db', '#1abc9c', '#e91e63', '#f1c40f', '#9b59b2', '#e67e22', '#16a085', '#2c3e50']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            const container = document.getElementById('columnsContainerInt');
            container.innerHTML = '';
            const max = Math.max(...data.map(d => d.total), 1);
            data.slice(0,6).forEach((item, i) => {
                const col = document.createElement('div');
                col.className = 'col-internal';
                col.innerHTML = `
                    <div style="font-weight:800; color:#3e7070;">${item.total}</div>
                    <div class="sleeve-internal"><div class="bar-internal" style="height:${(item.total/max)*100}%; background:${['#3498db', '#1abc9c', '#e91e63', '#f1c40f', '#9b59b2', '#e67e22'][i%6]}"></div></div>
                    <div class="icon-cap-internal"><i class="fas fa-star"></i></div>
                    <div class="name-internal">${item.name}</div>
                `;
                container.appendChild(col);
            });
        }

        function updateProduitsChart() {
            fetch('../../FrontOffice/Produits/getStatsProduits.php')
                .then(r => r.text())
                .then(text => {
                    const [dispo, rupture] = text.split(':');
                    const d = parseInt(dispo);
                    const r = parseInt(rupture);
                    const max = Math.max(d, r, 1);
                    document.getElementById('bar-dispo-int').style.height = (d/max)*100 + '%';
                    document.getElementById('val-dispo-int').innerText = d;
                    document.getElementById('bar-rupture-int').style.height = (r/max)*100 + '%';
                    document.getElementById('val-rupture-int').innerText = r;
                });
        }
    </script>
</body>
</html>
-
