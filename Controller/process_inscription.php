<?php
require_once __DIR__ . "/../Controller/InscriptionController.php";
require_once __DIR__ . "/../Model/Inscription.php";

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$controller = new InscriptionController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST["action"] ?? "";

    if (
        empty($_POST["nb_personnes"]) ||
        empty($_POST["mode_paiement"]) ||
        empty($_POST["date_paiement"]) ||
        empty($_POST["circuit"])
    ) {
        header("Location: ../View/FrontOffice/inscription.php?error=missing_fields");
        exit;
    }

    $nb = (int) $_POST["nb_personnes"];
    $mode = trim($_POST["mode_paiement"]);
    $date = $_POST["date_paiement"];
    $id_parcours = (int) $_POST["circuit"];

    if ($nb <= 0) {
        header("Location: ../View/FrontOffice/inscription.php?error=invalid_number");
        exit;
    }

    if ($action == "add") {

        $inscription = new Inscription(
            null,
            $nb,
            $mode,
            $date,
            $id_parcours,
            1
        );

        $last_id = $controller->add($inscription);

        header("Location: ../View/FrontOffice/dossard.php?id_inscription=" . $last_id);
        exit;
    }

    if ($action == "update") {

        $id = (int) $_POST["id_inscription"];

        $inscription = new Inscription(
            $id,
            $nb,
            $mode,
            $date,
            $id_parcours,
            1
        );

        $controller->update($inscription, $id);

        header("Location: ../View/FrontOffice/inscription.php?success=update");
        exit;
    }
}

ob_end_flush();