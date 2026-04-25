<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isConnected()) {
    header('Location: login.php');
    exit;
}

$ctrl = new UserController();
$user = $ctrl->showUser(getUserId());

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .feedback { display:block; font-size:0.82rem; margin-top:4px; min-height:1.2em; }
        .feedback.error { color:#b42318; }
        .feedback.success { color:#0f766e; }
        .profile-preview { max-width:100px; max-height:100px; border-radius:12px; margin-top:8px; display:none; }
    </style>
</head>
<body>
    <div class="page">
        <div class="card-form fade-in">
            <h1>Modifier mon profil</h1>
            <p>Formulaire de modification pour mettre a jour vos informations personnelles.</p>
            <?php if (!empty($_GET['error'])): ?>
                <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <form method="POST" action="update_user.php" enctype="multipart/form-data" data-validate>
                <div class="form-grid">
                    <div class="field full-width">
                        <label for="nom_complet">Nom complet</label>
                        <input id="nom_complet" name="nom_complet" type="text" placeholder="Nom complet" value="<?= htmlspecialchars($user['nom_complet']) ?>" required minlength="3">
                        <span id="nomCompletFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="nom_user">Nom d utilisateur</label>
                        <input id="nom_user" name="nom_user" type="text" placeholder="Nom d utilisateur" value="<?= htmlspecialchars($user['nom_user']) ?>" required minlength="3" pattern="[a-zA-Z0-9_]+">
                        <span id="nomUserFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" placeholder="participant@email.com" value="<?= htmlspecialchars($user['email']) ?>" required>
                        <span id="emailFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="age">Age</label>
                        <input id="age" name="age" type="number" placeholder="25" min="1" max="120" value="<?= htmlspecialchars($user['age'] ?? '') ?>">
                        <span id="ageFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="poids">Poids (kg)</label>
                        <input id="poids" name="poids" type="number" placeholder="70" min="1" max="500" step="0.1" value="<?= htmlspecialchars($user['poids'] ?? '') ?>">
                        <span id="poidsFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="taille">Taille (cm)</label>
                        <input id="taille" name="taille" type="number" placeholder="175" min="1" max="300" value="<?= htmlspecialchars($user['taille'] ?? '') ?>">
                        <span id="tailleFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="tel">Telephone</label>
                        <input id="tel" name="tel" type="tel" placeholder="12345678" pattern="[0-9]{8}" title="Le numero doit contenir exactement 8 chiffres" value="<?= htmlspecialchars($user['tel'] ?? '') ?>">
                        <span id="telFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="pays">Pays</label>
                        <input id="pays" name="pays" type="text" placeholder="Tunisie" value="<?= htmlspecialchars($user['pays'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="ville">Ville / adresse exacte</label>
                        <input id="ville" name="ville" type="text" placeholder="Tunis" value="<?= htmlspecialchars($user['ville'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="occupation">Occupation</label>
                        <select id="occupation" name="occupation">
                            <option value="">Que faites-vous dans la vie ?</option>
                            <option value="Etudiant" <?= ($user['occupation'] ?? '') === 'Etudiant' ? 'selected' : '' ?>>Etudiant</option>
                            <option value="Employe" <?= ($user['occupation'] ?? '') === 'Employe' ? 'selected' : '' ?>>Employe</option>
                            <option value="Retraite" <?= ($user['occupation'] ?? '') === 'Retraite' ? 'selected' : '' ?>>Retraite</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Photo de profil</label>
                        <div class="file-upload">
                            <label class="file-upload-label">
                                Changer la photo
                                <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp">
                            </label>
                            <span class="file-upload-name"><?= $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'Aucun fichier' ?></span>
                        </div>
                        <span id="profilePictureFeedback" class="feedback"></span>
                        <img id="profilePicturePreview" class="profile-preview" alt="">
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Mettre a jour</button>
                    <a class="btn btn-secondary btn-back" href="profile.php">Retour</a>
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

    <script src="../assets/js/app.js"></script>
    <script src="user.js"></script>
</body>
</html>
