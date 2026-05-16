<?php
require_once __DIR__ . '/../../Controller/sponsoringController.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $controller = new sponsoringController();
    $controller->deleteSponsoring($id);
}

header('Location: mesSponsors.php');
exit;
