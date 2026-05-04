<?php
// getProduitsByStandAjax.php - VERSION SANS JSON
require_once __DIR__ . '/../../Controller/ProduitController.php';

$idStand = isset($_GET['id_stand']) ? (int)$_GET['id_stand'] : 0;

if ($idStand > 0) {
    $controller = new ProduitController();
    $produits = $controller->listProduits(); // On peut filtrer par stand si une méthode existe, sinon on filtre ici
    
    // On simule SUCCESS|Nom;Type;Prix;Stock;Image|Nom;Type;Prix;Stock;Image
    $response = "SUCCESS";
    $found = false;
    foreach ($produits as $prod) {
        if ($prod->getIDStand() === $idStand) {
            $img = $prod->getImage() ? $prod->getImage() : 'default.png';
            $response .= "|" . $prod->getNomProduit() . ";" . $prod->getType() . ";" . $prod->getPrixProduit() . ";" . $prod->getQteStock() . ";" . $img;
            $found = true;
        }
    }
    
    if (!$found) {
        echo "EMPTY";
    } else {
        echo $response;
    }
} else {
    echo "ERROR";
}
?>
