<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
include '../../../Controller/CommandeController.php';
include '../../../Controller/LigneCommandeController.php';
include '../../../Controller/ProduitController.php';

$prodCtrl = new ProduitController();

$stand_id = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : null;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : null;
if (!$stand_id) {
    die('Stand ID requis.');
}

$products = $prodCtrl->afficherProduitsParStand($stand_id);

// Convert to array with id as key for compatibility
$productsAssoc = [];
foreach ($products as $prod) {
    $id = $prod['id_produit'] ?? $prod['ID_produit'] ?? 0;
    if ($id) {
        $productsAssoc[$id] = [
            'nom' => $prod['nom_produit'] ?? $prod['Nom_produit'] ?? 'Inconnu',
            'prix' => $prod['prix_produit'] ?? $prod['Prix_produit'] ?? 0,
            'qte_stock' => $prod['qte_stock'] ?? $prod['Qte_stock'] ?? 0,
            'en_out_stock' => $prod['en_out_stock'] ?? $prod['En_out_stock'] ?? 0,
            'image' => $prod['image'] ?? null,
            'type' => $prod['type'] ?? 'Produit'
        ];
    }
}
$products = $productsAssoc;

$stands = [
    // Assuming we have stand names, but for now, just use stand_id
];
$stand_name = "Stand #$stand_id"; // Placeholder, can fetch from DB if needed

if ($stand_id) {
    // Already filtered
}

$currentPage = 'catalogue';
$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}
$role = $user['role'] ?? 'visiteur';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$success = '';
$cart = &$_SESSION['cart'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle AI cart application
    if (isset($_POST['apply_ai_cart']) && isset($_POST['ai_product_id']) && isset($_POST['ai_quantity'])) {
        $productIds = $_POST['ai_product_id'];
        $quantities = $_POST['ai_quantity'];
        
        // Clear current cart
        $_SESSION['cart'] = [];
        $cart = &$_SESSION['cart'];
        
        // Add AI selected products with stock validation
        for ($i = 0; $i < count($productIds); $i++) {
            $productId = (int) $productIds[$i];
            $quantity = max(1, (int) $quantities[$i]);
            
            if (isset($products[$productId])) {
                $item = $products[$productId];
                if (!$item['en_out_stock'] || $item['qte_stock'] <= 0) {
                    continue;
                }
                $quantity = min($quantity, $item['qte_stock']);
                if ($quantity <= 0) {
                    continue;
                }
                $cart[$productId] = [
                    'idproduit' => $productId,
                    'nom' => $item['nom'],
                    'prix' => $item['prix'],
                    'quantite' => $quantity,
                ];
            }
        }
        
        if (empty($cart)) {
            $message = 'Aucun produit disponible dans le panier IA. Essayez avec un budget différent.';
        } else {
            $success = 'Panier IA appliqué avec succès !';
            header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET) . '#popular-products');
            exit;
        }
    }

    if (isset($_POST['update_quantity'], $_POST['product_id'], $_POST['quantity'])) {
        $productId = (int) $_POST['product_id'];
        $quantity = max(0, (int) $_POST['quantity']);

        if (!isset($products[$productId])) {
            $message = 'Produit invalide.';
        } else {
            $item = $products[$productId];
            if (!$item['en_out_stock'] || $item['qte_stock'] <= 0) {
                unset($cart[$productId]);
                $message = "Le produit {$item['nom']} est en rupture de stock et a été retiré du panier.";
            } else {
                if ($quantity > $item['qte_stock']) {
                    $quantity = $item['qte_stock'];
                    $message = "Quantité limitée à la disponibilité actuelle ({$item['qte_stock']}) pour {$item['nom']}.";
                }
                if ($quantity <= 0) {
                    unset($cart[$productId]);
                    $success = "Produit {$item['nom']} supprimé du panier.";
                } else {
                    $cart[$productId] = [
                        'idproduit' => $productId,
                        'nom' => $item['nom'],
                        'prix' => $item['prix'],
                        'quantite' => $quantity,
                    ];
                    $success = "Quantité pour {$item['nom']} mise à jour à {$quantity}.";
                }
            }
        }
    }

    if (isset($_POST['delete_product_id'])) {
        $deleteId = (int) $_POST['delete_product_id'];
        if (isset($cart[$deleteId])) {
            unset($cart[$deleteId]);
            $success = 'Produit supprimé du panier.';
        }
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $cart = &$_SESSION['cart'];
        $success = 'Panier vidé.';
    }

    if (isset($_POST['validate_order'])) {
        if (empty($cart)) {
            $message = 'Le panier est vide, impossible de valider la commande.';
        } else {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['quantite'] * $item['prix'];
            }

            if ($total == 0) {
                // Commande gratuite
                $productController = new ProduitController();
                foreach ($cart as $item) {
                    $productId = $item['idproduit'];
                    if (!isset($products[$productId]) || !$products[$productId]['en_out_stock'] || $products[$productId]['qte_stock'] < $item['quantite']) {
                        $message = 'Stock insuffisant pour un produit du panier. Ajustez votre sélection avant de continuer.';
                        break;
                    }
                }

                if (empty($message)) {
                    $commandeC = new CommandeController();
                    $ligneC = new LigneCommandeController();
                    $userId = $user['id_user'] ?? $user['id'];
                    $commande = new Commande(null, $userId, $stand_id, date('Y-m-d H:i:s'), 'paye', $total);
                    $newCommandeId = $commandeC->addCommande($commande);

                    if ($newCommandeId) {
                        foreach ($cart as $item) {
                            $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                            $ligneC->addLigneCommande($ligne);
                            $productController->decrementStock($item['idproduit'], $item['quantite']);
                        }

                        $_SESSION['cart'] = [];
                        header('Location: produit.php?created=' . urlencode($newCommandeId) . '&success=' . urlencode('Commande gratuite validée !'));
                        exit;
                    }

                    $message = 'Erreur lors de la création de la commande. Veuillez réessayer.';
                }
            } else {
                // Validate stock one last time before payment
                foreach ($cart as $item) {
                    $productId = $item['idproduit'];
                    if (!isset($products[$productId]) || !$products[$productId]['en_out_stock'] || $products[$productId]['qte_stock'] < $item['quantite']) {
                        $message = 'Stock insuffisant pour un produit du panier. Ajustez votre sélection avant de continuer.';
                        break;
                    }
                }

                if (empty($message)) {
                    // Redirection vers paiement
                    header('Location: ../paiement.php?type=commande&id=0&montant=' . $total . '&stand_id=' . $stand_id);
                    exit;
                }
            }
        }
    }

    if (isset($_POST['update_cart']) && !isset($_POST['delete_product_id']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = (int) $id;
            $qty = max(0, (int) $qty);

            if ($qty > 0 && isset($products[$id])) {
                $item = $products[$id];
                if (!$item['en_out_stock'] || $item['qte_stock'] <= 0) {
                    unset($cart[$id]);
                    $message = "Le produit {$item['nom']} est en rupture de stock et a été retiré du panier.";
                    continue;
                }

                if ($qty > $item['qte_stock']) {
                    $qty = $item['qte_stock'];
                    $message = "Quantité limitée à {$item['qte_stock']} pour {$item['nom']} en raison du stock disponible.";
                }
                $cart[$id]['quantite'] = $qty;
            } else {
                unset($cart[$id]);
            }
        }
        $success = 'Panier mis à jour.';
    }
}

