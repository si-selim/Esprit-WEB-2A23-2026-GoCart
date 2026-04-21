<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isConnected()) { header('Location: login.php'); exit; }

$ctrl = new UserController();
$userId = getUserId();

$successMsg = '';
$errorMsg = '';
$activeSection = $_GET['section'] ?? 'view';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nom_complet = trim($_POST['nom_complet'] ?? '');
        $nom_user = trim($_POST['nom_user'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $poids = !empty($_POST['poids']) ? (float)$_POST['poids'] : null;
        $taille = !empty($_POST['taille']) ? (int)$_POST['taille'] : null;
        $tel = trim($_POST['tel'] ?? '') ?: null;
        $pays = trim($_POST['pays'] ?? '') ?: null;
        $ville = trim($_POST['ville'] ?? '') ?: null;
        $occupation = trim($_POST['occupation'] ?? '') ?: null;

        if ($nom_complet === '' || $nom_user === '' || $email === '') {
            $errorMsg = 'Veuillez remplir tous les champs obligatoires.';
        } elseif (strlen($nom_complet) < 3) {
            $errorMsg = 'Le nom complet doit contenir au moins 3 caracteres.';
        } elseif (strlen($nom_user) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $nom_user)) {
            $errorMsg = 'Nom d\'utilisateur invalide (min 3 car., lettres/chiffres/underscores).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Adresse email invalide.';
        } elseif ($tel !== null && !preg_match('/^\d{8}$/', $tel)) {
            $errorMsg = 'Le telephone doit contenir exactement 8 chiffres.';
        } else {
            $profile_picture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $result = $ctrl->saveProfilePicture($_FILES['profile_picture']);
                if ($result === false) {
                    $errorMsg = 'Type de fichier non autorise ou photo trop volumineuse (max 2 Mo).';
                } else {
                    $profile_picture = $result;
                }
            }
            if ($errorMsg === '') {
                $currentUser = $ctrl->showUser($userId);
                $role = $currentUser['role'];
                $userObj = new User(null, $nom_complet, $nom_user, '', $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture);
                try {
                    if ($profile_picture) {
                        $oldPic = $currentUser['profile_picture'];
                        $ctrl->modifierUserAvecPhoto($userObj, $userId);
                        if ($oldPic) {
                            $ctrl->deleteOldPicture($oldPic);
                        }
                        $_SESSION['user']['profile_picture'] = $profile_picture;
                    } else {
                        $ctrl->modifierUser($userObj, $userId);
                    }
                    $_SESSION['user']['nom'] = $nom_complet;
                    $_SESSION['user']['username'] = $nom_user;
                    $_SESSION['user']['email'] = $email;
                    $successMsg = 'Profil mis a jour avec succes.';
                } catch (PDOException $e) {
                    $errorMsg = ($e->getCode() == 23000) ? 'Ce nom d\'utilisateur existe deja.' : 'Erreur lors de la mise a jour.';
                }
            }
        }
        $activeSection = 'edit';

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if ($current === '' || $new === '') {
            $errorMsg = 'Veuillez remplir les deux champs.';
        } elseif (strlen($new) < 6) {
            $errorMsg = 'Le nouveau mot de passe doit contenir au moins 6 caracteres.';
        } else {
            $hash = $ctrl->getPasswordHash($userId);
            if (!password_verify($current, $hash)) {
                $errorMsg = 'Mot de passe actuel incorrect.';
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $ctrl->changePassword($userId, $newHash);
                $successMsg = 'Mot de passe modifie avec succes.';
            }
        }
        $activeSection = 'settings';

    } elseif ($action === 'delete_account') {
        $ctrl->supprimerUser($userId);
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

$u = $ctrl->showUser($userId);
if (!$u) { session_destroy(); header('Location: login.php'); exit; }
$initial = mb_strtoupper(mb_substr($u['nom_complet'], 0, 1));
$currentPage = 'profile';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Mon profil — BarchaThon</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .topbar-spacer { height: 20px; }
        .pw-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; max-width: 500px; }
        .feedback { display:block; font-size:0.82rem; margin-top:4px; min-height:1.2em; }
        .feedback.error { color:#b42318; }
        .feedback.success { color:#0f766e; }
        .profile-preview { max-width:100px; max-height:100px; border-radius:12px; margin-top:8px; display:none; }
        @media(max-width:760px){ .pw-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="topbar-spacer"></div>

<div class="profile-layout">
    <aside class="panel fade-in">
        <h2>Mon espace</h2>
        <div class="avatar" style="width:80px;height:80px;border-radius:50%;margin:16px auto;">
            <?php if (!empty($u['profile_picture'])): ?>
                <img src="images/uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="">
            <?php else: ?>
                <?php echo $initial; ?>
            <?php endif; ?>
        </div>
        <div style="text-align:center;font-weight:700;font-size:1rem;margin-bottom:4px;"><?php echo htmlspecialchars($u['nom_complet']); ?></div>
        <div style="text-align:center;color:var(--muted);font-size:.85rem;margin-bottom:18px;"><?php echo htmlspecialchars($u['role']); ?></div>
        <div class="menu">
            <a class="<?php echo $activeSection==='view'?'active':''; ?>" href="profile.php?section=view"><i class="fas fa-user"></i> Mon profil</a>
            <a class="<?php echo $activeSection==='edit'?'active':''; ?>" href="profile.php?section=edit"><i class="fas fa-pen"></i> Modifier</a>
            <a class="<?php echo $activeSection==='settings'?'active':''; ?>" href="profile.php?section=settings"><i class="fas fa-lock"></i> Mot de passe</a>
            <a href="#" onclick="event.preventDefault(); showConfirm('Voulez-vous vraiment supprimer votre compte ?', function(){ document.getElementById('delete-form').submit(); });" style="color:var(--coral);"><i class="fas fa-trash"></i> Supprimer mon compte</a>
        </div>
    </aside>

    <main class="panel">
        <?php if ($successMsg): ?>
            <div class="success-msg"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="error-msg"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <?php if ($activeSection === 'view'): ?>

            <section class="hero slide-up">
                <div>
                    <h1><?php echo htmlspecialchars($u['nom_complet']); ?></h1>
                    <p>Role : <strong><?php echo htmlspecialchars($u['role']); ?></strong></p>
                    <p>Nom d'utilisateur : <strong><?php echo htmlspecialchars($u['nom_user']); ?></strong></p>
                </div>
                <div class="avatar">
                    <?php if (!empty($u['profile_picture'])): ?>
                        <img src="images/uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="">
                    <?php else: ?>
                        <?php echo $initial; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section>
                <h2 class="section-title">Informations personnelles</h2>
                <div class="info-grid">
                    <div class="info-card slide-up"><div class="label">Age</div><div class="value"><?php echo $u['age'] ? htmlspecialchars($u['age']) : '-'; ?></div></div>
                    <div class="info-card slide-up"><div class="label">Poids</div><div class="value"><?php echo $u['poids'] ? htmlspecialchars($u['poids']).' kg' : '-'; ?></div></div>
                    <div class="info-card slide-up"><div class="label">Taille</div><div class="value"><?php echo $u['taille'] ? htmlspecialchars($u['taille']).' cm' : '-'; ?></div></div>
                    <div class="info-card slide-up"><div class="label">Email</div><div class="value"><?php echo htmlspecialchars($u['email']); ?></div></div>
                    <div class="info-card slide-up"><div class="label">Pays</div><div class="value"><?php echo $u['pays'] ? htmlspecialchars($u['pays']) : '-'; ?></div></div>
                    <div class="info-card slide-up"><div class="label">Ville</div><div class="value"><?php echo $u['ville'] ? htmlspecialchars($u['ville']) : '-'; ?></div></div>
                    <div class="info-card slide-up"><div class="label">Telephone</div><div class="value"><?php echo $u['tel'] ? htmlspecialchars($u['tel']) : '-'; ?></div></div>
                    <div class="info-card slide-up"><div class="label">Occupation</div><div class="value"><?php echo $u['occupation'] ? htmlspecialchars($u['occupation']) : '-'; ?></div></div>
                </div>
            </section>

        <?php elseif ($activeSection === 'edit'): ?>

            <section>
                <h2 class="section-title">Modifier mon profil</h2>
                <form method="POST" action="profile.php" enctype="multipart/form-data" class="card-form fade-in" data-validate>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-grid">
                        <div class="field full-width">
                            <label for="nom_complet">Nom complet</label>
                            <input id="nom_complet" name="nom_complet" type="text" required minlength="3" value="<?php echo htmlspecialchars($u['nom_complet']); ?>">
                            <span id="nomCompletFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="nom_user">Nom d'utilisateur</label>
                            <input id="nom_user" name="nom_user" type="text" required minlength="3" pattern="[a-zA-Z0-9_]+" value="<?php echo htmlspecialchars($u['nom_user']); ?>">
                            <span id="nomUserFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($u['email']); ?>">
                            <span id="emailFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="age">Age</label>
                            <input id="age" name="age" type="number" min="1" max="120" value="<?php echo htmlspecialchars($u['age'] ?? ''); ?>">
                            <span id="ageFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="poids">Poids (kg)</label>
                            <input id="poids" name="poids" type="number" min="1" max="500" step="0.1" value="<?php echo htmlspecialchars($u['poids'] ?? ''); ?>">
                            <span id="poidsFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="taille">Taille (cm)</label>
                            <input id="taille" name="taille" type="number" min="1" max="300" value="<?php echo htmlspecialchars($u['taille'] ?? ''); ?>">
                            <span id="tailleFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="tel">Telephone</label>
                            <input id="tel" name="tel" type="tel" pattern="[0-9]{8}" value="<?php echo htmlspecialchars($u['tel'] ?? ''); ?>">
                            <span id="telFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="pays">Pays</label>
                            <input id="pays" name="pays" type="text" value="<?php echo htmlspecialchars($u['pays'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="ville">Ville</label>
                            <input id="ville" name="ville" type="text" value="<?php echo htmlspecialchars($u['ville'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="occupation">Occupation</label>
                            <select id="occupation" name="occupation">
                                <option value="">—</option>
                                <option value="Etudiant" <?php echo ($u['occupation'] ?? '')==='Etudiant'?'selected':''; ?>>Etudiant</option>
                                <option value="Employe" <?php echo ($u['occupation'] ?? '')==='Employe'?'selected':''; ?>>Employe</option>
                                <option value="Retraite" <?php echo ($u['occupation'] ?? '')==='Retraite'?'selected':''; ?>>Retraite</option>
                            </select>
                        </div>
                        <div class="field full-width">
                            <label>Photo de profil</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    Changer la photo
                                    <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp">
                                </label>
                                <span class="file-upload-name"><?php echo $u['profile_picture'] ? htmlspecialchars($u['profile_picture']) : 'Aucun fichier'; ?></span>
                            </div>
                            <span id="profilePictureFeedback" class="feedback"></span>
                            <img id="profilePicturePreview" class="profile-preview" alt="">
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Enregistrer</button>
                        <a class="btn btn-secondary" href="profile.php">Annuler</a>
                    </div>
                </form>
            </section>

        <?php elseif ($activeSection === 'settings'): ?>

            <section>
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
                        <form class="password-form" method="POST" action="profile.php">
                            <input type="hidden" name="action" value="change_password">
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

        <?php endif; ?>
    </main>
</div>

<form id="delete-form" method="POST" action="profile.php" style="display:none;">
    <input type="hidden" name="action" value="delete_account">
</form>

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

<script src="../assets/js/app.js"></script>
<script src="user.js"></script>
</body>
</html>
