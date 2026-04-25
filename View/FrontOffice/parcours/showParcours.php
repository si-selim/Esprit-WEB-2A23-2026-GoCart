<?php
require_once __DIR__ . "/../../Model/Parcours.php";
require_once __DIR__ . "/../../Controller/ParcoursController.php";

/* Création d’un objet Parcours (test) */
$parcours1 = new Parcours(
    null,
    "Parcours Test",
    "Ariana",
    "Centre Ville",
    10.5,
    "moyen",
    1
);

echo "<h2>Affichage avec var_dump :</h2>";
var_dump($parcours1);

echo "<hr>";

/* Controller */
$controller = new ParcoursController();

/* Afficher tous les parcours */
$parcours = $controller->afficherParcours();

echo "<h2>Liste des parcours :</h2>";
echo "<pre>";
print_r($parcours);
echo "</pre>";
?>