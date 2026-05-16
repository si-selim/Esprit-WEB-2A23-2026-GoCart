<?php
include_once (__DIR__ . '/../config.php');
include_once (__DIR__ . '/../model/sponsoring.php');

class sponsoringController {
    public function deleteSponsoring($id) {
        $sql = "DELETE FROM sponsoring WHERE idSponsoring = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        $req->execute();
    }

    public function addSponsoring(Sponsoring $sponsoring) {
        $sql = "INSERT INTO sponsoring (nomSponsoring, dateDebut, dateFin, montant, etat, idSponsor, idMarathon)
                VALUES (:nomSponsoring, :dateDebut, :dateFin, :montant, :etat, :idSponsor, :idMarathon)";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'nomSponsoring' => $sponsoring->getNomSponsoring(),
            'dateDebut'     => $sponsoring->getDateDebut(),
            'dateFin'       => $sponsoring->getDateFin(),
            'montant'       => $sponsoring->getMontant(),
            'etat'          => $sponsoring->getEtat(),
            'idSponsor'     => $sponsoring->getIdSponsor(),
            'idMarathon'    => $sponsoring->getIdMarathon()
        ]);
    }

    public function updateSponsoring(Sponsoring $sponsoring, $id) {
        $db = config::getConnexion();
        $query = $db->prepare(
            'UPDATE sponsoring SET
                nomSponsoring = :nomSponsoring,
                dateDebut = :dateDebut,
                dateFin = :dateFin,
                montant = :montant,
                etat = :etat,
                idSponsor = :idSponsor,
                idMarathon = :idMarathon
            WHERE idSponsoring = :id'
        );
        $query->execute([
            'id'            => $id,
            'nomSponsoring' => $sponsoring->getNomSponsoring(),
            'dateDebut'     => $sponsoring->getDateDebut(),
            'dateFin'       => $sponsoring->getDateFin(),
            'montant'       => $sponsoring->getMontant(),
            'etat'          => $sponsoring->getEtat(),
            'idSponsor'     => $sponsoring->getIdSponsor(),
            'idMarathon'    => $sponsoring->getIdMarathon()
        ]);
    }

    public function showSponsoring($id) {
        $sql = "SELECT * FROM sponsoring WHERE idSponsoring = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch();
    }

    public function showMarathon($id) {
        $sql = "SELECT * FROM marathon WHERE id_marathon = ?";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllSponsoring(): array {
        $sql = "SELECT * FROM sponsoring";
        $db = config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSponsoringBySponsor($idSponsor): array {
        $sql = "SELECT * FROM sponsoring WHERE idSponsor = :idSponsor";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':idSponsor', $idSponsor);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllMarathonsSimple(): array {
        $sql = "SELECT * FROM marathon";
        $db = config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSponsoringDistributionByAmountRange(): array {
        $sql = "SELECT
                    SUM(CASE WHEN montant < 500 THEN 1 ELSE 0 END) AS range_0_500,
                    SUM(CASE WHEN montant >= 500 AND montant < 1000 THEN 1 ELSE 0 END) AS range_500_1000,
                    SUM(CASE WHEN montant >= 1000 THEN 1 ELSE 0 END) AS range_1000_plus
                FROM sponsoring";
        $db = config::getConnexion();
        $row = $db->query($sql)->fetch();
        return [
            ['label' => '0 - 500',    'count' => (int) $row['range_0_500']],
            ['label' => '500 - 1000', 'count' => (int) $row['range_500_1000']],
            ['label' => '1000+',      'count' => (int) $row['range_1000_plus']]
        ];
    }

    /** @deprecated Prefer iterating over getAllSponsoring() in views. */
    public function afficherSponsoring($showActions = true, $deleteOnly = false, $viewOnly = false) {
        $rows = $this->getAllSponsoring();
        include __DIR__ . '/../View/FrontOffice/partials/_sponsoring_rows.php';
    }

    /** @deprecated Prefer iterating over getSponsoringBySponsor() in views. */
    public function afficherSponsoringSponsor($idSponsor, $showActions = true) {
        $rows = $this->getSponsoringBySponsor($idSponsor);
        $deleteOnly = false;
        $viewOnly   = false;
        include __DIR__ . '/../View/FrontOffice/partials/_sponsoring_rows.php';
    }

    /** @deprecated Prefer iterating over getAllMarathonsSimple() in views. */
    public function afficherMarathon($chooseButton = false, $extraParams = '', $formParams = '') {
        $rows = $this->getAllMarathonsSimple();
        include __DIR__ . '/../View/FrontOffice/partials/_marathon_choose_rows.php';
    }
}
