<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/LigneCommande.php');

class LigneCommandeController {

    public function addLigneCommande(LigneCommande $ligne) {
        $sql = "INSERT INTO lignecommande (idcommande, idproduit, quantite, prixunitaire) VALUES (:idcommande, :idproduit, :quantite, :prixunitaire)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idcommande' => $ligne->getIdcommande(),
                'idproduit' => $ligne->getIdproduit(),
                'quantite' => $ligne->getQuantite(),
                'prixunitaire' => $ligne->getPrixunitaire()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateLigneCommande(LigneCommande $ligne, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE lignecommande SET 
                    idcommande = :idcommande,
                    idproduit = :idproduit,
                    quantite = :quantite,
                    prixunitaire = :prixunitaire
                WHERE idligne = :id'
            );
            $query->execute([
                'id' => $id,
                'idcommande' => $ligne->getIdcommande(),
                'idproduit' => $ligne->getIdproduit(),
                'quantite' => $ligne->getQuantite(),
                'prixunitaire' => $ligne->getPrixunitaire()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function deleteLigneCommande($id) {
        $sql = "DELETE FROM lignecommande WHERE idligne = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function getLignesCommande($idCommande) {
        $sql = "SELECT * FROM lignecommande WHERE idcommande = :idcommande";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':idcommande', $idCommande);
            $req->execute();
            return $req;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function listLignesCommandeFiltered($commandeId = null, $search = '', $sort = 'idligne', $dir = 'ASC') {
        $allowedSort = ['idligne', 'idcommande', 'idproduit', 'quantite', 'prixunitaire'];
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $sort = in_array($sort, $allowedSort) ? $sort : 'idligne';

        $sql = "SELECT * FROM lignecommande";
        $params = [];
        $where = [];

        if (!empty($commandeId)) {
            $where[] = "idcommande = :idcommande";
            $params['idcommande'] = $commandeId;
        }

        if (!empty($search)) {
            $where[] = "(idligne LIKE :search OR idcommande LIKE :search OR idproduit LIKE :search OR quantite LIKE :search OR prixunitaire LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY $sort $dir";

        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function getLigneCommande($id) {
        $sql = "SELECT * FROM lignecommande WHERE idligne = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id);

        try {
            $query->execute();
            return $query->fetch();
        } catch(Exception $e) {
            die('Error: '. $e->getMessage());
        }
    }
}
?>
