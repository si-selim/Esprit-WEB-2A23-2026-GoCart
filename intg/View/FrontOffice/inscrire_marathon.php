<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/InscriptionMarathonController.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';

$user = getCurrentUser();
$userId = $user['id_user'] ?? $user['id'];
if (!$user || $user['role'] !== 'participant' || !$userId) {
    header('Location: login.php');
    exit;
}

$id_marathon = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;
if ($id_marathon <= 0) {
    header('Location: listMarathons.php');
    exit;
}

$marathonCtrl = new MarathonController();
$marathon = $marathonCtrl->showMarathon($id_marathon);
if (!$marathon) {
    header('Location: listMarathons.php');
    exit;
}

$inscCtrl = new InscriptionMarathonController();

// Vérifier si déjà inscrit
if ($inscCtrl->estDejaInscrit($userId, $id_marathon)) {
    $redirect_url = $parcours_id > 0 ? "detailParcours.php?id=$parcours_id&error=" . urlencode('Vous êtes déjà inscrit à ce marathon.') : "detailMarathon.php?id=$id_marathon&error=" . urlencode('Vous êtes déjà inscrit à ce marathon.');
    header('Location: ' . $redirect_url);
    exit;
}

// Vérifier les places disponibles
if ($marathon['nb_places_dispo'] <= 0) {
    $redirect_url = $parcours_id > 0 ? "detailParcours.php?id=$parcours_id&error=" . urlencode('Marathon complet.') : "detailMarathon.php?id=$id_marathon&error=" . urlencode('Marathon complet.');
    header('Location: ' . $redirect_url);
    exit;
}

$prix = (float)$marathon['prix_marathon'];

if ($prix == 0) {
    // Inscription gratuite
    $inscription_id = $inscCtrl->inscrireUtilisateur($userId, $id_marathon, 0);
    if ($inscription_id) {
        $inscCtrl->mettreAJourPaiement($inscription_id, 'paye', 'gratuit');
        $inscCtrl->decrementerPlaces($id_marathon);
        $redirect_url = "inscription_confirmation.php?marathon_id=$id_marathon&parcours_id=$parcours_id&inscription_id=$inscription_id";
        header('Location: ' . $redirect_url);
    } else {
        $redirect_url = $parcours_id > 0 ? "detailParcours.php?id=$parcours_id&error=" . urlencode('Erreur lors de l\'inscription.') : "detailMarathon.php?id=$id_marathon&error=" . urlencode('Erreur lors de l\'inscription.');
        header('Location: ' . $redirect_url);
    }
} else {
    // Redirection vers paiement
    $redirect_url = "paiement.php?type=marathon&id=$id_marathon&montant=$prix";
    if ($parcours_id > 0) {
        $redirect_url .= "&parcours_id=$parcours_id";
    }
    header('Location: ' . $redirect_url);
}

exit;
?>
