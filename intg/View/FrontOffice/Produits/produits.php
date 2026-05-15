<?php
// produits.php
require_once __DIR__ . '/../../Controller/standcontroller.php';
require_once __DIR__ . '/../../Controller/produitcontroller.php';

$idStand = isset($_GET['id_stand']) ? intval($_GET['id_stand']) : 0;
$standName = "Catalogue Produits";

if ($idStand > 0) {
    $standCtr = new StandController();
    $stand = $standCtr->getStand($idStand);
    if ($stand) {
        $standName = $stand['nom_stand'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($standName) ?> | BarchaThon</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            color: #666;
            font-style: italic;
        }
        .loading-spinner {
            text-align: center;
            padding: 50px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <img src="logo.png" alt="BarchaThon Logo">
                <span>BarchaThon</span>
            </div>
            <nav class="nav">
                <a href="../BackOffice/tab_stand.php">Accueil</a>
                <a href="stand.html">Catalogue</a>
                <a href="#">S’inscrire</a>
                <a href="#" class="btn-login">Se connecter</a>
            </nav>
        </div>
    </header>

    <div class="stand">
        <div class="top">
            <h1><?= htmlspecialchars($standName) ?></h1>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a class="btn-manipuler" href="crud-produit.html" style="margin-right: 15px;">⚙️ Manipuler Produit</a>
                <a href="stand.html">Retour stands</a>
                <a href="javascript:history.back()">Retour détails</a>
            </div>
        </div>

        <div class="grid-products">
            <div class="products" id="products-grid">
                <div class="loading-spinner">Chargement des produits...</div>
            </div>

            <aside class="side">
                <h3>Panier participant</h3>
                <div id="cart-items">
                    <p style="color:#999; font-size: 0.9em;">Votre panier est vide.</p>
                </div>
                <p><strong>Total: 0.000 TND</strong></p>
                <button class="btn" style="width:100%; margin-top:20px;">Commander</button>
            </aside>
        </div>
    </div>

    

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idStand = <?= $idStand ?>;
        const grid = document.getElementById('products-grid');

        if (idStand <= 0) {
            grid.innerHTML = '<div class="no-products">ID de stand invalide.</div>';
            return;
        }

        fetch(`getProduitsByStandAjax.php?id_stand=${idStand}`)
            .then(response => response.text())
            .then(text => {
                grid.innerHTML = '';
                if(text.startsWith('SUCCESS')) {
                    const rows = text.split('|').slice(1);
                    rows.forEach(row => {
                        const [nom, type, prix, qte, image] = row.split(';');
                        const card = document.createElement('div');
                        card.className = 'card';
                        const stockBadge = parseInt(qte) <= 0 ? '<span style="color:red; font-size:0.8em; font-weight:bold;">[RUPTURE]</span>' : '';
                        
                        // Gestion de l'image
                        let imageHtml = '';
                        if (image && image !== 'default.png' && image !== 'undefined') {
                            imageHtml = `<img class="card-img" src="../../uploads/produits/${image}" alt="${nom}">`;
                        } else {
                            imageHtml = `<div class="card-img-placeholder">
                                <span>Pas d'image</span>
                            </div>`;
                        }

                        card.innerHTML = `
                            ${imageHtml}
                            <div class="card-body">
                                <h3>${nom} ${stockBadge}</h3>
                                <p class="type-tag"><strong>Type:</strong> ${type}</p>
                                <p class="desc">${nom} - Qualité garantie pour cet événement.</p>
                                <p style="font-size:0.8em; color:#666; margin-top:-5px; margin-bottom:12px;">En stock: ${qte}</p>
                                <hr class="card-separator">
                                <div class="card-footer">
                                    <span class="price">${parseFloat(prix).toFixed(3)} TND</span>
                                    <button class="btn btn-add" ${parseInt(qte) <= 0 ? 'disabled style="background:#ccc;"' : ''} onclick="alert('Ajouté au panier !')">Ajouter au panier</button>
                                </div>
                            </div>
                        `;
                        grid.appendChild(card);
                    });
                } else if(text === 'EMPTY') {
                    grid.innerHTML = '<div class="no-products">Aucun produit n\'est disponible pour ce stand actuellement.</div>';
                } else {
                    grid.innerHTML = '<div class="no-products">Erreur lors du chargement des produits.</div>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                grid.innerHTML = '<div class="no-products">Erreur de connexion.</div>';
            });
    });
    </script>
</body>
</html>
