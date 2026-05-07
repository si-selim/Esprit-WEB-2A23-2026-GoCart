<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/MarathonController.php';

$controller = new MarathonController();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $controller->supprimerMarathon((int)$_GET['id']);
}

// Redirect back to front catalogue or back office list
if (isset($_GET['redirect']) && $_GET['redirect'] === 'front') {
    header('Location: ../../FrontOffice/listMarathons.php');
} else {
    header('Location: listMarathons.php');
}
exit;
