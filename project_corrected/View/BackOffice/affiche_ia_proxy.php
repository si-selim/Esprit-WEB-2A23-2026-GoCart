<?php
/**
 * Proxy serveur → API Anthropic (Claude)
 * Contourne le CORS : l'appel est fait côté serveur PHP.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data || !isset($data['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt manquant']);
    exit;
}

$prompt   = $data['prompt'];
$marathon = $data['marathon'] ?? [];
$parcours = $data['parcours'] ?? [];

// ── Construction du prompt ──────────────────────────────────────────────────
$parcoursDesc = '';
if (empty($parcours)) {
    $parcoursDesc = 'Aucun parcours défini pour ce marathon.';
} else {
    foreach ($parcours as $p) {
        $heure = !empty($p['heure_depart']) ? ', heure de départ : ' . $p['heure_depart'] : '';
        $parcoursDesc .= "• {$p['nom_parcours']} : {$p['distance']} km, départ {$p['point_depart']} → arrivée {$p['point_arrivee']}, difficulté {$p['difficulte']}{$heure}\n";
    }
}

$fullPrompt = <<<PROMPT
Tu es un designer expert en communication événementielle sportive.
Génère une affiche HTML complète et visuelle pour un marathon.
Utilise UNIQUEMENT du HTML avec des styles inline (pas de classes Bootstrap, pas de CSS externe).
L'affiche doit être moderne, premium, inspirante. Style sombre : fond dégradé bleu nuit → bleu ciel, textes blancs.

Données du marathon :
- Nom : {$marathon['nom_marathon']}
- Organisateur : {$marathon['organisateur_marathon']}
- Région : {$marathon['region_marathon']}
- Date : {$marathon['date_marathon']}
- Places disponibles : {$marathon['nb_places_dispo']}
- Prix : {$marathon['prix_marathon']} TND

Parcours :
{$parcoursDesc}

L'affiche HTML doit contenir :
1. Un bandeau haut avec le nom du marathon en très grand (font-size: 2.5rem+, font-weight: 900)
2. Un slogan court et motivant sous le nom
3. Un bloc de 4 infos clés (date, région, organisateur, prix) dans des cards colorées semi-transparentes
4. Une section "Parcours" listant chaque parcours dans un bloc avec : nom, distance, point de départ, point d'arrivée, difficulté (badge coloré : vert=facile, orange=moyen, rouge=difficile), heure si disponible
5. Un footer avec "BarchaThon — Administration" et un message d'inscription

Réponds UNIQUEMENT avec le code HTML de l'affiche (commence par <div ...). Sans backticks, sans explication, sans balises html/body/head.
PROMPT;

// ── Appel à l'API Anthropic ─────────────────────────────────────────────────
$apiKey = getenv('ANTHROPIC_API_KEY');
// Si pas de variable d'environnement, lire depuis config locale si elle existe
if (!$apiKey) {
    $configFile = __DIR__ . '/../../config_anthropic.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        if (defined('ANTHROPIC_API_KEY')) {
            $apiKey = ANTHROPIC_API_KEY;
        }
    }
}

$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'messages'   => [
        ['role' => 'user', 'content' => $fullPrompt]
    ]
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 60,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error: ' . $curlErr]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode !== 200 || !isset($result['content'][0]['text'])) {
    http_response_code(500);
    echo json_encode([
        'error'    => 'Erreur API Anthropic',
        'http'     => $httpCode,
        'response' => $result
    ]);
    exit;
}

$html = $result['content'][0]['text'];
// Nettoyer les éventuels backticks
$html = preg_replace('/^```html?\s*/i', '', trim($html));
$html = preg_replace('/```\s*$/', '', $html);

echo json_encode(['html' => trim($html)]);
