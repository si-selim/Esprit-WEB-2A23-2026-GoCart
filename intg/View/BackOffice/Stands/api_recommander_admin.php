<?php
/**
 * API Recommandation Admin
 * Retourne les stands les plus proches avec statistiques de produits
 */

require_once __DIR__ . '/../../../Controller/StandController.php';
require_once __DIR__ . '/../../../Controller/ProduitController.php';

// Désactiver l'affichage des erreurs pour ne pas casser le JSON
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$lon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

if ($lat === null || $lon === null) {
    echo json_encode(['error' => 'Paramètres de localisation manquants.']);
    exit;
}

try {
    $standController = new StandController();
    $produitController = new ProduitController();

    // Récupérer les stands triés par distance
    $recommendations = $standController->getRecommendationsByCoords($lat, $lon);

    $response = [];
    foreach ($recommendations as $stand) {
        // Statistiques produits pour chaque stand
        $id_stand = (int)$stand['ID_stand'];
        $total_produits = $produitController->countProduitsByStand($id_stand);
        
        // Calculer le stock réel
        $produits = $produitController->getProduitsByStand($id_stand);
        $en_stock = 0;
        if (is_array($produits)) {
            foreach ($produits as $p) {
                if (isset($p['qte_stock']) && (int)$p['qte_stock'] > 0) {
                    $en_stock++;
                }
            }
        }

        $response[] = [
            'ID_stand'       => $id_stand,
            'nom_stand'      => $stand['nom_stand'],
            'distance_km'    => $stand['distance_km'],
            'position'       => $stand['position'],
            'total_produits' => $total_produits,
            'en_stock'       => $en_stock,
            'label'          => 'Parcours #' . ($stand['ID_parcours'] ?? '?')
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
