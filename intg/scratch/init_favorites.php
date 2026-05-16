<?php
require_once __DIR__ . '/../config.php';
$db = config::getConnexion();
try {
    $sql = "CREATE TABLE IF NOT EXISTS favorites (
        id_favorite INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        id_produit INT NOT NULL,
        UNIQUE KEY user_product (id_user, id_produit)
    )";
    $db->exec($sql);
    echo "Table favorites créée ou déjà existante.";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
