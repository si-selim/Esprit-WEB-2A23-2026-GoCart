<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `user` WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$initial = mb_strtoupper(mb_substr($user['nom_complet'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
    <link rel="stylesheet" href="../view/assets/css/style.css">
</head>
<body>
    <div class="profile-layout">
        <aside class="panel fade-in">
            <h2>Mon espace</h2>
            <div class="menu">
                <a class="active" href="profile.php">Profil</a>
                <a href="#infos">Informations personnelles</a>
                <a href="#settings">Parametre et confidentialite</a>
                <a href="modifyUser.php">Modifier mon profil</a>
                <a href="#" onclick="event.preventDefault(); showConfirm('Voulez-vous vraiment supprimer votre compte ?', function(){ document.getElementById('delete-form').submit(); });">Supprimer mon compte</a>
                <a href="logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="panel">
            <section class="hero slide-up">
                <div>
                    <h1><?= htmlspecialchars($user['nom_complet']) ?></h1>
                    <p>Role : <strong><?= htmlspecialchars($user['role']) ?></strong></p>
                    <p>Nom d utilisateur : <strong><?= htmlspecialchars($user['nom_user']) ?></strong></p>
                </div>
                <div class="avatar">
                    <?php if ($user['profile_picture'] && file_exists(__DIR__ . '/../uploads/' . $user['profile_picture'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil">
                    <?php else: ?>
                        <?= $initial ?>
                    <?php endif; ?>
                </div>
            </section>

            <section id="infos">
                <h2 class="section-title">Informations personnelles</h2>
                <div class="info-grid">
                    <div class="info-card slide-up"><div class="label">Age</div><div class="value"><?= $user['age'] ? htmlspecialchars($user['age']) : '-' ?></div></div>
                    <div class="info-card slide-up"><div class="label">Poids</div><div class="value"><?= $user['poids'] ? htmlspecialchars($user['poids']) . ' kg' : '-' ?></div></div>
                    <div class="info-card slide-up"><div class="label">Taille</div><div class="value"><?= $user['taille'] ? htmlspecialchars($user['taille']) . ' cm' : '-' ?></div></div>
                    <div class="info-card slide-up"><div class="label">Email</div><div class="value"><?= htmlspecialchars($user['email']) ?></div></div>
                    <div class="info-card slide-up"><div class="label">Pays</div><div class="value"><?= $user['pays'] ? htmlspecialchars($user['pays']) : '-' ?></div></div>
                    <div class="info-card slide-up"><div class="label">Ville / zone exacte</div><div class="value"><?= $user['ville'] ? htmlspecialchars($user['ville']) : '-' ?></div></div>
                    <div class="info-card slide-up"><div class="label">Telephone</div><div class="value"><?= $user['tel'] ? htmlspecialchars($user['tel']) : '-' ?></div></div>
                    <div class="info-card slide-up"><div class="label">Occupation</div><div class="value"><?= $user['occupation'] ? htmlspecialchars($user['occupation']) : '-' ?></div></div>
                </div>
            </section>

            <section id="settings">
                <h2 class="section-title">Parametre et confidentialite</h2>
                <div class="info-grid">
                    <div class="info-card fade-in">
                        <div class="label">Compte</div>
                        <div class="value">Votre compte est connecte en session locale.</div>
                    </div>
                    <div class="info-card fade-in">
                        <div class="label">Confidentialite</div>
                        <div class="value">Les informations personnelles sont visibles uniquement dans votre espace.</div>
                    </div>
                    <div class="info-card fade-in">
                        <div class="label">Changer le mot de passe</div>
                        <form class="password-form" method="POST" action="change_password.php">
                            <input type="password" name="current_password" placeholder="Mot de passe actuel" required>
                            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required minlength="6">
                            <button type="submit">Modifier le mot de passe</button>
                        </form>
                    </div>
                    <div class="info-card fade-in">
                        <div class="label">Deconnexion</div>
                        <div class="value"><a href="logout.php">Se deconnecter maintenant</a></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <form id="delete-form" method="POST" action="delete_account.php" style="display:none;"></form>

    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirmation</h3>
            <p id="confirm-message"></p>
            <div class="modal-actions">
                <button id="confirm-yes" class="btn btn-danger">Oui, supprimer</button>
                <button class="btn btn-secondary" data-modal-close>Annuler</button>
            </div>
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
