<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
include '../../../Controller/FavoriteController.php';

$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$userId = $user['id_user'] ?? $user['id'];
$favCtrl = new FavoriteController();

$userFavorites = $favCtrl->getFavoritesByUser($userId);
$favoriteIds = array_column($userFavorites, 'ID_produit');

header('Content-Type: application/json');
echo json_encode($favoriteIds);
?>