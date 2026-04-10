<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creer un compte participant</title>
    <link rel="stylesheet" href="../view/assets/css/style.css">
</head>
<body>
    <div class="page">
        <div class="card-form fade-in">
            <h1>Creer un compte participant</h1>
            <p>Formulaire d inscription pour creer un nouveau compte participant.</p>
            <?php if (!empty($_GET['error'])): ?>
                <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <form method="POST" action="create_user.php" enctype="multipart/form-data" data-validate>
                <div class="form-grid">
                    <div class="field full-width">
                        <label for="name">Nom complet</label>
                        <input id="name" name="nom_complet" type="text" placeholder="Nom complet" required minlength="3">
                    </div>
                    <div class="field">
                        <label for="username">Nom d utilisateur</label>
                        <input id="username" name="nom_user" type="text" placeholder="Nom d utilisateur" required minlength="3" pattern="[a-zA-Z0-9_]+">
                    </div>
                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <input id="password" name="mot_de_passe" type="password" placeholder="Mot de passe" required minlength="6">
                    </div>
                    <div class="field">
                        <label for="age">Age</label>
                        <input id="age" name="age" type="number" placeholder="25" min="1" max="120">
                    </div>
                    <div class="field">
                        <label for="weight">Poids (kg)</label>
                        <input id="weight" name="poids" type="number" placeholder="70" min="1" max="500" step="0.1">
                    </div>
                    <div class="field">
                        <label for="height">Taille (cm)</label>
                        <input id="height" name="taille" type="number" placeholder="175" min="1" max="300">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" placeholder="participant@email.com" required>
                    </div>
                    <div class="field">
                        <label for="country">Pays</label>
                        <input id="country" name="pays" type="text" placeholder="Tunisie">
                    </div>
                    <div class="field">
                        <label for="city">Ville / adresse exacte</label>
                        <input id="city" name="ville" type="text" placeholder="Tunis">
                    </div>
                    <div class="field">
                        <label for="phone">Telephone</label>
                        <input id="phone" name="tel" type="tel" placeholder="12345678" pattern="[0-9]{8}" title="Le numero doit contenir exactement 8 chiffres">
                    </div>
                    <div class="field">
                        <label for="occupation">Occupation</label>
                        <select id="occupation" name="occupation">
                            <option value="">Que faites-vous dans la vie ?</option>
                            <option value="Etudiant">Etudiant</option>
                            <option value="Employe">Employe</option>
                            <option value="Retraite">Retraite</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Photo de profil</label>
                        <div class="file-upload">
                            <label class="file-upload-label">
                                Choisir une photo
                                <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp">
                            </label>
                            <span class="file-upload-name">Aucun fichier</span>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Confirmer</button>
                    <a class="btn btn-secondary" href="login.php">Retour</a>
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
