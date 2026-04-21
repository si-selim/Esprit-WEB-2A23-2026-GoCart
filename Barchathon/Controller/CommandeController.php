<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/Commande.php');

class CommandeController {

    public function listCommandes() {
        return $this->listCommandesFiltered();
    }

    public function listCommandesFiltered($search = '', $sort = 'datecommande', $dir = 'DESC') {
        $allowedSort = ['datecommande', 'montanttotale', 'idcommande', 'idutilisateur', 'idstand'];
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $sort = in_array($sort, $allowedSort) ? $sort : 'datecommande';

        $sql = "SELECT * FROM commande";
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE idcommande LIKE :search OR idutilisateur LIKE :search OR idstand LIKE :search OR statut LIKE :search OR montanttotale LIKE :search";
            $params['search'] = '%' . $search . '%';
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

    public function listCommandesByStatut($statut) {
        $sql = "SELECT * FROM commande WHERE statut = :statut ORDER BY datecommande DESC";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':statut', $statut);
            $req->execute();
            return $req;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteCommande($id) {
        $sql = "DELETE FROM commande WHERE idcommande = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addCommande(Commande $commande) {
        $sql = "INSERT INTO commande (idutilisateur, idstand, datecommande, statut, montanttotale) VALUES (:idutilisateur, :idstand, :datecommande, :statut, :montanttotal)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idutilisateur' => $commande->getIdutilisateur(),
                'idstand' => $commande->getIdstand(),
                'datecommande' => $commande->getDatecommande(),
                'statut' => $commande->getStatut(),
                'montanttotal' => $commande->getMontanttotal()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateCommande(Commande $commande, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE commande SET 
                    idutilisateur = :idutilisateur,
                    idstand = :idstand,
                    datecommande = :datecommande,
                    statut = :statut,
                    montanttotale = :montanttotal
                WHERE idcommande = :id'
            );
            $query->execute([
                'id' => $id,
                'idutilisateur' => $commande->getIdutilisateur(),
                'idstand' => $commande->getIdstand(),
                'datecommande' => $commande->getDatecommande(),
                'statut' => $commande->getStatut(),
                'montanttotal' => $commande->getMontanttotal()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showCommande($id) {
        $sql="SELECT * FROM commande WHERE idcommande = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id);

        try {
            $query->execute();
            $commande = $query->fetch();
            return $commande;
        } catch(Exception $e) {
            die('Error: '. $e->getMessage());
        }
    }
}
?>
