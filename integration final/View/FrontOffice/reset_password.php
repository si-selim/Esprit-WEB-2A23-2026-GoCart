<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$token = trim($token);

$ctrl = new UserController();
$user = $token ? $ctrl->findByResetToken($token) : false;

if (!$token || !$user) {
    $error = 'Lien invalide ou expire. Veuillez faire une nouvelle demande.';
}

if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pwd  = $_POST['password'] ?? '';
    $pwd2 = $_POST['password_confirm'] ?? '';

    if (strlen($pwd) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caracteres.';
    } elseif ($pwd !== $pwd2) {
        $error = 'Les deux mots de passe ne correspondent pas.';
    } else {
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        $ctrl->resetPassword($user['id_user'], $hash);
        $success = 'Votre mot de passe a ete reinitialise avec succes. Vous pouvez maintenant vous connecter.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reinitialiser le mot de passe</title>
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <style>
        html[data-theme="dark"] body { background:#0f172a !important; }
        html[data-theme="dark"] .card-form { background:#1e293b !important; border-color:rgba(255,255,255,0.08) !important; box-shadow:0 14px 40px rgba(0,0,0,.4) !important; }
        html[data-theme="dark"] .card-form h1 { color:#e2e8f0; }
        html[data-theme="dark"] .card-form > p { color:#94a3b8; }
        html[data-theme="dark"] label { color:#e2e8f0; }
        html[data-theme="dark"] input { background:#162032 !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
        html[data-theme="dark"] input:focus { background:#1e293b !important; border-color:#14b8a6 !important; }
        html[data-theme="dark"] .btn-secondary { background:rgba(255,255,255,0.06) !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
    </style>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --bg:#f4fbfb; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:"Segoe UI",sans-serif; color:var(--ink);
            background:linear-gradient(180deg,#fefaf0,var(--bg));
            min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px;
        }
        .page-narrow { width:100%; max-width:460px; }
        .card-form {
            background:#fff; border-radius:24px; padding:40px 36px;
            box-shadow:0 14px 40px rgba(16,42,67,.10); border:1px solid rgba(16,42,67,.07);
        }
        .card-form h1 { font-size:1.8rem; font-weight:900; margin-bottom:10px; }
        .card-form > p { color:#627d98; font-size:0.93rem; line-height:1.6; margin-bottom:22px; }
        .error-msg { background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:12px 16px; color:#b42318; font-size:0.9rem; margin-bottom:18px; }
        .success-msg { background:#ecfdf5; border:1px solid #a7f3d0; border-radius:12px; padding:12px 16px; color:#065f46; font-size:0.9rem; margin-bottom:18px; }
        .field-mb { margin-bottom:18px; }
        .field-mb label { display:block; font-weight:700; margin-bottom:7px; font-size:0.93rem; }
        .field-mb input {
            width:100%; border:1.5px solid #cbd5e1; border-radius:12px;
            padding:12px 15px; font:inherit; font-size:0.95rem;
            background:white; transition:border .2s, box-shadow .2s;
        }
        .field-mb input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
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
    </style>
</head>
<body>
    <div class="page-narrow">
        <div class="card-form fade-in">
            <h1>Nouveau mot de passe</h1>
            <p>Choisissez un nouveau mot de passe pour votre compte BarchaThon.</p>

            <?php if ($error): ?>
                <div class="error-msg">&#9888;&#65039; <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg">&#9989; <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (!$success && !$error): ?>
            <form method="POST" action="reset_password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="field-mb">
                    <label for="password">Nouveau mot de passe (8 caracteres minimum)</label>
                    <input id="password" name="password" type="password" placeholder="Nouveau mot de passe" required minlength="8">
                </div>
                <div class="field-mb">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input id="password_confirm" name="password_confirm" type="password" placeholder="Confirmer" required minlength="8">
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Reinitialiser</button>
                    <a class="btn btn-secondary" href="login.php">Annuler</a>
                </div>
            </form>
            <?php elseif ($error): ?>
                <div class="actions">
                    <a class="btn btn-primary" href="forgot_password.php">Nouvelle demande</a>
                    <a class="btn btn-secondary" href="login.php">Retour</a>
                </div>
            <?php else: ?>
                <div class="actions">
                    <a class="btn btn-primary" href="login.php">Se connecter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
