<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: ../Stands/listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/produitcontroller.php';
require_once __DIR__ . '/../../../Controller/standcontroller.php';

$controller = new ProduitController();

// Si ce n'est pas une requête POST, rediriger vers la liste des produits
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: listProduitsFront.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idProduit    = isset($_POST['idProduit']) && !empty($_POST['idProduit']) ? (int)$_POST['idProduit'] : null;
    $idStand      = isset($_POST['idStand']) ? (int)$_POST['idStand'] : null;
    $parcoursId   = isset($_POST['parcours_id']) ? (int)$_POST['parcours_id'] : null;
    $nomProduit   = isset($_POST['nomProduit']) ? trim($_POST['nomProduit']) : null;
    $type         = isset($_POST['type']) ? trim($_POST['type']) : null;
    $prixProduit  = isset($_POST['prixProduit']) ? (float)$_POST['prixProduit'] : null;
    $qteStock     = isset($_POST['quantiteStock']) ? (int)$_POST['quantiteStock'] : null;
    $enOutStock   = isset($_POST['stock']) ? $_POST['stock'] : null;

    // Convertir en_out_stock en entier (0 ou 1)
    $enOutStock = (int)($enOutStock === "1" || $enOutStock === 1 || (is_string($enOutStock) && stripos($enOutStock, 'dispo') !== false));

    $action = $_POST['action'] ?? 'add';

    // Gestion de l'upload de l'image
    $imageName = null;
    if (isset($_FILES['photoProduit']) && $_FILES['photoProduit']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/produits/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['photoProduit']['name'], PATHINFO_EXTENSION));
        // Générer un nom unique
        $imageName = uniqid('prod_') . '.' . $fileExt;
        $targetFile = $uploadDir . $imageName;
        
        if (!move_uploaded_file($_FILES['photoProduit']['tmp_name'], $targetFile)) {
            $imageName = null; // En cas d'erreur, garder null
        }
    }
    
    if ($action === 'add' && $imageName === null) {
        $imageName = '';
    }

    if ($idStand && $nomProduit && $type && $prixProduit !== null && $qteStock !== null && $enOutStock !== null) {
        try {
            // Vérifier que le stand existe avant d'ajouter le produit
            $standController = new StandController();
            $standExists = $standController->getStand($idStand);

            if (!$standExists) {
                $formUrl = $parcoursId ? "crud-produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "crud-produit.php?stand_id=$idStand";
                echo "<script>alert('❌ Erreur: Le stand avec l\\'ID ($idStand) n\\'existe pas.'); window.location.href='$formUrl';</script>";
                exit;
            }

            if ($action === 'update' && $idProduit) {
                // Si l'image n'est pas modifiée, on garde l'ancienne.
                // Le modèle est déjà géré pour ignorer null
                $produit = new Produit($idProduit, $idStand, $nomProduit, $type, $prixProduit, $qteStock, $enOutStock, $imageName);
                if ($controller->updateProduit($produit, $idProduit)) {
                    $redirectUrl = $parcoursId ? "produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "produit.php?stand_id=$idStand";
                    header("Location: $redirectUrl");
                    exit;
                } else {
                    $formUrl = $parcoursId ? "crud-produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "crud-produit.php?stand_id=$idStand";
                    header('Location: ' . $formUrl . '?error=' . urlencode('Erreur lors de la modification.'));
                    exit;
                }
            } else {
                // ADD MODE
                $produit = new Produit(null, $idStand, $nomProduit, $type, $prixProduit, $qteStock, $enOutStock, $imageName);
                
                // On essaie d'ajouter et on capture les erreurs précises
                if ($controller->addProduit($produit)) {
                    $redirectUrl = $parcoursId ? "produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "produit.php?stand_id=$idStand";
                    header("Location: $redirectUrl");
                    exit;
                } else {
                    // Rediriger vers le formulaire avec un message d'erreur
                    $formUrl = $parcoursId ? "crud-produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "crud-produit.php?stand_id=$idStand";
                    header('Location: ' . $formUrl . '?error=' . urlencode('L\'ajout a échoué.'));
                    exit;
                }
            }
        } catch (Exception $e) {
            $errorMessage = 'Erreur lors de l\'ajout du produit.';
            // Rediriger vers le formulaire avec l'erreur
            $formUrl = $parcoursId ? "crud-produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "crud-produit.php?stand_id=$idStand";
            header('Location: ' . $formUrl . '?error=' . urlencode($errorMessage));
            exit;
        }
    } else {
        $formUrl = $parcoursId ? "crud-produit.php?stand_id=$idStand&parcours_id=$parcoursId" : "crud-produit.php?stand_id=$idStand";
        header('Location: ' . $formUrl . '?error=' . urlencode('Certains champs sont vides ou invalides.'));
        exit;
    }
}
?>
