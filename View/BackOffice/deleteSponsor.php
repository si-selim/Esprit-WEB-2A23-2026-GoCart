<?php
require_once __DIR__ . '/../../Controller/sponsorController.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $controller = new sponsorController();
    $controller->deleteSponsor($id);
}

header('Location: dashboard.php?tab=sponsors');
exit;
?>
