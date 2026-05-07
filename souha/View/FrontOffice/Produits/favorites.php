<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
include '../../../Controller/CommandeController.php';
include '../../../Controller/LigneCommandeController.php';
include '../../../Controller/ProduitController.php';
include '../../../Controller/FavoriteController.php';

$prodCtrl = new ProduitController();
$favCtrl = new FavoriteController();
$user = getCurrentUser();

if (!$user) {
    header('Location: ../login.php');
    exit;
}

$role = $user['role'] ?? 'visiteur';
$userId = $user['id_user'] ?? $user['id'];

// Initialisation du panier si inexistant
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = &$_SESSION['cart'];

// Normalisation du panier : s'assurer que chaque item a un nom et un prix
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
$message = '';
$success = '';

// Récupération des favoris
$rawFavorites = $favCtrl->getFavoritesByUser($userId);
$products = [];
foreach ($rawFavorites as $p) {
    $id = $p['ID_produit'];
    $products[$id] = [
        'nom' => $p['nom_produit'],
        'prix' => $p['prix_produit'],
        'qte_stock' => $p['qte_stock'],
        'en_out_stock' => $p['en_out_stock'],
        'image' => $p['image'],
        'type' => $p['type'],
        'id_stand' => $p['ID_stand']
    ];
}

// Logique du panier (copiée de produit.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'], $_POST['product_id'], $_POST['quantity'])) {
        $productId = (int) $_POST['product_id'];
        $quantity = max(0, (int) $_POST['quantity']);

        if (!isset($products[$productId])) {
            // Le produit n'est peut-être pas dans les favoris mais on peut quand même l'ajouter s'il existe en DB
            $p = $prodCtrl->getProduit($productId);
            if ($p) {
                $products[$productId] = [
                    'nom' => $p['nom_produit'],
                    'prix' => $p['prix_produit'],
                    'qte_stock' => $p['qte_stock'],
                    'en_out_stock' => $p['en_out_stock'],
                    'image' => $p['image'],
                    'type' => $p['type'],
                    'id_stand' => $p['ID_stand']
                ];
            }
        }

        if (isset($products[$productId])) {
            $item = $products[$productId];
            if (!$item['en_out_stock'] || $item['qte_stock'] <= 0) {
                unset($cart[$productId]);
                $message = "Le produit {$item['nom']} est en rupture de stock.";
            } else {
                if ($quantity > $item['qte_stock']) {
                    $quantity = $item['qte_stock'];
                    $message = "Quantité limitée au stock ({$item['qte_stock']}).";
                }
                if ($quantity <= 0) {
                    unset($cart[$productId]);
                } else {
                    $cart[$productId] = [
                        'idproduit' => $productId,
                        'nom' => $item['nom'],
                        'prix' => $item['prix'],
                        'quantite' => $quantity,
                        'id_stand' => $item['id_stand']
                    ];
                    $success = "Panier mis à jour.";
                }
            }
        }
    }

    if (isset($_POST['delete_product_id'])) {
        unset($cart[(int)$_POST['delete_product_id']]);
        $success = 'Produit supprimé.';
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $cart = &$_SESSION['cart'];
        $success = 'Panier vidé.';
    }

    if (isset($_POST['validate_order'])) {
        if (empty($cart)) {
            $message = 'Panier vide.';
        } else {
            // Pour simplifier, on prend le stand du premier produit du panier
            $firstItem = reset($cart);
            $stand_id = $firstItem['id_stand'] ?? 0;
            $total = 0;
            foreach ($cart as $item) { $total += $item['quantite'] * $item['prix']; }
            
            header('Location: ../paiement.php?type=commande&id=0&montant=' . $total . '&stand_id=' . $stand_id);
            exit;
        }
    }
}

