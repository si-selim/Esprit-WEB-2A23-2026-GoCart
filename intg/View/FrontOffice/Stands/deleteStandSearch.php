<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
require_once __DIR__ . '/../../Controller/standcontroller.php';

$message = "";
$messageClass = "";

// Récupérer la valeur du champ
if (isset($_REQUEST['searchVal'])) {
    $searchVal = trim($_REQUEST['searchVal']);
    
    if (!empty($searchVal)) {
        $controller = new StandController();
        
        // Appeler deleteStandByValue (qui gère la sécurité PDO via search et delete)
        if ($controller->deleteStandByValue($searchVal)) {
            $message = "✅ Stand supprimé avec succès !";
            $messageClass = "success-msg";
        } else {
            $message = "❌ Stand introuvable.";
            $messageClass = "error-msg";
        }
    } else {
        $message = "Veuillez entrer l'ID ou le nom du stand à supprimer.";
        $messageClass = "error-msg";
    }
} else {
    $message = "Aucune requête de suppression reçue.";
    $messageClass = "error-msg";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression de Stand</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        .result-container h1 { font-size: 24px; margin-bottom: 20px; color: #333; }
        .success-msg { color: #28a745; font-weight: bold; font-size: 18px; margin-bottom: 20px;}
        .error-msg { color: #dc3545; font-weight: bold; font-size: 18px; margin-bottom: 20px;}
        .btn-retour {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-retour:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="result-container">
        <h1>Résultat de la suppression</h1>
        
        <div class="<?= $messageClass ?>"><?= htmlspecialchars($message) ?></div>
        
        <br>
        <a href="crud-stand.html" class="btn-retour">⬅ Retour à la gestion</a>
    </div>
</body>
</html>
