<?php
/**
 * API Simulation de Paiement — BarchaThon
 * POST /Controller/simulate_payment.php
 * Body JSON : { "id_inscription": 5, "mode": "card", "montant": 160 }
 *
 * Tarifs (synchronisés avec inscription.js) :
 *   id_parcours 1 = 10 km  → 20 TND/pers
 *   id_parcours 2 = 21 km  → 40 TND/pers
 *   id_parcours 3 = 42 km  → 60 TND/pers
 *   Remise groupe : 3-4 pers → -10% | 5+ pers → -20%
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../Model/config.php';
require_once __DIR__ . '/InscriptionController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$id     = intval($body['id_inscription'] ?? 0);
$mode   = trim($body['mode'] ?? '');
$montant = floatval($body['montant'] ?? 0);


if (!$id || !$mode || $montant <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes ou invalides']);
    exit;
}

if (!in_array($mode, ['cash', 'card', 'transfer'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Mode de paiement invalide']);
    exit;
}


$controller  = new InscriptionController();
$inscription = $controller->getById($id);

if (!$inscription) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Inscription introuvable']);
    exit;
}

if ($inscription['statut_paiement'] === 'paid') {
    echo json_encode(['success' => false, 'error' => 'Cette inscription est déjà payée', 'already_paid' => true]);
    exit;
}


$tarifs = [1 => 20, 2 => 40, 3 => 60];
$nb     = intval($inscription['nb_personnes']);
$parc   = intval($inscription['id_parcours']);
$pu     = $tarifs[$parc] ?? 0;
$montantServeur = $pu * $nb;
if ($nb >= 5)      $montantServeur *= 0.8;
elseif ($nb >= 3)  $montantServeur *= 0.9;
$montantServeur = round($montantServeur, 2);


if (abs($montant - $montantServeur) > 0.02) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Montant incohérent (attendu : ' . $montantServeur . ' TND)'
    ]);
    exit;
}


function simulerGateway(string $mode, float $montant, int $id): array {

    usleep(400000); 

    $txId = strtoupper(bin2hex(random_bytes(6)));

    if ($mode === 'card') {
        
        if (mt_rand(1, 10) <= 9) {
            return [
                'success'        => true,
                'transaction_id' => 'CARD-' . $txId,
                'gateway'        => 'Konnect Sandbox',
                'message'        => 'Paiement carte accepté',
                'montant'        => $montant,
                'devise'         => 'TND',
                'timestamp'      => date('Y-m-d H:i:s'),
            ];
        }
        return [
            'success' => false,
            'error'   => 'Carte refusée — fonds insuffisants (simulation)',
            'code'    => 'CARD_DECLINED',
        ];
    }

    if ($mode === 'transfer') {
        return [
            'success'        => true,
            'transaction_id' => 'VIR-' . $txId,
            'gateway'        => 'Virement Bancaire',
            'message'        => 'Virement initié — confirmation sous 24h ouvrables',
            'rib'            => 'TN59 0800 0000 0012 3456 7890',
            'reference'      => 'MARATHON-INS-' . $id,
            'montant'        => $montant,
            'devise'         => 'TND',
            'timestamp'      => date('Y-m-d H:i:s'),
        ];
    }

    
    return [
        'success'        => true,
        'transaction_id' => 'CSH-' . $txId,
        'gateway'        => 'Espèces',
        'message'        => 'Paiement espèces enregistré — à régler au bureau 3',
        'montant'        => $montant,
        'devise'         => 'TND',
        'timestamp'      => date('Y-m-d H:i:s'),
    ];
}

$result = simulerGateway($mode, $montantServeur, $id);





if ($result['success']) {
    $controller->payer($id);
    echo json_encode([
        'success'        => true,
        'id_inscription' => $id,
        'transaction'    => $result,
        'message'        => 'Paiement confirmé ! Votre dossard est disponible.',
    ]);
} else {
    http_response_code(402);
    echo json_encode([
        'success' => false,
        'error'   => $result['error'] ?? 'Paiement refusé',
        'details' => $result,
    ]);
}