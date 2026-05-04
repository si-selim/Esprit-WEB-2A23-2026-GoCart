<?php
require_once __DIR__ . "/../../Model/Marathon.php";
require_once __DIR__ . "/../../Controller/MarathonController.php";

/* Création d’un objet Marathon (test) */
$marathon1 = new Marathon(
    null,
    "Marathon de Tunis",
    "image.jpg",
    "Fédération Tunisienne",
    "Tunis",
    "2026-05-10",
    1000,
    50.00
);

echo "<h2>Affichage avec var_dump :</h2>";
var_dump($marathon1);

echo "<hr>";

/* Controller */
$controller = new MarathonController();

/* Afficher tous les marathons (test DB) */
$marathons = $controller->afficherMarathon();

echo "<h2>Liste des marathons :</h2>";
echo "<pre>";
print_r($marathons);
echo "</pre>";
?>
