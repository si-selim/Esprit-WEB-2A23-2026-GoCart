<?php
/**
 * Configuration de la clé API Anthropic (Claude)
 * ⚠️  Ne PAS committer ce fichier dans Git — ajoutez-le à .gitignore
 *
 * Obtenez votre clé sur : https://console.anthropic.com/settings/keys
 */
define('ANTHROPIC_API_KEY', 'sk-ant-VOTRE_CLE_ICI');

// Le proxy affiche_ia_proxy.php lit cette constante automatiquement.
// Vous pouvez aussi passer par une variable d'environnement serveur :
//   putenv('ANTHROPIC_API_KEY=sk-ant-...');
