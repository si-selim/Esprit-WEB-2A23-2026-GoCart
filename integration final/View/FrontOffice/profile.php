<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isConnected()) { header('Location: login.php'); exit; }

$ctrl = new UserController();
$userId = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_face_descriptor') {
    header('Content-Type: application/json');
    $descJson = $_POST['descriptor'] ?? '';
    $arr = json_decode($descJson, true);
    if (is_array($arr) && count($arr) === 128) {
        $ctrl->saveFaceDescriptor($userId, $descJson);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Descripteur invalide.']);
    }
    exit;
}

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
        $sexe = trim($_POST['sexe'] ?? '') ?: null;
        if ($sexe !== null && !in_array($sexe, ['homme','femme','autre'])) { $sexe = null; }

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
                $userObj = new User(null, $nom_complet, $nom_user, '', $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture, 'active', $sexe);
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

    } elseif ($action === 'delete_face') {
        $ctrl->saveFaceDescriptor($userId, null);
        $successMsg = 'Visage supprime avec succes.';
        $activeSection = 'face';
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
        .cb-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:12px; margin-top:16px; }
        .cb-card { position:relative; border:2px solid var(--card-border,#e2e8f0); border-radius:18px; padding:16px 10px 14px; cursor:pointer; text-align:center; background:var(--card,#fff); transition:all .2s ease; outline:none; }
        .cb-card:hover { border-color:rgba(15,118,110,.4); transform:translateY(-2px); box-shadow:0 6px 20px rgba(15,118,110,.1); }
        .cb-card.active { border-color:#0f766e; background:rgba(15,118,110,.05); }
        .cb-dots { display:flex; gap:5px; justify-content:center; margin-bottom:11px; }
        .cb-dot { width:18px; height:18px; border-radius:50%; display:inline-block; flex-shrink:0; }
        .cb-label { font-weight:700; font-size:.86rem; color:var(--ink,#102a43); }
        .cb-desc { color:var(--muted,#627d98); font-size:.74rem; margin-top:3px; line-height:1.35; }
        .cb-check { position:absolute; top:8px; right:8px; width:20px; height:20px; border-radius:50%; background:#0f766e; color:#fff; display:none; align-items:center; justify-content:center; }
        .cb-card.active .cb-check { display:flex; }
        html[data-theme="dark"] .cb-card { background:#1e293b; border-color:rgba(255,255,255,.1); }
        html[data-theme="dark"] .cb-card.active { background:rgba(20,184,166,.08); border-color:#14b8a6; }
        html[data-theme="dark"] .cb-label { color:#e2e8f0; }
        html[data-theme="dark"] .cb-card.active .cb-check { background:#14b8a6; }
        html[data-theme="dark"] .cb-card:hover { border-color:rgba(20,184,166,.5); }
        @media(max-width:760px){ .pw-grid { grid-template-columns: 1fr; } }
        .face-video-box { position:relative; width:100%; max-width:460px; aspect-ratio:4/3; background:#0f172a; border-radius:18px; overflow:hidden; margin:0 0 16px; }
        .face-video-box video, .face-video-box canvas { position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; }
        .face-enrolled { display:flex; align-items:center; gap:12px; padding:13px 18px; background:#ecfdf5; border:1.5px solid #a7f3d0; border-radius:14px; color:#065f46; font-weight:700; font-size:.93rem; margin-bottom:18px; }
        html[data-theme="dark"] .face-enrolled { background:rgba(6,95,70,.15); border-color:rgba(20,184,166,.3); color:#6ee7b7; }
        .face-del-btn { margin-left:auto; background:transparent; border:1.5px solid #fca5a5; color:#b42318; border-radius:8px; padding:5px 13px; cursor:pointer; font-size:.82rem; font-weight:700; transition:background .15s; }
        .face-del-btn:hover { background:#fef2f2; }
        html[data-theme="dark"] .face-del-btn { border-color:rgba(248,113,113,.4); color:#fca5a5; }
        .face-msg { padding:11px 15px; border-radius:11px; margin-bottom:16px; font-size:.9rem; font-weight:600; }
        .face-msg.info { background:#e0f2fe; color:#075985; }
        .face-msg.success { background:#ecfdf5; color:#065f46; }
        .face-msg.error { background:#fef2f2; color:#b42318; }
        html[data-theme="dark"] .face-msg.info { background:rgba(7,89,133,.2); color:#7dd3fc; }
        html[data-theme="dark"] .face-msg.success { background:rgba(6,95,70,.2); color:#6ee7b7; }
        html[data-theme="dark"] .face-msg.error { background:rgba(180,35,24,.2); color:#fca5a5; }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="topbar-spacer"></div>

<div class="profile-layout">
    <aside class="panel fade-in">
        <h2>Mon espace</h2>
        <div class="avatar" style="width:80px;height:80px;border-radius:50%;margin:16px auto;<?php echo !empty($u['profile_picture']) ? 'background:transparent;box-shadow:none;animation:none;border-color:transparent;' : ''; ?>">
            <?php if (!empty($u['profile_picture'])): ?>
                <img src="images/uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="">
            <?php else: ?>
                <?php echo $initial; ?>
            <?php endif; ?>
        </div>
        <div style="text-align:center;font-weight:700;font-size:1rem;margin-bottom:4px;"><?php echo htmlspecialchars($u['nom_complet']); ?></div>
        <div style="text-align:center;color:var(--muted);font-size:.85rem;margin-bottom:8px;"><?php echo htmlspecialchars($u['role']); ?></div>
        <div style="text-align:center;margin-bottom:18px;">
            <?php if ((int)($u['verified'] ?? 1) === 1): ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:20px;background:#ecfdf5;color:#065f46;font-size:.78rem;font-weight:700;">&#10003; Verifie</span>
            <?php else: ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:20px;background:#fef9c3;color:#854d0e;font-size:.78rem;font-weight:700;">&#9888; Non verifie</span>
            <?php endif; ?>
        </div>
        <div class="menu">
            <a class="<?php echo $activeSection==='view'?'active':''; ?>" href="profile.php?section=view"><i class="fas fa-user"></i> Mon profil</a>
            <a class="<?php echo $activeSection==='edit'?'active':''; ?>" href="profile.php?section=edit"><i class="fas fa-pen"></i> Modifier</a>
            <a class="<?php echo $activeSection==='settings'?'active':''; ?>" href="profile.php?section=settings"><i class="fas fa-lock"></i> Mot de passe</a>
            <a class="<?php echo $activeSection==='accessibility'?'active':''; ?>" href="profile.php?section=accessibility"><i class="fas fa-eye"></i> Accessibilite</a>
            <a class="<?php echo $activeSection==='face'?'active':''; ?>" href="profile.php?section=face"><i class="fas fa-camera"></i> Face ID</a>
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
                    <p>Compte : <?php if ((int)($u['verified'] ?? 1) === 1): ?><span style="color:#065f46;font-weight:700;">&#10003; Verifie</span><?php else: ?><span style="color:#854d0e;font-weight:700;">&#9888; Non verifie</span><?php endif; ?></p>
                    <p>Nom d'utilisateur : <strong><?php echo htmlspecialchars($u['nom_user']); ?></strong></p>
                </div>
                <div class="avatar"<?php echo !empty($u['profile_picture']) ? ' style="background:transparent;box-shadow:none;animation:none;border-color:transparent;"' : ''; ?>>
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
                    <div class="info-card slide-up"><div class="label">Statut du compte</div><div class="value"><?php if ((int)($u['verified'] ?? 1) === 1): ?><span style="color:#065f46;font-weight:700;">&#10003; Verifie</span><?php else: ?><span style="color:#854d0e;font-weight:700;">&#9888; Non verifie — consultez votre boite email</span><?php endif; ?></div></div>
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
                            <label>Sexe</label>
                            <div class="gender-picker">
                                <div class="gender-opt">
                                    <input type="radio" id="sexe_h" name="sexe" value="homme" <?php echo ($u['sexe'] ?? '') === 'homme' ? 'checked' : ''; ?>>
                                    <label for="sexe_h"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9.5" cy="14.5" r="5.5"/><line x1="14.28" y1="9.72" x2="20" y2="4"/><polyline points="15.5 4 20 4 20 8.5"/></svg></span>Homme</label>
                                </div>
                                <div class="gender-opt">
                                    <input type="radio" id="sexe_f" name="sexe" value="femme" <?php echo ($u['sexe'] ?? '') === 'femme' ? 'checked' : ''; ?>>
                                    <label for="sexe_f"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="5.5"/><line x1="12" y1="14" x2="12" y2="21"/><line x1="8.5" y1="18" x2="15.5" y2="18"/></svg></span>Femme</label>
                                </div>
                                <div class="gender-opt">
                                    <input type="radio" id="sexe_a" name="sexe" value="autre" <?php echo ($u['sexe'] ?? '') === 'autre' ? 'checked' : ''; ?>>
                                    <label for="sexe_a"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5.5"/><line x1="12" y1="1.5" x2="12" y2="6.5"/><line x1="12" y1="17.5" x2="12" y2="22.5"/><line x1="1.5" y1="12" x2="6.5" y2="12"/><line x1="17.5" y1="12" x2="22.5" y2="12"/></svg></span>Autre</label>
                                </div>
                            </div>
                        </div>
                        <div class="field full-width">
                            <label>Photo de profil</label>
                            <?php if (!empty($u['profile_picture'])): ?>
                                <img src="images/uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="Photo actuelle" style="display:block;width:90px;height:90px;object-fit:cover;border-radius:50%;margin-bottom:10px;border:2px solid var(--card-border);">
                            <?php else: ?>
                                <div style="display:flex;align-items:center;justify-content:center;width:90px;height:90px;border-radius:50%;background:var(--primary-grad);color:#fff;font-size:2rem;font-weight:700;margin-bottom:10px;"><?php echo $initial; ?></div>
                            <?php endif; ?>
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
                        <div class="label">Connexion Face ID</div>
                        <div class="value">
                            <?php if (!empty($u['face_descriptor'])): ?>
                                <span style="color:#065f46;font-weight:700;">&#10003; Visage enregistre</span><br>
                                <a href="profile.php?section=face" style="display:inline-block;margin-top:8px;">Recapturer mon visage</a>
                            <?php else: ?>
                                <a href="profile.php?section=face" style="display:inline-block;padding:8px 14px;background:#0f766e;color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:.9rem;">Configurer Face ID</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-card fade-in">
                        <div class="label">Deconnexion</div>
                        <div class="value"><a href="logout.php">Se deconnecter maintenant</a></div>
                    </div>
                </div>
            </section>

        <?php elseif ($activeSection === 'accessibility'): ?>

            <section class="slide-up">
                <h2 class="section-title">Accessibilite visuelle</h2>
                <p style="color:var(--muted);font-size:.93rem;margin-bottom:4px;">Selectionnez le mode qui correspond a votre vision. Le filtre s'applique instantanement sur toutes les pages du site.</p>
                <p style="color:var(--muted);font-size:.82rem;margin-bottom:0;">Les pastilles de couleur ci-dessous montrent l'effet en temps reel selon le mode actif.</p>
                <div class="cb-grid" id="cb-grid">
                    <button class="cb-card" data-mode="normal" type="button">
                        <span class="cb-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                        <div class="cb-dots"><span class="cb-dot" style="background:#ef4444"></span><span class="cb-dot" style="background:#22c55e"></span><span class="cb-dot" style="background:#eab308"></span><span class="cb-dot" style="background:#3b82f6"></span><span class="cb-dot" style="background:#a855f7"></span></div>
                        <div class="cb-label">Normal</div>
                        <div class="cb-desc">Aucun filtre</div>
                    </button>
                    <button class="cb-card" data-mode="deuteranopia" type="button">
                        <span class="cb-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                        <div class="cb-dots"><span class="cb-dot" style="background:#ef4444"></span><span class="cb-dot" style="background:#22c55e"></span><span class="cb-dot" style="background:#eab308"></span><span class="cb-dot" style="background:#3b82f6"></span><span class="cb-dot" style="background:#a855f7"></span></div>
                        <div class="cb-label">Deuteranopie</div>
                        <div class="cb-desc">Difficulte vert–rouge</div>
                    </button>
                    <button class="cb-card" data-mode="protanopia" type="button">
                        <span class="cb-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                        <div class="cb-dots"><span class="cb-dot" style="background:#ef4444"></span><span class="cb-dot" style="background:#22c55e"></span><span class="cb-dot" style="background:#eab308"></span><span class="cb-dot" style="background:#3b82f6"></span><span class="cb-dot" style="background:#a855f7"></span></div>
                        <div class="cb-label">Protanopie</div>
                        <div class="cb-desc">Difficulte rouge–vert</div>
                    </button>
                    <button class="cb-card" data-mode="tritanopia" type="button">
                        <span class="cb-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                        <div class="cb-dots"><span class="cb-dot" style="background:#ef4444"></span><span class="cb-dot" style="background:#22c55e"></span><span class="cb-dot" style="background:#eab308"></span><span class="cb-dot" style="background:#3b82f6"></span><span class="cb-dot" style="background:#a855f7"></span></div>
                        <div class="cb-label">Tritanopie</div>
                        <div class="cb-desc">Difficulte bleu–jaune</div>
                    </button>
                    <button class="cb-card" data-mode="achromatopsia" type="button">
                        <span class="cb-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                        <div class="cb-dots"><span class="cb-dot" style="background:#ef4444"></span><span class="cb-dot" style="background:#22c55e"></span><span class="cb-dot" style="background:#eab308"></span><span class="cb-dot" style="background:#3b82f6"></span><span class="cb-dot" style="background:#a855f7"></span></div>
                        <div class="cb-label">Achromatopsie</div>
                        <div class="cb-desc">Niveaux de gris</div>
                    </button>
                </div>
                <script>
                (function(){
                    var html = document.documentElement;
                    var current = localStorage.getItem('cb-mode') || 'normal';
                    document.querySelectorAll('.cb-card').forEach(function(card){
                        if (card.dataset.mode === current) card.classList.add('active');
                        card.addEventListener('click', function(){
                            var mode = card.dataset.mode;
                            document.querySelectorAll('.cb-card').forEach(function(c){ c.classList.remove('active'); });
                            card.classList.add('active');
                            localStorage.setItem('cb-mode', mode);
                            if (mode === 'normal') {
                                html.removeAttribute('data-cb');
                                html.style.filter = '';
                            } else {
                                html.setAttribute('data-cb', mode);
                                html.style.filter = 'url(#cb-' + mode + ')';
                            }
                        });
                    });
                })();
                </script>

                <div class="voice-card">
                    <div class="voice-card-head">
                        <div class="voice-card-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                        </div>
                        <div>
                            <h3 style="margin:0;font-size:1.1rem;">Mode voix (navigation sans clavier)</h3>
                            <p style="margin:2px 0 0;color:var(--muted);font-size:.88rem;">Activez le micro et parlez pour naviguer. Idéal si vous ne pouvez pas utiliser le clavier.</p>
                        </div>
                        <button type="button" class="voice-test-btn" onclick="if(window.BarchaVoice) window.BarchaVoice.toggle();">Activer / Désactiver</button>
                    </div>
                    <p style="margin:14px 0 6px;font-weight:700;color:var(--ink);">Raccourci : <kbd class="vk">Ctrl</kbd> + <kbd class="vk">G</kbd></p>
                    <p style="margin:0 0 10px;color:var(--muted);font-size:.85rem;">Fonctionne sur Chrome, Edge ou Safari. Autorisez le microphone la première fois. Dites « aide » pour voir toutes les commandes.</p>
                    <div class="voice-cmds">
                        <div><strong>« accueil »</strong> · « catalogue » · « mon profil »</div>
                        <div><strong>« modifier profil »</strong> · « mot de passe » · « accessibilité »</div>
                        <div><strong>« face id »</strong> · « déconnexion » · « retour »</div>
                        <div><strong>« mode sombre »</strong> · « actualiser » · « défiler vers le bas »</div>
                        <div><strong>« clique [texte] »</strong> · « aide » · « arrêter »</div>
                    </div>
                </div>
                <style>
                    .voice-card { margin-top:26px; padding:20px 22px; background:linear-gradient(135deg,rgba(15,118,110,.06),rgba(20,184,166,.03)); border:1.5px solid rgba(15,118,110,.18); border-radius:18px; }
                    html[data-theme="dark"] .voice-card { background:linear-gradient(135deg,rgba(20,184,166,.08),rgba(15,118,110,.04)); border-color:rgba(20,184,166,.25); }
                    .voice-card-head { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
                    .voice-card-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; flex-shrink:0; box-shadow:0 6px 18px rgba(15,118,110,.3); }
                    .voice-test-btn { margin-left:auto; padding:8px 16px; background:#0f766e; color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-size:.88rem; transition:transform .15s, background .15s; }
                    .voice-test-btn:hover { background:#115e59; transform:translateY(-1px); }
                    .vk { background:#f1f5f9; padding:2px 8px; border-radius:6px; border:1px solid #cbd5e1; font-weight:700; font-family:"Segoe UI",sans-serif; font-size:.85rem; }
                    html[data-theme="dark"] .vk { background:#162032; border-color:rgba(255,255,255,.12); color:#e2e8f0; }
                    .voice-cmds { display:flex; flex-direction:column; gap:4px; font-size:.87rem; color:var(--muted); padding:12px 14px; background:rgba(255,255,255,.55); border-radius:10px; margin-top:8px; }
                    html[data-theme="dark"] .voice-cmds { background:rgba(0,0,0,.2); color:#94a3b8; }
                    .voice-cmds strong { color:#0f766e; font-weight:700; }
                    html[data-theme="dark"] .voice-cmds strong { color:#5eead4; }
                </style>
            </section>

        <?php elseif ($activeSection === 'face'): ?>

            <section class="slide-up">
                <h2 class="section-title">Connexion Face ID</h2>
                <p style="color:var(--muted);font-size:.93rem;margin-bottom:20px;">Enregistrez votre visage pour vous connecter sans mot de passe. Positionnez votre visage dans le cadre et cliquez sur <strong>Enregistrer</strong>.</p>

                <?php if (!empty($u['face_descriptor'])): ?>
                <div class="face-enrolled">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Visage enregistre — Face ID actif
                    <button type="button" class="face-del-btn" onclick="showConfirm('Supprimer votre visage enregistre ?', function(){ document.getElementById('delete-face-form').submit(); })">Supprimer</button>
                </div>
                <?php endif; ?>

                <div id="face-status" class="face-msg info">Chargement des modeles de reconnaissance faciale...</div>

                <div class="face-video-box">
                    <video id="face-video" autoplay muted playsinline></video>
                    <canvas id="face-overlay"></canvas>
                </div>

                <div class="actions">
                    <button id="face-capture" class="btn btn-primary" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:7px;flex-shrink:0"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <?php echo !empty($u['face_descriptor']) ? 'Recapturer mon visage' : 'Enregistrer mon visage'; ?>
                    </button>
                </div>
            </section>

            <form id="delete-face-form" method="POST" action="profile.php?section=face" style="display:none;">
                <input type="hidden" name="action" value="delete_face">
            </form>

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
<?php if ($activeSection === 'face'): ?>
<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script defer>
    (function(){
        var MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
        var video = document.getElementById('face-video');
        var overlay = document.getElementById('face-overlay');
        var statusEl = document.getElementById('face-status');
        var captureBtn = document.getElementById('face-capture');
        var FACE_OPTS;

        function setStatus(type, text) {
            statusEl.className = 'face-msg ' + type;
            statusEl.textContent = text;
        }

        async function init() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                FACE_OPTS = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.3 });
                setStatus('info', 'Modeles charges. Demande d\'acces a la camera...');
                var stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.onloadedmetadata = function() {
                    overlay.width = video.videoWidth;
                    overlay.height = video.videoHeight;
                    setStatus('info', 'Camera prete — positionnez votre visage dans le cadre.');
                    captureBtn.disabled = false;
                    drawLoop();
                };
            } catch (err) {
                setStatus('error', 'Erreur : ' + (err.message || err));
            }
        }

        async function drawLoop() {
            if (!video || video.paused || video.ended) return;
            var det = await faceapi.detectSingleFace(video, FACE_OPTS).withFaceLandmarks();
            var ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
            if (det) {
                var box = det.detection.box;
                ctx.strokeStyle = '#14b8a6';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
            }
            requestAnimationFrame(drawLoop);
        }

        async function detectWithRetry(attempts) {
            for (var i = 0; i < attempts; i++) {
                var r = await faceapi.detectSingleFace(video, FACE_OPTS).withFaceLandmarks().withFaceDescriptor();
                if (r) return r;
                await new Promise(function(res){ setTimeout(res, 400); });
            }
            return null;
        }

        captureBtn.addEventListener('click', async function() {
            captureBtn.disabled = true;
            setStatus('info', 'Capture en cours...');
            try {
                var result = await detectWithRetry(5);
                if (!result) {
                    setStatus('error', 'Aucun visage detecte. Verifiez l\'eclairage et positionnez-vous face a la camera, puis reessayez.');
                    captureBtn.disabled = false;
                    return;
                }
                var descriptor = Array.from(result.descriptor);
                var body = new URLSearchParams();
                body.append('action', 'save_face_descriptor');
                body.append('descriptor', JSON.stringify(descriptor));
                var res = await fetch('profile.php', { method: 'POST', body: body });
                var data = await res.json();
                if (data.ok) {
                    setStatus('success', 'Visage enregistre avec succes ! Vous pouvez desormais vous connecter par Face ID.');
                    captureBtn.disabled = false;
                    setTimeout(function(){ location.reload(); }, 1600);
                } else {
                    setStatus('error', data.error || 'Erreur lors de l\'enregistrement.');
                    captureBtn.disabled = false;
                }
            } catch (err) {
                setStatus('error', 'Erreur : ' + (err.message || err));
                captureBtn.disabled = false;
            }
        });

        window.addEventListener('load', init);
    })();
</script>
<?php endif; ?>

</body>
</html>
