<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$body        = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($body['message'] ?? '');

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message vide']);
    exit;
}

require_once __DIR__ . '/../config_groq.php';
$apiKey = GROQ_API_KEY;

// ✅ Charger config + ParcoursController (même dossier Controller/)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Parcours.php';
require_once __DIR__ . '/ParcoursController.php';

$parcoursController = new ParcoursController();
$listeParcours      = $parcoursController->afficherParcours();

// ✅ Construire la liste des parcours pour le prompt IA
$circuitsTexte = "";
foreach ($listeParcours as $p) {
    $dist    = (float)$p['distance'];
    $nom     = $p['nom_parcours'];
    $diff    = $p['difficulte'];
    $depart  = $p['point_depart']  ?? '—';
    $arrivee = $p['point_arrivee'] ?? '—';

    // Même logique de prix que le JS front
    if ($dist < 15)     $prix = 20;
    elseif ($dist < 25) $prix = 40;
    else                $prix = 60;

    $circuitsTexte .= "- {$nom} (niveau {$diff}, {$dist} km, départ : {$depart}, arrivée : {$arrivee}) → {$prix} TND/personne\n";
}

if (empty($circuitsTexte)) {
    $circuitsTexte = "- Aucun parcours disponible pour le moment.\n";
}

$systemPrompt = "Tu es l'assistant virtuel officiel du Marathon BarchaThon de Carthage.
Tu réponds UNIQUEMENT aux questions liées aux inscriptions au marathon.

PARCOURS OFFICIELS DISPONIBLES :
{$circuitsTexte}
RÈGLE DE CALCUL DU PRIX :
- Distance < 15 km  → 20 TND par personne
- Distance < 25 km  → 40 TND par personne
- Distance ≥ 25 km  → 60 TND par personne

REMISES GROUPE :
- 3 ou 4 personnes : -10% sur le total
- 5 personnes et plus : -20% sur le total

MODES DE PAIEMENT :
- Espèces (cash) : bureau d'inscription, Stade El Menzah, Bureau 3
- Carte bancaire : paiement en ligne via la modal de paiement
- Virement : RIB TN59 0800 0000 0012 3456 7890

RÈGLES IMPORTANTES :
- Paiement dans les 7 jours après inscription
- Aucun remboursement après confirmation
- Dossard disponible dans l'onglet Dossard après paiement

INSTRUCTIONS STRICTES :
- Liste UNIQUEMENT les parcours de la section PARCOURS OFFICIELS ci-dessus.
- N'invente aucun parcours, n'utilise jamais Circuit 1/2/3 ni 10km/21km/42km fixes.
- Utilise toujours les vrais noms : ex. 'Parcours Marina Run', 'Parcours Yasmine Run', etc.
- Si on te demande les parcours disponibles, liste-les tous avec leur distance et prix.

Si la question ne concerne pas le marathon BarchaThon, réponds uniquement :
'Je suis spécialisé uniquement pour les inscriptions au Marathon BarchaThon.'

Réponds toujours en français, de façon claire et concise.";

$payload = json_encode([
    'model'    => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userMessage]
    ],
    'max_tokens'  => 512,
    'temperature' => 0.7
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur réseau : ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $err = json_decode($response, true);
    $msg = $err['error']['message'] ?? $response;
    http_response_code(500);
    echo json_encode(['error' => 'Erreur Groq (' . $httpCode . ') : ' . $msg]);
    exit;
}

$data  = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? 'Désolé, je ne peux pas répondre pour le moment.';

echo json_encode(['reply' => $reply]);