<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/InscriptionMarathonController.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/CommandeController.php';
require_once __DIR__ . '/../../Controller/LigneCommandeController.php';
require_once __DIR__ . '/../../Controller/ProduitController.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$userId = $user['id_user'] ?? $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listMarathons.php');
    exit;
}

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$montant = (float)($_POST['montant'] ?? 0);
$action = $_POST['action'] ?? 'now';
$methode = $_POST['methode_paiement'] ?? '';
$paymentMethodId = $_POST['payment_method_id'] ?? '';
$d17_reference = trim($_POST['d17_reference'] ?? '');
$paypal_email = trim($_POST['paypal_email'] ?? '');
$parcours_id = isset($_POST['parcours_id']) ? (int)$_POST['parcours_id'] : 0;
$stand_id = isset($_POST['stand_id']) ? (int)$_POST['stand_id'] : 0;

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_d17($value) {
    return preg_match('/^[A-Za-z0-9\-]{5,30}$/', $value);
}

$message = '';
$success = false;

if ($action === 'now') {
    if (!$methode) {
        $message = 'Sélectionnez une méthode de paiement.';
    }

    if ($methode === 'd17') {
        if (!$d17_reference) {
            $message = 'La référence D17 est requise.';
        } elseif (!is_valid_d17($d17_reference)) {
            $message = 'Référence D17 invalide. Utilisez uniquement lettres, chiffres et tirets.';
        }
    }

    if ($methode === 'paypal') {
        if (!$paypal_email) {
            $message = 'L\'email PayPal est requis.';
        } elseif (!is_valid_email($paypal_email)) {
            $message = 'Email PayPal invalide.';
        }
    }

    if ($methode === 'stripe' && !$paymentMethodId) {
        $message = 'Aucun identifiant de paiement Stripe reçu. Réessayez.';
    }
}

if ($message) {
    $redirectUrl = "paiement.php?type=$type&id=$id&montant=$montant";
    if ($parcours_id > 0) {
        $redirectUrl .= "&parcours_id=$parcours_id";
    }
    if ($stand_id > 0) {
        $redirectUrl .= "&stand_id=$stand_id";
    }
    $redirectUrl .= "&error=" . urlencode($message);
    header('Location: ' . $redirectUrl);
    exit;
}

if ($type === 'marathon') {
    $inscCtrl = new InscriptionMarathonController();
    if ($inscCtrl->estDejaInscrit($userId, $id)) {
        $message = 'Vous êtes déjà inscrit à ce marathon.';
    } else {
        // Simuler le paiement (en production, intégrer vraie API de paiement)
        $paiement_reussi = true; // Simulation

        if ($paiement_reussi) {
            // Inscrire l'utilisateur
            $inscription_id = $inscCtrl->inscrireUtilisateur($userId, $id, $montant);

            if ($inscription_id) {
                // Mettre à jour le statut de paiement
                $inscCtrl->mettreAJourPaiement($inscription_id, 'paye', $methode);

                // Décrémenter les places
                $inscCtrl->decrementerPlaces($id);

                $success = true;
                $message = 'Inscription au marathon confirmée !';
            } else {
                $message = 'Erreur lors de l\'inscription.';
            }
        } else {
            $message = 'Paiement échoué. Veuillez réessayer.';
        }
    }

    // Redirection
    if ($success) {
        $redirect_url = "inscription_confirmation.php?marathon_id=$id&parcours_id=$parcours_id&inscription_id=$inscription_id";
        header('Location: ' . $redirect_url);
    } else {
        $redirect_url = $parcours_id > 0 ? "paiement.php?type=marathon&id=$id&montant=$montant&parcours_id=$parcours_id&error=" . urlencode($message) : "paiement.php?type=marathon&id=$id&montant=$montant&error=" . urlencode($message);
        header('Location: ' . $redirect_url);
    }
    exit;

}

