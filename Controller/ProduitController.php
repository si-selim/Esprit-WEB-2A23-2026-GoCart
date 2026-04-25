<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Produit.php';

class ProduitController {

    // ========================
    // AFFICHER PRODUITS PAR STAND
    // ========================
    public function afficherProduitsParStand($id_stand) {
        $sql = "SELECT ID_produit as id_produit, ID_stand as id_stand, nom_produit, type, prix_produit, qte_stock, en_out_stock FROM produit WHERE ID_stand = :id_stand";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id_stand' => $id_stand]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ========================
    // AJOUTER PRODUIT
    // ========================
    public function ajouterProduit(Produit $p) {
        $sql = "INSERT INTO produit (ID_stand, nom_produit, type, prix_produit, qte_stock, en_out_stock)
                VALUES (:id_stand, :nom, :type, :prix, :qte, :en_stock)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id_stand' => $p->getIdStand(),
                ':nom' => $p->getNomProduit(),
                ':type' => $p->getType(),
                ':prix' => $p->getPrixProduit(),
                ':qte' => $p->getQteStock(),
                ':en_stock' => $p->getEnOutStock()
            ]);

            return $db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // MODIFIER PRODUIT
    // ========================
    public function modifierProduit(Produit $p, $id) {
        $sql = "UPDATE produit SET
                ID_stand = :id_stand,
                nom_produit = :nom,
                type = :type,
                prix_produit = :prix,
                qte_stock = :qte,
                en_out_stock = :en_stock
                WHERE ID_produit = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id' => $id,
                ':id_stand' => $p->getIdStand(),
                ':nom' => $p->getNomProduit(),
                ':type' => $p->getType(),
                ':prix' => $p->getPrixProduit(),
                ':qte' => $p->getQteStock(),
                ':en_stock' => $p->getEnOutStock()
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // SUPPRIMER PRODUIT
    // ========================
    public function supprimerProduit($id) {
        $sql = "DELETE FROM produit WHERE ID_produit = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // GET PRODUIT BY ID
    // ========================
    public function showProduit($id) {
        $sql = "SELECT ID_produit as id_produit, ID_stand as id_stand, nom_produit, type, prix_produit, qte_stock, en_out_stock FROM produit WHERE ID_produit = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}