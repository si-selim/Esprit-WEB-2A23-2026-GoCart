<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$userId = $user['id_user'] ?? $user['id'];

include '../../Controller/CommandeController.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$orderId) {
    header('Location: Mes commandes.php?error=invalid_id');
    exit;
}

$commandeC = new CommandeController();
$commande = $commandeC->showCommande($orderId);

if (!$commande || $commande['idutilisateur'] != $userId || strtolower(trim($commande['statut'])) !== 'en cours') {
    header('Location: Mes commandes.php?error=invalid_order');
    exit;
}

// Confirmer la commande
$commandeC->updateCommandeStatus($orderId, 'confirmé');

// Envoyer email de confirmation
require_once '../../Controller/Mailer.php';
$userEmail = $user['email'] ?? '';
if ($userEmail) {
    $subject = 'Confirmation de commande - BarchaThon';
    $body = "<p>Bonjour,</p>\n"
          . "<p>Votre commande #" . $orderId . " a été confirmée avec succès.</p>\n"
          . "<p>Montant : <strong>" . number_format($commande['montanttotale'], 2, ',', ' ') . " TND</strong></p>\n"
          . "<p>Date : " . date('d/m/Y H:i', strtotime($commande['datecommande'])) . "</p>\n"
          . "<p>Stand : #" . $commande['idstand'] . "</p>\n"
          . "<p>Vous pouvez consulter les détails de votre commande sur la page <a href='http://" . $_SERVER['HTTP_HOST'] . "/Integ-standProduit/View/FrontOffice/orderDetails.php?id=" . $orderId . "'>Voir les détails</a>.</p>\n"
          . "<p>Merci pour votre confiance.</p>";
    Mailer::send($userEmail, $subject, $body);
}

header('Location: Mes commandes.php?confirmed=1');
exit;
?>