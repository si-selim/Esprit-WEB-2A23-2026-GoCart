<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM `user` WHERE nom_user = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nom_user'] = $user['nom_user'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom_complet'] = $user['nom_complet'];

            if ($user['role'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: profile.php');
            }
            exit;
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link rel="stylesheet" href="../view/assets/css/style.css">
</head>
<body>
    <div class="page-narrow">
        <div class="card-form fade-in">
            <h1>Se connecter</h1>
            <p>Connectez-vous pour acceder a votre espace personnel.</p>
            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="field-mb">
                    <label for="username">Nom d utilisateur</label>
                    <input id="username" name="username" type="text" placeholder="Nom d utilisateur" required>
                </div>
                <div class="field-mb">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" placeholder="Mot de passe" required>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Connexion</button>
                    <a class="btn btn-secondary" href="register.php">S inscrire</a>
                </div>
            </form>
        </div>
    </div>

    <div id="feedback-modal" class="modal-overlay">
        <div class="modal-box">
            <div id="feedback-icon" class="feedback-icon success"></div>
            <p id="feedback-message"></p>
        </div>
    </div>

    <script src="../view/assets/js/app.js"></script>
</body>
</html>
