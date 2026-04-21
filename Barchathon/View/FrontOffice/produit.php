<?php
session_start();
include '../../Controller/CommandeController.php';
include '../../Controller/LigneCommandeController.php';

$products = [
    101 => ['nom' => 'Produit A', 'prix' => 35.00],
    102 => ['nom' => 'Produit B', 'prix' => 49.90],
    103 => ['nom' => 'Produit C', 'prix' => 19.50],
];

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

    if (isset($_POST['validate_order'])) {
        if (empty($cart)) {
            $message = 'Le panier est vide, impossible de valider la commande.';
        } else {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['quantite'] * $item['prix'];
            }

            $commandeC = new CommandeController();
            $ligneC = new LigneCommandeController();
            $commande = new Commande(null, 1, 1, date('Y-m-d H:i:s'), 'en cours', $total);
            $newCommandeId = $commandeC->addCommande($commande);

            if ($newCommandeId) {
                foreach ($cart as $item) {
                    $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                    $ligneC->addLigneCommande($ligne);
                }

                $_SESSION['cart'] = [];
                header('Location: produit.php?created=' . urlencode($newCommandeId));
                exit;
            }

            $message = 'Erreur lors de la création de la commande. Veuillez réessayer.';
        }
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $success = 'Panier vidé.';
    }
}

if (isset($_GET['created'])) {
    $createdId = htmlspecialchars($_GET['created']);
    $success = "Commande #{$createdId} créée avec succès.";
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
    <title>Catalogue produits</title>
    <style>
        body { margin:0; font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#fff8e7,#eef8f8); color:#102a43; }
        .wrap { width:min(1200px, calc(100% - 32px)); margin:24px auto 40px; }
        .hero { background:linear-gradient(135deg,#102a43,#0f766e); color:#fff; border-radius:28px; padding:28px; box-shadow:0 20px 40px rgba(16,42,67,.16); }
        .panel { margin-top:18px; background:#fff; border-radius:28px; padding:24px; box-shadow:0 18px 40px rgba(16,42,67,.08); overflow:hidden; }
        .grid { display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start; }
        .product-card, .cart-card { background:#f8fafc; border-radius:24px; padding:18px; border:1px solid #e2e8f0; }
        .product-card h2, .cart-card h2 { margin-top:0; font-size:1.2rem; color:#102a43; }
        .product-item { background:#fff; border:1px solid #cbd5e1; border-radius:18px; padding:18px; margin-bottom:16px; }
        .product-item:last-child { margin-bottom:0; }
        .product-item strong { display:block; margin-bottom:6px; font-size:1.05rem; }
        input[type=number] { width:80px; border-radius:12px; border:1px solid #cbd5e1; padding:10px; }
        .field-error { display:block; min-height:1.2em; margin-top:6px; color:#b91c1c; font-size:0.85rem; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:14px; padding:12px 16px; font-weight:700; cursor:pointer; }
        .btn-primary { background:#0f766e; color:#fff; }
        .btn-secondary { background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; }
        .btn-danger { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
        .message { padding:16px; border-radius:18px; margin-bottom:18px; }
        .message.success { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .message.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px 10px; border-bottom:1px solid #dbeafe; text-align:left; }
        th { background:#eff6ff; color:#1e40af; }
        @media (max-width:960px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <h1>Catalogue produits</h1>
            <p>Choisissez vos produits et définissez les quantités. Cliquez sur Actualiser le nombre pour mettre à jour le panier.</p>
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
                    <?php foreach ($products as $id => $product): ?>
                        <div class="product-item">
                            <strong><?php echo htmlspecialchars($product['nom']); ?> (ID <?php echo $id; ?>)</strong>
                            <div>Prix unitaire : <strong><?php echo number_format($product['prix'], 2, ',', ' '); ?> TND</strong></div>
                            <form method="post" style="margin-top:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                                <label>
                                    Quantité
                                    <input type="number" name="quantity" class="quantity-input" value="<?php echo isset($cart[$id]) ? htmlspecialchars($cart[$id]['quantite']) : '1'; ?>" min="0" step="1" />
                                </label>
                                <span class="field-error" aria-live="polite"></span>
                                <input type="hidden" name="product_id" value="<?php echo $id; ?>" />
                                <button type="submit" name="update_quantity" class="btn btn-primary">Actualiser le nombre</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-card">
                    <h2>Panier actuel</h2>
                    <?php if (empty($cart)): ?>
                        <p>Votre panier est vide.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix U. (TND)</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantite']); ?></td>
                                        <td><?php echo number_format($item['prix'], 2, ',', ' '); ?> TND</td>
                                        <td><?php echo number_format($item['quantite'] * $item['prix'], 2, ',', ' '); ?> TND</td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <th colspan="3">Total panier</th>
                                    <th><?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</th>
                                </tr>
                            </tbody>
                        </table>
                        <form method="post" style="margin-top:18px; display:flex; gap:14px; flex-wrap:wrap;">
                            <button type="submit" name="validate_order" class="btn btn-primary">Valider commande</button>
                            <button type="submit" name="clear_cart" class="btn btn-danger">Vider le panier</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.product-item form');

            forms.forEach(function (form) {
                const quantityInput = form.querySelector('.quantity-input');
                const errorSpan = form.querySelector('.field-error');
                const submitButton = form.querySelector('button[type="submit"]');

                function validateQuantity() {
                    const value = quantityInput.value.trim();
                    const intValue = Number(value);

                    if (value === '' || !/^[0-9]+$/.test(value) || intValue < 0) {
                        errorSpan.textContent = 'Entrez un nombre entier positif ou zéro.';
                        submitButton.disabled = true;
                        return false;
                    }

                    errorSpan.textContent = '';
                    submitButton.disabled = false;
                    return true;
                }

                quantityInput.addEventListener('input', validateQuantity);
                form.addEventListener('submit', function (event) {
                    if (!validateQuantity()) {
                        event.preventDefault();
                        quantityInput.focus();
                    }
                });

                validateQuantity();
            });
        });
    </script>
</body>
</html>