$cartTotal = 0;
foreach ($cart as $item) {
    $cartTotal += $item['quantite'] * $item['prix'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $stand_name ? "Produits du $stand_name" : 'Catalogue produits'; ?> — BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:#f8fafc; }
        .page { width:min(1200px,calc(100% - 32px)); margin:0 auto; padding:28px 0 60px; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.05); font-size:0.92rem; transition:transform .2s; }
        .back-link:hover { transform:translateY(-2px); }

        /* Clean Hero */
        .clean-hero { background: white; border-radius: 24px; padding: 50px 60px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin-bottom: 40px; position: relative; overflow: hidden; }
        .clean-hero-text { max-width: 55%; position: relative; z-index: 2; }
        .clean-hero-text h1 { font-size: 3.2rem; color: var(--ink); font-weight: 900; text-transform: uppercase; letter-spacing: -1px; margin-bottom: 15px; line-height: 1.1; }
        .clean-hero-text p { color: #64748b; font-size: 1.1rem; line-height: 1.6; margin-bottom: 25px; }
        .clean-hero-img { font-size: 10rem; opacity: 0.9; position: relative; z-index: 2; filter: drop-shadow(0 20px 30px rgba(15,118,110,0.2)); transform: rotate(-10deg); transition: transform 0.5s; }
        .clean-hero:hover .clean-hero-img { transform: rotate(0deg) scale(1.05); }
        .clean-hero::after { content:''; position:absolute; top:-50%; right:-10%; width:400px; height:400px; background:radial-gradient(circle, rgba(20,184,166,0.1) 0%, rgba(255,255,255,0) 70%); border-radius:50%; z-index:1; }

        /* Promo Blocks */
        .promo-blocks { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 50px; }
        .promo-box { padding: 30px 20px; text-align: center; border-radius: 16px; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 10px 25px rgba(15,118,110,0.15); color: white; transition: transform .3s ease; }
        .promo-box:hover { transform: translateY(-5px); }
        .promo-box h3 { font-size: 1.6rem; margin-bottom: 8px; font-weight: 800; }
        .promo-box p { font-size: 0.95rem; opacity: 0.9; font-weight: 500; }

        /* Layout */
        .panel { background: transparent; padding: 0; box-shadow: none; }
        .grid { display:grid; grid-template-columns:3fr 1fr; gap:30px; align-items:start; }
        
        .section-title-center { text-align: center; font-size: 1.2rem; color: #64748b; letter-spacing: 2px; margin-bottom: 40px; font-weight: 600; text-transform: uppercase; }

        /* Product Grid */
        /* Minimal circular product cards like the photo */
        .product-grid { display:flex; flex-wrap:wrap; gap:30px; justify-content:center; }
        
        /* Effect to hide/dim other products when hovering one */
        .product-grid:hover .product-card-item {
            opacity: 0.15;
            filter: grayscale(80%) blur(2px);
            transition: all 0.4s ease;
        }
        .product-grid .product-card-item:hover {
            opacity: 1 !important;
            filter: grayscale(0%) blur(0) !important;
            z-index: 50;
        }

        .product-card-item { 
            background: transparent; 
            border: none; 
            padding: 10px; 
            text-align: center; 
            cursor: pointer; 
            position: relative; 
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); 
            width: 220px;
            box-shadow: none;
        }
        .product-card-item:hover { transform: translateY(-10px) scale(1.1); }
        .product-card-item.out-of-stock { cursor: not-allowed; opacity: 0.6; transform: none; }
        
        .product-img { 
            width: 200px; 
            height: 200px; 
            background: #fff; 
            border-radius: 50%; 
            margin: 0 auto 15px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 4rem; 
            transition: all 0.4s ease; 
            overflow: hidden; 
            border: 4px solid #e2e8f0; 
            padding: 5px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }
        
        /* Colorful Borders - rotating colors like the photo */
        .product-card-item:nth-child(4n+1) .product-img { border-color: #ec4899; } /* Pink */
        .product-card-item:nth-child(4n+2) .product-img { border-color: #3b82f6; } /* Blue */
        .product-card-item:nth-child(4n+3) .product-img { border-color: #f59e0b; } /* Yellow */
        .product-card-item:nth-child(4n+4) .product-img { border-color: #10b981; } /* Green */
        
        .product-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        
        .product-card-item:hover .product-img { 
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
            transform: rotate(3deg);
        }
        
        .product-name { 
            font-weight: 600; 
            font-size: 0.95rem; 
            color: var(--ink); 
            margin-bottom: 5px; 
            transition: color 0.3s;
        }
        .product-card-item:hover .product-name { color: var(--teal); }
        
        .product-price { color: #64748b; font-weight: 700; font-size: 1rem; }
        .product-actions { display:flex; justify-content:center; gap:8px; margin-top:14px; }
        .product-actions a { display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 12px; border-radius:999px; font-size:0.8rem; font-weight:700; text-decoration:none; border:1px solid transparent; transition: all .2s ease; }
        .product-actions .btn-edit { background:#0f766e; color:#fff; border-color:#0f766e; }
        .product-actions .btn-edit:hover { background:#0d665e; }
        .product-actions .btn-delete { background:#fff; color:#ef4444; border-color:#fecaca; }
        .product-actions .btn-delete:hover { background:#ef4444; color:#fff; }
        
        .badge-round { position: absolute; top: 5px; right: 20px; background: #10b981; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; z-index: 5; }
        .badge-round.out { background: var(--coral); }

        /* Tooltip style window - Positioned to the right & BIGGER */
        .product-info-tooltip {
            position: absolute;
            top: 50%;
            left: 110%; /* Position to the right of the card */
            transform: translateY(-50%) scale(0.8);
            width: 320px; /* Much bigger */
            background: rgba(255, 255, 255, 0.99);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 30px 30px 80px rgba(16, 42, 67, 0.25);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 200; 
            text-align: left;
            border: 1px solid rgba(15, 118, 110, 0.15);
            pointer-events: none;
        }
        .product-card-item:hover .product-info-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) scale(1);
        }
        .tooltip-row { margin-bottom: 15px; display: flex; flex-direction: column; }
        .tooltip-label { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px; }
        .tooltip-value { color: var(--ink); font-weight: 700; font-size: 1.1rem; }
        .tooltip-stock { font-weight: 900; margin-top: 10px; display: inline-block; padding: 8px 18px; border-radius: 12px; font-size: 0.9rem; text-transform: uppercase; }
        .stock-yes { background: #dcfce7; color: #166534; }
        .stock-no { background: #fee2e2; color: #991b1b; }

        /* Cart */
        .cart-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); border: none; position: sticky; top: 100px; }
        .cart-card h2 { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; color: var(--ink); border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; font-weight: 800; }
        
        input[type=number] { width: 70px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 8px; text-align: center; font-weight: 600; color: var(--ink); }
        input[type=number]:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(15,118,110,0.1); }
        
        .cart-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:15px 0; border-bottom: 1px dashed #e2e8f0; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item-info { display:flex; flex-direction:column; gap:4px; }
        .cart-item-name { font-weight:700; color:var(--ink); font-size: 0.95rem; }
        .cart-item-price { color:var(--teal); font-weight: 600; font-size: 0.9rem; }
        .cart-item-actions { display:flex; align-items:center; gap:8px; }
        
        .btn-sm { padding:6px 10px; font-size:0.85rem; border-radius: 8px; }
        .btn-delete { background:#fef2f2; color:#ef4444; border: 1px solid #fecaca; }
        .btn-delete:hover { background: #ef4444; color: white; }
        
        .cart-total { margin-top:20px; font-weight:900; color:var(--ink); font-size: 1.3rem; text-align: right; border-top: 2px solid #f1f5f9; padding-top: 15px; }
        
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:999px; padding:14px 24px; font-weight:800; cursor:pointer; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem; transition: all 0.2s; }
        .btn-primary { background:var(--teal); color:#fff; width: 100%; margin-top: 15px; box-shadow: 0 8px 20px rgba(15,118,110,0.2); }
        .btn-primary:hover { background: #0d665e; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(15,118,110,0.3); }
        .btn-danger { background:white; color:var(--coral); border:2px solid var(--coral); width: 100%; margin-top: 10px; }
        .btn-danger:hover { background: var(--coral); color: white; }
        
        .message { padding:16px 20px; border-radius:16px; margin-bottom:24px; font-weight: 600; }
        .message.success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        .message.error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        @keyframes fadeOut { 0% { opacity:1; } 100% { opacity:0; display:none; } }
        .message.fade-out { animation: fadeOut .5s ease-in forwards; }
        
        .btn-add-product { position: absolute; top: 30px; right: 30px; background: linear-gradient(135deg, #10b981, #059669); color: white; text-decoration: none; box-shadow: 0 4px 15px rgba(16,185,129,0.3); padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: transform 0.2s, box-shadow 0.2s; z-index: 10; }
        .btn-add-product:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(16,185,129,0.4); }

        /* AI Agent Styles */
        .ai-agent-section {
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #generateCartBtn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        #generateCartBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15,118,110,0.3);
        }
        
        #generateCartBtn:disabled {
            cursor: not-allowed;
        }
        
        #aiResult {
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width:960px) { 
            .grid { grid-template-columns:1fr; }
            .promo-blocks { grid-template-columns: 1fr; }
            .clean-hero { flex-direction: column; text-align: center; padding: 40px 20px; }
            .clean-hero-text { max-width: 100%; margin-bottom: 30px; }
            .clean-hero-img { font-size: 6rem; }
            .btn-add-product { top: auto; bottom: 20px; right: 50%; transform: translateX(50%); }
            .ai-form-container { flex-direction: column; align-items: center; }
            .ai-input-group { width: 100%; max-width: 300px; }
            .ai-input-group input { width: 100%; }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="../detailParcours.php?id=<?php echo $parcours_id; ?>">← Retour au parcours</a>

    <section class="clean-hero">
        <div class="clean-hero-text">
            <h1><?php echo $stand_name ? "Produits du $stand_name" : 'Catalogue produits'; ?></h1>
            <p><?php echo $stand_name ? "Découvrez notre sélection exclusive de produits d'hydratation et de nutrition pour optimiser votre marathon." : 'Choisissez vos produits et définissez les quantités. Cliquez sur Actualiser pour mettre à jour le panier.'; ?></p>
            <a href="#popular-products" class="btn btn-primary" style="display:inline-block; width:auto;">Découvrir les produits</a>
        </div>
        <div class="clean-hero-img">
            🥤
        </div>
        <?php if (isOrganisateur()): ?>
            <a href="crud-produit.php?stand_id=<?php echo $stand_id; ?>&parcours_id=<?php echo $parcours_id; ?>" class="btn-add-product"><i class="fa-solid fa-plus"></i> Ajouter un produit</a>
        <?php endif; ?>
    </section>

    <div class="promo-blocks">
        <div class="promo-box" style="background: linear-gradient(135deg, #10b981, #059669);">
            <h3>-10% Off</h3>
            <p>Sur votre première commande en ligne</p>
        </div>
        <div class="promo-box" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
            <h3>Retrait Rapide</h3>
            <p>Directement au stand du parcours</p>
        </div>
        <div class="promo-box" style="background: linear-gradient(135deg, #0f766e, #0f172a);">
            <h3>Besoin d'aide ?</h3>
            <p>Nos organisateurs sont à votre écoute</p>
        </div>
    </div>

    <!-- AI Agent Section -->
    <section class="ai-agent-section" style="background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(248,250,252,0.9)); border-radius: 24px; padding: 40px; margin-bottom: 50px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); backdrop-filter: blur(10px); border: 1px solid rgba(15,118,110,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: var(--ink); font-size: 2rem; font-weight: 900; margin-bottom: 10px;">🤖 Agent IA Healthy</h2>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Laissez notre IA vous composer un panier équilibré et healthy adapté à votre budget et au nombre de personnes. <strong>Règle principale:</strong> Chaque produit est pris en quantité égale au nombre de personnes, sauf réduction pour budget faible ou produits moins healthy.</p>
        </div>

        <div class="ai-form-container" style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
            <div class="ai-input-group" style="display: flex; flex-direction: column; gap: 8px;">
                <label for="budget" style="color: var(--ink); font-weight: 700; font-size: 0.9rem;">Budget (TND)</label>
                <input type="number" id="budget" min="1" max="10000" step="0.01" placeholder="Ex: 50.00" style="padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; font-weight: 600; color: var(--ink); width: 150px; transition: border-color 0.3s;" required>
            </div>

            <div class="ai-input-group" style="display: flex; flex-direction: column; gap: 8px;">
                <label for="personnes" style="color: var(--ink); font-weight: 700; font-size: 0.9rem;">Nombre de personnes</label>
                <input type="number" id="personnes" min="1" max="50" placeholder="Ex: 2" style="padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; font-weight: 600; color: var(--ink); width: 150px; transition: border-color 0.3s;" required>
            </div>

            <div class="ai-input-group" style="display: flex; align-items: end;">
                <button id="generateCartBtn" style="background: linear-gradient(135deg, var(--teal), #0d665e); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(15,118,110,0.2);">
                    🧠 Générer le panier
                </button>
            </div>
        </div>

        <!-- Debug section (hidden by default, can be shown for testing) -->
        <div id="debugSection" style="display: none; margin-top: 20px; padding: 20px; background: rgba(255,255,255,0.8); border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 style="color: var(--ink); margin-bottom: 15px;">🔍 Analyse des produits disponibles</h3>
            <div id="debugContent" style="font-size: 0.85rem; color: #64748b;"></div>
            <button id="toggleDebug" style="margin-top: 10px; padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 8px; cursor: pointer;">Masquer l'analyse</button>
        </div>

        <div id="aiResult" style="display: none; background: white; border-radius: 16px; padding: 20px; margin-top: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid rgba(15,118,110,0.1);">
            <h3 style="color: var(--ink); font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <span>✅</span> Panier généré et appliqué automatiquement !
            </h3>
            <div id="aiCartSummary" style="color: #64748b; margin-bottom: 15px;"></div>
        </div>
    </section>

    <section class="panel" id="popular-products">
            <?php if ($message !== ''): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="grid">
                <div>
                    <h2 class="section-title-center">Produits Populaires</h2>
                    <div class="product-grid">
                        <?php foreach ($products as $id => $product): ?>
                            <div class="product-card-item<?php echo $product['en_out_stock'] ? '' : ' out-of-stock'; ?>" data-product-id="<?php echo $id; ?>" data-product-available="<?php echo $product['en_out_stock']; ?>" onclick="addToCart(<?php echo $id; ?>)">
                                <div class="product-info-tooltip">
                                    <div class="tooltip-row">
                                        <span class="tooltip-label">Type</span>
                                        <span class="tooltip-value"><?php echo htmlspecialchars($product['type']); ?></span>
                                    </div>
                                    <div class="tooltip-row">
                                        <span class="tooltip-label">Nom</span>
                                        <span class="tooltip-value"><?php echo htmlspecialchars($product['nom']); ?></span>
                                    </div>
                                    <div class="tooltip-row">
                                        <span class="tooltip-label">Prix</span>
                                        <span class="tooltip-value" style="color:var(--teal);"><?php echo number_format($product['prix'], 2, ',', ' '); ?> TND</span>
                                    </div>
                                    <div class="tooltip-stock <?php echo $product['en_out_stock'] ? 'stock-yes' : 'stock-no'; ?>">
                                        <?php echo $product['en_out_stock'] ? '✅ En Stock' : '❌ Rupture'; ?>
                                    </div>
                                </div>

                                <?php if ($product['en_out_stock']): ?>
                                    <div class="badge-round">DISPO</div>
                                <?php else: ?>
                                    <div class="badge-round out">ÉPUISÉ</div>
                                <?php endif; ?>
                                
                                <div class="product-img">
                                    <?php if ($product['image'] && file_exists(__DIR__ . '/../../../uploads/produits/' . $product['image'])): ?>
                                        <img src="../../../uploads/produits/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
                                    <?php else: ?>
                                        📦
                                    <?php endif; ?>
                                </div>
                                <div class="product-name"><?php echo htmlspecialchars($product['nom']); ?></div>
                                <div class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> TND</div>
                                <?php if (isOrganisateur()): ?>
                                    <div class="product-actions">
                                        <a href="crud-produit.php?stand_id=<?php echo $stand_id; ?>&parcours_id=<?php echo $parcours_id; ?>&edit_id=<?php echo $id; ?>" class="btn-edit" onclick="event.stopPropagation();">Modifier</a>
                                        <a href="deleteProduit.php?id=<?php echo $id; ?>&stand_id=<?php echo $stand_id; ?>&parcours_id=<?php echo $parcours_id; ?>" class="btn-delete" onclick="event.stopPropagation(); return confirm('Voulez-vous vraiment supprimer ce produit ?');">Supprimer</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($products)): ?>
                            <p style="text-align:center; color:#64748b; font-style:italic; grid-column:1/-1; padding: 40px;">Aucun produit disponible pour ce stand actuellement.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="cart-card">
                        <h2>🛒 Panier actuel</h2>
                        <?php if (empty($cart)): ?>
                            <p style="color:#64748b; text-align:center; padding: 20px 0; font-style:italic;">Votre panier est vide.</p>
                        <?php else: ?>
                            <form method="post" id="cartForm" action="#popular-products">
                                <input type="hidden" name="update_cart" value="1">
                                <?php foreach ($cart as $id => $item): ?>
                                    <div class="cart-item">
                                        <div class="cart-item-info">
                                            <span class="cart-item-name"><?php echo htmlspecialchars($item['nom']); ?></span>
                                            <span class="cart-item-price"><?php echo number_format($item['prix'], 2, ',', ' '); ?> TND</span>
                                        </div>
                                        <div class="cart-item-actions">
                                            <input type="number" name="quantities[<?php echo $id; ?>]" value="<?php echo htmlspecialchars($item['quantite']); ?>" min="0" max="<?php echo $products[$id]['qte_stock'] ?? 999; ?>" class="cart-qty-input" />
                                            <button type="submit" name="delete_product_id" value="<?php echo $id; ?>" class="btn btn-sm btn-delete" title="Supprimer">✕</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="cart-total"><?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</div>
                                <div>
                                    <button type="submit" name="validate_order" class="btn btn-primary">Valider la commande</button>
                                    <button type="submit" name="clear_cart" class="btn btn-danger">Vider le panier</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        const productData = <?php echo json_encode($products); ?>;

        function addToCart(productId) {
            const product = productData[productId];
            if (!product || Number(product.en_out_stock) !== 1 || Number(product.qte_stock) <= 0) {
                alert('Ce produit est en rupture de stock et ne peut pas être ajouté.');
                return;
            }
            const form = document.createElement('form');
            form.method = 'post';
            form.action = window.location.pathname + window.location.search + '#popular-products';
            form.style.display = 'none';
            const inputId = document.createElement('input');
            inputId.name = 'product_id';
            inputId.value = productId;
            const inputQty = document.createElement('input');
            inputQty.name = 'quantity';
            inputQty.value = '1';
            const inputUpdate = document.createElement('input');
            inputUpdate.name = 'update_quantity';
            inputUpdate.value = '1';
            form.appendChild(inputId);
            form.appendChild(inputQty);
            form.appendChild(inputUpdate);
            document.body.appendChild(form);
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const cartForm = document.getElementById('cartForm');
            if (!cartForm) return;

            const timeoutMap = new Map();
            cartForm.querySelectorAll('.cart-qty-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const id = input.name;
                    if (timeoutMap.has(id)) {
                        clearTimeout(timeoutMap.get(id));
                    }
                    timeoutMap.set(id, setTimeout(function () {
                        cartForm.submit();
                    }, 450));
                });
            });
        });

        // Auto-hide success message after 3 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const successMsg = document.querySelector('.message.success');
            if (successMsg) {
                setTimeout(function () {
                    successMsg.classList.add('fade-out');
                    setTimeout(function () {
                        successMsg.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        });

        // AI Agent functionality
        document.addEventListener('DOMContentLoaded', function () {
            const budgetInput = document.getElementById('budget');
            const personnesInput = document.getElementById('personnes');
            const generateBtn = document.getElementById('generateCartBtn');
            const aiResult = document.getElementById('aiResult');
            const aiCartSummary = document.getElementById('aiCartSummary');

            // Focus effects for inputs
            [budgetInput, personnesInput].forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = 'var(--teal)';
                    this.style.boxShadow = '0 0 0 3px rgba(15,118,110,0.1)';
                });
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#e2e8f0';
                    this.style.boxShadow = 'none';
                });
            });

            generateBtn.addEventListener('click', function() {
                const budget = parseFloat(budgetInput.value);
                const personnes = parseInt(personnesInput.value);

                // Enhanced validation with helpful suggestions
                if (!budget || budget <= 0) {
                    alert('Veuillez saisir un budget valide supérieur à 0 TND.');
                    budgetInput.focus();
                    return;
                }
                
                if (!personnes || personnes <= 0 || personnes > 20) {
                    alert('Veuillez saisir un nombre de personnes valide (1-20).');
                    personnesInput.focus();
                    return;
                }

                // Budget recommendations
                const recommendedMinBudget = personnes * 5;
                const recommendedMaxBudget = personnes * 25;
                
                if (budget < recommendedMinBudget) {
                    if (!confirm(`Votre budget (${budget} TND) semble faible pour ${personnes} personne(s). Budget recommandé: ${recommendedMinBudget}-${recommendedMaxBudget} TND. Voulez-vous continuer ?`)) {
                        return;
                    }
                } else if (budget > recommendedMaxBudget * 2) {
                    if (!confirm(`Votre budget (${budget} TND) est élevé. Cela pourrait donner beaucoup de produits. Voulez-vous continuer ?`)) {
                        return;
                    }
                }

                // Visual feedback while generating
                const originalText = generateBtn.textContent;
                generateBtn.textContent = '🤔 Analyse des produits healthy...';
                generateBtn.disabled = true;
                generateBtn.style.opacity = '0.7';

                // Simulate processing time for better UX
                setTimeout(() => {
                    const aiCart = generateAICart(budget, personnes);
                    
                    if (Object.keys(aiCart).length === 0) {
                        alert('Aucun produit healthy trouvé dans ce stand ou budget insuffisant. Essayez d\'augmenter votre budget.');
                        generateBtn.textContent = originalText;
                        generateBtn.disabled = false;
                        generateBtn.style.opacity = '1';
                        return;
                    }
                    
                    displayAICart(aiCart);
                    applyAICartToSession();
                    
                    // Reset button
                    generateBtn.textContent = originalText;
                    generateBtn.disabled = false;
                    generateBtn.style.opacity = '1';
                }, 1000);
            });

            function generateAICart(budget, personnes) {
                // Get available products from PHP data
                const products = <?php echo json_encode($products); ?>;
                const availableProducts = Object.entries(products).filter(([id, product]) => 
                    Number(product.en_out_stock) === 1 && Number(product.qte_stock) > 0
                );

                // Enhanced healthy product detection with scoring system
                function getHealthyScore(product) {
                    const name = product.nom.toLowerCase();
                    const type = product.type.toLowerCase();
                    let score = 0;
                    
                    // High priority healthy keywords (+10 points each)
                    const highPriorityHealthy = ['eau', 'jus', 'fruit', 'légume', 'naturel', 'bio', 'healthy', 'fitness', 'energy', 'protéine', 'vitamine', 'minéral', 'antioxydant', 'thé', 'infusion'];
                    highPriorityHealthy.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score += 10;
                    });
                    
                    // Medium priority healthy keywords (+5 points each)
                    const mediumPriorityHealthy = ['boisson', 'smoothie', 'barre énergétique', 'noix', 'fruit sec', 'yaourt', 'compote', 'chocolat noir'];
                    mediumPriorityHealthy.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score += 5;
                    });
                    
                    // Low priority but acceptable (+2 points each)
                    const lowPriorityAcceptable = ['céréale', 'muesli', 'granola'];
                    lowPriorityAcceptable.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score += 2;
                    });
                    
                    // Unhealthy keywords (negative points)
                    const unhealthyKeywords = ['chocolat blanc', 'chocolat au lait', 'bonbon', 'sucrerie', 'gâteau', 'biscuit', 'chips', 'soda', 'alcool', 'frit', 'gras', 'sucre', 'monster', 'red bull', 'boisson énergétique'];
                    unhealthyKeywords.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score -= 15;
                    });
                    
                    // Medium unhealthy (-5 points)
                    const mediumUnhealthy = ['cola', 'limonade', 'sirop'];
                    mediumUnhealthy.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score -= 5;
                    });
                    
                    // Price consideration (cheaper products get slight bonus for accessibility)
                    if (product.prix <= 5) score += 2;
                    
                    return score;
                }

                // Filter and score products
                const scoredProducts = availableProducts.map(([id, product]) => ({
                    id,
                    product,
                    score: getHealthyScore(product)
                })).filter(item => item.score >= 0) // Only keep non-negative scores
                .sort((a, b) => b.score - a.score); // Sort by score descending

                // If no healthy products found, use all available products but with lower priority
                const baseProducts = scoredProducts.length > 0 ? scoredProducts : 
                    availableProducts.map(([id, product]) => ({ id, product, score: 1 }))
                    .sort((a, b) => a.product.prix - b.product.prix); // Sort by price ascending

                function shuffleArray(array) {
                    return array.slice().sort(() => Math.random() - 0.5);
                }

                const highScore = shuffleArray(baseProducts.filter(item => item.score >= 8));
                const midScore = shuffleArray(baseProducts.filter(item => item.score >= 5 && item.score < 8));
                const lowScore = shuffleArray(baseProducts.filter(item => item.score < 5));

                const selection = [];
                const hasDarkChocolate = product => product.nom.toLowerCase().includes('chocolat noir');
                let darkChocolateAdded = false;

                highScore.slice(0, 3).forEach(item => {
                    if (hasDarkChocolate(item.product) && darkChocolateAdded) return;
                    if (hasDarkChocolate(item.product)) darkChocolateAdded = true;
                    selection.push(item);
                });

                midScore.slice(0, 2).forEach(item => {
                    if (hasDarkChocolate(item.product) && darkChocolateAdded) return;
                    if (hasDarkChocolate(item.product)) darkChocolateAdded = true;
                    selection.push(item);
                });

                if (selection.length < 4 && lowScore.length > 0) {
                    const lowPick = lowScore.find(item => !hasDarkChocolate(item.product));
                    if (lowPick) {
                        selection.push(lowPick);
                    } else {
                        selection.push(lowScore[0]);
                    }
                }

                const selectedProducts = selection.length > 0 ? selection : baseProducts;

                // Generate balanced cart with smart quantity limits
                const cart = {};
                let remainingBudget = budget;
                const baseQuantity = Math.max(1, Math.ceil(personnes / 2));

                // Smart quantity limits based on product type and persons
                function getMaxQuantity(product, personnes, budget) {
                    const name = product.nom.toLowerCase();
                    const type = product.type.toLowerCase();
                    const score = getHealthyScore(product);
                    const isFullyHealthy = score >= 8; // 100% healthy threshold
                    const budgetPerPerson = budget / personnes;
                    
                    // Base quantity = number of persons
                    let baseQuantity = personnes;
                    
                    // Reduce quantity if budget is low (< 10 DT per person)
                    if (budgetPerPerson < 10) {
                        baseQuantity = Math.max(1, Math.floor(personnes * 0.7)); // 70% of persons
                    }
                    
                    // Reduce quantity for non-100% healthy products
                    if (!isFullyHealthy) {
                        if (score >= 5) {
                            baseQuantity = Math.max(1, Math.floor(personnes * 0.5)); // 50% of persons for medium healthy
                        } else {
                            baseQuantity = Math.max(1, Math.floor(personnes * 0.3)); // 30% of persons for low healthy
                        }
                    }
                    
                    // Special limits for specific product types
                    if (name.includes('monster') || name.includes('red bull') || name.includes('boisson énergétique')) {
                        return Math.min(product.qte_stock, Math.max(1, Math.floor(personnes / 4))); // Max 1 per 4 persons
                    }
                    
                    // Supplements and vitamins: always limited
                    if (name.includes('supplement') || name.includes('vitamine') || name.includes('protéine')) {
                        return Math.min(product.qte_stock, Math.max(1, Math.floor(personnes / 2))); // Max 0.5 per person
                    }
                    
                    // Nuts and dried fruits: sharing portions
                    if (name.includes('noix') || name.includes('fruit sec')) {
                        return Math.min(product.qte_stock, Math.max(1, Math.floor(personnes / 2))); // Max 0.5 per person
                    }
                    
                    // Chocolate: sharing
                    if (name.includes('chocolat')) {
                        return Math.min(product.qte_stock, Math.max(1, Math.floor(personnes / 3))); // Max 1 per 3 persons
                    }
                    
                    // Apply stock limit and ensure minimum 1
                    return Math.min(product.qte_stock, Math.max(1, baseQuantity));
                }

                // Select products with balanced approach and diversity
                const maxProducts = Math.min(selectedProducts.length, Math.min(6, Math.floor(budget / 3))); // Max 6 products, min 3 DT per product
                
                // Track categories to ensure diversity
                const categoryCount = {
                    drinks: 0,
                    fruits: 0,
                    snacks: 0,
                    supplements: 0,
                    other: 0
                };
                
                function getCategory(product) {
                    const name = product.nom.toLowerCase();
                    const type = product.type.toLowerCase();
                    
                    if (name.includes('eau') || name.includes('jus') || name.includes('boisson') || type.includes('boisson')) return 'drinks';
                    if (name.includes('fruit') || name.includes('banane') || name.includes('pomme') || name.includes('orange')) return 'fruits';
                    if (name.includes('barre') || name.includes('snack') || name.includes('healthy') || name.includes('céréale') || name.includes('chocolat')) return 'snacks';
                    if (name.includes('supplement') || name.includes('vitamine') || name.includes('protéine')) return 'supplements';
                    return 'other';
                }
                
                for (let i = 0; i < maxProducts && remainingBudget > 0; i++) {
                    const { id, product, score } = selectedProducts[i];
                    
                    if (cart[id]) continue; // Skip if already in cart
                    
                    const category = getCategory(product);
                    
                    // Limit products per category to ensure diversity
                    if (category !== 'other' && categoryCount[category] >= 2) continue;
                    
                    const maxQty = getMaxQuantity(product, personnes, budget);
                    const affordableQty = Math.min(maxQty, Math.floor(remainingBudget / product.prix));
                    
                    if (affordableQty > 0) {
                        // Main rule: quantity = number of persons, with exceptions
                        let quantity = personnes; // Base quantity = number of persons
                        
                        // Exception 1: Reduce if budget is low (< 10 DT per person)
                        const budgetPerPerson = budget / personnes;
                        if (budgetPerPerson < 10) {
                            quantity = Math.max(1, Math.floor(personnes * 0.7));
                        }
                        
                        // Exception 2: Reduce if product is not 100% healthy
                        if (score < 8) {
                            if (score >= 5) {
                                quantity = Math.max(1, Math.floor(personnes * 0.5)); // 50% for medium healthy
                            } else {
                                quantity = Math.max(1, Math.floor(personnes * 0.3)); // 30% for low healthy
                            }
                        }
                        
                        // Apply stock and affordability limits
                        quantity = Math.min(quantity, maxQty, affordableQty);
                        quantity = Math.max(1, quantity); // Minimum 1
                        
                        const cost = quantity * product.prix;
                        
                        if (cost <= remainingBudget && cost >= 1) {
                            cart[id] = {
                                idproduit: id,
                                nom: product.nom,
                                prix: product.prix,
                                quantite: quantity,
                                type: product.type,
                                score: score,
                                category: category
                            };
                            remainingBudget -= cost;
                            categoryCount[category]++;
                        }
                    }
                }

                // If budget still has significant amount left (>15 DT) and we have less than 4 products, add more variety
                if (remainingBudget > 15 && Object.keys(cart).length < 4) {
                    const remainingProducts = selectedProducts.filter(({ id }) => !cart[id])
                        .filter(({ product }) => product.prix <= remainingBudget / 2)
                        .sort((a, b) => b.score - a.score) // Sort by score first
                        .slice(0, 3); // Take top 3 remaining products
                    
                    for (const { id, product } of remainingProducts) {
                        const category = getCategory(product);
                        
                        // Skip if we already have 2+ products in this category
                        if (category !== 'other' && categoryCount[category] >= 2) continue;
                        
                        const maxQty = getMaxQuantity(product, personnes, budget);
                        
                        // Same logic: quantity = number of persons with exceptions
                        let quantity = personnes;
                        const budgetPerPerson = budget / personnes;
                        const score = getHealthyScore(product);
                        
                        if (budgetPerPerson < 10) {
                            quantity = Math.max(1, Math.floor(personnes * 0.7));
                        }
                        
                        if (score < 8) {
                            quantity = score >= 5 ? Math.max(1, Math.floor(personnes * 0.5)) : Math.max(1, Math.floor(personnes * 0.3));
                        }
                        
                        quantity = Math.min(quantity, maxQty, Math.floor(remainingBudget / product.prix));
                        quantity = Math.max(1, quantity);
                        const cost = quantity * product.prix;
                        
                        if (cost <= remainingBudget && quantity > 0 && cost >= 2) { // Minimum 2 DT for additional items
                            cart[id] = {
                                idproduit: id,
                                nom: product.nom,
                                prix: product.prix,
                                quantite: quantity,
                                type: product.type,
                                score: getHealthyScore(product),
                                category: category
                            };
                            remainingBudget -= cost;
                            categoryCount[category]++;
                        }
                    }
                }

                return cart;
            }

            function displayAICart(cart) {
                const total = Object.values(cart).reduce((sum, item) => sum + (item.quantite * item.prix), 0);
                const itemCount = Object.values(cart).reduce((sum, item) => sum + item.quantite, 0);
                const avgHealthScore = Object.values(cart).reduce((sum, item) => sum + (item.score || 1), 0) / Object.keys(cart).length;
                
                let summary = `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px;">`;
                summary += `<div><strong>${Object.keys(cart).length}</strong> produits healthy</div>`;
                summary += `<div><strong>${itemCount}</strong> articles au total</div>`;
                summary += `<div><strong>${total.toFixed(2)} TND</strong> sur ${budgetInput.value} TND</div>`;
                summary += `<div><strong>Score santé: ${avgHealthScore.toFixed(1)}/10</strong></div>`;
                summary += `</div>`;

                summary += `<div style="border-top: 1px solid #e2e8f0; padding-top: 15px;">`;
                summary += `<div style="margin-bottom: 10px; font-size: 0.9rem; color: #64748b;">`;
                summary += `<strong>🤖 Logique de l'IA:</strong> Quantité = nombre de personnes (${personnesInput.value}) pour produits 100% healthy. Réduction pour budget faible ou produits moins healthy. Boissons énergétiques limitées, chocolat noir en partage.`;
                summary += `</div>`;
                
                // Group by category for better display
                const categorizedItems = {};
                Object.values(cart).forEach(item => {
                    const category = item.category || 'other';
                    if (!categorizedItems[category]) categorizedItems[category] = [];
                    categorizedItems[category].push(item);
                });
                
                Object.entries(categorizedItems).forEach(([category, items]) => {
                    const categoryNames = {
                        drinks: '🥤 Boissons',
                        fruits: '🍎 Fruits',
                        snacks: '🍫 Snacks',
                        supplements: '💊 Suppléments',
                        other: '📦 Autres'
                    };
                    
                    summary += `<div style="margin-bottom: 15px;">`;
                    summary += `<div style="font-size: 0.8rem; color: var(--teal); font-weight: 700; margin-bottom: 5px; text-transform: uppercase;">${categoryNames[category] || category}</div>`;
                    
                    items.forEach(item => {
                        const healthIndicator = item.score >= 8 ? '🟢' : item.score >= 5 ? '🟡' : '🟠';
                        summary += `<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; padding: 6px; background: rgba(15,118,110,0.05); border-radius: 6px; font-size: 0.85rem;">`;
                        summary += `<div style="flex: 1;">`;
                        summary += `<span style="font-weight: 600;">${item.nom}</span>`;
                        summary += `<span style="color: #64748b; margin-left: 6px;">${healthIndicator} ${item.score}/10</span>`;
                        summary += `</div>`;
                        summary += `<div style="text-align: right;">`;
                        summary += `<span style="color: var(--teal); font-weight: 600;">${(item.quantite * item.prix).toFixed(2)} TND</span>`;
                        summary += `<br><span style="color: #64748b; font-size: 0.75rem;">(x${item.quantite})</span>`;
                        summary += `</div>`;
                        summary += `</div>`;
                    });
                    
                    summary += `</div>`;
                });
                
                summary += `</div>`;

                aiCartSummary.innerHTML = summary;
                aiResult.style.display = 'block';
                
                // Store cart for later application
                window.aiGeneratedCart = cart;
            }

            function applyAICartToSession() {
                if (!window.aiGeneratedCart) return;

                // Create form to add all AI cart items at once
                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';

                // Add clear cart action
                const clearInput = document.createElement('input');
                clearInput.name = 'clear_cart';
                clearInput.value = '1';
                form.appendChild(clearInput);

                // Add AI cart items
                Object.values(window.aiGeneratedCart).forEach((item, index) => {
                    const idInput = document.createElement('input');
                    idInput.name = `ai_product_id[${index}]`;
                    idInput.value = item.idproduit;
                    form.appendChild(idInput);

                    const qtyInput = document.createElement('input');
                    qtyInput.name = `ai_quantity[${index}]`;
                    qtyInput.value = item.quantite;
                    form.appendChild(qtyInput);
                });

                // Add apply AI cart flag
                const applyInput = document.createElement('input');
                applyInput.name = 'apply_ai_cart';
                applyInput.value = '1';
                form.appendChild(applyInput);

                document.body.appendChild(form);
                form.submit();
            }

            // Debug functionality
            let debugVisible = false;
            const debugSection = document.getElementById('debugSection');
            const debugContent = document.getElementById('debugContent');
            const toggleDebug = document.getElementById('toggleDebug');

            // Add debug button to the form
            const debugBtn = document.createElement('button');
            debugBtn.id = 'showDebugBtn';
            debugBtn.textContent = '🔍 Voir analyse IA';
            debugBtn.style.cssText = 'background: #64748b; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-left: 10px; font-size: 0.8rem;';
            generateBtn.parentNode.appendChild(debugBtn);

            debugBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!debugVisible) {
                    showProductAnalysis();
                    debugSection.style.display = 'block';
                    debugBtn.textContent = '🔍 Masquer analyse';
                    debugVisible = true;
                } else {
                    debugSection.style.display = 'none';
                    debugBtn.textContent = '🔍 Voir analyse IA';
                    debugVisible = false;
                }
            });

            toggleDebug.addEventListener('click', function() {
                debugSection.style.display = 'none';
                debugBtn.textContent = '🔍 Voir analyse IA';
                debugVisible = false;
            });

            function showProductAnalysis() {
                const products = <?php echo json_encode($products); ?>;
                const availableProducts = Object.entries(products).filter(([id, product]) => 
                    Number(product.en_out_stock) === 1 && Number(product.qte_stock) > 0
                );

                function getHealthyScore(product) {
                    const name = product.nom.toLowerCase();
                    const type = product.type.toLowerCase();
                    let score = 0;
                    
                    const highPriorityHealthy = ['eau', 'jus', 'fruit', 'légume', 'naturel', 'bio', 'healthy', 'fitness', 'energy', 'protéine', 'vitamine', 'minéral', 'antioxydant', 'thé', 'infusion'];
                    highPriorityHealthy.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score += 10;
                    });
                    
                    const mediumPriorityHealthy = ['boisson', 'smoothie', 'barre énergétique', 'noix', 'fruit sec', 'yaourt', 'compote', 'chocolat noir'];
                    mediumPriorityHealthy.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score += 5;
                    });
                    
                    const lowPriorityAcceptable = ['céréale', 'muesli', 'granola'];
                    lowPriorityAcceptable.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score += 2;
                    });
                    
                    const unhealthyKeywords = ['chocolat blanc', 'chocolat au lait', 'bonbon', 'sucrerie', 'gâteau', 'biscuit', 'chips', 'soda', 'alcool', 'frit', 'gras', 'sucre', 'monster', 'red bull', 'boisson énergétique'];
                    unhealthyKeywords.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score -= 15;
                    });
                    
                    const mediumUnhealthy = ['cola', 'limonade', 'sirop'];
                    mediumUnhealthy.forEach(keyword => {
                        if (name.includes(keyword) || type.includes(keyword)) score -= 5;
                    });
                    
                    if (product.prix <= 5) score += 2;
                    
                    return score;
                }

                let content = `<div style="max-height: 300px; overflow-y: auto;">`;
                content += `<table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">`;
                content += `<thead><tr style="background: #f8fafc;"><th style="padding: 8px; border: 1px solid #e2e8f0; text-align: left;">Produit</th><th style="padding: 8px; border: 1px solid #e2e8f0;">Prix</th><th style="padding: 8px; border: 1px solid #e2e8f0;">Stock</th><th style="padding: 8px; border: 1px solid #e2e8f0;">Score Santé</th><th style="padding: 8px; border: 1px solid #e2e8f0;">Évaluation</th></tr></thead>`;
                content += `<tbody>`;

                availableProducts.forEach(([id, product]) => {
                    const score = getHealthyScore(product);
                    const evaluation = score >= 8 ? '🟢 Excellent' : score >= 5 ? '🟡 Bon' : score >= 0 ? '🟠 Moyen' : '🔴 Non healthy';
                    const rowStyle = score >= 8 ? 'background: rgba(16,185,129,0.1);' : score >= 5 ? 'background: rgba(245,158,11,0.1);' : score >= 0 ? 'background: rgba(249,115,22,0.1);' : 'background: rgba(239,68,68,0.1);';
                    
                    content += `<tr style="${rowStyle}">`;
                    content += `<td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 600;">${product.nom}</td>`;
                    content += `<td style="padding: 8px; border: 1px solid #e2e8f0;">${product.prix} TND</td>`;
                    content += `<td style="padding: 8px; border: 1px solid #e2e8f0;">${product.qte_stock}</td>`;
                    content += `<td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 600;">${score}/10</td>`;
                    content += `<td style="padding: 8px; border: 1px solid #e2e8f0;">${evaluation}</td>`;
                    content += `</tr>`;
                });

                content += `</tbody></table>`;
                content += `<p style="margin-top: 10px; font-size: 0.8rem;"><strong>💡 Conseils:</strong> L'IA privilégie les produits avec score ≥5. Les produits avec score négatif sont exclus. Un score élevé indique un produit très healthy.</p>`;
                content += `</div>`;

                debugContent.innerHTML = content;
            }
        });
    </script>
</body>
</html>
