<?php
require_once __DIR__ . "/DossardController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../View/FrontOffice/inscription.php");
    exit;
}

$controller     = new DossardController();
$id_inscription = $_POST['id_inscription'];
$nom_global     = trim($_POST['nom_global']);

$existing = $controller->getByInscription($id_inscription);

for ($i = 0; $i < count($_POST['numero']); $i++) {
    $taille = $_POST['taille'][$i];
    $color  = trim($_POST['couleur'][$i]);

    if (!isValidHex($color)) {
        die("Couleur invalide !");
    }

    if (isset($existing[$i])) {
        $controller->updateSizeAndColor($existing[$i]['id_dossard'], $taille, $color);
    } else {
        $d = new Dossard(
            null,
            $nom_global,
            $_POST['numero'][$i],
            $taille,
            $color,
            $id_inscription
        );
        $controller->add($d);
    }
}

$parcours_id = isset($_POST['parcours_id']) ? (int)$_POST['parcours_id'] : 0;
$redirect_url = "../View/FrontOffice/voirDossard.php?id_inscription=" . $id_inscription;
if ($parcours_id > 0) {
    $redirect_url .= "&parcours_id=" . $parcours_id;
}
header("Location: " . $redirect_url);
exit;
