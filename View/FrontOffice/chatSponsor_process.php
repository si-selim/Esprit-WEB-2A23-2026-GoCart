<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/../../controller/chatSponsorController.php";
//include "../../controller/chatSponsorController.php";

if (isset($_POST['message']) && !empty($_POST['message'])) {

    $chat = new chatSponsorController();

    //echo $chat->send($_POST['message']);
    // Le contrôleur fait un return, le routeur fait l'echo final pour le client (JS)
    echo $chat->send($_POST['message']);
    exit; // Important pour arrêter l'exécution ici et ne pas envoyer de HTML inutile
}