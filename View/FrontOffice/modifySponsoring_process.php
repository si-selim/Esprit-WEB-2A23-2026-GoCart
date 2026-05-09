<?php
require_once __DIR__ . '/../../Controller/sponsoringController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requiredFields = ['idSponsoring', 'name', 'dateDebut', 'dateFin', 'montant', 'etat', 'idSponsor', 'idMarathon'];
    $errors = [];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $field;
        }
    }

    if (!empty($errors)) {
        header('Location: modifySponsoring.php?id=' . urlencode($_POST['idSponsoring'] ?? '')); 
        exit;
    }

    $idSponsoring = intval($_POST['idSponsoring']);
    $sponsoring = new Sponsoring(
        $idSponsoring,
        trim($_POST['name']),
        trim($_POST['dateDebut']),
        trim($_POST['dateFin']),
        floatval($_POST['montant']),
        trim($_POST['etat']),
        intval($_POST['idSponsor']),
        intval($_POST['idMarathon'])
    );

    $controller = new sponsoringController();
    $controller->updateSponsoring($sponsoring, $idSponsoring);

    header('Location: mesSponsors.php');
    exit;
}

header('Location: modifySponsoring.php');
exit;
