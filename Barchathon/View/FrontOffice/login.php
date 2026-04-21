<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $ctrl = new UserController();
    $row = $ctrl->findByUsername($username);
    if ($row && password_verify($password, $row['mot_de_passe'])) {
        $_SESSION['user'] = [
            'id'       => $row['id_user'],
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
    $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
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
            </form>
        </div>
    </div>
</body>
</html>
