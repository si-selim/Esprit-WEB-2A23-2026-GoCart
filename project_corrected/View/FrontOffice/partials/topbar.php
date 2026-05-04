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
<script>
(function(){
    document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');
    var _cb=localStorage.getItem('cb-mode');
    if(_cb&&_cb!=='normal')document.documentElement.setAttribute('data-cb',_cb);
})();
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    html[data-theme="dark"] body { background:#0f172a; color:#e2e8f0; }
    html[data-theme="dark"] .fo-topbar { background:rgba(2,6,23,0.97); border-color:rgba(255,255,255,0.06); }
    html[data-theme="dark"] .fo-link { color:#cbd5e1; border-color:rgba(255,255,255,0.1); }
    html[data-theme="dark"] .fo-link:hover { background:rgba(255,255,255,0.06); color:#fff; }
    html[data-theme="dark"] .fo-link.active { background:#0f766e; color:#fff; border-color:#0f766e; }
    html[data-theme="dark"] .fo-user { background:rgba(30,41,59,.8); border-color:rgba(255,255,255,0.1); color:#e2e8f0; }
    html[data-theme="dark"] h1,html[data-theme="dark"] h2,html[data-theme="dark"] h3 { color:#e2e8f0; }
    html[data-theme="dark"] .stat-card,html[data-theme="dark"] .feature-card,
    html[data-theme="dark"] .card-form,html[data-theme="dark"] .section-card { background:#1e293b; border-color:rgba(255,255,255,0.08); }
    html[data-theme="dark"] .card-form::before { display:none; }
    html[data-theme="dark"] input,html[data-theme="dark"] select { background:#1e293b; color:#e2e8f0; border-color:rgba(255,255,255,0.1); outline:none !important; }
    html[data-theme="dark"] label { color:#e2e8f0; }
    html[data-theme="dark"] table th { background:#162032; color:#94a3b8; }
    html[data-theme="dark"] tbody tr:hover { background:rgba(20,184,166,0.05); }
    html[data-theme="dark"] td,html[data-theme="dark"] th { border-bottom-color:rgba(255,255,255,0.07); }
    html[data-theme="dark"] .muted { color:#94a3b8; }
    html[data-theme="dark"] p { color:#94a3b8; }
    html[data-theme="dark"] .stat-lbl { color:#94a3b8; }
    #theme-toggle {
        background:transparent; border:1.5px solid rgba(15,118,110,0.35); border-radius:50%;
        cursor:pointer; color:#102a43; height:38px; width:38px; padding:0;
        display:inline-flex; align-items:center; justify-content:center;
        transition:transform .25s ease, background .25s ease, border-color .25s ease;
        flex-shrink:0;
    }
    #theme-toggle svg { display:block; }
    #theme-toggle:hover { transform:rotate(20deg); background:rgba(15,118,110,0.08); border-color:rgba(15,118,110,0.55); }
    html[data-theme="dark"] #theme-toggle { color:#e2e8f0; border-color:rgba(20,184,166,0.45); }
    html[data-theme="dark"] #theme-toggle:hover { background:rgba(20,184,166,0.12); border-color:rgba(20,184,166,0.7); }
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
    .fo-left { display:flex; align-items:center; gap:14px; flex-shrink:0; }
    .fo-brand-cluster { display:flex; align-items:center; gap:10px; }
    .fo-greeting {
        display:flex; align-items:center; gap:7px;
        font-size:0.95rem; font-weight:600; white-space:nowrap;
        color:#0f766e; padding:7px 16px;
        background:rgba(15,118,110,0.07); border-radius:999px;
        border:1px solid rgba(15,118,110,0.14);
    }
    .fo-greeting svg { flex-shrink:0; width:16px; height:16px; }
    html[data-theme="dark"] .fo-greeting { color:#5eead4; background:rgba(20,184,166,0.08); border-color:rgba(20,184,166,0.2); }
    .fo-brand { display:inline-flex; align-items:center; gap:12px; text-decoration:none; color:#102a43; font-weight:900; font-size:1.1rem; }
    .fo-brand img {
        height:50px; border-radius:10px; object-fit:cover;
        filter:drop-shadow(0 2px 10px rgba(255,183,3,0.45)) drop-shadow(0 0 4px rgba(255,220,80,0.25));
        transition:filter .3s ease, transform .3s ease;
    }
    .fo-brand:hover img {
        filter:drop-shadow(0 4px 14px rgba(255,183,3,0.7)) drop-shadow(0 0 8px rgba(255,220,80,0.4));
        transform:scale(1.04);
    }
    html[data-theme="dark"] .fo-brand img {
        filter:drop-shadow(0 2px 10px rgba(20,184,166,0.35)) drop-shadow(0 0 4px rgba(20,184,166,0.15));
    }
    html[data-theme="dark"] .fo-brand:hover img {
        filter:drop-shadow(0 4px 14px rgba(20,184,166,0.55)) drop-shadow(0 0 8px rgba(20,184,166,0.3));
    }
    html[data-theme="dark"] .fo-brand { color:#e2e8f0; }
    .fo-nav { display:flex; align-items:center; gap:6px; flex-wrap:nowrap; }
    .fo-link, .fo-cta, .fo-user {
        text-decoration:none; border-radius:999px; padding:8px 13px;
        font-weight:700; font-size:0.85rem;
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
    @media(max-width:900px){ .fo-topbar-shell{flex-wrap:wrap;padding:10px 0;min-height:auto;} .fo-nav{width:100%;flex-wrap:wrap;} }
    @media(max-width:768px){ .fo-greeting{display:none;} }
    html[data-cb="protanopia"]    { filter:url(#cb-protanopia); }
    html[data-cb="deuteranopia"]  { filter:url(#cb-deuteranopia); }
    html[data-cb="tritanopia"]    { filter:url(#cb-tritanopia); }
    html[data-cb="achromatopsia"] { filter:url(#cb-achromatopsia); }
</style>
<svg width="0" height="0" style="position:absolute;overflow:hidden" aria-hidden="true">
    <defs>
        <filter id="cb-protanopia" color-interpolation-filters="sRGB">
            <feColorMatrix type="matrix" values="0.56667 0.43333 0 0 0  0.55833 0.44167 0 0 0  0 0.24167 0.75833 0 0  0 0 0 1 0"/>
        </filter>
        <filter id="cb-deuteranopia" color-interpolation-filters="sRGB">
            <feColorMatrix type="matrix" values="0.625 0.375 0 0 0  0.7 0.3 0 0 0  0 0.3 0.7 0 0  0 0 0 1 0"/>
        </filter>
        <filter id="cb-tritanopia" color-interpolation-filters="sRGB">
            <feColorMatrix type="matrix" values="0.95 0.05 0 0 0  0 0.43333 0.56667 0 0  0 0.475 0.525 0 0  0 0 0 1 0"/>
        </filter>
        <filter id="cb-achromatopsia" color-interpolation-filters="sRGB">
            <feColorMatrix type="matrix" values="0.299 0.587 0.114 0 0  0.299 0.587 0.114 0 0  0.299 0.587 0.114 0 0  0 0 0 1 0"/>
        </filter>
    </defs>
</svg>
<div class="fo-topbar">
    <div class="fo-topbar-shell">
        <div class="fo-left">
            <div class="fo-brand-cluster">
                <a class="fo-brand" href="<?php echo $_frontBase; ?>accueil.php">
                    <img src="<?php echo $_assetsBase; ?>/images/logo_barchathon.jpg" alt="BarchaThon">
                    BarchaThon
                </a>
                <button id="theme-toggle" type="button" aria-label="Basculer mode sombre/clair" title="Basculer mode sombre/clair"></button>
            </div>
            <?php if ($role !== 'visiteur'): ?>
            <div id="dyn-greeting" class="fo-greeting"></div>
            <script>
            (function(){
                var el = document.getElementById('dyn-greeting');
                if (!el) return;
                var SUN  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
                var SET  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="9" x2="12" y2="2"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/></svg>';
                var MOON = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
                var h = new Date().getHours();
                var greet, icon;
                if      (h >= 5  && h < 12) { greet = 'Bonjour';        icon = SUN;  }
                else if (h >= 12 && h < 17) { greet = 'Bon après-midi'; icon = SUN;  }
                else if (h >= 17 && h < 21) { greet = 'Bonsoir';        icon = SET;  }
                else                        { greet = 'Bonne nuit';      icon = MOON; }
                var name = <?php echo json_encode($user['nom'] ?? ''); ?>;
                el.innerHTML = icon + greet + ', <strong>' + name + '</strong>';
            })();
            </script>
            <?php endif; ?>
        </div>
        <nav class="fo-nav">
            <a class="fo-link <?php echo $currentPage==='accueil'?'active':''; ?>" href="<?php echo $_frontBase; ?>accueil.php">Accueil</a>
            <a class="fo-link <?php echo $currentPage==='catalogue'?'active':''; ?>" href="<?php echo $_frontBase; ?>listMarathons.php">Catalogue</a>


            <?php if ($role === 'visiteur'): ?>
                <a class="fo-link" href="<?php echo $_frontBase; ?>register.php">S'inscrire</a>
                <a class="fo-cta" href="<?php echo $_frontBase; ?>login.php">Se connecter</a>

            <?php elseif ($role === 'participant'): ?>
                <a class="fo-link" href="<?php echo $_frontBase; ?>Mes commandes.php">Voir mes commandes</a>
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
<script src="<?php echo $_assetsBase; ?>/js/theme.js"></script>
<script>
(function(){
    function setRole(){ if(document.body) document.body.dataset.userRole = <?php echo json_encode($role); ?>; }
    if(document.body) setRole(); else document.addEventListener('DOMContentLoaded', setRole);
})();
</script>
<script src="<?php echo $_assetsBase; ?>/js/voice-nav.js" defer></script>
