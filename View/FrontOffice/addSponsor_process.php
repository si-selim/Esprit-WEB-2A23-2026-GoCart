<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/sponsorController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: addSponsor.php');
    exit;
}

$userId = getUserId();

$requiredFields = ['name', 'type', 'address', 'contact', 'email'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        header('Location: addSponsor.php?error=missing_fields');
        exit;
    }
}

$sponsor = new Sponsor(
    null,
    $_POST['name'],
    $_POST['type'],
    $_POST['address'],
    $_POST['contact'],
    $_POST['email'],
    $_POST['website'] ?? '',
    $userId
);

$controller = new sponsorController();
$controller->addSponsor($sponsor);

header('Location: mesSponsors.php');
exit;
