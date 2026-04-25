<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Stand.php';

class StandController {

    // ========================
    // AFFICHER STANDS PAR PARCOURS
    // ========================
    public function afficherStandsParParcours($id_parcours) {
        $sql = "SELECT ID_stand as id_stand, nom_stand, position, description, ID_parcours as id_parcours FROM stand WHERE ID_parcours = :id_parcours";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id_parcours' => $id_parcours]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ========================
    // AJOUTER STAND
    // ========================
    public function ajouterStand(Stand $s) {
        $sql = "INSERT INTO stand (nom_stand, position, description, ID_parcours)
                VALUES (:nom, :position, :description, :id_parcours)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $s->getNomStand(),
                ':position' => $s->getPosition(),
                ':description' => $s->getDescription(),
                ':id_parcours' => $s->getIdParcours()
            ]);

            return $db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // MODIFIER STAND
    // ========================
    public function modifierStand(Stand $s, $id) {
        $sql = "UPDATE stand SET
                nom_stand = :nom,
                position = :position,
                description = :description,
                ID_parcours = :id_parcours
                WHERE ID_stand = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id' => $id,
                ':nom' => $s->getNomStand(),
                ':position' => $s->getPosition(),
                ':description' => $s->getDescription(),
                ':id_parcours' => $s->getIdParcours()
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================
    // SUPPRIMER STAND
    // ========================
    public function supprimerStand($id) {
        $sql = "DELETE FROM stand WHERE ID_stand = :id";
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
    // GET STAND BY ID
    // ========================
    public function showStand($id) {
        $sql = "SELECT ID_stand as id_stand, nom_stand, position, description, ID_parcours as id_parcours FROM stand WHERE ID_stand = :id";
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