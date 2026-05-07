<?php
// getStandsByParcours.php - VERSION SANS JSON
require_once __DIR__ . '/../../Controller/standcontroller.php';

if (isset($_GET['id_parcours'])) {
    $idParcours = intval($_GET['id_parcours']);
    $controller = new StandController();
    $stands = $controller->getStandsByParcours($idParcours);
    
    $response = "SUCCESS|";
    foreach ($stands as $stand) {
        $response .= $stand['ID_stand'] . ":" . $stand['nom_stand'] . ":" . $stand['position'] . ":" . $stand['description'] . ";";
    }
    echo $response;
} else {
    echo "ERROR|Paramètre id_parcours manquant";
}
?>
