<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';

$error = '';
if (($_GET['err'] ?? '') === 'google' && !empty($_SESSION['google_login_error'])) {
    $error = $_SESSION['google_login_error'];
    unset($_SESSION['google_login_error']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $ctrl = new UserController();
    $row = $ctrl->findByUsername($username);
    if ($row && password_verify($password, $row['mot_de_passe'])) {
        if (($row['status'] ?? 'active') === 'banned') {
            $error = 'Votre compte a ete bloque. Contactez un administrateur.';
        } else {
            $_SESSION['user'] = [
                'id'       => $row['id_user'],
                'id_user'  => $row['id_user'],
                'username' => $row['nom_user'],
                'nom'      => $row['nom_complet'],
                'role'     => $row['role'],
                'email'    => $row['email'],
                'profile_picture' => $row['profile_picture'],
            ];
            if ($row['role'] === 'admin') {
                header('Location: ../BackOffice/dashboard.php');
            } else {
                header('Location: accueil.php');
            }
            exit;
        }
    } else {
        $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
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
        html[data-theme="dark"] div[style*="border-top"] { border-top-color:rgba(255,255,255,0.1) !important; }
        html[data-theme="dark"] a[href*="face_login"] { background:#162032 !important; color:#14b8a6 !important; border-color:rgba(20,184,166,0.35) !important; }
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
            display:flex; align-items:center; justify-content:center;
            padding:20px;
        }

        /* PAGE NARROW — comme login.html */
        .page-narrow {
            width:100%; max-width:460px;
        }

        /* CARD FORM — comme login.html */
        .card-form {
            background:#fff;
            border-radius:24px;
            padding:40px 36px;
            box-shadow:0 14px 40px rgba(16,42,67,.10);
            border:1px solid rgba(16,42,67,.07);
        }
        .card-form h1 {
            font-size:1.9rem; font-weight:900;
            margin-bottom:10px; color:var(--ink);
        }
        .card-form > p {
            color:#627d98; font-size:0.93rem;
            line-height:1.6; margin-bottom:22px;
        }

        /* ERROR BOX */
        .error-msg {
            background:#fef2f2; border:1px solid #fecaca;
            border-radius:12px; padding:12px 16px;
            color:#b42318; font-size:0.9rem; margin-bottom:18px;
        }

        /* FIELD */
        .field-mb { margin-bottom:18px; }
        .field-mb label {
            display:block; font-weight:700;
            margin-bottom:7px; color:var(--ink); font-size:0.93rem;
        }
        .field-mb input {
            width:100%; border:1.5px solid #cbd5e1; border-radius:12px;
            padding:12px 15px; font:inherit; font-size:0.95rem;
            background:white; transition:border .2s, box-shadow .2s;
        }
        .field-mb input:focus {
            outline:none; border-color:var(--teal);
            box-shadow:0 0 0 3px rgba(15,118,110,.12);
        }

        /* ACTIONS — comme login.html */
        .actions {
            display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;
        }
        .btn {
            text-decoration:none; padding:12px 20px; border-radius:12px;
            font-weight:700; border:none; cursor:pointer;
            display:inline-flex; align-items:center; justify-content:center;
            font-size:0.95rem; transition:opacity .15s, transform .15s;
            flex:1;
        }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary {
            background:linear-gradient(135deg,var(--teal),#14b8a6);
            color:white;
        }
        .btn-secondary {
            background:#f1f5f9; color:var(--ink);
            border:1px solid #cbd5e1; flex:none;
            padding:12px 18px;
        }

        /* FADE IN */
        .fade-in {
            animation:fadeIn .4s ease;
        }
        @keyframes fadeIn {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
    </style>
</head>
<body>
    <div class="page-narrow">
        <div class="card-form fade-in">
            <h1>Se connecter</h1>
            <p>Connectez-vous pour accéder à votre espace personnel.</p>

            <?php if ($error): ?>
                <div id="error-box" class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php else: ?>
                <div id="error-box" class="error-msg" style="display:none;"></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="field-mb">
                    <label for="username">Nom d'utilisateur</label>
                    <input id="username" name="username" type="text" placeholder="Nom d'utilisateur" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="field-mb">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" placeholder="Mot de passe" required>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Connexion</button>
                    <a class="btn btn-secondary" href="register.php">Creer un compte</a>
                    <a class="btn btn-secondary" href="accueil.php">Retour</a>
                </div>
                <div style="text-align:right;margin-top:12px;">
                    <a href="forgot_password.php" style="color:#0f766e;font-size:.88rem;font-weight:600;text-decoration:none;">Mot de passe oublie ?</a>
                </div>
            </form>
            <div style="text-align:center;margin-top:18px;padding-top:18px;border-top:1px solid #e2e8f0;display:flex;flex-direction:column;gap:10px;align-items:stretch;">
                <a href="google_login.php" style="display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:11px 18px;background:#fff;color:#3c4043;border:1.5px solid #dadce0;border-radius:12px;text-decoration:none;font-weight:700;font-size:.93rem;transition:box-shadow .15s,background .15s;">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                    Continuer avec Google
                </a>
                <a href="face_login.php" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;background:#f0fdfa;color:#0f766e;border:1.5px solid #99f6e4;border-radius:12px;text-decoration:none;font-weight:700;font-size:.92rem;">
                    &#128100; Se connecter avec Face ID
                </a>
            </div>
        </div>
    </div>
</body>
</html>
