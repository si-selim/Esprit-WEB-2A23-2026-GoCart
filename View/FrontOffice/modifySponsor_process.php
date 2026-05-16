<?php
require_once __DIR__ . '/../../Controller/sponsorController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check required fields
    $requiredFields = ['name', 'type', 'address', 'contact', 'email'];
    $errors = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = $field;
        }
    }
    if (!empty($errors)) {
        // Redirect back with error
        header('Location: modifySponsor.php?id=' . $id . '&error=missing_fields');
        exit;
    }

    // Get current sponsor
    $controller = new sponsorController();
    $currentSponsor = $controller->showSponsor($id);

    // Create Sponsor object with updated data
    $sponsor = new Sponsor(
        $id,
        $_POST['name'],
        $_POST['type'],
        $_POST['address'],
        $_POST['contact'],
        $_POST['email'],
        $_POST['website'],
        1                        // intval($_POST['idUser'])
    );

    // Update in database
    $controller->updateSponsor($sponsor, $id);

    // Redirect back to sponsors page
    header('Location: mesSponsors.php');
    exit;
} else {
    // If not POST or no id, redirect
    header('Location: mesSponsors.php');
    exit;
}
?>