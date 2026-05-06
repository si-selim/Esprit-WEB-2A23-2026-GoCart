<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Inscription.php";

class InscriptionController {

    
    public function add(Inscription $inscription) {

        $sql = "INSERT INTO inscription
        (nb_personnes, mode_de_paiement, date_inscription, date_paiement, id_user, id_parcours, statut_paiement)
        VALUES (:nb, :mode, NOW(), :date, :user, :parcours, :statut)";

        $db = Config::getConnexion();
        $stmt = $db->prepare($sql);

        $stmt->execute([
            'nb' => $inscription->getNbPersonnes(),
            'mode' => $inscription->getModePaiement(),
            'date' => date('Y-m-d H:i:s'),
            'user' => $inscription->getIdUser(),
            'parcours' => $inscription->getIdParcours(),
            'statut' => $inscription->getStatutPaiement() ?? "unpaid"
        ]);

        return $db->lastInsertId();
    }

    
    public function delete($id) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM inscription WHERE id_inscription=?");
        $stmt->execute([$id]);
    }

    
    public function getAll() {
        $db = Config::getConnexion();
        return $db->query("SELECT * FROM inscription ORDER BY id_inscription DESC")
                  ->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getById($id) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM inscription WHERE id_inscription=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function update(Inscription $inscription, $id) {

        $db = Config::getConnexion();

        $stmt = $db->prepare("
            UPDATE inscription SET
            nb_personnes=:nb,
            mode_de_paiement=:mode,
            date_paiement=:date,
            id_parcours=:parcours
            WHERE id_inscription=:id
        ");

        $stmt->execute([
            'nb' => $inscription->getNbPersonnes(),
            'mode' => $inscription->getModePaiement(),
            'date' => $inscription->getDatePaiement(),
            'parcours' => $inscription->getIdParcours(),
            'id' => $id
        ]);
    }

    
    public function getPaidStats() {

    $db = Config::getConnexion();

    $paid = $db->query("
        SELECT COUNT(*) 
        FROM inscription 
        WHERE statut_paiement='paid'
    ")->fetchColumn();

    $unpaid = $db->query("
        SELECT COUNT(*) 
        FROM inscription 
        WHERE statut_paiement='unpaid'
    ")->fetchColumn();

    return [
        'paid' => $paid,
        'unpaid' => $unpaid
    ];
}

    
    public function payer($id) {

        $db = Config::getConnexion();

        $stmt = $db->prepare("
            UPDATE inscription 
            SET statut_paiement='paid'
            WHERE id_inscription=?
        ");

        $stmt->execute([$id]);
    }
    public function getStats() {

    $db = Config::getConnexion();

    $totalInscriptions = $db->query("SELECT COUNT(*) FROM inscription")->fetchColumn();

    $totalParticipants = $db->query("SELECT SUM(nb_personnes) FROM inscription")->fetchColumn();

    $parcours = $db->query("
        SELECT id_parcours, COUNT(*) as total
        FROM inscription
        GROUP BY id_parcours
    ")->fetchAll(PDO::FETCH_ASSOC);

    $paiement = $db->query("
        SELECT mode_de_paiement, COUNT(*) as total
        FROM inscription
        GROUP BY mode_de_paiement
    ")->fetchAll(PDO::FETCH_ASSOC);

    return [
        'totalInscriptions' => $totalInscriptions,
        'totalParticipants' => $totalParticipants,
        'parcours' => $parcours,
        'paiement' => $paiement
    ];
}
}



$controller = new InscriptionController();

if (isset($_GET['delete'])) {

    $controller->delete($_GET['delete']);

    $redirect = $_GET['redirect'] ?? 'back';

    if ($redirect == "front_inscription") {
        header("Location: ../View/FrontOffice/inscription.php");
    } else {
        header("Location: ../View/BackOffice/afficher.php");
    }
    exit;
}

if (isset($_GET['pay'])) {

    $controller->payer($_GET['pay']);

    header("Location: ../View/FrontOffice/inscription.php");
    exit;
}
?>