<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
include '../../../Controller/ProduitController.php';

header('Content-Type: application/json');

$groqApiKey = '';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get JSON input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$budget = isset($input['budget']) ? (float)$input['budget'] : 0;
$personnes = isset($input['personnes']) ? (int)$input['personnes'] : 0;
$stand_id = isset($input['stand_id']) ? (int)$input['stand_id'] : 0;

if ($budget <= 0 || $personnes <= 0 || $stand_id <= 0) {
    echo json_encode(['error' => 'Paramètres invalides (budget, personnes ou stand_id).']);
    exit;
}

// Fetch products
$prodCtrl = new ProduitController();
$products = $prodCtrl->afficherProduitsParStand($stand_id);

$availableProducts = [];
foreach ($products as $prod) {
    $id = $prod['id_produit'] ?? $prod['ID_produit'] ?? 0;
    $stock = $prod['qte_stock'] ?? $prod['Qte_stock'] ?? 0;
    $en_out_stock = $prod['en_out_stock'] ?? $prod['En_out_stock'] ?? 0;
    
    if ($id && $en_out_stock && $stock > 0) {
        $availableProducts[] = [
            'id' => $id,
            'nom' => $prod['nom_produit'] ?? $prod['Nom_produit'] ?? 'Inconnu',
            'prix' => $prod['prix_produit'] ?? $prod['Prix_produit'] ?? 0,
            'stock' => $stock,
            'type' => $prod['type'] ?? 'Produit'
        ];
    }
}

if (empty($availableProducts)) {
    echo json_encode(['error' => 'Aucun produit disponible en stock pour ce stand.']);
    exit;
}

// Prepare Groq API Prompt
$systemPrompt = "Tu es un expert en nutrition sportive spécialisé pour les marathoniens.
Le client veut préparer un panier pour un marathon pour $personnes personne(s) avec un budget maximum de $budget TND.
Voici la liste des produits disponibles en stock :
" . json_encode($availableProducts) . "

INSTRUCTIONS IMPORTANTES:
1. Analyse les produits et sélectionne UNIQUEMENT ceux qui sont bénéfiques pour un coureur de marathon (ex: eau, boissons énergétiques, barres protéinées, fruits, snacks énergétiques).
2. ÉVITE ABSOLUMENT les produits non adaptés (ex: sodas sucrés, snacks trop gras ou frits, chocolat au lait ou blanc si ce n'est pas utile, etc.).
3. Adapte les quantités selon le nombre de personnes ($personnes). Idéalement, chaque personne devrait avoir l'essentiel, mais NE DÉPASSE JAMAIS la quantité en 'stock' disponible pour un produit. Si le client demande pour 10 personnes mais qu'il y a 7 eaux en stock, tu ne peux suggérer que 7 eaux au maximum.
4. LE TOTAL DU PANIER (prix unitaire * quantité) NE DOIT JAMAIS DÉPASSER LE BUDGET DE $budget TND. Si le budget est trop faible, sélectionne les produits les plus vitaux (comme l'eau) en priorité.
5. Tu dois OBLIGATOIREMENT retourner ta réponse au format JSON strictement valide, avec la structure suivante et RIEN D'AUTRE (pas de texte avant ou après le JSON) :
{
  \"explication\": \"Une phrase ou deux expliquant de manière motivante et experte pourquoi ce choix de produits est parfait pour leur marathon.\",
  \"calories_estimees\": \"Texte court (ex: 'Env. 500 kcal par personne')\",
  \"score_marathon\": NombreEntierEntre1Et10,
  \"recommandations\": \"Un court conseil nutritionnel pour la course\",
  \"produits\": [
    {
      \"idproduit\": IdDuProduitChoisi,
      \"nom\": \"Nom exact du produit\",
      \"prix\": PrixUnitaire,
      \"quantite\": QuantiteChoisie (doit etre <= au stock fourni),
      \"raison\": \"Une courte raison du choix (ex: 'Hydratation essentielle')\"
    }
  ]
}";

$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => "Génère le panier intelligent pour $personnes personnes avec $budget TND maximum. Formatte la réponse en JSON strict."]
    ],
    'temperature' => 0.2,
    'response_format' => ['type' => 'json_object']
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $groqApiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing if needed

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode(['error' => 'Erreur curl: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'Erreur API Groq (HTTP '.$httpCode.'): ' . $response]);
    exit;
}

$responseDecoded = json_decode($response, true);
if (isset($responseDecoded['choices'][0]['message']['content'])) {
    $content = $responseDecoded['choices'][0]['message']['content'];
    
    // Parse the inner JSON
    $cartData = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'L\'IA a retourné un format invalide. Veuillez réessayer.']);
        exit;
    }
    
    // Security check: validate stock and budget locally to prevent AI hallucinations
    $validatedProducts = [];
    $totalPrice = 0;
    
    if (isset($cartData['produits']) && is_array($cartData['produits'])) {
        foreach ($cartData['produits'] as $item) {
            // Find the product in available products
            $foundProd = null;
            foreach ($availableProducts as $ap) {
                if ($ap['id'] == $item['idproduit']) {
                    $foundProd = $ap;
                    break;
                }
            }
            
            if ($foundProd) {
                $qty = (int)$item['quantite'];
                // Check stock
                if ($qty > $foundProd['stock']) {
                    $qty = $foundProd['stock'];
                }
                
                if ($qty > 0) {
                    $itemTotal = $qty * $foundProd['prix'];
                    // Check budget (if adding this exceeds budget, we reduce qty or skip)
                    if ($totalPrice + $itemTotal > $budget) {
                        $affordableQty = floor(($budget - $totalPrice) / $foundProd['prix']);
                        if ($affordableQty > 0) {
                            $qty = $affordableQty;
                            $itemTotal = $qty * $foundProd['prix'];
                        } else {
                            continue; // Skip this product
                        }
                    }
                    
                    $totalPrice += $itemTotal;
                    $validatedProducts[] = [
                        'idproduit' => $foundProd['id'],
                        'nom' => $foundProd['nom'],
                        'prix' => $foundProd['prix'],
                        'quantite' => $qty,
                        'raison' => $item['raison'] ?? 'Produit recommandé'
                    ];
                }
            }
        }
    }
    
    $cartData['produits'] = $validatedProducts;
    $cartData['total'] = round($totalPrice, 2);
    
    if (empty($validatedProducts)) {
        echo json_encode(['error' => 'L\'IA n\'a pas pu trouver de combinaison adaptée respectant les stocks et le budget (ou budget trop faible).']);
        exit;
    }
    
    echo json_encode($cartData);
} else {
    echo json_encode(['error' => 'Réponse inattendue de l\'API Groq.']);
}
