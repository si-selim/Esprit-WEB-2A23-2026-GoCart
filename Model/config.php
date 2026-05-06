<?php

class Config {

    public static function getConnexion() {
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=projetwebinscription+dossard",
                "root",
                ""
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (Exception $e) {
            die("Erreur connexion BD : " . $e->getMessage());
        }
    }
}