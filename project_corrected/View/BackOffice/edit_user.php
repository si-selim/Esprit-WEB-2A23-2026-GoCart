<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isAdmin()) {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$ctrl = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_user'] ?? 0);
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $nom_user = trim($_POST['nom_user'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'participant';
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $poids = !empty($_POST['poids']) ? (float)$_POST['poids'] : null;
    $taille = !empty($_POST['taille']) ? (int)$_POST['taille'] : null;
    $tel = trim($_POST['tel'] ?? '') ?: null;
    $pays = trim($_POST['pays'] ?? '') ?: null;
    $ville = trim($_POST['ville'] ?? '') ?: null;
    $occupation = trim($_POST['occupation'] ?? '') ?: null;
    $sexe = trim($_POST['sexe'] ?? '') ?: null;
    if ($sexe !== null && !in_array($sexe, ['homme','femme','autre'])) { $sexe = null; }

    if (!in_array($role, ['admin', 'participant', 'organisateur'])) {
        $role = 'participant';
    }

    if ($nom_complet === '' || $nom_user === '' || $email === '') {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Veuillez remplir tous les champs obligatoires.'));
        exit;
    }

    if (strlen($nom_complet) < 3) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le nom complet doit contenir au moins 3 caracteres.'));
        exit;
    }

    if (strlen($nom_user) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $nom_user)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le nom d\'utilisateur doit contenir au moins 3 caracteres (lettres, chiffres, underscores).'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Veuillez entrer une adresse email valide.'));
        exit;
    }

    if ($tel !== null && !preg_match('/^\d{8}$/', $tel)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le numero de telephone doit contenir exactement 8 chiffres.'));
        exit;
    }

    if ($age !== null && ($age < 1 || $age > 120)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('L\'age doit etre entre 1 et 120.'));
        exit;
    }

    if ($poids !== null && ($poids < 1 || $poids > 500)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le poids doit etre entre 1 et 500 kg.'));
        exit;
    }

    if ($taille !== null && ($taille < 1 || $taille > 300)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('La taille doit etre entre 1 et 300 cm.'));
        exit;
    }

    $existing = $ctrl->showUser($id);
    if (!$existing) {
        header('Location: dashboard.php?tab=utilisateurs&error=' . urlencode('Utilisateur introuvable.'));
        exit;
    }
    $userObj = new User(null, $nom_complet, $nom_user, $existing['mot_de_passe'], $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $existing['profile_picture'], 'active', $sexe);

    try {
        $ctrl->modifierUser($userObj, $id);
        header('Location: dashboard.php?tab=utilisateurs&success=' . urlencode('Utilisateur modifie avec succes.'));
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Ce nom d\'utilisateur existe deja.'));
        } else {
            header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Erreur lors de la mise a jour.'));
        }
    }
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php?tab=utilisateurs');
    exit;
}

$user = $ctrl->showUser($id);

