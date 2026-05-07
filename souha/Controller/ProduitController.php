<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Produit.php';

class ProduitController {

    // =====================
    // LIST (READ ALL)
    // =====================
    public function listProduits($sort = null, $order = 'ASC') {
        $sql = "SELECT * FROM produit";
        $allowedSorts = ['ID_produit', 'ID_stand', 'nom_produit', 'type', 'prix_produit', 'qte_stock', 'en_out_stock'];
        if ($sort && in_array($sort, $allowedSorts)) {
            $order = ($order === 'DESC') ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $sort $order";
        }
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // =====================
    // GET BY STAND
    // =====================
    public function getProduitsByStand($idStand) {
        $sql = "SELECT * FROM produit WHERE ID_stand = :ID_stand";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':ID_stand', (int) $idStand, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('getProduitsByStand error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alias for backward compatibility
     */
    public function afficherProduitsParStand($id_stand) {
        return $this->getProduitsByStand($id_stand);
    }

    // =====================
    // ADD
    // =====================
    public function addProduit($produit) {
        $sql = "INSERT INTO produit (ID_stand, nom_produit, type, prix_produit, qte_stock, en_out_stock, image) 
                VALUES (:ID_stand, :nom_produit, :type, :prix_produit, :qte_stock, :en_out_stock, :image)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $result = $query->execute([
                'ID_stand'      => $produit->getIDStand(),
                'nom_produit'   => $produit->getNomProduit(),
                'type'          => $produit->getType(),
                'prix_produit'  => $produit->getPrixProduit(),
                'qte_stock'     => $produit->getQteStock(),
                'en_out_stock'  => $produit->getEnOutStock(),
                'image'         => $produit->getImage()
            ]);
            return $result; // Retourner le résultat de execute() qui est true/false
        } catch (Exception $e) {
            error_log('addProduit error: ' . $e->getMessage());
            throw $e; // Relancer l'exception pour qu'elle soit capturée dans addProduit.php
        }
    }

    // =====================
    // DELETE
    // =====================
    public function deleteProduit($idProduit) {
        $sql = "DELETE FROM produit WHERE ID_produit = :ID_produit";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':ID_produit', (int) $idProduit, PDO::PARAM_INT);
            $req->execute();
            return true;
        } catch (Exception $e) {
            error_log('deleteProduit error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // GET BY ID
    // =====================
    public function getProduit($idProduit) {
        $sql = "SELECT * FROM produit WHERE ID_produit = :ID_produit";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':ID_produit', (int) $idProduit, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) {
            error_log('getProduit error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias for backward compatibility
     */
    public function showProduit($id) {
        return $this->getProduit($id);
    }

    // =====================
    // SEARCH BY ID OR NAME
    // =====================
    public function searchProduit($search) {
        $db = config::getConnexion();
        try {
            if (ctype_digit(trim($search))) {
                $query = $db->prepare("SELECT * FROM produit WHERE ID_produit = :id");
                $query->bindValue(':id', (int) $search, PDO::PARAM_INT);
                $query->execute();
                $result = $query->fetch();
                if ($result) return $result;
            }
            $query = $db->prepare("SELECT * FROM produit WHERE nom_produit LIKE :nom OR type LIKE :nom");
            $query->bindValue(':nom', '%' . $search . '%');
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('searchProduit error: ' . $e->getMessage());
            return null;
        }
    }

    // =====================
    // UPDATE
    // =====================
    public function updateProduit($produit, $idProduit) {
        try {
            $db = config::getConnexion();
            $sql = 'UPDATE produit SET 
                        ID_stand = :ID_stand,
                        nom_produit = :nom_produit,
                        type = :type,
                        prix_produit = :prix_produit,
                        qte_stock = :qte_stock,
                        en_out_stock = :en_out_stock';
            
            $params = [
                'ID_produit'    => (int) $idProduit,
                'ID_stand'      => $produit->getIDStand(),
                'nom_produit'   => $produit->getNomProduit(),
                'type'          => $produit->getType(),
                'prix_produit'  => $produit->getPrixProduit(),
                'qte_stock'     => $produit->getQteStock(),
                'en_out_stock'  => $produit->getEnOutStock()
            ];

            if ($produit->getImage() !== null) {
                $sql .= ', image = :image';
                $params['image'] = $produit->getImage();
            }

            $sql .= ' WHERE ID_produit = :ID_produit';
            $query = $db->prepare($sql);
            $query->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log('updateProduit error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // UTILS
    // =====================
    public function countProduitsByStand($idStand) {
        $sql = "SELECT COUNT(*) FROM produit WHERE ID_stand = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $idStand]);
            return $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function decrementStock($idProduit, $quantity) {
        $sql = "UPDATE produit SET qte_stock = GREATEST(qte_stock - :quantity, 0), en_out_stock = CASE WHEN GREATEST(qte_stock - :quantity, 0) = 0 THEN 0 ELSE 1 END WHERE ID_produit = :ID_produit";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'quantity' => max(0, (int) $quantity),
                'ID_produit' => (int) $idProduit
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log('decrementStock error: ' . $e->getMessage());
            return false;
        }
    }
}