<?php
// Session déjà démarrée dans la page principale
require_once __DIR__ . '/session.php';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
$currentPage = $currentPage ?? '';

// Calcul dynamique du chemin vers assets selon la profondeur de la page appelante
$_scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
$_viewRoot   = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
$_relDepth   = substr_count(trim(str_replace($_viewRoot, '', dirname($_scriptPath)), '/'), '/');
$_assetsBase = str_repeat('../', $_relDepth + 1) . 'assets';
$_frontBase  = str_repeat('../', $_relDepth);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    .fo-topbar {
        position:sticky; top:0; z-index:1000;
        backdrop-filter:blur(16px);
        background:rgba(255,255,255,0.95);
        border-bottom:1px solid rgba(16,42,67,0.08);
        box-shadow:0 4px 18px rgba(16,42,67,0.06);
    }
    .fo-topbar-shell {
        width:min(1200px,calc(100% - 32px));
        margin:0 auto; min-height:72px;
        display:flex; align-items:center;
        justify-content:space-between; gap:16px;
    }
    .fo-brand { display:inline-flex; align-items:center; gap:12px; text-decoration:none; color:#102a43; font-weight:900; font-size:1.1rem; flex-shrink:0; }
    .fo-brand img { height:50px; border-radius:10px; object-fit:cover; }
    .fo-nav { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
    .fo-link, .fo-cta, .fo-user {
        text-decoration:none; border-radius:999px; padding:9px 16px;
        font-weight:700; font-size:0.88rem;
        transition:transform .15s,background .15s,box-shadow .15s;
        white-space:nowrap;
    }
    .fo-link { color:#102a43; border:1px solid rgba(16,42,67,0.12); background:transparent; }
    .fo-link:hover { background:rgba(16,42,67,0.05); transform:translateY(-1px); }
    .fo-link.active { color:white; background:#102a43; border-color:#102a43; }
    .fo-cta { color:white; background:linear-gradient(135deg,#0f766e,#14b8a6); border:none; box-shadow:0 5px 16px rgba(15,118,110,.22); }
    .fo-cta:hover { transform:translateY(-1px); }
    .fo-user { background:linear-gradient(135deg,#fff7ed,#fff); border:1px solid rgba(255,183,3,.3); color:#102a43; display:flex; align-items:center; gap:7px; pointer-events:none; }
    .fo-role-badge { background:rgba(15,118,110,.12); color:#0f766e; border-radius:999px; padding:2px 8px; font-size:0.75rem; font-weight:700; }
    @media(max-width:768px){ .fo-topbar-shell{flex-wrap:wrap;padding:10px 0;min-height:auto;} .fo-nav{width:100%;} }
</style>
<div class="fo-topbar">
    <div class="fo-topbar-shell">
        <a class="fo-brand" href="<?php echo $_frontBase; ?>accueil.php">
            <img src="<?php echo $_assetsBase; ?>/images/logo_barchathon.jpg" alt="BarchaThon">
            BarchaThon
        </a>
        <nav class="fo-nav">
            <a class="fo-link <?php echo $currentPage==='accueil'?'active':''; ?>" href="<?php echo $_frontBase; ?>accueil.php">Accueil</a>
            <a class="fo-link <?php echo $currentPage==='catalogue'?'active':''; ?>" href="<?php echo $_frontBase; ?>listMarathons.php">Catalogue</a>

            <?php if ($role === 'visiteur'): ?>
                <a class="fo-link" href="<?php echo $_frontBase; ?>register.php">S'inscrire</a>
                <a class="fo-cta" href="<?php echo $_frontBase; ?>login.php">Se connecter</a>

            <?php elseif ($role === 'participant'): ?>
                <a class="fo-link <?php echo $currentPage==='profile'?'active':''; ?>" href="<?php echo $_frontBase; ?>profile.php">Mon profil</a>
                <span class="fo-user"><?php echo htmlspecialchars($user['nom']); ?> <span class="fo-role-badge">participant</span></span>
                <a class="fo-link" href="<?php echo $_frontBase; ?>logout.php">Se déconnecter</a>

            <?php elseif ($role === 'organisateur'): ?>
                <a class="fo-link <?php echo $currentPage==='profile'?'active':''; ?>" href="<?php echo $_frontBase; ?>profile.php">Mon profil</a>
                <span class="fo-user"><?php echo htmlspecialchars($user['nom']); ?> <span class="fo-role-badge">organisateur</span></span>
                <a class="fo-link" href="<?php echo $_frontBase; ?>logout.php">Se déconnecter</a>

            <?php elseif ($role === 'admin'): ?>
                <a class="fo-link" href="<?php echo $_frontBase; ?>../BackOffice/dashboard.php">Dashboard</a>
                <a class="fo-link <?php echo $currentPage==='profile'?'active':''; ?>" href="<?php echo $_frontBase; ?>profile.php">Mon profil</a>
                <span class="fo-user"><?php echo htmlspecialchars($user['nom']); ?> <span class="fo-role-badge">admin</span></span>
                <a class="fo-link" href="<?php echo $_frontBase; ?>logout.php">Se déconnecter</a>
            <?php endif; ?>
        </nav>
    </div>
</div>
