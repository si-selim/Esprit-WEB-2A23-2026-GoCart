<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Favorite.php';

class FavoriteController {

    public function addFavorite($id_user, $id_produit) {
        $sql = "INSERT IGNORE INTO favorites (id_user, id_produit) VALUES (:id_user, :id_produit)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'id_user' => $id_user,
                'id_produit' => $id_produit
            ]);
        } catch (Exception $e) {
            error_log('addFavorite error: ' . $e->getMessage());
            return false;
        }
    }

    public function removeFavorite($id_user, $id_produit) {
        $sql = "DELETE FROM favorites WHERE id_user = :id_user AND id_produit = :id_produit";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'id_user' => $id_user,
                'id_produit' => $id_produit
            ]);
        } catch (Exception $e) {
            error_log('removeFavorite error: ' . $e->getMessage());
            return false;
        }
    }

    public function getFavoritesByUser($id_user) {
        $sql = "SELECT p.* FROM produit p 
                JOIN favorites f ON p.ID_produit = f.id_produit 
                WHERE f.id_user = :id_user";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('getFavoritesByUser error: ' . $e->getMessage());
            return [];
        }
    }

    public function isFavorite($id_user, $id_produit) {
        $sql = "SELECT COUNT(*) FROM favorites WHERE id_user = :id_user AND id_produit = :id_produit";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_user' => $id_user,
                'id_produit' => $id_produit
            ]);
            return $query->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
