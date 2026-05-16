<?php
require_once __DIR__ . '/../config.php';

class InscriptionMarathonController {

    // ========================
    // AJOUTER INSCRIPTION
    // ========================
    public function inscrireUtilisateur($id_user, $id_marathon, $prix = 0) {
        $sql = "INSERT INTO inscription_marathon (id_user, id_marathon, date_inscription, statut_paiement, montant_paye)
                VALUES (:id_user, :id_marathon, NOW(), :statut, :montant)";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $statut = ($prix == 0) ? 'paye' : 'en_attente';
            $query->execute([
                'id_user' => $id_user,
                'id_marathon' => $id_marathon,
                'statut' => $statut,
                'montant' => $prix
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // VERIFIER SI DEJA INSCRIT
    // ========================
    public function estDejaInscrit($id_user, $id_marathon) {
        $sql = "SELECT COUNT(*) FROM inscription_marathon WHERE id_user = :id_user AND id_marathon = :id_marathon";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user, 'id_marathon' => $id_marathon]);
            return $query->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // METTRE A JOUR STATUT PAIEMENT
    // ========================
    public function mettreAJourPaiement($id_inscription, $statut, $methode_paiement = null) {
        $sql = "UPDATE inscription_marathon SET statut_paiement = :statut";
        $params = ['statut' => $statut, 'id' => $id_inscription];

        if ($methode_paiement) {
            $sql .= ", methode_paiement = :methode";
            $params['methode'] = $methode_paiement;
        }

        $sql .= " WHERE id_inscription = :id";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // DECREMENTER PLACES MARATHON
    // ========================
    public function decrementerPlaces($id_marathon) {
        $sql = "UPDATE marathon SET nb_places_dispo = nb_places_dispo - 1 WHERE id_marathon = :id AND nb_places_dispo > 0";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id_marathon]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>