$cartTotal = 0;
foreach ($cart as $item) { $cartTotal += $item['quantite'] * $item['prix']; }
$currentPage = 'favoris';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:#f8fafc; }
        .page { width:min(1200px,calc(100% - 32px)); margin:0 auto; padding:28px 0 60px; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.05); font-size:0.92rem; transition:transform .2s; }
        .back-link:hover { transform:translateY(-2px); }

        .hero { background: white; border-radius: 24px; padding: 40px 60px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin-bottom: 40px; }
        .hero-text h1 { font-size: 2.5rem; color: var(--ink); font-weight: 900; text-transform: uppercase; margin-bottom: 10px; }
        .hero-text p { color: #64748b; font-size: 1.1rem; }
        .hero-icon { 
            font-size: 5rem; 
            color: #ef4444; 
            filter: drop-shadow(0 10px 15px rgba(239, 68, 68, 0.3)); 
            animation: heartbeat 1.5s infinite ease-in-out;
            display: inline-block;
        }

        @keyframes heartbeat {
            0% { transform: scale(1); }
            14% { transform: scale(1.3); }
            28% { transform: scale(1); }
            42% { transform: scale(1.15); }
            70% { transform: scale(1); }
        }

        .grid-layout { display: grid; grid-template-columns: 3fr 1fr; gap: 30px; }

        .product-grid { display: flex; flex-wrap: wrap; gap: 25px; justify-content: flex-start; }
        .product-card-item { 
            background: white; 
            border-radius: 20px;
            padding: 20px; 
            text-align: center; 
            width: 220px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            position: relative;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }
        .product-card-item:hover { transform: translateY(-10px) scale(1.05); box-shadow: 0 15px 35px rgba(15,118,110,0.1); }
        .product-card-item.out-of-stock { opacity: 0.6; cursor: not-allowed; }
        
        .favorite-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #fff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            color: #ef4444;
            cursor: pointer;
            border: none;
            z-index: 5;
        }
        
        .product-img { 
            width: 140px; 
            height: 140px; 
            background: #f1f5f9; 
            border-radius: 50%; 
            margin: 0 auto 15px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 3rem; 
            overflow: hidden;
            border: 3px solid #e2e8f0;
        }
        .product-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .product-name { font-weight: 700; font-size: 1rem; color: var(--ink); margin-bottom: 5px; }
        .product-price { color: var(--teal); font-weight: 800; font-size: 0.95rem; }
        
        /* Sidebar Cart */
        .cart-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); position: sticky; top: 100px; }
        .cart-card h2 { font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; color: var(--ink); border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 15px; font-weight: 800; }
        .cart-item { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 0; border-bottom: 1px dashed #e2e8f0; }
        .cart-item-name { font-weight:700; color:var(--ink); font-size: 0.9rem; }
        .cart-item-price { color:var(--teal); font-weight: 600; font-size: 0.85rem; }
        .cart-qty-input { width: 50px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 5px; text-align: center; font-weight: 600; }
        .btn-delete-cart { background:#fef2f2; color:#ef4444; border: 1px solid #fecaca; padding: 5px 8px; border-radius: 6px; cursor: pointer; }
        .cart-total { margin-top:15px; font-weight:900; color:var(--ink); font-size: 1.2rem; text-align: right; border-top: 2px solid #f1f5f9; padding-top: 10px; }
        
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:999px; padding:12px 20px; font-weight:800; cursor:pointer; text-transform: uppercase; font-size: 0.8rem; width: 100%; margin-top: 10px; transition: all 0.2s; }
        .btn-primary { background:var(--teal); color:#fff; }
        .btn-danger { background:#fef2f2; color:var(--coral); border:1px solid var(--coral); }
        
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 24px; grid-column: 1 / -1; }
        .message { padding:15px; border-radius:12px; margin-bottom:20px; font-weight:600; font-size:0.9rem; }
        .message.success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        .message.error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

        @media (max-width: 900px) { .grid-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="../listMarathons.php">← Retour au catalogue</a>

    <section class="hero">
        <div class="hero-text">
            <h1>Mes Favoris</h1>
            <p>Cliquez sur un produit pour l'ajouter directement à votre panier.</p>
        </div>
        <div class="hero-icon">❤️</div>
    </section>

    <?php if ($message): ?><div class="message error"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>

    <div class="grid-layout">
        <div class="product-grid">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-heart" style="font-size:3rem; color:#cbd5e1; margin-bottom:15px; display:block;"></i>
                    <h3>Aucun favori pour le moment</h3>
                    <p style="margin-bottom:20px;">Parcourez le catalogue pour ajouter des coups de cœur !</p>
                    <a href="../listMarathons.php" class="btn btn-primary" style="width:auto;">Voir le catalogue</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $id => $p): ?>
                    <div class="product-card-item <?php echo $p['en_out_stock'] ? '' : 'out-of-stock'; ?>" 
                         id="fav-card-<?php echo $id; ?>" 
                         onclick="addToCart(<?php echo $id; ?>)">
                        <button class="favorite-btn" onclick="toggleFav(event, <?php echo $id; ?>)" title="Retirer des favoris">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                        <div class="product-img">
                            <?php if ($p['image'] && file_exists(__DIR__ . '/../../../uploads/produits/' . $p['image'])): ?>
                                <img src="../../../uploads/produits/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['nom']); ?>">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>
                        <div class="product-name"><?php echo htmlspecialchars($p['nom']); ?></div>
                        <div class="product-price"><?php echo number_format($p['prix'], 2, ',', ' '); ?> TND</div>
                        <?php if (!$p['en_out_stock']): ?><div style="color:var(--coral); font-size:0.7rem; font-weight:800; margin-top:5px;">EPUISE</div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-sidebar">
            <div class="cart-card">
                <h2>🛒 Votre Panier</h2>
                <?php if (empty($cart)): ?>
                    <p style="color:#64748b; text-align:center; padding:15px; font-style:italic; font-size:0.9rem;">Le panier est vide.</p>
                <?php else: ?>
                    <form method="post" id="cartForm">
                        <?php foreach ($cart as $id => $item): ?>
                            <div class="cart-item">
                                <div>
                                    <div class="cart-item-name"><?php echo htmlspecialchars($item['nom']); ?></div>
                                    <div class="cart-item-price"><?php echo number_format($item['prix'], 2, ',', ' '); ?> TND</div>
                                </div>
                                <div style="display:flex; align-items:center; gap:5px;">
                                    <input type="number" name="qty_dummy" value="<?php echo $item['quantite']; ?>" class="cart-qty-input" readonly>
                                    <button type="submit" name="delete_product_id" value="<?php echo $id; ?>" class="btn-delete-cart">✕</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="cart-total"><?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</div>
                        <button type="submit" name="validate_order" class="btn btn-primary">Commander</button>
                        <button type="submit" name="clear_cart" class="btn btn-danger">Vider</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function addToCart(productId) {
        const form = document.createElement('form');
        form.method = 'post';
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

    function toggleFav(event, productId) {
        event.stopPropagation();
        const card = document.getElementById('fav-card-' + productId);
        
        const formData = new FormData();
        formData.append('id_produit', productId);
        formData.append('action', 'toggle');

        fetch('toggleFavoriteAjax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'removed') {
                card.style.transform = 'scale(0)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html>