if (!$user) {
    header('Location: dashboard.php?tab=utilisateurs&error=' . urlencode('Utilisateur introuvable.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier utilisateur</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
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
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="../assets/images/logo_barchathon.jpg" alt="Logo Barchathon">
                <div>
                    <strong>Admin Back Office</strong><br>
                    <small>Modifier utilisateur</small>
                </div>
            </div>
            <nav class="side-nav">
         <a class="side-link <?php echo $activeTab==='home'?'active':''; ?>" href="dashboard.php?tab=home">Dashboard</a>
        <a class="side-link <?php echo $activeTab==='utilisateurs'?'active':''; ?>" href="dashboard.php?tab=utilisateurs">Utilisateurs</a>
        <a class="side-link <?php echo $activeTab==='marathons'?'active':''; ?>" href="dashboard.php?tab=marathons">Marathons</a>
        <a class="side-link <?php echo $activeTab==='stands'?'active':''; ?>" href="dashboard.php?tab=stands">Stands</a>
        <a class="side-link <?php echo $activeTab==='commandes'?'active':''; ?>" href="dashboard.php?tab=commandes">Commandes</a>
        <a class="side-link <?php echo $activeTab==='sponsors'?'active':''; ?>" href="dashboard.php?tab=sponsors">Sponsors</a>
            </nav>
        </aside>
        <main class="content">
            <div class="mobile-nav">
                <a class="btn btn-secondary" href="dashboard.php?tab=utilisateurs">Retour</a>
            </div>
            <div class="head">
                <div>
                    <h1>Modifier : <?= htmlspecialchars($user['nom_complet']) ?></h1>
                    <div class="muted">Modification des informations de l'utilisateur #<?= $user['id_user'] ?>.</div>
                </div>
            </div>
            <section class="section-card fade-in">
                <?php if (!empty($_GET['error'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <form method="POST" action="edit_user.php" data-validate>
                    <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                    <div class="form-grid">
                        <div class="field full-width">
                            <label for="nom_complet">Nom complet</label>
                            <input id="nom_complet" name="nom_complet" type="text" value="<?= htmlspecialchars($user['nom_complet']) ?>" required minlength="3">
                            <span id="nomCompletFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="nom_user">Nom d utilisateur</label>
                            <input id="nom_user" name="nom_user" type="text" value="<?= htmlspecialchars($user['nom_user']) ?>" required minlength="3" pattern="[a-zA-Z0-9_]+">
                            <span id="nomUserFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="participant" <?= $user['role'] === 'participant' ? 'selected' : '' ?>>Participant</option>
                                <option value="organisateur" <?= $user['role'] === 'organisateur' ? 'selected' : '' ?>>Organisateur</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            <span id="emailFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="age">Age</label>
                            <input id="age" name="age" type="number" value="<?= $user['age'] ?? '' ?>" min="1" max="120">
                            <span id="ageFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="poids">Poids (kg)</label>
                            <input id="poids" name="poids" type="number" value="<?= $user['poids'] ?? '' ?>" min="1" max="500" step="0.1">
                            <span id="poidsFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="taille">Taille (cm)</label>
                            <input id="taille" name="taille" type="number" value="<?= $user['taille'] ?? '' ?>" min="1" max="300">
                            <span id="tailleFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="tel">Telephone</label>
                            <input id="tel" name="tel" type="tel" value="<?= htmlspecialchars($user['tel'] ?? '') ?>" pattern="[0-9]{8}" title="Le numero doit contenir exactement 8 chiffres">
                            <span id="telFeedback" class="feedback"></span>
                        </div>
                        <div class="field">
                            <label for="pays">Pays</label>
                            <input id="pays" name="pays" type="text" value="<?= htmlspecialchars($user['pays'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="ville">Ville / adresse exacte</label>
                            <input id="ville" name="ville" type="text" value="<?= htmlspecialchars($user['ville'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="occupation">Occupation</label>
                            <select id="occupation" name="occupation">
                                <option value="">-</option>
                                <option value="Etudiant" <?= ($user['occupation'] ?? '') === 'Etudiant' ? 'selected' : '' ?>>Etudiant</option>
                                <option value="Employe" <?= ($user['occupation'] ?? '') === 'Employe' ? 'selected' : '' ?>>Employe</option>
                                <option value="Retraite" <?= ($user['occupation'] ?? '') === 'Retraite' ? 'selected' : '' ?>>Retraite</option>
                            </select>
                        </div>
                        <div class="field full-width">
                            <label>Sexe</label>
                            <div class="gender-picker">
                                <div class="gender-opt">
                                    <input type="radio" id="sexe_h" name="sexe" value="homme" <?= ($user['sexe'] ?? '') === 'homme' ? 'checked' : '' ?>>
                                    <label for="sexe_h"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9.5" cy="14.5" r="5.5"/><line x1="14.28" y1="9.72" x2="20" y2="4"/><polyline points="15.5 4 20 4 20 8.5"/></svg></span>Homme</label>
                                </div>
                                <div class="gender-opt">
                                    <input type="radio" id="sexe_f" name="sexe" value="femme" <?= ($user['sexe'] ?? '') === 'femme' ? 'checked' : '' ?>>
                                    <label for="sexe_f"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="5.5"/><line x1="12" y1="14" x2="12" y2="21"/><line x1="8.5" y1="18" x2="15.5" y2="18"/></svg></span>Femme</label>
                                </div>
                                <div class="gender-opt">
                                    <input type="radio" id="sexe_a" name="sexe" value="autre" <?= ($user['sexe'] ?? '') === 'autre' ? 'checked' : '' ?>>
                                    <label for="sexe_a"><span class="gender-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5.5"/><line x1="12" y1="1.5" x2="12" y2="6.5"/><line x1="12" y1="17.5" x2="12" y2="22.5"/><line x1="1.5" y1="12" x2="6.5" y2="12"/><line x1="17.5" y1="12" x2="22.5" y2="12"/></svg></span>Autre</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                        <a class="btn btn-secondary" href="dashboard.php?tab=utilisateurs">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <div id="feedback-modal" class="modal-overlay">
        <div class="modal-box">
            <div id="feedback-icon" class="feedback-icon success"></div>
            <p id="feedback-message"></p>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
    <script src="../FrontOffice/user.js"></script>
</body>
</html>
