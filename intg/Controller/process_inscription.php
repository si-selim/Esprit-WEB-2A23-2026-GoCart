<?php
require_once __DIR__ . "/../Controller/InscriptionController.php";
require_once __DIR__ . "/../Controller/ParcoursController.php";
require_once __DIR__ . "/../Model/Inscription.php";
require_once __DIR__ . "/../View/FrontOffice/partials/session.php"; // ✅ pour getUserId()

if (session_status() === PHP_SESSION_NONE) session_start();

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$controller = new InscriptionController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST["action"] ?? "";

    // ✅ Récupérer l'id_user via la fonction de session.php
    $id_user = getUserId();

    // ✅ Bloquer si non connecté
    if (!$id_user) {
        header("Location: ../View/FrontOffice/login.php?error=not_logged_in");
        exit;
    }

    // ✅ Vérifier les champs obligatoires
    if (
        empty($_POST["nb_personnes"]) ||
        empty($_POST["mode_paiement"]) ||
        empty($_POST["date_paiement"]) ||
        empty($_POST["circuit"])
    ) {
        header("Location: ../View/FrontOffice/inscription.php?error=missing_fields");
        exit;
    }

    $nb          = (int)  $_POST["nb_personnes"];
    $mode        = trim(  $_POST["mode_paiement"]);
    $date        =        $_POST["date_paiement"];
    $id_parcours = (int)  $_POST["circuit"]; // ✅ vrai id_parcours depuis la base

    // ✅ Valider nb_personnes
    if ($nb <= 0) {
        header("Location: ../View/FrontOffice/inscription.php?error=invalid_number");
        exit;
    }

    // ✅ Vérifier que id_parcours existe vraiment en base
    $parcoursCtrl    = new ParcoursController();
    $allParcours     = $parcoursCtrl->afficherParcours();
    $parcoursValides = array_map('intval', array_column($allParcours, 'id_parcours'));

    if (!in_array($id_parcours, $parcoursValides)) {
        header("Location: ../View/FrontOffice/inscription.php?error=invalid_parcours");
        exit;
    }

    // ✅ Valider mode de paiement
    $modesAutorises = ['cash', 'card', 'transfer'];
    if (!in_array($mode, $modesAutorises)) {
        header("Location: ../View/FrontOffice/inscription.php?error=missing_fields");
        exit;
    }

    // ✅ Valider la date
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj) {
        header("Location: ../View/FrontOffice/inscription.php?error=missing_fields");
        exit;
    }

    // ──────────────────────────────────────────────
    // ACTION : AJOUTER
    // ──────────────────────────────────────────────
    if ($action == "add") {

        $inscription = new Inscription(
            null,           // id_inscription (auto-increment)
            $nb,            // nb_personnes
            $mode,          // mode_de_paiement
            $date,          // date_paiement
            $id_parcours,   // ✅ vrai id_parcours depuis la base
            (int) $id_user  // ✅ vrai id_user depuis la session
        );

        $last_id = $controller->add($inscription);

        header("Location: ../View/FrontOffice/dossard.php?id_inscription=" . $last_id . "&parcours_id=" . $id_parcours);
        exit;
    }

    // ──────────────────────────────────────────────
    // ACTION : MODIFIER
    // ──────────────────────────────────────────────
    if ($action == "update") {

        $id = (int) $_POST["id_inscription"];

        if ($id <= 0) {
            header("Location: ../View/FrontOffice/inscription.php?error=missing_fields");
            exit;
        }

        $inscription = new Inscription(
            $id,            // id_inscription
            $nb,            // nb_personnes
            $mode,          // mode_de_paiement
            $date,          // date_paiement
            $id_parcours,   // ✅ vrai id_parcours depuis la base
            (int) $id_user  // ✅ vrai id_user depuis la session
        );

        $controller->update($inscription, $id);

        header("Location: ../View/FrontOffice/inscription.php?success=update");
        exit;
    }
}

ob_end_flush();