<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';
require_once __DIR__ . '/../../Controller/Mailer.php';

define('RECAPTCHA_SITE_KEY', '6Lc1RcUsAAAAAJJ9E9stq2yPeLHbyE82JgAY7si7');
define('RECAPTCHA_SECRET_KEY', '6Lc1RcUsAAAAAOrReCtPIpSQGMLUd1ZEcaWPiD0c');

function verifyRecaptcha($token) {
    if (empty($token)) return false;
    $data = http_build_query([
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $data,
            'timeout' => 8
        ]
    ]);
    $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    if ($response === false) return false;
    $json = json_decode($response, true);
    return !empty($json['success']) && ($json['score'] ?? 0) >= 0.5;
}

$ctrl = new UserController();
$error = '';
$success = '';

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
    $sexe = trim($_POST['sexe'] ?? '') ?: null;
    if ($sexe !== null && !in_array($sexe, ['homme','femme','autre'])) { $sexe = null; }

    if (!verifyRecaptcha($_POST['g-recaptcha-response'] ?? '')) {
        $error = 'Veuillez confirmer que vous n\'etes pas un robot.';
    } elseif ($nom_complet === '' || $nom_user === '' || $mot_de_passe === '' || $email === '') {
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
            if ($result === false || $result === null) {
                $error = 'Echec de l\'enregistrement de la photo (type non autorise, trop volumineuse, ou dossier non accessible).';
            } else {
                $profile_picture = $result;
            }
        } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = 'Erreur lors du televersement de la photo.';
        }

        if ($error === '') {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $userObj = new User(null, $nom_complet, $nom_user, $hash, $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture, 'active', $sexe);
            $token = bin2hex(random_bytes(32));
            try {
                $ctrl->ajouterUserAvecVerification($userObj, $token);
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $verifyUrl = $scheme . '://' . $host . $base . '/verify_email.php?token=' . urlencode($token);
                $safeName = htmlspecialchars($nom_complet, ENT_QUOTES, 'UTF-8');
                $safeUrl = htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8');
                $mailBody = "<div style='font-family:Segoe UI,sans-serif;color:#102a43;'>"
                    . "<h2>Bienvenue sur BarchaThon, {$safeName} !</h2>"
                    . "<p>Merci de vous etre inscrit. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>"
                    . "<p><a href='{$safeUrl}' style='display:inline-block;padding:12px 20px;background:#0f766e;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;'>Verifier mon email</a></p>"
                    . "<p>Ou copiez ce lien dans votre navigateur :<br><code>{$safeUrl}</code></p>"
                    . "<p style='color:#627d98;font-size:0.85rem;'>Si vous n'avez pas cree de compte, ignorez cet email.</p>"
                    . "</div>";
                $sent = Mailer::send($email, 'Verifiez votre compte BarchaThon', $mailBody);
                if ($sent) {
                    $success = 'Compte cree ! Un email de verification a ete envoye a ' . htmlspecialchars($email) . '. Cliquez sur le lien pour activer votre compte.';
                } else {
                    $success = 'Compte cree, mais l\'email de verification n\'a pas pu etre envoye. Contactez un administrateur.';
                }
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
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
    <style>
        html[data-theme="dark"] body { background:#0f172a !important; }
        html[data-theme="dark"] .card-form { background:#1e293b !important; border-color:rgba(255,255,255,0.08) !important; box-shadow:0 14px 40px rgba(0,0,0,.4) !important; }
        html[data-theme="dark"] .card-form h1 { color:#e2e8f0; }
        html[data-theme="dark"] .card-form > p { color:#94a3b8; }
        html[data-theme="dark"] label { color:#e2e8f0; }
        html[data-theme="dark"] input,html[data-theme="dark"] select { background:#162032 !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
        html[data-theme="dark"] input:focus,html[data-theme="dark"] select:focus { background:#1e293b !important; border-color:#14b8a6 !important; }
        html[data-theme="dark"] .btn-secondary { background:rgba(255,255,255,0.06) !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
        html[data-theme="dark"] .file-upload-label { background:rgba(255,255,255,0.05) !important; color:#e2e8f0 !important; border-color:rgba(20,184,166,0.25) !important; }
    </style>
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
            display:block;
            padding:0; margin:0;
        }
        .page-narrow {
            width:100%; max-width:680px;
            margin:0 auto;
            padding:100px 20px 40px;
        }
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
        .gender-picker { display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; }
        .gender-opt { position:relative; }
        .gender-opt input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
        .gender-opt label { display:inline-flex; align-items:center; gap:10px; padding:8px 20px 8px 8px; border-radius:999px; border:2px solid #e2e8f0; cursor:pointer; font-weight:700; font-size:0.9rem; color:#64748b; background:#f8fafc; transition:all .22s ease; user-select:none; }
        .gender-opt label:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,.07); }
        .gender-icon { width:36px; height:36px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#e2e8f0; flex-shrink:0; transition:all .22s ease; }
        .gender-opt:has([value=homme]) label:hover { border-color:#93c5fd; }
        .gender-opt:has([value=homme]) label:hover .gender-icon { background:rgba(59,130,246,.15); color:#3b82f6; }
        .gender-opt:has([value=homme]) input:checked + label { border-color:#3b82f6; color:#1d4ed8; background:rgba(59,130,246,.06); }
        .gender-opt:has([value=homme]) input:checked + label .gender-icon { background:rgba(59,130,246,.2); color:#3b82f6; }
        .gender-opt:has([value=femme]) label:hover { border-color:#f9a8d4; }
        .gender-opt:has([value=femme]) label:hover .gender-icon { background:rgba(236,72,153,.15); color:#ec4899; }
        .gender-opt:has([value=femme]) input:checked + label { border-color:#ec4899; color:#be185d; background:rgba(236,72,153,.06); }
        .gender-opt:has([value=femme]) input:checked + label .gender-icon { background:rgba(236,72,153,.2); color:#ec4899; }
        .gender-opt:has([value=autre]) label:hover { border-color:#c4b5fd; }
        .gender-opt:has([value=autre]) label:hover .gender-icon { background:rgba(139,92,246,.15); color:#8b5cf6; }
        .gender-opt:has([value=autre]) input:checked + label { border-color:#8b5cf6; color:#6d28d9; background:rgba(139,92,246,.06); }
        .gender-opt:has([value=autre]) input:checked + label .gender-icon { background:rgba(139,92,246,.2); color:#8b5cf6; }
        html[data-theme="dark"] .gender-opt label { background:#1e293b; border-color:rgba(255,255,255,.1); color:#94a3b8; }
        html[data-theme="dark"] .gender-icon { background:rgba(255,255,255,.07); }
        html[data-theme="dark"] .gender-opt:has([value=homme]) input:checked + label { border-color:#60a5fa; color:#93c5fd; background:rgba(96,165,250,.08); }
        html[data-theme="dark"] .gender-opt:has([value=homme]) input:checked + label .gender-icon { background:rgba(96,165,250,.2); color:#60a5fa; }
        html[data-theme="dark"] .gender-opt:has([value=femme]) input:checked + label { border-color:#f472b6; color:#f9a8d4; background:rgba(244,114,182,.08); }
        html[data-theme="dark"] .gender-opt:has([value=femme]) input:checked + label .gender-icon { background:rgba(244,114,182,.2); color:#f472b6; }
        html[data-theme="dark"] .gender-opt:has([value=autre]) input:checked + label { border-color:#a78bfa; color:#c4b5fd; background:rgba(167,139,250,.08); }
        html[data-theme="dark"] .gender-opt:has([value=autre]) input:checked + label .gender-icon { background:rgba(167,139,250,.2); color:#a78bfa; }
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
<?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <div class="page-narrow">
        <div class="card-form fade-in">
            <h1>Creer un compte</h1>
            <p>Formulaire d'inscription pour creer un nouveau compte.</p>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="error-msg" style="background:#ecfdf5;border-color:#a7f3d0;color:#065f46;"><?php echo htmlspecialchars($success); ?></div>
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
                        <label>Sexe</label>
                        <div class="gender-picker">
                            <div class="gender-opt">
                                <input type="radio" id="sexe_h" name="sexe" value="homme" <?php echo ($_POST['sexe'] ?? '') === 'homme' ? 'checked' : ''; ?>>
                                <label for="sexe_h"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9.5" cy="14.5" r="5.5"/><line x1="14.28" y1="9.72" x2="20" y2="4"/><polyline points="15.5 4 20 4 20 8.5"/></svg></span>Homme</label>
                            </div>
                            <div class="gender-opt">
                                <input type="radio" id="sexe_f" name="sexe" value="femme" <?php echo ($_POST['sexe'] ?? '') === 'femme' ? 'checked' : ''; ?>>
                                <label for="sexe_f"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="5.5"/><line x1="12" y1="14" x2="12" y2="21"/><line x1="8.5" y1="18" x2="15.5" y2="18"/></svg></span>Femme</label>
                            </div>
                            <div class="gender-opt">
                                <input type="radio" id="sexe_a" name="sexe" value="autre" <?php echo ($_POST['sexe'] ?? '') === 'autre' ? 'checked' : ''; ?>>
                                <label for="sexe_a"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5.5"/><line x1="12" y1="1.5" x2="12" y2="6.5"/><line x1="12" y1="17.5" x2="12" y2="22.5"/><line x1="1.5" y1="12" x2="6.5" y2="12"/><line x1="17.5" y1="12" x2="22.5" y2="12"/></svg></span>Autre</label>
                            </div>
                        </div>
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
                <input type="hidden" id="recaptcha-token" name="g-recaptcha-response">
                <div class="actions">
                    <button class="btn btn-primary" id="submit-btn" type="submit">Creer le compte</button>
                    <a class="btn btn-secondary" href="login.php">Retour</a>
                </div>
            </form>
            <div style="text-align:center;margin-top:18px;padding-top:18px;border-top:1px solid #e2e8f0;">
                <div style="color:#627d98;font-size:.85rem;margin-bottom:10px;">ou</div>
                <a href="google_login.php" style="display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:11px 18px;background:#fff;color:#3c4043;border:1.5px solid #dadce0;border-radius:12px;text-decoration:none;font-weight:700;font-size:.93rem;">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                    S'inscrire avec Google
                </a>
            </div>
        </div>
    </div>
    <script src="user.js"></script>
    <script>
        grecaptcha.ready(function() {
            document.querySelector('form[action="register.php"]').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = this;
                grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'register'}).then(function(token) {
                    document.getElementById('recaptcha-token').value = token;
                    form.submit();
                });
            });
        });
    </script>
</body>
</html>
