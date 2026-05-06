<?php
/**
 * Agent IA BarchaThon — Groq API (GRATUIT, sans carte bancaire)
 * POST /Controller/ai_agent_chat.php
 * Body JSON : { "message": "votre question" }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');










$GROQ_API_KEY = '';









if (empty($GROQ_API_KEY) || $GROQ_API_KEY === 'gsk_COLLEZ_VOTRE_CLE_ICI') {
    http_response_code(500);
    echo json_encode(['error' => 'Clé Groq non configurée. Ouvrez ai_agent_chat.php et collez votre clé gsk_...']);
    exit;
}

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


$systemPrompt = "Tu es l'assistant virtuel officiel du Marathon BarchaThon de Carthage.
Tu réponds UNIQUEMENT aux questions liées aux inscriptions au marathon.

CIRCUITS ET TARIFS :
- Circuit 1 — 10 km  : 20 TND par personne
- Circuit 2 — 21 km  : 40 TND par personne (semi-marathon)
- Circuit 3 — 42 km  : 60 TND par personne (marathon complet)

REMISES GROUPE :
- 3 ou 4 personnes : -10% sur le total
- 5 personnes et plus : -20% sur le total

EXEMPLES DE CALCUL :
- 1 personne 10km = 20 TND
- 2 personnes 21km = 80 TND
- 3 personnes 42km = 162 TND (remise 10%)
- 5 personnes 10km = 80 TND (remise 20%)

MODES DE PAIEMENT :
- Espèces (cash) : bureau d'inscription, Stade El Menzah, Bureau 3
- Carte bancaire : paiement en ligne via la modal de paiement
- Virement : RIB TN59 0800 0000 0012 3456 7890

RÈGLES :
- Paiement dans les 7 jours après inscription
- Aucun remboursement après confirmation
- Dossard disponible dans l'onglet Dossard après paiement

Si la question ne concerne pas le marathon, réponds :
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
        'Authorization: Bearer ' . $GROQ_API_KEY
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false   
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