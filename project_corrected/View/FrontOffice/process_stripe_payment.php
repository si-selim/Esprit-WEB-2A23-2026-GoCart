<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/InscriptionMarathonController.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/CommandeController.php';
require_once __DIR__ . '/../../Controller/LigneCommandeController.php';
require_once __DIR__ . '/../../Controller/ProduitController.php';
require_once __DIR__ . '/../../Controller/Mailer.php';
require_once __DIR__ . '/../../StripePayment.php';

header('Content-Type: application/json');

$stripe_secret = 'your stripe_secret_key_here'; // Remplacez par votre clé secrète Stripe
$stripe = new StripePayment($stripe_secret);

$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$montant = (float)($_POST['montant'] ?? 0);
$paymentMethodId = $_POST['payment_method_id'] ?? $_POST['stripe_payment_method_id'] ?? '';
$methodePaiement = $_POST['methode_paiement'] ?? 'stripe';
$parcours_id = isset($_POST['parcours_id']) ? (int)$_POST['parcours_id'] : 0;
$stand_id = isset($_POST['stand_id']) ? (int)$_POST['stand_id'] : 0;

$userId = $user['id_user'] ?? $user['id'];

if (!$paymentMethodId || !$type || !$montant || ($type === 'marathon' && !$id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

try {
        if ($type === 'commande') {
            $prodCtrl = new ProduitController();
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Le panier est vide.']);
                exit;
            }

            foreach ($_SESSION['cart'] as $item) {
                $currentProduct = $prodCtrl->getProduit($item['idproduit']);
                if (!$currentProduct || !$currentProduct['en_out_stock'] || $currentProduct['qte_stock'] < $item['quantite']) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Stock insuffisant pour l’un des produits du panier. Ajustez votre commande.']);
                    exit;
                }
            }
        }

    $paymentIntent = $stripe->createAndConfirmPaymentIntent(
        $montant,
        'usd',
        $paymentMethodId,
        [
            'user_id' => $userId,
            'type' => $type,
            'id' => $id,
            'parcours_id' => $parcours_id,
            'stand_id' => $stand_id
        ]
    );

    if ($paymentIntent['status'] === 'succeeded') {
        // Paiement réussi, traiter l'inscription ou la commande
        if ($type === 'marathon') {
            $inscCtrl = new InscriptionMarathonController();
            
            if ($inscCtrl->estDejaInscrit($userId, $id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Vous êtes déjà inscrit à ce marathon']);
                exit;
            }

            $inscription_id = $inscCtrl->inscrireUtilisateur($userId, $id, $montant);

            if ($inscription_id) {
                $inscCtrl->mettreAJourPaiement($inscription_id, 'paye', 'stripe');
                $inscCtrl->decrementerPlaces($id);

                $_SESSION['success_message'] = '✅ Inscription au marathon confirmée !';
                
                echo json_encode([
                    'success' => true,
                    'redirect' => "inscription_confirmation.php?marathon_id=$id&parcours_id=$parcours_id&inscription_id=$inscription_id"
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'inscription']);
            }
        } elseif ($type === 'commande') {
            $commandeC = new CommandeController();
            $ligneC = new LigneCommandeController();
            $prodCtrl = new ProduitController();

            $commande = new Commande(null, $userId, $stand_id ?: null, date('Y-m-d H:i:s'), 'en cours', $montant, 'stripe');
            $newCommandeId = $commandeC->addCommande($commande);

            if ($newCommandeId) {
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                        $ligneC->addLigneCommande($ligne);
                        $prodCtrl->decrementStock($item['idproduit'], $item['quantite']);
                    }
                }

                $_SESSION['cart'] = [];
                $_SESSION['success_message'] = '✅ Paiement confirmé !';

                $userEmail = $user['email'] ?? '';
                if ($userEmail) {
                    $subject = 'Confirmation de paiement - BarchaThon';
                    $body = "<p>Bonjour,</p>\n"
                          . "<p>Votre paiement de <strong>" . number_format($montant, 2, ',', ' ') . " TND</strong> a bien été enregistré.</p>\n"
                          . "<p>Vous pouvez consulter votre commande et vos détails sur la page <a href='https://{$_SERVER['HTTP_HOST']}/Integ-standProduit/View/FrontOffice/Mes%20commandes.php'>Voir mes commandes</a>.</p>\n"
                          . "<p>Merci pour votre confiance.</p>";
                    Mailer::send($userEmail, $subject, $body);
                }

                echo json_encode([
                    'success' => true,
                    'redirect' => 'Mes commandes.php'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la création de la commande']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Type invalide']);
        }
    } elseif ($paymentIntent['status'] === 'requires_action') {
        // Le paiement nécessite une authentification supplémentaire (3D Secure, etc.)
        echo json_encode([
            'success' => false,
            'client_secret' => $paymentIntent['client_secret'] ?? '',
            'requires_action' => true
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Paiement échoué. Statut: ' . ($paymentIntent['status'] ?? 'inconnu')]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}


