<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';

$ctrl = new UserController();
$token = strtolower(trim($_GET['token'] ?? ''));
$status = 'error';
$message = 'Lien de verification invalide.';

if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $row = $ctrl->findByVerificationToken($token);
    if ($row) {
        if ((int)$row['verified'] === 1) {
            $status = 'already';
            $message = 'Votre compte est deja verifie. Vous pouvez vous connecter.';
        } else {
            $ctrl->markVerified((int)$row['id_user']);
            $status = 'success';
            $message = 'Votre compte a ete verifie avec succes ! Vous pouvez maintenant vous connecter.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification de l'email — BarchaThon</title>
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <style>
        html[data-theme="dark"] body { background:#0f172a !important; color:#e2e8f0; }
        html[data-theme="dark"] .card { background:#1e293b !important; box-shadow:0 14px 40px rgba(0,0,0,.4) !important; }
        html[data-theme="dark"] h1 { color:#e2e8f0; }
        html[data-theme="dark"] p { color:#94a3b8; }
    </style>
    <style>
        body { font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#fefaf0,#f4fbfb); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; margin:0; color:#102a43; }
        .card { background:#fff; border-radius:24px; padding:48px 40px; box-shadow:0 14px 40px rgba(16,42,67,.10); max-width:480px; text-align:center; }
        .icon { font-size:3.5rem; margin-bottom:16px; }
        .success { color:#0f766e; }
        .already { color:#b45309; }
        .error { color:#b42318; }
        h1 { font-size:1.6rem; margin:0 0 12px; }
        p { color:#627d98; line-height:1.6; margin-bottom:24px; }
        .btn { display:inline-block; padding:12px 22px; background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; border-radius:12px; text-decoration:none; font-weight:700; }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($status === 'success'): ?>
            <div class="icon success">&#10003;</div>
            <h1 class="success">Email verifie</h1>
        <?php elseif ($status === 'already'): ?>
            <div class="icon already">&#8505;</div>
            <h1 class="already">Deja verifie</h1>
        <?php else: ?>
            <div class="icon error">&times;</div>
            <h1 class="error">Lien invalide</h1>
        <?php endif; ?>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a class="btn" href="login.php">Se connecter</a>
    </div>
</body>
</html>
