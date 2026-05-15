<?php
/**
 * Script pour marquer les commandes non confirmées après 24h comme "non valide"
 * À exécuter périodiquement (cron job recommandé)
 */

include_once(__DIR__ . '/config.php');
include_once(__DIR__ . '/Controller/CommandeController.php');

$commandeC = new CommandeController();

// Récupérer toutes les commandes en cours
$commandesEnCours = $commandeC->listCommandesByStatut('en cours')->fetchAll();

$currentTime = time();
$twentyFourHoursAgo = $currentTime - (24 * 60 * 60); // 24 heures en secondes

$updatedCount = 0;

foreach ($commandesEnCours as $commande) {
    $commandeTime = strtotime($commande['datecommande']);

    if ($commandeTime < $twentyFourHoursAgo) {
        // La commande a plus de 24h, la marquer comme non valide
        $commandeC->updateCommandeStatus($commande['idcommande'], 'non valide');
        $updatedCount++;
    }
}

echo "Script exécuté avec succès. $updatedCount commande(s) marquée(s) comme 'non valide'.\n";
?>