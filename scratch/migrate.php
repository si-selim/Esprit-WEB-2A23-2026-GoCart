<?php
require_once __DIR__ . '/../config.php';
try {
    $db = config::getConnexion();
    $db->exec('ALTER TABLE user ADD solde_achat FLOAT DEFAULT 0');
    echo "OK: solde_achat added\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
