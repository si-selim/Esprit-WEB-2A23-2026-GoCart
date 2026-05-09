<?php
require_once __DIR__ . '/../../Controller/sponsoringController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check required fields
    $requiredFields = ['name', 'dateDebut', 'dateFin', 'montant', 'etat', 'idSponsor', 'idMarathon'];
    $errors = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = $field;
        }
    }
    if (!empty($errors)) {
        // Redirect back with error
        header('Location: addSponsoring.php?error=missing_fields');
        exit;
    }

    // Create Sponsoring object
    $sponsoring = new Sponsoring(
        null,
        $_POST['name'],
        $_POST['dateDebut'],
        $_POST['dateFin'],
        floatval($_POST['montant']),
        $_POST['etat'],
        intval($_POST['idSponsor']),
        intval($_POST['idMarathon'])
    );

    // Add to database
    $controller = new sponsoringController();
    $controller->addSponsoring($sponsoring);

    // Redirect back to sponsors page or sponsoring list
    header('Location: mesSponsors.php');
    exit;
} else {
    // If not POST, redirect or show error
    header('Location: addSponsoring.php');
    exit;
}
?>