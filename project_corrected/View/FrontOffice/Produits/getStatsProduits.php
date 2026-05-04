<?php
// getStatsProduits.php - VERSION SANS JSON
require_once __DIR__ . '/../../../config.php';

try {
    $db = config::getConnexion();
    $sql = "SELECT en_out_stock as status, COUNT(*) as total FROM produit GROUP BY en_out_stock";
    $query = $db->query($sql);
    $dataRaw = $query->fetchAll(PDO::FETCH_ASSOC);

    $dispo = 0;
    $rupture = 0;
    foreach ($dataRaw as $row) {
        $status = strtolower(trim($row['status']));
        if (stripos($status, 'dispo') !== false || $status === '1') {
            $dispo += (int)$row['total'];
        } else {
            $rupture += (int)$row['total'];
        }
    }
    // Format simple : Dispo:Rupture
    echo $dispo . ":" . $rupture;

} catch (Exception $e) {
    echo "0:0";
}
?>
