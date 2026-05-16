<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    
    // Create objectif table
    $db->exec("CREATE TABLE IF NOT EXISTS objectif (
        id_objectif INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        type_objectif VARCHAR(50) NOT NULL,
        target_value INT NOT NULL,
        recompense VARCHAR(255) NOT NULL,
        description_recompense TEXT,
        etat TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create user_objectif_claim table
    $db->exec("CREATE TABLE IF NOT EXISTS user_objectif_claim (
        id_claim INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        id_objectif INT NOT NULL,
        date_claim DATETIME DEFAULT CURRENT_TIMESTAMP,
        statut VARCHAR(50) DEFAULT 'reclamé',
        FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE,
        FOREIGN KEY (id_objectif) REFERENCES objectif(id_objectif) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Add last_login_date and consecutive_logins to user if they don't exist
    try {
        $db->exec("ALTER TABLE user ADD COLUMN last_login_date DATE NULL");
    } catch(Exception $e) {}
    
    try {
        $db->exec("ALTER TABLE user ADD COLUMN consecutive_logins INT DEFAULT 0");
    } catch(Exception $e) {}

    echo "Tables and columns successfully created or updated.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
