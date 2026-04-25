<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Marathon.php';

class MarathonController {

    // ========================
    // AFFICHER MARATHONS
    // ========================
    public function afficherMarathon() {
        $sql = "SELECT * FROM marathon";
        $db = config::getConnexion();

        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ========================
    // AJOUTER MARATHON
    // ========================
    public function ajouterMarathon(Marathon $m) {
    $sql = "INSERT INTO marathon 
    (nom_marathon, image_marathon, organisateur_marathon, region_marathon, date_marathon, nb_places_dispo, prix_marathon)
    VALUES (:nom, :image, :organisateur, :region, :date, :places, :prix)";

    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        return $query->execute([
            'nom' => $m->getNomMarathon(),
            'image' => $m->getImageMarathon(),
            'organisateur' => $m->getOrganisateurMarathon(),
            'region' => $m->getRegionMarathon(),
            'date' => $m->getDateMarathon(),
            'places' => $m->getNbPlacesDispo(),
            'prix' => $m->getPrixMarathon()
        ]);
    } catch (Exception $e) {
        return false;
    }
}

    // ========================
    // MODIFIER MARATHON
    // ========================
    public function modifierMarathon(Marathon $m, $id) {
        $sql = "UPDATE marathon SET 
            nom_marathon=:nom,
            image_marathon=:image,
            organisateur_marathon=:organisateur,
            region_marathon=:region,
            date_marathon=:date,
            nb_places_dispo=:places,
            prix_marathon=:prix
        WHERE id_marathon=:id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom' => $m->getNomMarathon(),
                'image' => $m->getImageMarathon(),
                'organisateur' => $m->getOrganisateurMarathon(),
                'region' => $m->getRegionMarathon(),
                'date' => $m->getDateMarathon(),
                'places' => $m->getNbPlacesDispo(),
                'prix' => $m->getPrixMarathon()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ========================
    // SUPPRIMER MARATHON
    // ========================
    public function supprimerMarathon($id) {
        $sql = "DELETE FROM marathon WHERE id_marathon = :id";
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
    // SHOW MARATHON
    // ========================
    public function showMarathon($id) {
    $sql = "SELECT * FROM marathon WHERE id_marathon = :id";
    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            'id' => $id
        ]);

        return $query->fetch();
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}
    // ========================
    // Region
    // ========================
    public function getRegions() {
    $sql = "SELECT DISTINCT region_marathon FROM marathon";
    $db = config::getConnexion();

    try {
        return $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

   public function saveUploadedImage($file) {
    if (!isset($file) || $file['error'] !== 0) {
        return null;
    }

    // __DIR__ = Barchathon_corrected/Controller/
    // ../View/FrontOffice/images/uploads/ = chemin correct vers les images
    $folder = __DIR__ . "/../View/FrontOffice/images/uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = time() . '_' . uniqid() . '.' . $ext;
    $path = $folder . $name;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return null;
    }

    // Valeur stockée en DB : relative à FrontOffice/
    return "images/uploads/" . $name;
}

    public function rechercherMarathon($search) {
        $sql = "SELECT * FROM marathon 
                WHERE nom_marathon LIKE :s 
                   OR organisateur_marathon LIKE :s 
                   OR region_marathon LIKE :s
                ORDER BY nom_marathon ASC";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['s' => "%$search%"]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function filtrerMarathon($region) {
    $sql = "SELECT * FROM marathon 
            WHERE region_marathon = :region
            ORDER BY nom_marathon ASC";
    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute(['region' => $region]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

    public function listMarathons() {
        $sql = "SELECT * FROM marathon";
        $db = config::getConnexion();

        try {
            $result = $db->query($sql)->fetchAll();
            $list = [];
            foreach ($result as $row) {
                $list[] = new Marathon(
                    $row['id_marathon'],
                    $row['nom_marathon'],
                    $row['image_marathon'],
                    $row['organisateur_marathon'],
                    $row['region_marathon'],
                    $row['date_marathon'],
                    $row['nb_places_dispo'],
                    $row['prix_marathon']
                );
            }
            return $list;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function statsNbMarathonsDispo() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(nb_places_dispo) as total_places,
                    AVG(prix_marathon) as prix_moyen
                FROM marathon 
                WHERE nb_places_dispo > 0";

        $db = config::getConnexion();

        try {
            return $db->query($sql)->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>