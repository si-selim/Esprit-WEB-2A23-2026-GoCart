<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/CommandeController.php';

if (!isAdmin()) {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$cCtrl = new CommandeController();

$searchC = trim($_GET['search'] ?? '');
$sortC = $_GET['sort'] ?? 'datecommande';
$dirC = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

$listCommandes = $cCtrl->listCommandesFiltered($searchC, $sortC, $dirC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="commandes_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['ID Commande', 'ID Utilisateur', 'ID Stand', 'Date Commande', 'Montant Total (TND)', 'Methode Paiement', 'Statut'], ';');

foreach ($listCommandes as $commande) {
    $statut_label = '';
    switch($commande['statut']) {
        case 'en attente de validation':
            $statut_label = 'en attente';
            break;
        case 'validée':
            $statut_label = 'validé';
            break;
        case 'annulée':
            $statut_label = 'annulé';
            break;
        case 'non valide':
            $statut_label = 'non valide';
            break;
        case 'EN COURS':
            $statut_label = 'en cours';
            break;
        default:
            $statut_label = $commande['statut'];
            break;
    }
    
    fputcsv($output, [
        $commande['idcommande'],
        $commande['idutilisateur'],
        $commande['idstand'] ?? '-',
        date('d/m/Y H:i', strtotime($commande['datecommande'])),
        number_format($commande['montanttotale'], 2, ',', ' '),
        $commande['methode_paiement'] ?? 'N/A',
        $statut_label
    ], ';');
}

fclose($output);
exit;
