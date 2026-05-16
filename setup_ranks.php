<?php
require_once 'config.php';

try {
    $pdo = config::getConnexion();

    // Ajouter xp dans user
    $pdo->exec("ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `xp` INT(11) DEFAULT 0;");
    
    // Ajouter statut_course dans inscription
    $pdo->exec("ALTER TABLE `inscription` ADD COLUMN IF NOT EXISTS `statut_course` VARCHAR(50) DEFAULT 'inscrit';");

    echo "Base de données mise à jour avec succès.\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
