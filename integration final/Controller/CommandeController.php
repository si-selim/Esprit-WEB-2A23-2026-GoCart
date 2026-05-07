<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/Commande.php');

class CommandeController {

    public function listCommandes() {
        return $this->listCommandesFiltered();
    }

    public function listCommandesFiltered($search = '', $sort = 'datecommande', $dir = 'DESC') {
        $allowedSort = ['datecommande', 'montanttotale', 'idcommande', 'idutilisateur', 'idstand', 'idorganisateur'];
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $sort = in_array($sort, $allowedSort) ? $sort : 'datecommande';

        $sql = "SELECT * FROM commande";
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE idcommande LIKE :search OR idutilisateur LIKE :search OR idstand LIKE :search OR idorganisateur LIKE :search OR statut LIKE :search OR montanttotale LIKE :search";
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
        // Récupérer l'idorganisateur basé sur l'idstand
        $idorganisateur = $this->getOrganisateurByStand($commande->getIdstand());
        
        $sql = "INSERT INTO commande (idutilisateur, idstand, idorganisateur, datecommande, statut, montanttotale, modePaiement) VALUES (:idutilisateur, :idstand, :idorganisateur, :datecommande, :statut, :montanttotal, :modePaiement)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idutilisateur' => $commande->getIdutilisateur(),
                'idstand' => $commande->getIdstand(),
                'idorganisateur' => $idorganisateur,
                'datecommande' => $commande->getDatecommande(),
                'statut' => $commande->getStatut(),
                'montanttotal' => $commande->getMontanttotal(),
                'modePaiement' => $commande->getModePaiement()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            error_log('Erreur addCommande: ' . $e->getMessage() . ' - Data: ' . json_encode([
                'idutilisateur' => $commande->getIdutilisateur(),
                'idstand' => $commande->getIdstand(),
                'idorganisateur' => $idorganisateur,
                'datecommande' => $commande->getDatecommande(),
                'statut' => $commande->getStatut(),
                'montanttotal' => $commande->getMontanttotal(),
                'modePaiement' => $commande->getModePaiement()
            ]));
            return false;
        }
    }

    public function updateCommande(Commande $commande, $id) {
        // Récupérer l'idorganisateur basé sur l'idstand
        $idorganisateur = $this->getOrganisateurByStand($commande->getIdstand());
        
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE commande SET 
                    idutilisateur = :idutilisateur,
                    idstand = :idstand,
                    idorganisateur = :idorganisateur,
                    datecommande = :datecommande,
                    statut = :statut,
                    montanttotale = :montanttotal,
                    modePaiement = :modePaiement
                WHERE idcommande = :id'
            );
            $query->execute([
                'id' => $id,
                'idutilisateur' => $commande->getIdutilisateur(),
                'idstand' => $commande->getIdstand(),
                'idorganisateur' => $idorganisateur,
                'datecommande' => $commande->getDatecommande(),
                'statut' => $commande->getStatut(),
                'montanttotal' => $commande->getMontanttotal(),
                'modePaiement' => $commande->getModePaiement()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function updateCommandeStatus($id, $statut) {
        $sql = "UPDATE commande SET statut = :statut WHERE idcommande = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'statut' => $statut
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function updateCommandePayment($id, $statut, $modePaiement = null) {
        $sql = "UPDATE commande SET statut = :statut";
        if ($modePaiement !== null) {
            $sql .= ", modePaiement = :modePaiement";
        }
        $sql .= " WHERE idcommande = :id";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $params = [
                'id' => $id,
                'statut' => $statut
            ];
            if ($modePaiement !== null) {
                $params['modePaiement'] = $modePaiement;
            }
            $query->execute($params);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function expireOldOrders($hours = 24) {
        $sql = "UPDATE commande SET statut = 'non valide' WHERE statut = 'en cours' AND datecommande <= DATE_SUB(NOW(), INTERVAL :hours HOUR)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':hours', $hours, PDO::PARAM_INT);
            $query->execute();
            return $query->rowCount();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showCommande($id) {
        $sql = "SELECT * FROM commande WHERE idcommande = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    /**
     * Récupère l'ID de l'organisateur basé sur l'ID du stand
     * Suit la chaîne : stand -> parcours -> marathon -> organisateur
     */
    public function getOrganisateurByStand($idStand) {
        $sql = "SELECT m.organisateur_marathon 
                FROM stand s 
                JOIN parcours p ON s.ID_parcours = p.id_parcours 
                JOIN marathon m ON p.id_marathon = m.id_marathon 
                WHERE s.ID_stand = :id_stand";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_stand' => $idStand]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['organisateur_marathon'] : null;
        } catch (Exception $e) {
            error_log('Error getOrganisateurByStand: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les commandes d'un organisateur
     */
    public function getCommandesByOrganisateur($idOrganisateur) {
        $sql = "SELECT * FROM commande WHERE idorganisateur = :idorganisateur ORDER BY datecommande DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idorganisateur' => $idOrganisateur]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error getCommandesByOrganisateur: ' . $e->getMessage());
        }
    }
}
?>
