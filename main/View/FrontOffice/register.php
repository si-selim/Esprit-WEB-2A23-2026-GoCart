<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';

$ctrl = new UserController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $nom_user = trim($_POST['nom_user'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'participant';
    if (!in_array($role, ['participant', 'organisateur'])) { $role = 'participant'; }
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $poids = !empty($_POST['poids']) ? (float)$_POST['poids'] : null;
    $taille = !empty($_POST['taille']) ? (int)$_POST['taille'] : null;
    $email = trim($_POST['email'] ?? '');
    $pays = trim($_POST['pays'] ?? '') ?: null;
    $ville = trim($_POST['ville'] ?? '') ?: null;
    $tel = trim($_POST['tel'] ?? '') ?: null;
    $occupation = trim($_POST['occupation'] ?? '') ?: null;

    if ($nom_complet === '' || $nom_user === '' || $mot_de_passe === '' || $email === '') {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (strlen($nom_complet) < 3) {
        $error = 'Le nom complet doit contenir au moins 3 caracteres.';
    } elseif (strlen($nom_user) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $nom_user)) {
        $error = 'Le nom d\'utilisateur doit contenir au moins 3 caracteres (lettres, chiffres, underscores).';
    } elseif (strlen($mot_de_passe) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } elseif ($tel !== null && !preg_match('/^\d{8}$/', $tel)) {
        $error = 'Le numero de telephone doit contenir exactement 8 chiffres.';
    } elseif ($age !== null && ($age < 1 || $age > 120)) {
        $error = 'L\'age doit etre entre 1 et 120.';
    } elseif ($poids !== null && ($poids < 1 || $poids > 500)) {
        $error = 'Le poids doit etre entre 1 et 500 kg.';
    } elseif ($taille !== null && ($taille < 1 || $taille > 300)) {
        $error = 'La taille doit etre entre 1 et 300 cm.';
    } else {
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $result = $ctrl->saveProfilePicture($_FILES['profile_picture']);
            if ($result === false) {
                $error = 'Type de fichier non autorise ou photo trop volumineuse (max 2 Mo).';
            } else {
                $profile_picture = $result;
            }
        }

        if ($error === '') {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $userObj = new User(null, $nom_complet, $nom_user, $hash, $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture);
            try {
                $ctrl->ajouterUser($userObj);
                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'Ce nom d\'utilisateur existe deja.';
                } else {
                    $error = 'Erreur lors de la creation du compte.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creer un compte — BarchaThon</title>
    <style>
        :root {
            --ink:#102a43; --teal:#0f766e; --sun:#ffb703;
            --coral:#e76f51; --bg:#f4fbfb;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:"Segoe UI",sans-serif;
            color:var(--ink);
            background:linear-gradient(180deg,#fefaf0,var(--bg));
            min-height:100vh;
            display:flex; align-items:center; justify-content:center;
            padding:20px;
        }
        .page-narrow { width:100%; max-width:680px; }
        .card-form {
            background:#fff; border-radius:24px; padding:40px 36px;
            box-shadow:0 14px 40px rgba(16,42,67,.10);
            border:1px solid rgba(16,42,67,.07);
        }
        .card-form h1 { font-size:1.9rem; font-weight:900; margin-bottom:10px; color:var(--ink); }
        .card-form > p { color:#627d98; font-size:0.93rem; line-height:1.6; margin-bottom:22px; }
        .error-msg {
            background:#fef2f2; border:1px solid #fecaca;
            border-radius:12px; padding:12px 16px;
            color:#b42318; font-size:0.9rem; margin-bottom:18px;
        }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .full-width { grid-column:1 / -1; }
        .field label { display:block; font-weight:700; margin-bottom:7px; color:var(--ink); font-size:0.93rem; }
        .field input, .field select {
            width:100%; border:1.5px solid #cbd5e1; border-radius:12px;
            padding:12px 15px; font:inherit; font-size:0.95rem;
            background:white; transition:border .2s, box-shadow .2s;
        }
        .field input:focus, .field select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .file-upload { position:relative; }
        .file-upload-label {
            display:inline-block; padding:10px 16px; background:#f1f5f9;
            border:1.5px solid #cbd5e1; border-radius:12px; cursor:pointer;
            font-size:0.9rem; font-weight:600; transition:background .2s;
        }
        .file-upload-label:hover { background:#e2e8f0; }
        .file-upload-label input { display:none; }
        .file-upload-name { margin-left:10px; color:#627d98; font-size:0.88rem; }
        .feedback { display:block; font-size:0.82rem; margin-top:4px; min-height:1.2em; }
        .feedback.error { color:#b42318; }
        .feedback.success { color:#0f766e; }
        .profile-preview { max-width:100px; max-height:100px; border-radius:12px; margin-top:8px; display:none; }
        .actions { display:flex; gap:12px; margin-top:24px; flex-wrap:wrap; }
        .btn {
            text-decoration:none; padding:12px 20px; border-radius:12px;
            font-weight:700; border:none; cursor:pointer;
            display:inline-flex; align-items:center; justify-content:center;
            font-size:0.95rem; transition:opacity .15s, transform .15s; flex:1;
        }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; }
        .btn-secondary { background:#f1f5f9; color:var(--ink); border:1px solid #cbd5e1; flex:none; padding:12px 18px; }
        .fade-in { animation:fadeIn .4s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
        @media(max-width:600px){ .form-grid { grid-template-columns:1fr; } .card-form { padding:28px 20px; } }
    </style>
</head>
<body>
    <div class="page-narrow">
        <div class="card-form fade-in">
            <h1>Creer un compte</h1>
            <p>Formulaire d'inscription pour creer un nouveau compte.</p>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="register.php" enctype="multipart/form-data" data-validate>
                <div class="form-grid">
                    <div class="field full-width">
                        <label for="nom_complet">Nom complet</label>
                        <input id="nom_complet" name="nom_complet" type="text" placeholder="Nom complet" required minlength="3" value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>">
                        <span id="nomCompletFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="nom_user">Nom d'utilisateur</label>
                        <input id="nom_user" name="nom_user" type="text" placeholder="Nom d'utilisateur" required minlength="3" pattern="[a-zA-Z0-9_]+" value="<?php echo htmlspecialchars($_POST['nom_user'] ?? ''); ?>">
                        <span id="nomUserFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input id="mot_de_passe" name="mot_de_passe" type="password" placeholder="Mot de passe" required minlength="6">
                        <span id="motDePasseFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="participant" <?php echo ($_POST['role'] ?? '') === 'participant' ? 'selected' : ''; ?>>Participant</option>
                            <option value="organisateur" <?php echo ($_POST['role'] ?? '') === 'organisateur' ? 'selected' : ''; ?>>Organisateur</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" placeholder="email@exemple.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <span id="emailFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="age">Age</label>
                        <input id="age" name="age" type="number" placeholder="25" min="1" max="120" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">
                        <span id="ageFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="poids">Poids (kg)</label>
                        <input id="poids" name="poids" type="number" placeholder="70" min="1" max="500" step="0.1" value="<?php echo htmlspecialchars($_POST['poids'] ?? ''); ?>">
                        <span id="poidsFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="taille">Taille (cm)</label>
                        <input id="taille" name="taille" type="number" placeholder="175" min="1" max="300" value="<?php echo htmlspecialchars($_POST['taille'] ?? ''); ?>">
                        <span id="tailleFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="pays">Pays</label>
                        <input id="pays" name="pays" type="text" placeholder="Tunisie" value="<?php echo htmlspecialchars($_POST['pays'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="ville">Ville</label>
                        <input id="ville" name="ville" type="text" placeholder="Tunis" value="<?php echo htmlspecialchars($_POST['ville'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="tel">Telephone</label>
                        <input id="tel" name="tel" type="tel" placeholder="12345678" pattern="[0-9]{8}" title="Le numero doit contenir exactement 8 chiffres" value="<?php echo htmlspecialchars($_POST['tel'] ?? ''); ?>">
                        <span id="telFeedback" class="feedback"></span>
                    </div>
                    <div class="field">
                        <label for="occupation">Occupation</label>
                        <select id="occupation" name="occupation">
                            <option value="">Que faites-vous dans la vie ?</option>
                            <option value="Etudiant" <?php echo ($_POST['occupation'] ?? '') === 'Etudiant' ? 'selected' : ''; ?>>Etudiant</option>
                            <option value="Employe" <?php echo ($_POST['occupation'] ?? '') === 'Employe' ? 'selected' : ''; ?>>Employe</option>
                            <option value="Retraite" <?php echo ($_POST['occupation'] ?? '') === 'Retraite' ? 'selected' : ''; ?>>Retraite</option>
                        </select>
                    </div>
                    <div class="field full-width">
                        <label>Photo de profil</label>
                        <div class="file-upload">
                            <label class="file-upload-label">
                                Choisir une photo
                                <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp">
                            </label>
                            <span class="file-upload-name">Aucun fichier</span>
                        </div>
                        <span id="profilePictureFeedback" class="feedback"></span>
                        <img id="profilePicturePreview" class="profile-preview" alt="">
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Creer le compte</button>
                    <a class="btn btn-secondary" href="login.php">Retour</a>
                </div>
            </form>
        </div>
    </div>
    <script src="user.js"></script>
</body>
</html>