if ($type === 'commande') {
    $commandeC = new CommandeController();
    $ligneC = new LigneCommandeController();
    $prodCtrl = new ProduitController();
    $existingOrder = $id > 0 ? $commandeC->showCommande($id) : null;
    $existingOrderOwner = $existingOrder && $existingOrder['idutilisateur'] == $userId;

    if ($existingOrder && !$existingOrderOwner) {
        $redirectUrl = 'paiement.php?type=commande&id=' . $id . '&montant=' . $montant . '&stand_id=' . $stand_id . '&error=' . urlencode('Commande invalide.');
        header('Location: ' . $redirectUrl);
        exit;
    }

    if ($action === 'later') {
        if ($existingOrder && $existingOrderOwner && strtolower(trim($existingOrder['statut'])) === 'en cours') {
            $commandeC->updateCommandePayment($id, 'en cours', 'en attente');
            $success = true;
            $message = 'Commande conservée en attente de paiement. Vous pouvez la régler depuis Mes commandes.';
        } else {
            $cartValid = true;

            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                $cartValid = false;
                $message = 'Le panier est vide. Impossible de créer la commande.';
            }

            if ($cartValid) {
                foreach ($_SESSION['cart'] as $item) {
                    $currentProduct = $prodCtrl->getProduit($item['idproduit']);
                    if (!$currentProduct || !$currentProduct['en_out_stock'] || $currentProduct['qte_stock'] < $item['quantite']) {
                        $cartValid = false;
                        $message = 'Stock insuffisant pour l’un des produits du panier. Ajustez votre commande avant de payer.';
                        break;
                    }
                }
            }

            if ($cartValid) {
                $commande = new Commande(null, $userId, $stand_id ?: null, null, date('Y-m-d H:i:s'), 'en cours', $montant, 'en attente');
                $newCommandeId = $commandeC->addCommande($commande);

                if ($newCommandeId) {
                    foreach ($_SESSION['cart'] as $item) {
                        $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                        $ligneC->addLigneCommande($ligne);
                        $prodCtrl->decrementStock($item['idproduit'], $item['quantite']);
                    }

                    $_SESSION['cart'] = [];
                    $success = true;
                    $message = 'Votre commande a bien été ajoutée à Mes commandes avec le statut « en cours ». Vous avez 24h pour la finaliser.';
                } else {
                    $message = 'Erreur lors de la création de la commande.';
                }
            }
        }
    } else {
        $paiement_reussi = true; // Simulation
        $status = 'confirmé';
        $mode = $methode;

        if ($paiement_reussi) {
            if ($existingOrder && $existingOrderOwner && strtolower(trim($existingOrder['statut'])) === 'en cours') {
                $commandeC->updateCommandePayment($id, $status, $mode);
                $success = true;
                $message = 'Paiement de la commande confirmé et validé.';
            } else {
                $cartValid = true;

                if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                    $cartValid = false;
                    $message = 'Le panier est vide. Impossible de créer la commande.';
                }

                if ($cartValid) {
                    foreach ($_SESSION['cart'] as $item) {
                        $currentProduct = $prodCtrl->getProduit($item['idproduit']);
                        if (!$currentProduct || !$currentProduct['en_out_stock'] || $currentProduct['qte_stock'] < $item['quantite']) {
                            $cartValid = false;
                            $message = 'Stock insuffisant pour l’un des produits du panier. Ajustez votre commande avant de payer.';
                            break;
                        }
                    }
                }

                if ($cartValid) {
                    $commande = new Commande(null, $userId, $stand_id ?: null, null, date('Y-m-d H:i:s'), $status, $montant, $mode);
                    $newCommandeId = $commandeC->addCommande($commande);

                    if ($newCommandeId) {
                        foreach ($_SESSION['cart'] as $item) {
                            $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                            $ligneC->addLigneCommande($ligne);
                            $prodCtrl->decrementStock($item['idproduit'], $item['quantite']);
                        }

                        $_SESSION['cart'] = [];
                        $success = true;
                        $message = 'Paiement de la commande confirmé et validé.';
                    } else {
                        $message = 'Erreur lors de la création de la commande.';
                    }
                }
            }
        } else {
            $message = 'Paiement échoué. Veuillez réessayer.';
        }
    }

    if ($success) {
        header('Location: Mes commandes.php?success=' . urlencode($message));
    } else {
        $redirectUrl = 'paiement.php?type=commande&id=' . $id . '&montant=' . $montant . '&stand_id=' . $stand_id . '&error=' . urlencode($message);
        header('Location: ' . $redirectUrl);
    }
    exit;
}

header('Location: listMarathons.php');
exit;
?>
