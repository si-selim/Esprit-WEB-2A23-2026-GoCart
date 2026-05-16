<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
require_once __DIR__ . '/../lang.php';
include '../../../Controller/CommandeController.php';
include '../../../Controller/LigneCommandeController.php';
include '../../../Controller/ProduitController.php';
include '../../../Controller/FavoriteController.php';
include '../../../Controller/UserController.php';
include '../../../Controller/StandController.php';
include '../../../Controller/ParcoursController.php';
include '../../../Controller/MarathonController.php';

$prodCtrl = new ProduitController();
$favCtrl = new FavoriteController();
$userCtrl = new UserController();
$standCtrl = new StandController();
$pCtrl = new ParcoursController();
$mCtrl = new MarathonController();

$stand_id = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : null;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : null;
if (!$stand_id) {
    die('Stand ID requis.');
}

// Detect if the marathon linked to this stand is finished
$marathonTermine = false;
try {
    if ($parcours_id) {
        $parcours = $pCtrl->showParcours($parcours_id);
        if ($parcours && !empty($parcours['id_marathon'])) {
            $marathon = $mCtrl->showMarathon($parcours['id_marathon']);
            if ($marathon && !empty($marathon['date_marathon'])) {
                $marathonTermine = strtotime($marathon['date_marathon']) < strtotime('today');
            }
        }
    }
} catch (Exception $e) {
    $marathonTermine = false;
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
$userId = $user['id_user'] ?? $user['id'];
$userFavorites = $favCtrl->getFavoritesByUser($userId);
$favoriteIds = array_column($userFavorites, 'ID_produit');

$dbUser = $userCtrl->showUser($userId);
$nbreCommande = isset($dbUser['nbre_commande']) ? (int)$dbUser['nbre_commande'] : 0;
$isFirstOrder = ($nbreCommande === 0);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$success = '';
$cart = &$_SESSION['cart'];

// Normalisation du panier : s'assurer que chaque item a un nom et un prix
// Utile si le produit a été ajouté depuis un autre stand ou via les favoris
foreach ($cart as $id => &$item) {
    if (!isset($item['nom']) || !isset($item['prix'])) {
        $p = $prodCtrl->getProduit($id);
        if ($p) {
            $item['nom'] = $p['nom_produit'] ?? $p['Nom_produit'] ?? 'Inconnu';
            $item['prix'] = $p['prix_produit'] ?? $p['Prix_produit'] ?? 0;
        } else {
            unset($cart[$id]);
        }
    }
}
unset($item);

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
                    $commande = new Commande(null, $userId, $stand_id, null, date('Y-m-d H:i:s'), 'paye', $total);
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

            if ($qty > 0) {
                if (isset($cart[$id])) {
                    // Si le produit est déjà dans le panier, on peut mettre à jour sa quantité
                    // même s'il n'est pas dans le stand actuel ($products)
                    $cart[$id]['quantite'] = $qty;
                    
                    // On vérifie quand même le stock si le produit est dans la liste du stand actuel
                    if (isset($products[$id])) {
                        $item = $products[$id];
                        if ($qty > $item['qte_stock']) {
                            $cart[$id]['quantite'] = $item['qte_stock'];
                            $message = "Quantité limitée à {$item['qte_stock']} pour {$item['nom']}.";
                        }
                    }
                }
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
$discount = 0;
if ($isFirstOrder && $cartTotal > 0) {
    $discount = $cartTotal * 0.10;
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
        
        .btn-add-product {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; background: linear-gradient(135deg,#0f766e,#14b8a6);
            color: white; border-radius: 14px; font-weight: 700; text-decoration: none;
            font-size: 0.95rem; transition: transform .15s, box-shadow .15s;
            box-shadow: 0 5px 16px rgba(15,118,110,.22);
        }
        .btn-add-product:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(16,185,129,0.4); }

        .favorite-btn {
            position: absolute;
            top: 15px;
            left: 20px;
            z-index: 10;
            background: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            color: #cbd5e1;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        .favorite-btn:hover {
            transform: scale(1.1);
            color: #ef4444;
        }
        .favorite-btn.is-favorite {
            color: #ef4444;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
        }

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

    <?php if ($role === 'participant'): ?>
    <!-- Bouton favoris déplacé vers la section produits -->
    <?php endif; ?>
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
            <h2 style="color: var(--ink); font-size: 2rem; font-weight: 900; margin-bottom: 10px;">🥦 Dr. Brocoli, l'IA Nutritionniste</h2>
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
                <button id="generateCartBtn" style="background: <?php echo $marathonTermine ? 'linear-gradient(135deg, #94a3b8, #cbd5e1)' : 'linear-gradient(135deg, var(--teal), #0d665e)'; ?>; color: <?php echo $marathonTermine ? '#94a3b8' : 'white'; ?>; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; cursor: <?php echo $marathonTermine ? 'not-allowed' : 'pointer'; ?>; transition: all 0.3s; box-shadow: <?php echo $marathonTermine ? 'none' : '0 4px 15px rgba(15,118,110,0.2)'; ?>; opacity: <?php echo $marathonTermine ? '0.65' : '1'; ?>;"
                    <?php if ($marathonTermine): ?>disabled title="Marathon terminé — fonctionnalité indisponible"<?php endif; ?>>
                    🧠 Générer le panier
                </button>
            </div>
        </div>

        <!-- Loader -->
        <div id="aiLoader" style="display: none; text-align: center; margin-top: 20px; padding: 30px;">
            <div style="display: inline-block; width: 50px; height: 50px; border: 5px solid rgba(15,118,110,0.2); border-radius: 50%; border-top-color: var(--teal); animation: spin 1s ease-in-out infinite;"></div>
            <p style="color: var(--teal); font-weight: 700; margin-top: 15px;">L'IA Groq analyse les meilleurs produits pour votre marathon...</p>
        </div>
        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

        <div id="aiResult" style="display: none; background: white; border-radius: 16px; padding: 25px; margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid rgba(15,118,110,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <h3 style="color: var(--ink); font-size: 1.4rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.8rem;">🏃‍♂️</span> Panier Intelligent Marathon
                </h3>
                <div style="text-align: right;">
                    <div style="font-size: 0.9rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Score Marathon</div>
                    <div id="aiScore" style="font-size: 1.8rem; font-weight: 900; color: var(--teal);">9/10</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border-left: 4px solid var(--teal);">
                    <div style="font-weight: 800; color: var(--ink); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-lightbulb" style="color:var(--sun)"></i> L'avis de l'Expert</div>
                    <div id="aiExplanation" style="color: #475569; font-size: 0.95rem; line-height: 1.5;"></div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="background: #fff7ed; padding: 15px; border-radius: 12px; border: 1px solid #ffedd5;">
                        <div style="font-weight: 800; color: #ea580c; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-fire"></i> Énergie & Hydratation</div>
                        <div id="aiCalories" style="color: #9a3412; font-size: 0.9rem; font-weight: 600;"></div>
                    </div>
                    <div style="background: #ecfdf5; padding: 15px; border-radius: 12px; border: 1px solid #d1fae5;">
                        <div style="font-weight: 800; color: #059669; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-apple-whole"></i> Conseil Nutrition</div>
                        <div id="aiRecommendations" style="color: #065f46; font-size: 0.9rem; font-weight: 600;"></div>
                    </div>
                </div>
            </div>

            <h4 style="color: var(--ink); margin-bottom: 15px; font-size: 1.1rem; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px;">Votre sélection personnalisée :</h4>
            <div id="aiCartSummary" style="margin-bottom: 25px;"></div>
            
            <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                <div style="text-align: right; margin-right: 20px;">
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Estimé</div>
                    <div id="aiTotal" style="font-size: 1.5rem; font-weight: 900; color: var(--ink);">0.00 TND</div>
                </div>
                <button id="applyAiCartBtn" style="background: linear-gradient(135deg, var(--teal), #0d665e); color: white; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(15,118,110,0.2);">
                    🛒 Remplacer mon panier par cette sélection
                </button>
            </div>
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
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; margin-bottom: 30px;">
                        <h2 class="section-title-center" style="margin: 0;">Produits Populaires</h2>
                        <?php if ($role === 'participant'): ?>
                        <div style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); display: flex; gap: 10px;">
                            <button id="show-all-btn" onclick="showAllProducts()"
                                   title="Tous les produits"
                                   style="background: #6b7280; width: 55px; height: 55px;
                                          border-radius: 50%; display: none;
                                          align-items: center; justify-content: center;
                                          box-shadow: 0 8px 20px rgba(107, 114, 128, 0.3);
                                          transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); border: 3px solid white; text-decoration: none; cursor: pointer;">
                                <i class="fa-solid fa-th" style="color: white; font-size: 20px;"></i>
                            </button>
                            <button id="floating-fav-btn" onclick="showFavorites()"
                                   title="Mes Favoris"
                                   style="background: #ff5e57; width: 55px; height: 55px;
                                          border-radius: 50%; display: flex;
                                          align-items: center; justify-content: center;
                                          box-shadow: 0 8px 20px rgba(255, 94, 87, 0.3);
                                          transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); border: 3px solid white; text-decoration: none; cursor: pointer;">
                                <i class="fa-solid fa-heart" style="color: white; font-size: 24px;"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
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

                                <?php if ($role === 'participant'): ?>
                                <button class="favorite-btn <?php echo in_array($id, $favoriteIds) ? 'is-favorite' : ''; ?>" 
                                        onclick="toggleFavorite(event, <?php echo $id; ?>)" 
                                        title="<?php echo in_array($id, $favoriteIds) ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                    <i class="<?php echo in_array($id, $favoriteIds) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                                </button>
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
                                
                                <?php if ($isFirstOrder && $cartTotal > 0): ?>
                                    <div class="cart-total" style="color: #64748b; font-size: 1.1rem; border-top: none; padding-top: 15px; margin-top: 15px; border-top: 2px solid #f1f5f9;">
                                        Sous-total: <span><?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</span>
                                    </div>
                                    <div class="cart-total" style="color: #10b981; font-size: 1.1rem; border-top: none; padding-top: 5px; margin-top: 0;">
                                        Remise 1ère commande (-10%) : -<?php echo number_format($discount, 2, ',', ' '); ?> TND
                                    </div>
                                    <div class="cart-total" style="border-top: none; padding-top: 5px; margin-top: 0;">Total à payer : <?php echo number_format($cartTotal - $discount, 2, ',', ' '); ?> TND</div>
                                <?php else: ?>
                                    <div class="cart-total"><?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</div>
                                <?php endif; ?>
                                
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

        // AI Agent functionality (Groq API)
        document.addEventListener('DOMContentLoaded', function () {
            const budgetInput = document.getElementById('budget');
            const personnesInput = document.getElementById('personnes');
            const generateBtn = document.getElementById('generateCartBtn');
            const aiLoader = document.getElementById('aiLoader');
            const aiResult = document.getElementById('aiResult');
            const applyAiCartBtn = document.getElementById('applyAiCartBtn');

            // Focus effects
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
                const standId = <?php echo $stand_id; ?>;

                if (!budget || budget <= 0) {
                    alert('Veuillez saisir un budget valide supérieur à 0 TND.');
                    budgetInput.focus();
                    return;
                }
                
                if (!personnes || personnes <= 0 || personnes > 50) {
                    alert('Veuillez saisir un nombre de personnes valide (1-50).');
                    personnesInput.focus();
                    return;
                }

                // UI Update
                generateBtn.disabled = true;
                generateBtn.style.opacity = '0.7';
                aiResult.style.display = 'none';
                aiLoader.style.display = 'block';

                fetch('generate_ai_cart_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ budget: budget, personnes: personnes, stand_id: standId })
                })
                .then(response => response.json())
                .then(data => {
                    aiLoader.style.display = 'none';
                    generateBtn.disabled = false;
                    generateBtn.style.opacity = '1';

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    displayAICart(data);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    aiLoader.style.display = 'none';
                    generateBtn.disabled = false;
                    generateBtn.style.opacity = '1';
                    alert('Une erreur est survenue lors de la communication avec l\'IA.');
                });
            });

            function displayAICart(data) {
                document.getElementById('aiExplanation').textContent = data.explication || '';
                document.getElementById('aiCalories').textContent = data.calories_estimees || '';
                document.getElementById('aiRecommendations').textContent = data.recommandations || '';
                
                const score = data.score_marathon || 0;
                const scoreColor = score >= 8 ? 'var(--teal)' : score >= 5 ? 'var(--sun)' : 'var(--coral)';
                const scoreEl = document.getElementById('aiScore');
                scoreEl.textContent = `${score}/10`;
                scoreEl.style.color = scoreColor;

                document.getElementById('aiTotal').textContent = `${(data.total || 0).toFixed(2)} TND`;

                const summaryContainer = document.getElementById('aiCartSummary');
                let html = '';

                if (data.produits && data.produits.length > 0) {
                    data.produits.forEach(item => {
                        html += `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(15,118,110,0.03); border-radius: 8px; margin-bottom: 8px; border: 1px solid rgba(15,118,110,0.08);">
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: var(--ink); font-size: 1.05rem;">${item.nom}</div>
                                <div style="color: #64748b; font-size: 0.85rem; font-style: italic; margin-top: 4px;">" ${item.raison} "</div>
                            </div>
                            <div style="text-align: right; margin-left: 20px;">
                                <div style="color: var(--teal); font-weight: 800; font-size: 1.1rem;">${(item.quantite * item.prix).toFixed(2)} TND</div>
                                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; background: #e2e8f0; display: inline-block; padding: 2px 8px; border-radius: 10px; margin-top: 4px;">x${item.quantite}</div>
                            </div>
                        </div>`;
                    });
                } else {
                    html = '<p style="color: var(--coral);">Aucun produit sélectionné.</p>';
                }

                summaryContainer.innerHTML = html;
                aiResult.style.display = 'block';

                // Save data for apply button
                window.aiGeneratedCartData = data.produits;
            }

            applyAiCartBtn.addEventListener('click', function() {
                if (!window.aiGeneratedCartData || window.aiGeneratedCartData.length === 0) return;

                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';

                const applyInput = document.createElement('input');
                applyInput.name = 'apply_ai_cart';
                applyInput.value = '1';
                form.appendChild(applyInput);

                window.aiGeneratedCartData.forEach((item, index) => {
                    const idInput = document.createElement('input');
                    idInput.name = `ai_product_id[${index}]`;
                    idInput.value = item.idproduit;
                    form.appendChild(idInput);

                    const qtyInput = document.createElement('input');
                    qtyInput.name = `ai_quantity[${index}]`;
                    qtyInput.value = item.quantite;
                    form.appendChild(qtyInput);
                });

                document.body.appendChild(form);
                form.submit();
            });
        });
        function toggleFavorite(event, productId) {
            event.stopPropagation();
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            
            const formData = new FormData();
            formData.append('id_produit', productId);
            formData.append('action', 'toggle');

            fetch('toggleFavoriteAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'added') {
                    btn.classList.add('is-favorite');
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                    btn.title = 'Retirer des favoris';
                    
                    // Afficher le bouton flottant s'il existe
                    const floatBtn = document.getElementById('floating-fav-btn');
                    if (floatBtn) {
                        floatBtn.style.display = 'flex';
                        floatBtn.style.transform = 'translateY(-50%) scale(1.2)';
                        setTimeout(() => floatBtn.style.transform = 'translateY(-50%) scale(1)', 200);
                    }
                } else if (data === 'removed') {
                    btn.classList.remove('is-favorite');
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                    btn.title = 'Ajouter aux favoris';
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function showFavorites() {
            // Récupérer les favoris actuels via AJAX
            fetch('getFavoritesAjax.php')
                .then(response => response.json())
                .then(favoriteIds => {
                    console.log('Favorite IDs from AJAX:', favoriteIds);
                    
                    const productCards = document.querySelectorAll('.product-card-item');
                    console.log('Found product cards:', productCards.length);
                    
                    productCards.forEach(card => {
                        const productId = parseInt(card.dataset.productId);
                        console.log('Checking product ID:', productId, 'Is favorite:', favoriteIds.includes(productId));
                        
                        if (favoriteIds.includes(productId)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    // Mettre à jour le titre
                    const title = document.querySelector('.section-title-center');
                    if (title) {
                        title.textContent = 'Mes Favoris';
                    }
                    
                    // Mettre à jour les boutons
                    const showAllBtn = document.getElementById('show-all-btn');
                    if (showAllBtn) showAllBtn.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching favorites:', error);
                });
        }

        function showAllProducts() {
            const productCards = document.querySelectorAll('.product-card-item');
            
            productCards.forEach(card => {
                card.style.display = 'block';
            });
            
            // Mettre à jour le titre
            const title = document.querySelector('.section-title-center');
            if (title) {
                title.textContent = 'Produits Populaires';
            }
            
            // Mettre à jour les boutons
            const showAllBtn = document.getElementById('show-all-btn');
            const favBtn = document.getElementById('floating-fav-btn');
            if (showAllBtn) showAllBtn.style.display = 'none';
            if (favBtn) favBtn.style.display = 'flex';
        }
    </script>
</body>
</html>