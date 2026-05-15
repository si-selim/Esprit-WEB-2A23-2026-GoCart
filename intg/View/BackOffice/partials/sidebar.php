<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../FrontOffice/lang.php';
$_sidebarActive = $activeTab ?? 'marathons';
$_user = isset($user) ? $user : (function_exists('getCurrentUser') ? (getCurrentUser() ?? []) : []);
?>
<style>
    @keyframes sidebarIn { from { opacity:0; transform:translateX(-24px); } to { opacity:1; transform:translateX(0); } }
    @keyframes pageIn    { from { opacity:0; transform:translateY(20px); }   to { opacity:1; transform:translateY(0); } }
    @keyframes fadeUp    { from { opacity:0; transform:translateY(14px); }    to { opacity:1; transform:translateY(0); } }
    @keyframes popIn     { from { opacity:0; transform:scale(.92); }          to { opacity:1; transform:scale(1); } }
    .sidebar { animation: sidebarIn .55s cubic-bezier(.16,1,.3,1) both; }
    main, .dashboard-content, .content, .container, .table-container, .card, .panel, .stat-card {
        animation: pageIn .6s cubic-bezier(.16,1,.3,1) both;
    }
    .stats-grid > *, .grid > *, .cards-grid > *, .row > .col, .stat-card { animation: fadeUp .55s cubic-bezier(.16,1,.3,1) both; }
    .stats-grid > *:nth-child(1), .grid > *:nth-child(1), .cards-grid > *:nth-child(1) { animation-delay:.05s; }
    .stats-grid > *:nth-child(2), .grid > *:nth-child(2), .cards-grid > *:nth-child(2) { animation-delay:.10s; }
    .stats-grid > *:nth-child(3), .grid > *:nth-child(3), .cards-grid > *:nth-child(3) { animation-delay:.15s; }
    .stats-grid > *:nth-child(4), .grid > *:nth-child(4), .cards-grid > *:nth-child(4) { animation-delay:.20s; }
    .stats-grid > *:nth-child(5), .grid > *:nth-child(5), .cards-grid > *:nth-child(5) { animation-delay:.25s; }
    table tbody tr { animation: fadeUp .45s cubic-bezier(.16,1,.3,1) both; }
    table tbody tr:nth-child(1) { animation-delay:.04s; }
    table tbody tr:nth-child(2) { animation-delay:.08s; }
    table tbody tr:nth-child(3) { animation-delay:.12s; }
    table tbody tr:nth-child(4) { animation-delay:.16s; }
    table tbody tr:nth-child(5) { animation-delay:.20s; }
    table tbody tr:nth-child(6) { animation-delay:.24s; }
    table tbody tr:nth-child(7) { animation-delay:.28s; }
    table tbody tr:nth-child(8) { animation-delay:.32s; }
    .modal, .modal-overlay, .modal-box { animation: popIn .35s cubic-bezier(.16,1,.3,1) both; }
    button, .btn, .side-link, .menu a, a.button, .action-btn {
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease, color .2s ease;
    }
    button:not(:disabled):hover, .btn:hover, .action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(15,118,110,.18); }
    @media (prefers-reduced-motion: reduce) {
        .sidebar, main, .dashboard-content, .content, .container, .table-container, .card, .panel, .stat-card,
        .stats-grid > *, .grid > *, .cards-grid > *, .row > .col, table tbody tr,
        .modal, .modal-overlay, .modal-box {
            animation: none !important;
        }
        button, .btn, .side-link, .menu a, a.button, .action-btn { transition: none !important; }
    }
</style>
<aside class="sidebar">
    <div class="brand">
        <img class="brand-badge" src="../assets/images/logo_barchathon.jpg" alt="BarchaThon">
        <div>
            <strong>BarchaThon</strong><br>
            <small><?php echo t('bo_admin'); ?> &mdash; <?php echo htmlspecialchars($_user['nom'] ?? ''); ?></small>
        </div>
    </div>
    <nav class="side-nav">
        <a class="side-link <?php echo $_sidebarActive==='home'?'active':''; ?>" href="dashboard.php?tab=home"><?php echo t('bo_dashboard'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='utilisateurs'?'active':''; ?>" href="dashboard.php?tab=utilisateurs"><?php echo t('bo_users'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='marathons'?'active':''; ?>" href="dashboard.php?tab=marathons"><?php echo t('bo_marathons'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='stands'?'active':''; ?>" href="dashboard.php?tab=stands"><?php echo t('bo_stands'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='produits'?'active':''; ?>" href="dashboard.php?tab=produits"><?php echo t('bo_produits'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='commandes'?'active':''; ?>" href="dashboard.php?tab=commandes"><?php echo t('bo_commandes'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='sponsors'?'active':''; ?>" href="dashboard.php?tab=sponsors"><?php echo t('bo_sponsors'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='objectifs'?'active':''; ?>" href="objectifs.php">Objectifs & Récompenses</a>
        <a class="side-link <?php echo $_sidebarActive==='inscriptions'?'active':''; ?>" href="dashboard.php?tab=inscriptions"><?php echo t('bo_inscriptions'); ?></a>
        <a class="side-link <?php echo $_sidebarActive==='inscriptions_list'?'active':''; ?>" href="dashboard.php?tab=inscriptions_list"><?php echo t('bo_inscriptions_list'); ?></a>
        <a class="side-link" href="../FrontOffice/accueil.php"><?php echo t('bo_back'); ?></a>
        <a class="side-link" href="../FrontOffice/logout.php"><?php echo t('bo_logout'); ?></a>
    </nav>
</aside>
