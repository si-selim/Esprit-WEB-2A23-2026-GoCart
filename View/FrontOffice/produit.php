<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
include '../../Controller/CommandeController.php';
include '../../Controller/LigneCommandeController.php';
include '../../Controller/ProduitController.php';

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
    $productsAssoc[$prod['id_produit']] = [
        'nom' => $prod['nom_produit'],
        'prix' => $prod['prix_produit'],
        'qte_stock' => $prod['qte_stock'],
        'en_out_stock' => $prod['en_out_stock']
    ];
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
    header('Location: login.php');
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
    if (isset($_POST['update_quantity'], $_POST['product_id'], $_POST['quantity'])) {
        $productId = (int) $_POST['product_id'];
        $quantity = max(0, (int) $_POST['quantity']);

        if (!isset($products[$productId])) {
            $message = 'Produit invalide.';
        } else {
            $item = $products[$productId];
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
                $commandeC = new CommandeController();
                $ligneC = new LigneCommandeController();
                $userId = $user['id_user'] ?? $user['id'];
                $commande = new Commande(null, $userId, $stand_id, date('Y-m-d H:i:s'), 'paye', $total);
                $newCommandeId = $commandeC->addCommande($commande);

                if ($newCommandeId) {
                    foreach ($cart as $item) {
                        $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                        $ligneC->addLigneCommande($ligne);
                    }

                    $_SESSION['cart'] = [];
                    header('Location: produit.php?created=' . urlencode($newCommandeId) . '&success=' . urlencode('Commande gratuite validée !'));
                    exit;
                }

                $message = 'Erreur lors de la création de la commande. Veuillez réessayer.';
            } else {
                // Redirection vers paiement
                header('Location: paiement.php?type=commande&id=0&montant=' . $total . '&stand_id=' . $stand_id);
                exit;
            }
        }
    }

    if (isset($_POST['update_cart']) && !isset($_POST['delete_product_id']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = (int) $id;
            $qty = max(0, (int) $qty);
            if ($qty > 0 && isset($products[$id])) {
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
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }

        .hero { background:linear-gradient(135deg,#102a43,#0f766e); color:#fff; border-radius:28px; padding:28px; box-shadow:0 20px 40px rgba(16,42,67,.16); margin-bottom:28px; }
        .hero h1 { font-size:1.6rem; margin-bottom:8px; }
        .hero p { opacity:.9; }

        .panel { background:#f3fbff; border-radius:28px; padding:24px; box-shadow:0 18px 40px rgba(16,42,67,.08); overflow:hidden; }
        .grid { display:grid; grid-template-columns:2fr 1fr; gap:24px; align-items:start; }
        .product-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px; }
        .product-card-item { background:#fff; border:1px solid #cbd5e1; border-radius:18px; padding:18px; text-align:center; cursor:pointer; transition:transform .2s; }
        .product-card-item:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.1); }
        .product-img { width:100px; height:100px; background:#f0f0f0; border-radius:10px; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#ccc; }
        .product-name { font-weight:bold; margin-bottom:5px; }
        .product-price { color:#0f766e; font-weight:bold; margin-bottom:5px; }
        .product-stock { font-size:0.9rem; color:#059669; }
        .product-out { font-size:0.9rem; color:#e76f51; }
        .product-card, .cart-card { background:#ffffff; border-radius:24px; padding:18px; border:1px solid #cbd5e1; box-shadow:0 10px 24px rgba(15, 118, 110, .08); }
        .product-card h2, .cart-card h2 { margin-top:0; font-size:1.2rem; color:#0f2f3a; font-weight:900; }
        .product-item { background:#fff; border:1px solid #cbd5e1; border-radius:18px; padding:18px; margin-bottom:16px; color: #000; }
        .product-item:last-child { margin-bottom:0; }
        .product-item strong { display:block; margin-bottom:6px; font-size:1.05rem; }
        input[type=number] { width:80px; border-radius:12px; border:1px solid #cbd5e1; padding:10px; }
        .field-error { display:block; min-height:1.2em; margin-top:6px; color:#b91c1c; font-size:0.85rem; }
        .cart-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; background:#eef8ff; border:1px solid #d6e8f0; border-radius:16px; margin-bottom:12px; }
        .cart-item-info { display:flex; flex-direction:column; gap:5px; }
        .cart-item-name { font-weight:700; color:#102a43; }
        .cart-item-price { color:#0f766e; }
        .cart-item-actions { display:flex; align-items:center; gap:10px; }
        .btn-sm { padding:8px 12px; font-size:0.85rem; }
        .btn-delete { background:#ef4444; color:#fff; }
        .btn-delete:hover { opacity:.9; }
        .cart-total { margin-top:14px; font-weight:800; color:#102a43; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:14px; padding:12px 16px; font-weight:700; cursor:pointer; }
        .btn-primary { background:#0f766e; color:#fff; }
        .btn-secondary { background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; }
        .btn-danger { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
        .message { padding:16px; border-radius:18px; margin-bottom:18px; }
        .message.success { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .message.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        @keyframes fadeOut { 0% { opacity:1; } 100% { opacity:0; } }
        .message.fade-out { animation: fadeOut .5s ease-in forwards; }
        @media (max-width:960px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="detailParcours.php?id=<?php echo $parcours_id; ?>">← Retour au parcours</a>

    <section class="hero">
        <h1><?php echo $stand_name ? "Produits du $stand_name" : 'Catalogue produits'; ?></h1>
        <p><?php echo $stand_name ? "Découvrez les produits disponibles à ce stand." : 'Choisissez vos produits et définissez les quantités. Cliquez sur Actualiser le nombre pour mettre à jour le panier.'; ?></p>
    </section>

    <section class="panel">
            <?php if ($message !== ''): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="grid">
                <div class="product-card">
                    <h2>Produits</h2>
                    <div class="product-grid">
                        <?php foreach ($products as $id => $product): ?>
                            <div class="product-card-item" onclick="addToCart(<?php echo $id; ?>)">
                                <div class="product-img">📦</div>
                                <div class="product-name"><?php echo htmlspecialchars($product['nom']); ?></div>
                                <div class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> TND</div>
                                <div class="<?php echo $product['en_out_stock'] ? 'product-stock' : 'product-out'; ?>">
                                    <?php echo $product['en_out_stock'] ? 'En stock' : 'Épuisé'; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cart-card">
                    <h2>Panier actuel</h2>
                    <?php if (empty($cart)): ?>
                        <p>Votre panier est vide.</p>
                    <?php else: ?>
                        <form method="post" id="cartForm">
                            <input type="hidden" name="update_cart" value="1">
                            <?php foreach ($cart as $id => $item): ?>
                                <div class="cart-item">
                                    <div class="cart-item-info">
                                        <span class="cart-item-name"><?php echo htmlspecialchars($item['nom']); ?></span>
                                        <span class="cart-item-price"><?php echo number_format($item['prix'], 2, ',', ' '); ?> TND</span>
                                    </div>
                                    <div class="cart-item-actions">
                                        <input type="number" name="quantities[<?php echo $id; ?>]" value="<?php echo htmlspecialchars($item['quantite']); ?>" min="0" max="<?php echo $products[$id]['qte_stock'] ?? 999; ?>" class="cart-qty-input" />
                                        <button type="submit" name="delete_product_id" value="<?php echo $id; ?>" class="btn btn-sm btn-delete">✕</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="cart-total">Total: <?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</div>
                            <div style="margin-top:16px; display:flex; gap:12px; flex-wrap:wrap;">
                                <button type="submit" name="validate_order" class="btn btn-primary">Valider commande</button>
                                <button type="submit" name="clear_cart" class="btn btn-danger">Vider le panier</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
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
    </script>
</body>
</html>
