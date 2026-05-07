<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
require_once __DIR__ . '/../../../Controller/FavoriteController.php';

$user = getCurrentUser();
if (!$user || !isset($_POST['id_produit'])) {
    echo "Error";
    exit;
}

$id_user = $user['id_user'] ?? $user['id'];
$id_produit = (int)$_POST['id_produit'];
$action = $_POST['action'] ?? 'toggle';

$favoriteController = new FavoriteController();

if ($action === 'toggle') {
    if ($favoriteController->isFavorite($id_user, $id_produit)) {
        $favoriteController->removeFavorite($id_user, $id_produit);
        echo "removed";
    } else {
        $favoriteController->addFavorite($id_user, $id_produit);
        echo "added";
    }
}
?>
