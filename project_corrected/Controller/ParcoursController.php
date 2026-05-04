<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Parcours.php';

class ParcoursController {

    // ========================
    // AFFICHER PARCOURS
    // ========================
    public function afficherParcours() {
        $sql = "SELECT p.*, m.nom_marathon 
                FROM parcours p 
                JOIN marathon m ON p.id_marathon = m.id_marathon";
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ========================
    // AJOUTER PARCOURS
    // ========================
    public function ajouterParcours(Parcours $p) {
        $sql = "INSERT INTO parcours 
        (nom_parcours, point_depart, point_arrivee, distance, difficulte, id_marathon, heure_depart)
        VALUES (:nom, :depart, :arrivee, :distance, :difficulte, :id_marathon, :heure_depart)";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom'          => $p->getNomParcours(),
                ':depart'       => $p->getPointDepart(),
                ':arrivee'      => $p->getPointArrivee(),
                ':distance'     => $p->getDistance(),
                ':difficulte'   => $p->getDifficulte(),
                ':id_marathon'  => $p->getIdMarathon(),
                ':heure_depart' => $p->getHeureDepart(),
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // MODIFIER PARCOURS
    // ========================
    public function modifierParcours(Parcours $p, $id) {
        $sql = "UPDATE parcours SET 
                    nom_parcours=:nom,
                    point_depart=:depart,
                    point_arrivee=:arrivee,
                    distance=:distance,
                    difficulte=:difficulte,
                    id_marathon=:id_marathon,
                    heure_depart=:heure_depart
                WHERE id_parcours=:id";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id'           => $id,
                ':nom'          => $p->getNomParcours(),
                ':depart'       => $p->getPointDepart(),
                ':arrivee'      => $p->getPointArrivee(),
                ':distance'     => $p->getDistance(),
                ':difficulte'   => $p->getDifficulte(),
                ':id_marathon'  => $p->getIdMarathon(),
                ':heure_depart' => $p->getHeureDepart(),
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // SUPPRIMER PARCOURS
    // ========================
    public function supprimerParcours($id) {
        $sql = "DELETE FROM parcours WHERE id_parcours = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ========================
    // GET PARCOURS BY ID
    // ========================
    public function showParcours($id) {
        $sql = "SELECT * FROM parcours WHERE id_parcours = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch(Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function rechercherParcoursParNom($search) {
        $sql = "SELECT p.*, m.nom_marathon FROM parcours p JOIN marathon m ON p.id_marathon = m.id_marathon WHERE p.nom_parcours LIKE :s OR p.point_depart LIKE :s OR p.point_arrivee LIKE :s";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['s' => "%$search%"]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function filtrerParcours($difficulte) {
        $sql = "SELECT p.*, m.nom_marathon FROM parcours p JOIN marathon m ON p.id_marathon = m.id_marathon WHERE p.difficulte = :diff";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['diff' => $difficulte]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function statsParcours() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    AVG(distance) as dist_moyenne,
                    SUM(CASE WHEN difficulte='facile' THEN 1 ELSE 0 END) as facile,
                    SUM(CASE WHEN difficulte='moyen' THEN 1 ELSE 0 END) as moyen,
                    SUM(CASE WHEN difficulte='difficile' THEN 1 ELSE 0 END) as difficile
                FROM parcours";
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
