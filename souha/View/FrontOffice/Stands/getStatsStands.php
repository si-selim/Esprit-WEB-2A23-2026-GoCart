<?php
// getStatsStands.php - VERSION SANS JSON
require_once __DIR__ . '/../../../config.php';

try {
    $db = config::getConnexion();
    $sql = "SELECT s.nom_stand as name, COUNT(p.ID_produit) as total 
            FROM stand s 
            LEFT JOIN produit p ON s.ID_stand = p.ID_stand 
            GROUP BY s.ID_stand";
    $query = $db->query($sql);
    $data = $query->fetchAll(PDO::FETCH_ASSOC);

    // Format personnalisé : SUCCESS|Name1:Count1;Name2:Count2
    $response = "SUCCESS|";
    foreach ($data as $row) {
        $response .= $row['name'] . ":" . $row['total'] . ";";
    }
    echo $response;

} catch (Exception $e) {
    echo "ERROR|" . $e->getMessage();
}
?>
