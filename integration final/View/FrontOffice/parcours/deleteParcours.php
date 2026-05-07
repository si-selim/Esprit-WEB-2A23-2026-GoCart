<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/ParcoursController.php';

$controller = new ParcoursController();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $controller->supprimerParcours((int)$_GET['id']);
}

// redirect_to = front detail page with marathon id
if (isset($_GET['marathon_id'])) {
    header('Location: ../../FrontOffice/detailMarathon.php?id=' . (int)$_GET['marathon_id']);
} elseif (isset($_GET['redirect']) && $_GET['redirect'] === 'front') {
    header('Location: ../../FrontOffice/listMarathons.php');
} else {
    header('Location: listParcours.php');
}
exit;
