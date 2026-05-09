<?php
include_once (__DIR__ . '/../config.php');
include_once (__DIR__ . '/../model/sponsor.php');

class sponsorController {
    public function deleteSponsor($id) {
        $sql = "DELETE FROM sponsor WHERE idSponsor = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        $req->execute();
    }

    public function addSponsor(Sponsor $sponsor) {
        $sql = "INSERT INTO sponsor (nom, type, adresse, contact, email, pageWeb, idUser) VALUES (:nom, :type, :adresse, :contact, :email, :pageWeb, :idUser)";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'nom'     => $sponsor->getNom(),
            'type'    => $sponsor->getType(),
            'adresse' => $sponsor->getAdresse(),
            'contact' => $sponsor->getContact(),
            'email'   => $sponsor->getEmail(),
            'pageWeb' => $sponsor->getPageWeb(),
            'idUser'  => $sponsor->getIdUser()
        ]);
    }

    public function updateSponsor(Sponsor $sponsor, $id) {
        $db = config::getConnexion();
        $query = $db->prepare(
            'UPDATE sponsor SET
                nom = :nom,
                type = :type,
                adresse = :adresse,
                contact = :contact,
                email = :email,
                pageWeb = :pageWeb,
                idUser = :idUser
            WHERE idSponsor = :id'
        );
        $query->execute([
            'id'      => $id,
            'nom'     => $sponsor->getNom(),
            'type'    => $sponsor->getType(),
            'adresse' => $sponsor->getAdresse(),
            'contact' => $sponsor->getContact(),
            'email'   => $sponsor->getEmail(),
            'pageWeb' => $sponsor->getPageWeb(),
            'idUser'  => $sponsor->getIdUser()
        ]);
    }

    public function showSponsor($id) {
        $sql = "SELECT * FROM sponsor WHERE idSponsor = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch();
    }

    public function getAllSponsors(): array {
        $sql = "SELECT * FROM sponsor";
        $db = config::getConnexion();
        $query = $db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSponsorDistributionByType(): array {
        $sql = "SELECT type, COUNT(*) AS total FROM sponsor GROUP BY type";
        $db = config::getConnexion();
        $query = $db->query($sql);
        $results = [];
        while ($row = $query->fetch()) {
            $results[] = [
                'label' => $row['type'] ?? 'Inconnu',
                'count' => (int) $row['total']
            ];
        }
        return $results;
    }

    public function getSponsorsBySponsoringCount(int $limit = 10): array {
        $sql = "SELECT s.nom AS label, COUNT(sp.idSponsoring) AS total
                FROM sponsor s
                LEFT JOIN sponsoring sp ON s.idSponsor = sp.idSponsor
                GROUP BY s.idSponsor
                ORDER BY total DESC
                LIMIT :limit";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        $results = [];
        while ($row = $query->fetch()) {
            $results[] = [
                'label' => $row['label'] ?? 'Inconnu',
                'count' => (int) $row['total']
            ];
        }
        return $results;
    }

    /**
     * Backward-compatible wrapper that delegates HTML rendering to a partial view.
     * @deprecated Prefer iterating over getAllSponsors() in views.
     */
    public function afficherSponsor($showActions = true, $deleteOnly = false, $viewOnly = false, $chooseButton = false, $extraParams = '', $formParams = '') {
        $rows = $this->getAllSponsors();
        include __DIR__ . '/../View/FrontOffice/partials/_sponsor_rows.php';
    }
}
