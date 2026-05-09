<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../FrontOffice/lang.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

require_once __DIR__ . '/../../Controller/UserController.php';
require_once __DIR__ . '/../../Controller/CommandeController.php';
require_once __DIR__ . '/../../Controller/LigneCommandeController.php';
require_once __DIR__ . '/../../Controller/StandController.php';
require_once __DIR__ . '/../../Controller/ProduitController.php';
include '../../controller/sponsorController.php'; 
include '../../controller/sponsoringController.php'; 

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['csrf_token'];
function dashCsrfOk() {
    return isset($_POST['csrf']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf']);
}

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();
$uCtrl = new UserController();
$cCtrl = new CommandeController();
$lCtrl = new LigneCommandeController();
$sCtrl = new StandController();
$prodCtrl = new ProduitController();
$controller = new sponsorController(); 
$sController = new sponsoringController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['del_u']) || isset($_POST['ban_u']) || isset($_POST['unban_u']))) {
    if (!dashCsrfOk()) { http_response_code(403); exit('CSRF token invalide.'); }
    if (isset($_POST['del_u']))   { $uCtrl->supprimerUser((int)$_POST['del_u']);     header('Location: dashboard.php?tab=utilisateurs'); exit; }
    if (isset($_POST['ban_u']))   { $uCtrl->bloquerUser((int)$_POST['ban_u']);       header('Location: dashboard.php?tab=utilisateurs'); exit; }
    if (isset($_POST['unban_u'])) { $uCtrl->debloquerUser((int)$_POST['unban_u']);   header('Location: dashboard.php?tab=utilisateurs'); exit; }
}
if (isset($_GET['del_m'])) { $mCtrl->supprimerMarathon((int)$_GET['del_m']); header('Location: dashboard.php?tab=marathons'); exit; }
if (isset($_GET['del_p'])) { $pCtrl->supprimerParcours((int)$_GET['del_p']); header('Location: dashboard.php?tab=parcours'); exit; }
if (isset($_GET['del_s'])) { $sCtrl->deleteStand((int)$_GET['del_s']); header('Location: dashboard.php?tab=stands'); exit; }
if (isset($_GET['del_prod'])) { $prodCtrl->deleteProduit((int)$_GET['del_prod']); header('Location: dashboard.php?tab=produits'); exit; }

if (isset($_GET['delete_inscription'])) {
    require_once __DIR__ . '/../../Controller/InscriptionController.php';
    $iCtrlDel = new InscriptionController();
    $iCtrlDel->delete((int) $_GET['delete_inscription']);
    header('Location: dashboard.php?tab=inscriptions_list');
    exit;
}

if (isset($_GET['delete_commande'])) {
    $cCtrl->deleteCommande((int) $_GET['delete_commande']);
    header('Location: dashboard.php?tab=commandes');
    exit;
}

if (isset($_GET['delete_commandes'])) {
    $ids = explode(',', $_GET['delete_commandes']);
    foreach ($ids as $id) {
        $cCtrl->deleteCommande((int) $id);
    }
    header('Location: dashboard.php?tab=commandes');
    exit;
}

if (isset($_GET['delete_ligne'])) {
    $lCtrl->deleteLigneCommande((int) $_GET['delete_ligne']);
    $redirectParams = [];
    if (!empty($_GET['view_commande'])) {
        $redirectParams[] = 'view_commande=' . urlencode($_GET['view_commande']);
    }
    if (!empty($_GET['search_lignes'])) {
        $redirectParams[] = 'search_lignes=' . urlencode($_GET['search_lignes']);
    }
    if (!empty($_GET['sort_lignes'])) {
        $redirectParams[] = 'sort_lignes=' . urlencode($_GET['sort_lignes']);
    }
    if (!empty($_GET['dir_lignes'])) {
        $redirectParams[] = 'dir_lignes=' . urlencode($_GET['dir_lignes']);
    }
    header('Location: dashboard.php?tab=commandes' . (!empty($redirectParams) ? '&' . implode('&', $redirectParams) : ''));
    exit;
}

if (isset($_GET['delete_lignes'])) {
    $ids = explode(',', $_GET['delete_lignes']);
    foreach ($ids as $id) {
        $lCtrl->deleteLigneCommande((int) $id);
    }
    header('Location: dashboard.php?tab=commandes');
    exit;
}

$activeTab = $_GET['tab'] ?? 'home';
$marathons  = $mCtrl->afficherMarathon();
$parcours   = $pCtrl->afficherParcours();
$statsM     = $mCtrl->statsNbMarathonsDispo();
$statsP     = $pCtrl->statsParcours();
$regions    = $mCtrl->getRegions();

$searchM = $_GET['searchM'] ?? '';
$filterRegion = $_GET['region'] ?? '';
if ($searchM !== '') $marathons = $mCtrl->rechercherMarathon($searchM);
elseif ($filterRegion !== '') $marathons = $mCtrl->filtrerMarathon($filterRegion);

$searchP = $_GET['searchP'] ?? '';
$filterDiff = $_GET['difficulte'] ?? '';
if ($searchP !== '') $parcours = $pCtrl->rechercherParcoursParNom($searchP);
elseif ($filterDiff !== '') $parcours = $pCtrl->filtrerParcours($filterDiff);

$searchU = trim($_GET['searchU'] ?? '');
$filterRole = trim($_GET['filterRole'] ?? '');
$filterPays = trim($_GET['filterPays'] ?? '');
$sortBy = $_GET['sort'] ?? '';
$sortDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';
$totalUsers = $uCtrl->countUsers($searchU, $filterRole, $filterPays);
$dbUsers = $uCtrl->rechercherUsers($searchU, $filterRole, $filterPays, $sortBy, $sortDir);
$uPaysList = $uCtrl->getPays();

$dashStats = $uCtrl->statsUsers();
$dashTotalUsers = $dashStats['total'];
$dashParticipants = $dashStats['participants'];
$dashOrganisateurs = $dashStats['organisateurs'];
$dashAdmins = $dashStats['admins'];
$dashWithEmail = $dashStats['withEmail'];
$dashWithPhone = $dashStats['withPhone'];
$dashWithoutOccupation = $dashStats['withoutOccupation'];
$dashCompletionRate = $dashStats['completionRate'];
$dashAvgAge = $dashStats['avgAge'];
$dashTopCountry = $dashStats['topCountry'];
$dashTopOccupation = $dashStats['topOccupation'];
$dashPBar = $dashStats['pBar'];
$dashOBar = $dashStats['oBar'];
$dashABar = $dashStats['aBar'];

$searchC = trim($_GET['searchC'] ?? '');
$sortC = $_GET['sortC'] ?? 'datecommande';
$dirC = $_GET['dirC'] ?? 'DESC';
$viewCommande = $_GET['view_commande'] ?? null;
$searchL = trim($_GET['searchL'] ?? '');
$sortL = $_GET['sortL'] ?? 'idligne';
$dirL = $_GET['dirL'] ?? 'ASC';

$listCommandes = $cCtrl->listCommandesFiltered($searchC, $sortC, $dirC);
$lignes = $lCtrl->listLignesCommandeFiltered($viewCommande, $searchL, $sortL, $dirL);

$user = getCurrentUser();
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo t('bo_dashboard'); ?> - BarchaThon</title>
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .tag-no { background: rgba(239,68,68,.08); color: var(--coral); border-color: rgba(239,68,68,.15); }
        .tag-easy { background: rgba(16,185,129,.08); color: #059669; border-color: rgba(16,185,129,.15); }
        .tag-med { background: rgba(245,158,11,.08); color: #d97706; border-color: rgba(245,158,11,.15); }
        .tag-hard { background: rgba(239,68,68,.08); color: var(--coral); border-color: rgba(239,68,68,.15); }
        .marathon-img { width: 54px; height: 38px; object-fit: cover; border-radius: 10px; }
        .table-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn-sm { padding: 8px 14px !important; font-size: .85rem !important; }
        .btn-pdf { background: var(--nav); color: #fff; box-shadow: 0 4px 16px rgba(15,23,42,.2); }
        .btn-pdf:hover { box-shadow: 0 10px 28px rgba(15,23,42,.3); }
        .btn-affiche { background: #1565c0; color: #fff; border: none; box-shadow: 0 4px 14px rgba(21,101,192,.35); transition: all .2s; }
        .btn-affiche:hover { background: #0d47a1; box-shadow: 0 8px 22px rgba(13,71,161,.5); transform: translateY(-1px); }

        .table-shell {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
        }
        .table-shell table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-shell thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }
        .table-shell thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            color: #374151;
        }
        .table-shell tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-shell tbody tr:hover {
            background: #f8fafc;
        }
        html[data-theme="dark"] .table-shell {
            border-color: rgba(255,255,255,0.1);
            background: #1e293b;
        }
        html[data-theme="dark"] .table-shell thead th {
            background: #162032;
            border-bottom-color: rgba(255,255,255,0.1);
            color: #e2e8f0;
        }
        html[data-theme="dark"] .table-shell tbody td {
            border-bottom-color: rgba(255,255,255,0.05);
            color: #e2e8f0;
        }
        html[data-theme="dark"] .table-shell tbody tr:hover {
            background: #162032;
        }
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(148px,1fr));
            gap:14px; margin-bottom:20px;
        }
        .stat-card {
            background:white; border-radius:18px; padding: 10px 12px; ;
            box-shadow:0 8px 22px rgba(16,42,67,.07);
            border:1px solid rgba(16,42,67,.06); text-align:center;
        }
        .stat-val { font-size:1.90rem; font-weight:900; color:var(--teal); }
        .stat-lbl { color:#627d98; font-size:0.82rem; margin-top:4px; }
        .panel {
            background:white; border-radius:20px; padding:20px;
            box-shadow:0 8px 26px rgba(16,42,67,.07);
            border:1px solid rgba(16,42,67,.07);
            margin-bottom:14px;
        }
        .panel-header {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:16px; flex-wrap:wrap; gap:10px;
        }
        .panel-header h2 { font-size:1.2rem; font-weight:800; }
         /* FILTRE */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; }
        .filter-bar input,
        .filter-bar select {
            border-radius:11px; border:1px solid #cbd5e1;
            padding:9px 13px; font:inherit;
            flex:1; min-width:160px; font-size:0.88rem; background:white;
        }
        .filter-bar input:focus,
        .filter-bar select:focus { outline:none; border-color:var(--teal); }
        span.user-thumb {
            background: linear-gradient(135deg, var(--teal), var(--sun));
            color: #fff; font-weight: 900; font-size: .8rem; border-radius: 50%;

        }
        html[data-theme="dark"] .stat-card { background:#1e293b; border-color:rgba(255,255,255,0.07); }
        html[data-theme="dark"] .stat-val { color:#14b8a6; }
        html[data-theme="dark"] .stat-lbl { color:#94a3b8; }
        html[data-theme="dark"] .panel { background:#1e293b; border-color:rgba(255,255,255,0.07); }
        html[data-theme="dark"] .panel-header h2 { color:#e2e8f0; }
        html[data-theme="dark"] .filter-bar input,
        html[data-theme="dark"] .filter-bar select { background:#162032; color:#e2e8f0; border-color:rgba(255,255,255,0.1); }
        html[data-theme="dark"] .filter-bar input:focus,
        html[data-theme="dark"] .filter-bar select:focus { background:#1e293b; }




        /*sponsor*/


        :root {
            --ink:#102a43;
            --teal:#0f766e;
            --sun:#ffb703;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --coral:#e76f51;
            --line:#d9e2ec;
            --nav:#0b2032;
        }
        * { box-sizing:border-box; }
        html, body { margin:0; min-height:100%; }
        body {
            font-family: "Segoe UI", sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #F0F4F8 0%, var(--bg) 100%);
        }
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .sidebar {
            background: linear-gradient(180deg, #0b2032 0%, #12314a 100%);
            color: #fff;
            padding: 28px 22px;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .brand { display:grid; gap:10px; padding-bottom:20px; border-bottom:1px solid rgba(255,255,255,.14); }
        .brand-badge { width:52px; height:52px; border-radius:18px; object-fit:cover; }
        .brand small, .side-note { color:rgba(255,255,255,.72); }
        .side-nav { display:grid; gap:10px; }
        .side-link {
            text-decoration:none;
            color:#fff;
            border:1px solid rgba(255,255,255,.1);
            background:rgba(255,255,255,.05);
            border-radius:16px;
            padding:12px 14px;
            font-weight:700;
        }
        .side-link.active { background:linear-gradient(135deg,var(--teal),#14b8a6); }
        .content { padding:28px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
        .head h1 { margin:0; font-size:2.2rem; }
        .muted { color:var(--muted); line-height:1.65; max-width:760px; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; }
        .btn { text-decoration:none; padding:11px 16px; border-radius:14px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .btn-primary { background:var(--teal); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.1); }
        .btn-export { background: #102a43; color:#fff; }
        .grid { display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); margin-bottom:24px; }
        .card, .section-card { background:var(--card); border-radius:24px; padding:22px; box-shadow:0 14px 34px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .section-title { margin:0 0 14px; font-size:1.45rem; }
        .table-shell { overflow:auto; }
        table { width:100%; min-width:880px; border-collapse:collapse; background:#fff; }
        th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #e6edf3; vertical-align:top; }
        th { background:#102a43; color:#fff; position:sticky; top:0; }
        .tag { display:inline-block; padding:6px 10px; border-radius:999px; background:rgba(15,118,110,.12); color:var(--teal); font-weight:800; font-size:.86rem; }
        .toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; gap:16px; margin-bottom:18px; align-items:center; }
        .toolbar-row { display:flex; flex-wrap:wrap; gap:12px; align-items:center; }
        .search-box { flex:1; min-width:240px; display:flex; gap:10px; }
        .search-box input, .filter-group select { width:100%; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .filter-group { display:flex; gap:12px; flex-wrap:wrap; }
        .filter-group label { display:flex; flex-direction:column; gap:6px; font-size:.92rem; color:var(--muted); }
        .section-actions { margin-top:16px; display:flex; justify-content:flex-end; }
        .section-note { font-size:.95rem; color:var(--muted); margin-top:6px; }
        .mobile-nav { display:none; }
        .icon-btn { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; border:1px solid rgba(16,42,67,.12); background:#fff; color:var(--ink); cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; }
        .icon-btn:hover { transform:translateY(-1px); box-shadow:0 10px 18px rgba(16,42,67,.12); }
        .icon-delete { color:#d92d20; border-color:rgba(217,45,32,.15); }
        .icon-delete::before { content:"🗑"; font-size:2rem; }
        .row-actions { display:flex; gap:10px; }
        .icon-edit { color:#d97706; border-color:rgba(217,119,6,.18); }
        .icon-edit::before { content:"✏"; font-size:1rem; }
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.35); display:none; align-items:center; justify-content:center; padding:24px; z-index:9999; }
        .modal-overlay.active { display:flex; }
        .modal { width:min(500px,100%); background:#fff; border-radius:24px; padding:28px; box-shadow:0 24px 50px rgba(16,42,67,.2); }
        .modal h3 { margin:0 0 14px; font-size:1.4rem; color:var(--ink); }
        .modal p { margin:0 0 22px; color:var(--muted); line-height:1.6; }
        .modal-actions { display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap; }
        .modal-actions .btn { min-width:120px; }
        .modal-actions .btn-secondary { background:#f0f4f8; color:var(--ink); }
        .modal-actions .btn-danger { background:var(--coral); color:#fff; }


        /* 🔥 CONTENEUR GLOBAL */
.chat-box {
    width: 650px;
    height: 500px;
    max-width: 90%;
    margin: 20px auto;
    border: 1px solid #ccc;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    font-family: Arial, sans-serif;
    background: white;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* 📜 ZONE DES MESSAGES */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f7f7f8;
    display: flex;
    flex-direction: column;
}

/* 💬 BULLES */
.message {
    margin: 10px 0;
    padding: 12px 14px;
    border-radius: 12px;
    max-width: 75%;
    font-size: 1.2em;
    line-height: 1.4;
    word-wrap: break-word;
}

/* 👤 UTILISATEUR */
.user {
    background: #007bff;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

/* 🤖 IA */
.bot {
    background: #e5e5ea;
    color: black;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}

/* ⌨️ ZONE INPUT */
.chat-input-area {
    display: flex;
    border-top: 1px solid #ccc;
    background: #fff;
}

/* 📝 INPUT */
.chat-input-area input {
    flex: 1;
    padding: 12px;
    border: none;
    outline: none;
    font-size: 1.1em;
    border-bottom-left-radius: 12px;
}

/* 🔘 BOUTON */
.chat-input-area button {
    padding: 12px 18px;
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1.1em;
    border-bottom-right-radius: 12px;
    transition: background 0.2s;
}

.chat-input-area button:hover {
    background: #0056b3;
}

/* 📱 RESPONSIVE (mobile) */
@media (max-width: 600px) {
    .chat-box {
        width: 95%;
        height: 450px;
    }

    .message {
        font-size: 1.1em;
    }
}


:root {
            --ink:#102a43;
            --teal:#0f766e;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --line:#d9e2ec;
        }
        * { box-sizing:border-box; }
        html, body { margin:0; min-height:100%; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#F0F4F8 0%, var(--bg) 100%); }
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .brand { display:grid; gap:10px; padding-bottom:20px; border-bottom:1px solid rgba(255,255,255,.14); }
        .brand-badge { width:52px; height:52px; border-radius:18px; object-fit:cover; }
        .brand small, .side-note { color:rgba(255,255,255,.72); }
        .side-nav { display:grid; gap:10px; }
        .side-link { text-decoration:none; color:#fff; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.05); border-radius:16px; padding:12px 14px; font-weight:700; }
        .side-link.active { background:linear-gradient(135deg,var(--teal),#14b8a6); }
        .content { padding:28px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
        .head h1 { margin:0; font-size:2.2rem; }
        .muted { color:var(--muted); line-height:1.65; max-width:760px; }
        .section-card { background:var(--card); border-radius:24px; padding:28px; box-shadow:0 14px 34px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .section-card h1 { margin:0 0 12px; font-size:2rem; }
        .section-card p { margin:0 0 18px; color:var(--muted); line-height:1.6; }
        .chart-card { background:#f8fafb; border-radius:24px; padding:22px; }
        .chart-card canvas { width:100% !important; max-height:420px; }
        .footer-actions { margin-top:22px; display:flex; justify-content:flex-end; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:14px; text-decoration:none; font-weight:700; border:0; cursor:pointer; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        @media (max-width: 980px) { .layout { grid-template-columns:1fr; } .sidebar { position:relative; height:auto; } }
    
    :root {
            --ink:#102a43;
            --teal:#0f766e;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --line:#d9e2ec;
        }
        * { box-sizing:border-box; }
        html, body { margin:0; min-height:100%; }
        
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#F0F4F8 0%, var(--bg) 100%); }
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .sidebar {
            background: linear-gradient(180deg, #0b2032 0%, #12314a 100%);
            color: #fff;
            padding: 28px 22px;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }.brand { display:grid; gap:10px; padding-bottom:20px; border-bottom:1px solid rgba(255,255,255,.14); }
        .brand-badge { width:52px; height:52px; border-radius:18px; object-fit:cover; }
        .brand small, .side-note { color:rgba(255,255,255,.72); }
        .side-nav { display:grid; gap:10px; }
        .side-link { text-decoration:none; color:#fff; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.05); border-radius:16px; padding:12px 14px; font-weight:700; }
        .side-link.active { background:linear-gradient(135deg,var(--teal),#14b8a6); }
        .content { padding:28px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
        .head h1 { margin:0; font-size:2.2rem; }
        .muted { color:var(--muted); line-height:1.65; max-width:760px; }
        .section-card { background:var(--card); border-radius:24px; padding:28px; box-shadow:0 14px 34px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .section-card h1 { margin:0 0 12px; font-size:2rem; }
        .section-card p { margin:0 0 18px; color:var(--muted); line-height:1.6; }
        .chart-card { background:#f8fafb; border-radius:24px; padding:22px; }
        .chart-card canvas { width:100% !important; max-height:420px; }
        .footer-actions { margin-top:22px; display:flex; justify-content:flex-end; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:14px; text-decoration:none; font-weight:700; border:0; cursor:pointer; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        @media (max-width: 980px) { .layout { grid-template-columns:1fr; } .sidebar { position:relative; height:auto; } }
    
/*sponsor*/
    </style>
</head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<body>
<div class="layout">

<?php require __DIR__ . '/partials/sidebar.php'; ?>

<main class="content">

<?php if ($activeTab === 'home'): ?>

    <div class="mobile-nav">
        <a class="btn btn-primary" href="dashboard.php?tab=home">Dashboard</a>
        <a class="btn btn-secondary" href="dashboard.php?tab=utilisateurs">Utilisateurs</a>
        <a class="btn btn-secondary" href="dashboard.php?tab=commandes">Commandes</a>
    </div>
    <style>
        .dash-title-row { display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin-bottom:4px; }
        .dash-title-row h1 { margin:0; }
        .dash-greet-pill {
            display:inline-flex; align-items:center; gap:8px;
            padding:7px 16px; border-radius:999px; font-size:.88rem; font-weight:700;
            background:linear-gradient(135deg,rgba(15,118,110,.1),rgba(20,184,166,.08));
            color:#0f766e; border:1px solid rgba(15,118,110,.18);
            animation:greetIn .6s cubic-bezier(.22,1,.36,1) both;
        }
        .dash-greet-pill svg { flex-shrink:0; animation:greetSpin 3s ease-in-out infinite; }
        .dash-time-badge {
            margin-left:6px; padding:3px 9px; border-radius:999px; font-size:.78rem; font-weight:700;
            background:rgba(15,118,110,.12); color:#0f766e;
            animation:greetIn .8s .15s cubic-bezier(.22,1,.36,1) both;
        }
        html[data-theme="dark"] .dash-greet-pill { color:#5eead4; background:rgba(20,184,166,.08); border-color:rgba(20,184,166,.2); }
        html[data-theme="dark"] .dash-time-badge { background:rgba(20,184,166,.12); color:#5eead4; }
        @keyframes greetIn { from{opacity:0;transform:translateX(-12px) scale(.94)} to{opacity:1;transform:translateX(0) scale(1)} }
        @keyframes greetSpin { 0%,100%{transform:rotate(-8deg)} 50%{transform:rotate(8deg)} }
    </style>
    <div class="head">
        <div>
            <div class="dash-title-row">
                <h1><?php echo t('bo_dashboard'); ?></h1>
                <div id="dash-greet-pill" class="dash-greet-pill" style="opacity:0"></div>
            </div>
            <div class="muted">Visualisation des donnees utilisateurs avec graphiques et indicateurs cles pour la prise de decision.</div>
        </div>
        <div class="actions">
            <span class="tag">Statistiques</span>
            <span class="tag">Rapports</span>
        </div>
    </div>
    <script>
    (function(){
        var el = document.getElementById('dash-greet-pill');
        if (!el) return;
        var SUN  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
        var SET  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="9" x2="12" y2="2"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/></svg>';
        var MOON = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
        var now = new Date(), h = now.getHours();
        var greet, icon;
        if      (h >= 5  && h < 12) { greet = 'Bonjour';        icon = SUN; }
        else if (h >= 12 && h < 17) { greet = 'Bon après-midi'; icon = SUN; }
        else if (h >= 17 && h < 21) { greet = 'Bonsoir';        icon = SET; }
        else                        { greet = 'Bonne nuit';      icon = MOON; }
        var name = <?php echo json_encode($user['nom'] ?? 'Admin'); ?>;
        var mins = String(now.getMinutes()).padStart(2,'0');
        var timeStr = h + ':' + mins;
        el.innerHTML = icon + greet + ', <strong>' + name + '</strong><span class="dash-time-badge">' + timeStr + '</span>';
        el.style.opacity = '1';
        setInterval(function(){
            var n = new Date();
            var badge = el.querySelector('.dash-time-badge');
            if (badge) badge.textContent = n.getHours() + ':' + String(n.getMinutes()).padStart(2,'0');
        }, 30000);
    })();
    </script>
    <section class="stats-grid">
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashTotalUsers; ?></div>
            <div>Utilisateurs inscrits</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashParticipants; ?></div>
            <div>Participants actifs</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashOrganisateurs; ?></div>
            <div>Organisateurs</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashCompletionRate; ?>%</div>
            <div>Taux de profils completes</div>
        </div>
    </section>
    <section class="chart-grid">
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Repartition des utilisateurs par role</h2>
            <div class="chart-shell">
                <div>
                    <div class="metric">Participants <strong><?php echo $dashParticipants; ?></strong></div>
                    <div class="chart-bar"><span style="width:<?php echo $dashPBar; ?>%"></span></div>
                </div>
                <div>
                    <div class="metric">Organisateurs <strong><?php echo $dashOrganisateurs; ?></strong></div>
                    <div class="chart-bar"><span style="width:<?php echo $dashOBar; ?>%"></span></div>
                </div>
                <div>
                    <div class="metric">Admins <strong><?php echo $dashAdmins; ?></strong></div>
                    <div class="chart-bar"><span style="width:<?php echo $dashABar; ?>%"></span></div>
                </div>
                <div class="legend-row">
                    <div class="report-label">Total utilisateurs :</div>
                    <strong><?php echo $dashTotalUsers; ?></strong>
                </div>
            </div>
        </div>
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Profils completes</h2>
            <div class="chart-shell">
                <div class="circle-meter"><strong><?php echo $dashCompletionRate; ?>%</strong></div>
                <div class="metric">Niveau global de remplissage des profils utilisateurs</div>
                <ul class="report-list">
                    <li><span>Avec email</span><strong><?php echo $dashWithEmail; ?></strong></li>
                    <li><span>Avec telephone</span><strong><?php echo $dashWithPhone; ?></strong></li>
                    <li><span>Sans occupation</span><strong><?php echo $dashWithoutOccupation; ?></strong></li>
                </ul>
            </div>
        </div>
    </section>
    <section class="chart-grid">
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Rapports cles</h2>
            <ul class="report-list">
                <li><span>Age moyen des participants</span><strong><?php echo $dashAvgAge; ?> ans</strong></li>
                <li><span>Pays le plus represente</span><strong><?php echo htmlspecialchars($dashTopCountry); ?></strong></li>
                <li><span>Occupation dominante</span><strong><?php echo htmlspecialchars($dashTopOccupation); ?></strong></li>
                <li><span>Total utilisateurs</span><strong><?php echo $dashTotalUsers; ?></strong></li>
            </ul>
        </div>
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Roles</h2>
            <ul class="report-list">
                <li><span>Admins</span><strong><?php echo $dashAdmins; ?></strong></li>
                <li><span>Organisateurs</span><strong><?php echo $dashOrganisateurs; ?></strong></li>
                <li><span>Participants</span><strong><?php echo $dashParticipants; ?></strong></li>
            </ul>
        </div>
    </section>

<?php elseif ($activeTab === 'utilisateurs'): ?>

    <div class="head fade-in">
        <div>
            <h1><?php echo t('bo_users'); ?></h1>
            <div class="muted">Vue administrative pour consulter et gerer les utilisateurs. <?php echo $totalUsers; ?> utilisateur(s) au total.</div>
        </div>
        <div class="actions">
            <a class="btn btn-secondary btn-sm" href="export_users.php?search=<?php echo urlencode($searchU); ?>&role=<?php echo urlencode($filterRole); ?>&pays=<?php echo urlencode($filterPays); ?>">Exporter CSV</a>
            <span class="tag"><?php echo $totalUsers; ?> utilisateurs</span>
        </div>
    </div>

    <section class="section-card fade-in">
        <h2 class="section-title">Utilisateurs</h2>
        <form method="GET" action="">
            <input type="hidden" name="tab" value="utilisateurs">
            <div class="toolbar">
                <div class="search-box">
                    <input type="search" name="searchU" placeholder="Rechercher un utilisateur, un email ou un pays" value="<?php echo htmlspecialchars($searchU); ?>">
                </div>
                <div class="filter-group">
                    <label>
                        Trier par
                        <select name="sort" onchange="this.form.submit()">
                            <option value="">Par defaut</option>
                            <option value="nom_complet" <?php echo $sortBy==='nom_complet'?'selected':''; ?>>Nom</option>
                            <option value="age" <?php echo $sortBy==='age'?'selected':''; ?>>Age</option>
                            <option value="poids" <?php echo $sortBy==='poids'?'selected':''; ?>>Poids</option>
                            <option value="taille" <?php echo $sortBy==='taille'?'selected':''; ?>>Taille</option>
                            <option value="ville" <?php echo $sortBy==='ville'?'selected':''; ?>>Ville</option>
                        </select>
                    </label>
                    <label>
                        Ordre
                        <select name="dir" onchange="this.form.submit()">
                            <option value="asc" <?php echo $sortDir==='ASC'?'selected':''; ?>>Croissant</option>
                            <option value="desc" <?php echo $sortDir==='DESC'?'selected':''; ?>>Decroissant</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par role
                        <select name="filterRole" onchange="this.form.submit()">
                            <option value="">Tout</option>
                            <option value="participant" <?php echo $filterRole==='participant'?'selected':''; ?>>Participant</option>
                            <option value="organisateur" <?php echo $filterRole==='organisateur'?'selected':''; ?>>Organisateur</option>
                            <option value="admin" <?php echo $filterRole==='admin'?'selected':''; ?>>Admin</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par pays
                        <select name="filterPays" onchange="this.form.submit()">
                            <option value="">Tout</option>
                            <?php foreach ($uPaysList as $p): ?>
                                <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $filterPays===$p?'selected':''; ?>><?php echo htmlspecialchars($p); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>
        </form>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Nom utilisateur</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Pays</th>
                        <th>Ville / zone</th>
                        <th>Telephone</th>
                        <th>Occupation</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dbUsers)): ?>
                        <tr><td colspan="11" style="text-align:center;color:var(--muted);">Aucun utilisateur trouve.</td></tr>
                    <?php else: foreach ($dbUsers as $u): ?>
                    <tr>
                        <td><?php echo $u['id_user']; ?></td>
                        <td>
                            <?php if (!empty($u['profile_picture'])): ?>
                                <img class="user-thumb" src="../FrontOffice/images/uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="">
                            <?php else: ?>
                                <span class="user-thumb"><?php echo mb_strtoupper(mb_substr($u['nom_complet'], 0, 1)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
                        <td><?php echo htmlspecialchars($u['nom_user']); ?></td>
                        <td><span class="tag"><?php echo htmlspecialchars($u['role']); ?></span><?php if (($u['status'] ?? 'active') === 'banned'): ?> <span class="tag" style="background:#fee2e2;color:#b42318;">Bloque</span><?php endif; ?><?php if ($u['role'] !== 'admin' && (int)($u['verified'] ?? 1) === 0): ?> <span class="tag" style="background:#fef9c3;color:#854d0e;">Non verifie</span><?php endif; ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['pays'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['ville'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['tel'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['occupation'] ?? '-'); ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary btn-sm" href="edit_user.php?id=<?php echo $u['id_user']; ?>">Modifier</a>
                                <?php if (($u['status'] ?? 'active') === 'banned'): ?>
                                    <form method="POST" action="dashboard.php" style="display:inline;">
                                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="unban_u" value="<?php echo $u['id_user']; ?>">
                                        <button type="submit" class="btn btn-secondary btn-sm">Debloquer</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="dashboard.php" style="display:inline;">
                                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="ban_u" value="<?php echo $u['id_user']; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">Bloquer</button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm" onclick="showConfirm('Supprimer l\'utilisateur <?php echo htmlspecialchars(addslashes($u['nom_complet'])); ?> ?', function(){ document.getElementById('del-<?php echo $u['id_user']; ?>').submit(); });">Supprimer</button>
                                <form id="del-<?php echo $u['id_user']; ?>" method="POST" action="dashboard.php" style="display:none;">
                                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="del_u" value="<?php echo $u['id_user']; ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="section-note">Total : <?php echo $totalUsers; ?> utilisateur(s).</div>
    </section>

<?php elseif ($activeTab === 'marathons'): ?>

    <div class="head fade-in">
        <div>
            <h1><?php echo t('bo_marathons'); ?></h1>
            <div class="muted">Liste complete  consultation et suppression uniquement.</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo count($mCtrl->afficherMarathon()); ?></div>
            <div class="stat-lbl">Marathons affichés</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo number_format((float)($statsM['total_places']??0)); ?></div>
            <div class="stat-lbl">Places disponibles</div>
        </div>
    </div>

    <div class="panel">
        <div class="filter-bar">
            <input type="text" id="sM" placeholder="🔍 Rechercher par nom..." value="" autocomplete="off" style="flex:2 1 220px; height:44px;">
            <select id="rM" style="flex:1 1 180px; height:44px;">
                <option value="">Toutes les régions</option>
                <?php foreach ($regions as $r): ?>
                    <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <section class="section-card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 class="section-title" style="margin:0;">Liste des Marathons</h2>
            <span class="tag" id="marathonCountBadge"><?php echo count($marathons); ?> resultats</span>
        </div>
        <div class="table-shell">
            <table id="marathonTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Nom du Marathon</th>
                        <th>Organisateur</th>
                        <th>Region</th>
                        <th>Date</th>
                        <th>Places dispo</th>
                        <th>Prix (TND)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($marathons)): ?>
                        <tr><td colspan="9" style="text-align:center;color:var(--muted);">Aucun marathon trouve.</td></tr>
                    <?php else: foreach($marathons as $m2): ?>
                    <tr>
                        <td><strong><?php echo $m2['id_marathon']; ?></strong></td>
                        <td>
                            <img class="marathon-img"
                                 src="../FrontOffice/<?php echo htmlspecialchars($m2['image_marathon']); ?>"
                                 onerror="this.onerror=null;this.src='../FrontOffice/marathon/images/hero_runner.png';" alt="">
                        </td>
                        <td><strong><?php echo htmlspecialchars($m2['nom_marathon']); ?></strong></td>
                        <td><?php echo htmlspecialchars($m2['organisateur_marathon']); ?></td>
                        <td><span class="tag">📍  <?php echo htmlspecialchars($m2['region_marathon']); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($m2['date_marathon'])); ?></td>
                        <td>
                            <?php if($m2['nb_places_dispo'] > 0): ?>
                                <span class="tag"><?php echo $m2['nb_places_dispo']; ?></span>
                            <?php else: ?>
                                <span class="tag tag-no">Complet</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo number_format($m2['prix_marathon'],2); ?></strong></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary btn-sm" href="voirdetailsparcours.php?id=<?php echo $m2['id_marathon']; ?>">Voir details</a>
                                <a class="btn btn-affiche btn-sm" href="affiche_marathon.php?id=<?php echo $m2['id_marathon']; ?>" target="_blank">Affiche</a>
                                <button class="btn btn-danger btn-sm" onclick="askDelM(<?php echo $m2['id_marathon']; ?>, '<?php echo htmlspecialchars($m2['nom_marathon'], ENT_QUOTES); ?>')">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'parcours'): ?>

    <div class="head fade-in">
        <div>
            <h1><?php echo t('parcours_title'); ?></h1>
            <div class="muted">Liste complete  -  consultation et suppression uniquement.</div>
        </div>
    </div>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-val"><?php echo (int)($statsP['total']??0); ?></div>
            <div class="stat-lbl">Total Parcours</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #059669;">
            <div class="stat-val" style="color:#059669;"><?php echo (int)($statsP['facile']??0); ?></div>
            <div class="stat-lbl">Faciles</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #d97706;">
            <div class="stat-val" style="color:#d97706;"><?php echo (int)($statsP['moyen']??0); ?></div>
            <div class="stat-lbl">Moyens</div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--coral);">
            <div class="stat-val" style="color:var(--coral);"><?php echo (int)($statsP['difficile']??0); ?></div>
            <div class="stat-lbl">Difficiles</div>
        </div>
    </div>

    <div class="panel">
        <div class="filter-bar">
            <input type="text" id="sP" placeholder="🔍 Rechercher par nom de parcours..." value="" autocomplete="off" style="flex:2 1 220px; height:44px;">
            <select id="dP" style="flex:1 1 180px; height:44px;">
                <option value="">Toutes les difficultés</option>
                <option value="facile">🟢 Facile</option>
                <option value="moyen">🟡 Moyen</option>
                <option value="difficile">🔴 Difficile</option>
            </select>
        </div>
    </div>

    <section class="section-card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 class="section-title" style="margin:0;">Liste des Parcours</h2>
            <span class="tag" id="parcoursCountBadge"><?php echo count($parcours); ?> resultats</span>
        </div>
        <div class="table-shell">
            <table id="parcoursTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Parcours</th>
                        <th>Heure Départ</th>
                        <th>Point Depart</th>
                        <th>Point Arrivee</th>
                        <th>Distance (km)</th>
                        <th>Difficulte</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($parcours)): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--muted);">Aucun parcours trouve.</td></tr>
                    <?php else: foreach($parcours as $p2):
                        $dc = ['facile'=>'tag-easy','moyen'=>'tag-med','difficile'=>'tag-hard'][$p2['difficulte']]??'';
                    ?>
                    <tr>
                        <td><strong><?php echo $p2['id_parcours']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($p2['nom_parcours']); ?></strong></td>
                        <td><?php echo !empty($p2['heure_depart']) ? '<span style="font-weight:700;">'.htmlspecialchars(substr($p2['heure_depart'],0,5)).'</span>' : '<span style="color:#94a3b8;"> - </span>'; ?></td>
                        <td><?php echo htmlspecialchars($p2['point_depart']); ?></td>
                        <td><?php echo htmlspecialchars($p2['point_arrivee']); ?></td>
                        <td><strong><?php echo number_format((float)$p2['distance'],2); ?> km</strong></td>
                        <td><span class="tag <?php echo $dc; ?>"><?php echo htmlspecialchars($p2['difficulte']); ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary btn-sm" href="../FrontOffice/detailMarathon.php?id=<?php echo $p2['id_marathon']; ?>">Voir details</a>
                                <button class="btn btn-danger btn-sm" onclick="askDelP(<?php echo $p2['id_parcours']; ?>, '<?php echo htmlspecialchars($p2['nom_parcours'], ENT_QUOTES); ?>')">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'stands'): 
    $searchS = trim($_GET['searchS'] ?? '');
    $lat = $_GET['lat'] ?? null;
    $lon = $_GET['lon'] ?? null;

    if ($lat !== null && $lon !== null) {
        $listStands = $sCtrl->getRecommendationsByCoords((float)$lat, (float)$lon);
    } else {
        $listStands = $sCtrl->listStands();
        if ($searchS !== '') {
            $filteredS = [];
            foreach ($listStands as $st) {
                if (stripos($st['nom_stand'], $searchS) !== false || stripos((string)$st['ID_stand'], $searchS) !== false || stripos($st['position'], $searchS) !== false) {
                    $filteredS[] = $st;
                }
            }
            $listStands = $filteredS;
        }
    }
?>
    <div class="head fade-in">
        <div>
            <h1><?php echo t('bo_stands'); ?></h1>
            <div class="muted">Liste complète — consultation et suppression uniquement.</div>
        </div>
        <div class="actions" style="display:flex; gap:10px;">
            <button type="button" onclick="toggleIframe('iframe-stands')" class="btn btn-secondary btn-sm"><i class="fa-solid fa-chart-pie"></i> Statistiques</button>
            <button onclick="exportTableToPDF('table-stands', 'liste_stands.pdf', 'Rapport des Stands')" class="btn btn-pdf btn-sm"><i class="fa-solid fa-file-pdf"></i> Exporter PDF</button>
        </div>
    </div>

    <div id="iframe-stands" class="fade-in" style="display:none; width:100%; margin-bottom: 24px; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
        <iframe src="../FrontOffice/Stands/stat.php" style="width:100%; height:900px; border:none; display:block;"></iframe>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo count($listStands); ?></div>
            <div class="stat-lbl">Stands affichés</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo count($sCtrl->listStands()); ?></div>
            <div class="stat-lbl">Total des Stands</div>
        </div>
    </div>

    <div class="panel">
        <form method="GET" class="filter-bar" style="align-items: center;" onsubmit="return false;">
            <input type="hidden" name="tab" value="stands">
            <input type="text" name="searchS" placeholder=" Rechercher par nom, ID ou position..." value="<?php echo htmlspecialchars($searchS); ?>" style="flex:1;" oninput="liveFilterTable('table-stands', this.value)">
            <button type="button" id="btn-autour-moi" onclick="searchByLocation()" class="btn" style="background-color: #10b981; color: white; padding: 10px 20px; font-weight: bold; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 6px rgba(16,185,129,0.2);"><i class="fa-solid fa-location-dot"></i> Autour de moi</button>
        </form>
    </div>

    <section class="section-card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 class="section-title" style="margin:0;">Liste des Stands</h2>
            <span class="tag"><?php echo count($listStands); ?> resultats</span>
        </div>
        <div class="table-shell">
            <table id="table-stands">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Stand</th>
                        <th>Parcours</th>
                        <th>Position</th>
                        <th>Description</th>
                        <th>Produits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listStands)): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);">Aucun stand trouve.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listStands as $s): ?>
                        <tr>
                            <td><strong><?= $s['ID_stand'] ?></strong></td>
                            <td><strong><?= htmlspecialchars($s['nom_stand']) ?></strong></td>
                            <td><span class="tag">Parcours <?= htmlspecialchars($s['ID_parcours']) ?></span></td>
                            <td>
                                <?php if (isset($s['distance_km'])): ?>
                                    <span class="tag" style="background:rgba(16,185,129,0.1); color:#059669; border:1px solid rgba(16,185,129,0.2); font-weight:bold;">
                                        📍 <?= htmlspecialchars($s['position']) ?> (<?= $s['distance_km'] ?> km)
                                    </span>
                                <?php else: ?>
                                    <span class="tag tag-easy">📍 <?= htmlspecialchars($s['position']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                            <td><span class="tag tag-med"><?= $prodCtrl->countProduitsByStand($s['ID_stand']) ?> produits</span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-danger btn-sm" href="dashboard.php?tab=stands&del_s=<?= $s['ID_stand'] ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce stand ?')">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'produits'): 
    $searchP = trim($_GET['searchP'] ?? '');
    $listProduits = $prodCtrl->listProduits('ID_produit', 'ASC');
    
    if ($searchP !== '') {
        $filteredP = [];
        foreach ($listProduits as $p) {
            if (stripos($p['nom_produit'], $searchP) !== false || stripos((string)$p['ID_produit'], $searchP) !== false || stripos($p['type'], $searchP) !== false) {
                $filteredP[] = $p;
            }
        }
        $listProduits = $filteredP;
    }
?>
    <div class="head fade-in">
        <div>
            <h1><?php echo t('bo_produits'); ?></h1>
            <div class="muted">Liste complète — consultation et suppression uniquement.</div>
        </div>
        <div class="actions" style="display:flex; gap:10px;">
            <button type="button" onclick="toggleIframe('iframe-produits')" class="btn btn-secondary btn-sm"><i class="fa-solid fa-chart-pie"></i> Statistiques</button>
            <button onclick="exportTableToPDF('table-produits', 'liste_produits.pdf', 'Catalogue des Produits')" class="btn btn-pdf btn-sm"><i class="fa-solid fa-file-pdf"></i> Exporter PDF</button>
        </div>
    </div>

    <div id="iframe-produits" class="fade-in" style="display:none; width:100%; margin-bottom: 24px; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
        <iframe src="../FrontOffice/Produits/statP.php" style="width:100%; height:700px; border:none; display:block;"></iframe>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo count($listProduits); ?></div>
            <div class="stat-lbl">Produits affichés</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo count($prodCtrl->listProduits('ID_produit', 'ASC')); ?></div>
            <div class="stat-lbl">Total des Produits</div>
        </div>
    </div>

    <div class="panel">
        <form method="GET" class="filter-bar" style="align-items: center;" onsubmit="return false;">
            <input type="hidden" name="tab" value="produits">
            <input type="text" name="searchP" placeholder=" Rechercher par nom, ID ou type..." value="<?php echo htmlspecialchars($searchP); ?>" style="flex:1; border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1;" oninput="liveFilterTable('table-produits', this.value)">
        </form>
    </div>

    <section class="section-card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 class="section-title" style="margin:0;">Liste des Produits</h2>
            <span class="tag"><?php echo count($listProduits); ?> resultats</span>
        </div>
        <div class="table-shell">
            <table id="table-produits">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Produit</th>
                        <th>Type</th>
                        <th>Stand</th>
                        <th>Quantité</th>
                        <th>Stock</th>
                        <th>Prix (TND)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listProduits)): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--muted);">Aucun produit trouve.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listProduits as $p): ?>
                        <tr>
                            <td><strong><?= $p['ID_produit'] ?></strong></td>
                            <td><strong><?= htmlspecialchars($p['nom_produit']) ?></strong></td>
                            <td><span class="tag tag-med"><?= htmlspecialchars($p['type']) ?></span></td>
                            <td><span class="tag">Stand <?= htmlspecialchars($p['ID_stand']) ?></span></td>
                            <td><?= htmlspecialchars($p['qte_stock']) ?></td>
                            <td>
                                <?php 
                                $stockStr = $p['en_out_stock'];
                                $isInStock = (stripos($stockStr, 'dispo') !== false || $stockStr === '1' || $stockStr === 1);
                                if ($isInStock): ?>
                                    <span class="tag tag-easy">En Stock</span>
                                <?php else: ?>
                                    <span class="tag tag-no">Rupture</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= number_format($p['prix_produit'], 2) ?></strong></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-danger btn-sm" href="dashboard.php?tab=produits&del_prod=<?= $p['ID_produit'] ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?')">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'commandes'): ?>

    <div class="head fade-in">
        <div>
            <h1><?php echo t('bo_commandes'); ?></h1>
            <div class="muted">Gestion des commandes et lignes de commandes.</div>
        </div>
        <div class="actions">
            <a class="btn btn-secondary btn-sm" href="export_commandes.php?search=<?php echo urlencode($searchC); ?>&sort=<?php echo urlencode($sortC); ?>&dir=<?php echo urlencode($dirC); ?>">Exporter PDF</a>
            <span class="tag">Commandes</span>
        </div>
    </div>

    <?php
    // Statistiques des commandes
    $statsCommandes = [
        'total' => 0,
        'montantTotal' => 0,
        'enCours' => 0,
        'confirmee' => 0,
        'nonValide' => 0,
        'topProduit' => 'N/A',
        'topMois' => 'N/A',
        'benefice' => 0
    ];
    
    require_once __DIR__ . '/../../config.php';
    
    if (isset($_GET['delete_inscription'])) {
    require_once __DIR__ . '/../../Controller/InscriptionController.php';
    $ctrl = new InscriptionController();
    $ctrl->delete((int) $_GET['delete_inscription']);
    header('Location: dashboard.php?tab=inscriptions_list');
    exit;
}

    $commandesStmt = $cCtrl->listCommandesFiltered($searchC, $sortC, $dirC);
    $commandesData = $commandesStmt->fetchAll();
    
    foreach ($commandesData as $c) {
        $statsCommandes['total']++;
        $statsCommandes['montantTotal'] += (float)$c['montanttotale'];
        if (strtolower($c['statut']) === 'en cours') $statsCommandes['enCours']++;
        elseif (strtolower($c['statut']) === 'confirmé' || strtolower($c['statut']) === 'confirmée') $statsCommandes['confirmee']++;
        elseif (strtolower($c['statut']) === 'non valide') $statsCommandes['nonValide']++;
    }
    
    // Produit le plus vendu
    $db = config::getConnexion();
    $topProduitSQL = "SELECT lc.idproduit, COUNT(lc.idproduit) as qty FROM lignecommande lc GROUP BY lc.idproduit ORDER BY qty DESC LIMIT 1";
    $topProduitResult = $db->query($topProduitSQL)->fetch();
    if ($topProduitResult) {
        $statsCommandes['topProduit'] = $topProduitResult['idproduit'];
    }
    
    // Mois le plus actif
    $topMoisSQL = "SELECT DATE_FORMAT(datecommande, '%Y-%m') as mois, COUNT(*) as cnt FROM commande GROUP BY mois ORDER BY cnt DESC LIMIT 1";
    $topMoisResult = $db->query($topMoisSQL)->fetch();
    if ($topMoisResult) {
        $statsCommandes['topMois'] = date('m/Y', strtotime($topMoisResult['mois']));
    }
    
    // Bénéfice (montant confirmé/validé)
    $beneficeSQL = "SELECT SUM(montanttotale) as benefice FROM commande WHERE LOWER(statut) IN ('confirmé', 'confirmée', 'validé', 'validée')";
    $beneficeResult = $db->query($beneficeSQL)->fetch();
    if ($beneficeResult && $beneficeResult['benefice']) {
        $statsCommandes['benefice'] = (float)$beneficeResult['benefice'];
    }
    
    $listCommandes = $commandesData;
    ?>

    <section class="stats-grid" style="margin-bottom:24px;">
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $statsCommandes['total']; ?></div>
            <div>Commandes totales</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#0f766e;"><?php echo number_format($statsCommandes['montantTotal'], 2, ',', ' '); ?> TND</div>
            <div>Montant total</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#059669;"><?php echo $statsCommandes['enCours']; ?></div>
            <div>En cours</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#0891b2;"><?php echo $statsCommandes['confirmee']; ?></div>
            <div>Confirmées</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#dc2626;"><?php echo $statsCommandes['nonValide']; ?></div>
            <div>Non validées</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#f59e0b;"><?php echo $statsCommandes['topProduit']; ?></div>
            <div>Produit #1 vendu</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#8b5cf6;"><?php echo $statsCommandes['topMois']; ?></div>
            <div>Mois plus actif</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat" style="color:#10b981;"><?php echo number_format($statsCommandes['benefice'], 2, ',', ' '); ?> TND</div>
            <div>Bénéfice</div>
        </div>
    </section>

    <section class="section-card fade-in">
        <h2 class="section-title">Toutes les commandes</h2>
        <form method="GET" action="">
            <input type="hidden" name="tab" value="commandes">
            <div class="toolbar">
                <div class="search-box">
                    <input type="search" name="searchC" placeholder="Rechercher une commande..." value="<?php echo htmlspecialchars($searchC); ?>">
                </div>
                <div class="filter-group">
                    <label>
                        Trier par
                        <select name="sortC" onchange="this.form.submit()">
                            <option value="datecommande"<?php echo $sortC === 'datecommande' ? ' selected' : ''; ?>>Date</option>
                            <option value="montanttotale"<?php echo $sortC === 'montanttotale' ? ' selected' : ''; ?>>Montant</option>
                            <option value="idcommande"<?php echo $sortC === 'idcommande' ? ' selected' : ''; ?>>ID</option>
                        </select>
                    </label>
                    <label>
                        Ordre
                        <select name="dirC" onchange="this.form.submit()">
                            <option value="DESC"<?php echo $dirC === 'DESC' ? ' selected' : ''; ?>>Décroissant</option>
                            <option value="ASC"<?php echo $dirC === 'ASC' ? ' selected' : ''; ?>>Croissant</option>
                        </select>
                    </label>
                </div>
            </div>
        </form>
        <?php if (!empty($viewCommande)): ?>
        <div style="margin-bottom:12px; padding:12px; background:#e8f5e9; border-radius:8px; font-weight:700; color:#2e7d32;">Affichage des lignes pour commande #<?php echo htmlspecialchars($viewCommande); ?></div>
        <?php endif; ?>
        <div id="deleteCommandesArea" style="margin-bottom:12px; display:none;">
            <button type="button" class="btn btn-danger" onclick="deleteSelectedCommandes()">Supprimer les sélectionnés</button>
            <span id="selectedCommandesCount" style="margin-left:10px; color:#486581;"></span>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="selectAllCommandes" onchange="toggleAllCommandes(this)"></th>
                        <th>ID</th>
                        <th>Participant</th>
                        <th>Stand</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Paiement en ligne</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listCommandes as $commande): ?>
                        <?php
                        $statut_label = '';
                        $statut_lower = strtolower($commande['statut']);
                        switch($statut_lower) {
                            case 'en attente':
                            case 'en attente de validation':
                                $statut_label = 'En attente';
                                break;
                            case 'confirmé':
                            case 'confirmée':
                                $statut_label = 'Confirmée';
                                break;
                            case 'non valide':
                                $statut_label = 'Non validée';
                                break;
                            case 'en cours':
                                $statut_label = 'En cours';
                                break;
                            case 'paye':
                                $statut_label = 'Payée';
                                break;
                            default:
                                $statut_label = ucfirst($commande['statut']);
                                break;
                        }
                        ?>
                        <tr>
                            <td><input type="checkbox" class="selectCommande" value="<?php echo $commande['idcommande']; ?>"></td>
                            <td><?php echo $commande['idcommande']; ?></td>
                            <td>Utilisateur <?php echo $commande['idutilisateur']; ?></td>
                            <td>Stand <?php echo $commande['idstand']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($commande['datecommande'])); ?></td>
                            <td><?php echo number_format($commande['montanttotale'], 2, ',', ' '); ?> TND</td>
                            <td>OUI</td>
                            <td><?php echo htmlspecialchars($statut_label); ?></td>
                            <td>
                                <a href="orderDetailsAdmin.php?tab=commandes&view_commande=<?php echo $commande['idcommande']; ?>" class="btn btn-secondary" style="margin-right:8px;">Voir details</a>
                                <a href="?tab=commandes&delete_commande=<?php echo $commande['idcommande']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette commande ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section-card fade-in">
        <h2 class="section-title">Toutes les lignes de commandes</h2>
        <form method="GET" action="">
            <input type="hidden" name="tab" value="commandes">
            <div class="toolbar">
                <div class="search-box">
                    <input type="search" name="searchL" placeholder="Rechercher une ligne..." value="<?php echo htmlspecialchars($searchL); ?>">
                </div>
                <div class="filter-group">
                    <label>
                        Trier par
                        <select name="sortL" onchange="this.form.submit()">
                            <option value="idligne"<?php echo $sortL === 'idligne' ? ' selected' : ''; ?>>ID Ligne</option>
                            <option value="idcommande"<?php echo $sortL === 'idcommande' ? ' selected' : ''; ?>>ID Commande</option>
                            <option value="quantite"<?php echo $sortL === 'quantite' ? ' selected' : ''; ?>>Quantité</option>
                            <option value="prixunitaire"<?php echo $sortL === 'prixunitaire' ? ' selected' : ''; ?>>Prix</option>
                        </select>
                    </label>
                    <label>
                        Ordre
                        <select name="dirL" onchange="this.form.submit()">
                            <option value="ASC"<?php echo $dirL === 'ASC' ? ' selected' : ''; ?>>Croissant</option>
                            <option value="DESC"<?php echo $dirL === 'DESC' ? ' selected' : ''; ?>>Décroissant</option>
                        </select>
                    </label>
                </div>
            </div>
        </form>
        <?php if (!empty($viewCommande)): ?>
        <div style="margin-bottom:12px; padding:12px; background:#e8f5e9; border-radius:8px; font-weight:700; color:#2e7d32;">Affichage des lignes pour commande #<?php echo htmlspecialchars($viewCommande); ?></div>
        <?php endif; ?>
        <div id="deleteLignesArea" style="margin-bottom:12px; display:none;">
            <button type="button" class="btn btn-danger" onclick="deleteSelectedLignes()">Supprimer les sélectionnés</button>
            <span id="selectedLignesCount" style="margin-left:10px; color:#486581;"></span>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="selectAllLignes" onchange="toggleAllLignes(this)"></th>
                        <th>ID Ligne</th>
                        <th>ID Commande</th>
                        <th>ID Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire (TND)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lignes as $ligne): ?>
                        <tr>
                            <td><input type="checkbox" class="selectLigne" value="<?php echo $ligne['idligne']; ?>"></td>
                            <td><?php echo $ligne['idligne']; ?></td>
                            <td><?php echo $ligne['idcommande']; ?></td>
                            <td><?php echo $ligne['idproduit']; ?></td>
                            <td><?php echo $ligne['quantite']; ?></td>
                            <td><?php echo number_format($ligne['prixunitaire'], 2, ',', ' '); ?> TND</td>
                            <td>
                                <a href="?tab=commandes&delete_ligne=<?php echo $ligne['idligne']; ?>&view_commande=<?php echo urlencode($viewCommande); ?>&searchL=<?php echo urlencode($searchL); ?>&sortL=<?php echo urlencode($sortL); ?>&dirL=<?php echo urlencode($dirL); ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette ligne de commande ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php elseif ($activeTab === 'inscriptions'): 
    require_once __DIR__ . '/../../Controller/InscriptionController.php';
    $iCtrl = new InscriptionController();
    $stats = $iCtrl->getStats();
    $pay   = $iCtrl->getPaidStats();
?>

<div class="head fade-in">
    <div>
        <h1>Statistiques des Inscriptions</h1>
        <div class="muted">Analyse des inscriptions et des paiements aux marathons.</div>
    </div>
    <div class="actions">
        <span class="tag">Inscriptions</span>
    </div>
</div>

<!-- KPIs -->
<section class="stats-grid">
    <div class="card card-hover slide-up">
        <div class="stat"><?php echo $stats['totalInscriptions']; ?></div>
        <div>Total Inscriptions</div>
    </div>
    <div class="card card-hover slide-up">
        <div class="stat"><?php echo $stats['totalParticipants']; ?></div>
        <div>Total Participants</div>
    </div>
    <div class="card card-hover slide-up">
        <div class="stat" style="color:#059669;"><?php echo $pay['paid']; ?></div>
        <div>Paiements reçus</div>
    </div>
    <div class="card card-hover slide-up">
        <div class="stat" style="color:var(--coral);"><?php echo $pay['unpaid']; ?></div>
        <div>En attente</div>
    </div>
</section>

<!-- Charts -->
<section class="chart-grid">
    <div class="card chart-box fade-in">
        <h2 class="section-title" style="margin-top:0;">🗺️ Répartition des parcours</h2>
        <div style="position:relative; height:260px;">
            <canvas id="chartParcours"></canvas>
        </div>
    </div>
    <div class="card chart-box fade-in">
        <h2 class="section-title" style="margin-top:0;">💳 Modes de paiement</h2>
        <div style="position:relative; height:260px;">
            <canvas id="chartPaiement"></canvas>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const parcoursData = <?php echo json_encode($stats['parcours']); ?>;
const labelsParcours = parcoursData.map(p => {
    if (p.id_parcours == 1) return "10 km";
    if (p.id_parcours == 2) return "21 km";
    return "42 km";
});
const valuesParcours = parcoursData.map(p => p.total);

new Chart(document.getElementById('chartParcours'), {
    type: 'pie',
    data: {
        labels: labelsParcours,
        datasets: [{
            data: valuesParcours,
            backgroundColor: ['#0f766e','#ffb703','#e76f51'],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

const paiementData = <?php echo json_encode($stats['paiement']); ?>;
const labelsPaiement = paiementData.map(p => p.mode_de_paiement);
const valuesPaiement = paiementData.map(p => p.total);

new Chart(document.getElementById('chartPaiement'), {
    type: 'bar',
    data: {
        labels: labelsPaiement,
        datasets: [{
            label: 'Inscriptions',
            data: valuesPaiement,
            backgroundColor: ['rgba(15,118,110,.75)','rgba(255,183,3,.75)','rgba(231,111,81,.75)'],
            borderRadius: 10, borderSkipped: false
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
<?php elseif ($activeTab === 'inscriptions_list'):
    require_once __DIR__ . '/../../Controller/InscriptionController.php';
    $iCtrl2 = new InscriptionController();
    $listeInscriptions = $iCtrl2->getAll();
?>

<div class="head fade-in">
    <div>
        <h1>Gestion des Inscriptions</h1>
        <div class="muted">Liste complète des inscriptions aux marathons.</div>
    </div>
    <div class="actions">
        <span class="tag"><?php echo count($listeInscriptions); ?> inscription(s)</span>
    </div>
</div>

<section class="section-card fade-in">
    <h2 class="section-title">Liste des Inscriptions</h2>
    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>Mode paiement</th>
                    <th>Circuit</th>
                    <th>Nb personnes</th>
                    <th>Date paiement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listeInscriptions)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);">Aucune inscription trouvée.</td></tr>
                <?php else: foreach ($listeInscriptions as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['mode_de_paiement']); ?></td>
                    <td>
                        <?php
                        if ($row['id_parcours'] == 1) echo "10 km";
                        elseif ($row['id_parcours'] == 2) echo "21 km";
                        else echo "42 km";
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['nb_personnes']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_paiement']); ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="dashboard.php?tab=voir_dossard&id_inscription=<?php echo $row['id_inscription']; ?>" 
   class="btn btn-secondary btn-sm">Voir dossard</a>
                            
<a href="dashboard.php?tab=inscriptions_list&delete_inscription=<?php echo $row['id_inscription']; ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Supprimer cette inscription ?')">Supprimer</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="section-note">Total : <?php echo count($listeInscriptions); ?> inscription(s).</div>
</section>
<?php elseif ($activeTab === 'voir_dossard'):
    require_once __DIR__ . '/../../Controller/DossardController.php';
    require_once __DIR__ . '/../../Controller/InscriptionController.php';
    $id = (int)($_GET['id_inscription'] ?? 0);
    $dossardController = new DossardController();
    $inscriptionController = new InscriptionController();
    $data = $inscriptionController->getById($id);
    $nb = $data['nb_personnes'] ?? 0;
    $liste = $dossardController->getByInscription($id);
    $total = max($nb, count($liste));
?>

<div class="head fade-in">
    <div>
        <h1>Dossards — Inscription #<?php echo $id; ?></h1>
        <div class="muted"><?php echo $total; ?> dossard(s) pour cette inscription.</div>
    </div>
    <div class="actions">
        <a class="btn btn-secondary btn-sm" href="dashboard.php?tab=inscriptions_list">← Retour</a>
        <a class="btn btn-primary btn-sm" href="../FrontOffice/export_pdf.php?id_inscription=<?php echo $id; ?>">Exporter PDF</a>
    </div>
</div>

<?php if ($nb == 0): ?>
    <div class="card fade-in" style="padding:30px;text-align:center;color:var(--coral);">
        Aucune inscription trouvée.
    </div>
<?php else: ?>

<section class="section-card fade-in">
    <h2 class="section-title">Liste des dossards</h2>

    <?php if (count($liste) === 0): ?>
        <div style="padding:20px;color:var(--coral);">Aucun dossard trouvé.</div>
    <?php endif; ?>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Nom</th>
                    <th>Numéro</th>
                    <th>Taille</th>
                    <th>Couleur</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $total; $i++): ?>
                <tr>
                    <td>
                        <?php if (isset($liste[$i])): ?>
                            <span class="tag" style="background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2);">✔ Complété</span>
                        <?php else: ?>
                            <a href="dossard.php?id_inscription=<?php echo $id; ?>" class="btn btn-warning btn-sm">Compléter</a>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($liste[$i]['nom'] ?? '—'); ?></td>
                    <td><strong><?php echo htmlspecialchars($liste[$i]['numero'] ?? ($i + 1)); ?></strong></td>
                    <td><?php echo htmlspecialchars($liste[$i]['taille'] ?? '—'); ?></td>
                    <td>
                        <?php if (isset($liste[$i]['couleur'])): ?>
                            <div style="width:22px;height:22px;border-radius:6px;margin:auto;background:<?php echo htmlspecialchars($liste[$i]['couleur']); ?>;border:1px solid rgba(0,0,0,0.1);"></div>
                            <small><?php echo htmlspecialchars($liste[$i]['couleur']); ?></small>
                        <?php else: echo '—'; endif; ?>
                    </td>
                    <td>
                        <?php if (isset($liste[$i]['qr_code'])): ?>
                            <img src="../../qr/<?php echo htmlspecialchars($liste[$i]['qr_code']); ?>" width="70" style="border-radius:8px;">
                        <?php else: ?>
                            <span class="tag" style="background:#fee2e2;color:#b42318;">Pas de QR</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
    <div class="section-note">Total : <?php echo $total; ?> dossard(s).</div>
</section>

<?php endif; ?>
<?php elseif ($activeTab === 'sponsors'): ?>
<main class="content">
            <div class="mobile-nav">
                <a class="btn btn-secondary" href="dashboard.php">Dashboard</a>
                <a class="btn btn-primary" href="backoffice_Sponsor.php">Sponsors</a>
            </div>
            <div class="head">
                <div>
                    <h1>Section sponsors</h1>
                    <div class="muted">Vue administrative simple pour consulter les tables des sponsors et des sponsoring. Recherche, filtres et export statique sont disponibles.</div>
                </div>
                <div class="actions">
                    <span class="tag">sponsors</span>
                </div>
            </div>
            <section class="section-card">
                <h2 class="section-title">Sponsors</h2>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <label>
                                Rechercher un sponsor
                            <input type="search" id="searchSponsorBackoffice" placeholder="rechercher par nom">
                            </label>
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <div class="filter-group">
                            
                                filtrer ordre alphabétique
                                <select id="sortSponsorsBackoffice">
                                    <option value="">--</option>
                                    <option value="az">A-Z</option>
                                    <option value="za">Z-A</option>
                                </select>
                                
                            
                        </div>
                    </div>
                </div>
                <div class="table-shell">
                    <table id="sponsorsBackofficeTable">
                        <thead>
                            <tr>
                                <th>id Organisateur</th>
                                <th>id Sponsor</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th style="width: 500px;">Adresse</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>PageWeb</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $controller->afficherSponsor(false, true); ?>
                        </tbody>
                    </table>
                </div>
                <div class="section-actions">
                    <button class="btn btn-export" onclick="exportSponsorsExcel()">Exporter sponsors</button>
                    <a class="btn btn-export" <?php echo $activeTab==='sponsorStat'?'active':''; ?>href="dashboard.php?tab=sponsorStat">Stats Sponsors</a>
                </div>
                <div class="section-note">Affichage statique de la table des sponsors. Les actions de modification sont désactivées.</div>
            </section>

            <div class="modal-overlay" id="deleteModal">
                <div class="modal">
                    <h3>Confirmer la suppression</h3>
                    <p id="deleteMessage">Êtes-vous sûr de vouloir supprimer ce sponsor ?</p>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" id="cancelDelete">Annuler</button>
                        <button class="btn btn-danger" id="confirmDelete">Confirmer</button>
                    </div>
                </div>
            </div>

            <section class="section-card" style="margin-top:24px;">
                <h2 class="section-title">Sponsoring</h2>
                <div class="toolbar">
                    <div class="search-box">
                        <label>
                            rechercher un sponsoring
                        <input type="search" id="searchSponsoringBackoffice" placeholder="Rechercher par nom ou par état">
                        </label>
                    </div>
                    <div class="filter-group">
                        <label for="filterEtatBackoffice">
                            Filtrer par état
                            <select id="filterEtatBackoffice">
                                <option value="tout">Tout</option>
                                <option value="actif">Actif</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </label>

                        <label for="sortMontantBackoffice">
                            Trier par montant
                            <select id="sortMontantBackoffice">
                                <option value="">--</option>
                                <option value="asc">Croissant</option>
                                <option value="desc">Décroissant</option>
                            </select>
                        </label>

                        <label for="sortDateFinBackoffice">
                            Trier par date de fin
                            <select id="sortDateFinBackoffice">
                                <option value="">--</option>
                                <option value="asc">Croissant</option>
                                <option value="desc">Décroissant</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="table-shell">
                    <table id="sponsoringBackofficeTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom Sponsoring</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Montant</th>
                                <th>État</th>
                                <th>id Sponsor</th>
                                <th>id Marathon</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sController->afficherSponsoring(false, true, false); ?>
                        </tbody>
                    </table>
                </div>
                <div class="section-actions">
                    <button class="btn btn-export" onclick="exportSponsoringExcel()">Exporter sponsoring</button>
                    <a class="btn btn-export" <?php echo $activeTab==='sponsoringStat'?'active':''; ?>href="dashboard.php?tab=sponsoringStat">Stats Sponsoring</a>
                </div>
                <div class="section-note">Représentation simple des contrats de sponsoring, avec recherche et filtres par état et montant.</div>
            </section>
            



            <div class="chat-box">

                <div id="chatMessages" class="chat-messages"></div>

                <div class="chat-input-area">
                    <input type="text" id="chatInput" placeholder="Pose ta question...">
                    <button onclick="sendChat()">Envoyer</button>
            </div>




        </main>
    

<?php endif; ?>
 <?php if ($activeTab === 'sponsorStat'): ?>

<?php
include_once '../../controller/sponsorController.php';

$controller = new sponsorController();
$typeStats = $controller->getSponsorDistributionByType();
$sponsoringStats = $controller->getSponsorsBySponsoringCount(10);
?>

<main class="content">
    <div class="head">
        <div>
            <h1>Statistiques Sponsors</h1>
            <div class="muted">
                Utilisez cette page pour visualiser la répartition des sponsors.
            </div>
        </div>
    </div>

    <section class="section-card">

        <div class="toolbar" style="margin-bottom:18px;">
            <div class="select-group">
                <label for="sponsorStatType">Type de statistique</label>

                <select id="sponsorStatType"
                        style="width:280px; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink);">

                    <option value="type">Répartition par type</option>
                    <option value="sponsoring">Sponsors par nombre de sponsoring</option>

                </select>
            </div>
        </div>

        <div class="chart-card">
            <canvas id="sponsorStatsChart"></canvas>
        </div>

        <div class="footer-actions">
            <a class="btn btn-secondary <?php echo $activeTab === 'sponsors' ? 'active' : ''; ?>"
               href="dashboard.php?tab=sponsors">
                Retour
            </a>
        </div>

    </section>
</main>
<script>
document.addEventListener("DOMContentLoaded", function () {

    if (typeof Chart === "undefined") return;

    const statsByType = <?php echo json_encode($typeStats, JSON_HEX_TAG); ?>;
    const statsBySponsoring = <?php echo json_encode($sponsoringStats, JSON_HEX_TAG); ?>;

    const select = document.getElementById("sponsorStatType");
    const canvas = document.getElementById("sponsorStatsChart");

    // 🔥 sécurité
    if (!select || !canvas) return;

    const ctx = canvas.getContext("2d");
    let chart;

    function getData(mode) {
        const data = mode === "type" ? statsByType : statsBySponsoring;

        return {
            labels: data.map(d => d.label),
            values: data.map(d => d.count)
        };
    }

    function renderChart() {
        const { labels, values } = getData(select.value);

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: labels.map((_, i) => [
                        "#0f766e", "#14b8a6", "#c084fc",
                        "#fb7185", "#f97316", "#4f46e5"
                    ][i % 6]),
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "bottom"
                    }
                }
            }
        });
    }

    select.addEventListener("change", renderChart);

    renderChart();

});
</script>

<?php endif; ?>
<?php if ($activeTab === 'sponsoringStat'): ?>

<?php
include_once '../../controller/sponsoringController.php';
$controller = new sponsoringController();
$amountStats = $controller->getSponsoringDistributionByAmountRange();
?>

<main class="content">
            <div class="head">
                <div>
                    <h1>Statistiques Sponsoring</h1>
                    <div class="muted">Représentation des sponsoring selon les tranches de montant, avec un graphique vertical dynamique.</div>
                </div>
            </div>
            <section class="section-card">
                <div class="chart-card">
                    <canvas id="sponsoringStatsChart"></canvas>
                </div>
                <div class="footer-actions">
                    <a class="btn btn-secondary" href="dashboard.php?tab=sponsors">Retour</a>
                </div>
            </section>
</main>
<script>
        const amountStats = <?php echo json_encode($amountStats, JSON_HEX_TAG); ?>;
        const labels = amountStats.map(item => item.label);
        const values = amountStats.map(item => item.count);
        const ctx = document.getElementById('sponsoringStatsChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Nombre de sponsoring',
                    data: values,
                    backgroundColor: ['#0f766e', '#14b8a6', '#64748b'],
                    borderColor: ['#0f766e', '#14b8a6', '#64748b'],
                    borderWidth: 1,
                    borderRadius: 12,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: context => `${context.dataset.label}: ${context.parsed.y}` } }
                }
            }
        });
    </script>

<?php endif; ?>
</main>
</div>

<div id="confirm-modal" class="modal-overlay">
    <div class="modal-box">
        <div style="font-size:3rem;margin-bottom:12px;">ðŸ—‘ï¸</div>
        <h3>Confirmation</h3>
        <p id="confirm-message" style="color:#627d98;margin-bottom:0;"></p>
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
<script>
// ── AJAX search — Marathons ───────────────────────────────────────────────
<?php if ($activeTab === 'marathons'): ?>
(function(){
    var sM     = document.getElementById('sM');
    var rM     = document.getElementById('rM');
    var tbody  = document.querySelector('#marathonTable tbody');
    var badge  = document.getElementById('marathonCountBadge');
    var tM;

    function fetchMarathons() {
        var search = sM.value;
        var region = rM.value;
        fetch('ajax_search_marathons.php?search=' + encodeURIComponent(search) + '&region=' + encodeURIComponent(region))
            .then(function(r){ return r.json(); })
            .then(function(data){
                tbody.innerHTML = data.html;
                if (badge) badge.textContent = data.count + ' résultat(s)';
            })
            .catch(console.error);
    }

    sM.addEventListener('input', function(){
        clearTimeout(tM);
        tM = setTimeout(fetchMarathons, 350);
    });
    rM.addEventListener('change', function(){
        sM.value = '';
        fetchMarathons();
    });
})();

function askDelM(id, nom) {
    document.getElementById('confirm-message').textContent = 'Supprimer le marathon "' + nom + '" ?';
    document.getElementById('confirm-yes').onclick = function() {
        window.location.href = 'dashboard.php?tab=marathons&del_m=' + id;
    };
    document.getElementById('confirm-modal').classList.add('active');
}
<?php endif; ?>

// ── AJAX search — Parcours ────────────────────────────────────────────────
<?php if ($activeTab === 'parcours'): ?>
(function(){
    var sP     = document.getElementById('sP');
    var dP     = document.getElementById('dP');
    var tbody  = document.querySelector('#parcoursTable tbody');
    var badge  = document.getElementById('parcoursCountBadge');
    var tP;

    function fetchParcours() {
        var search = sP.value;
        var diff   = dP.value;
        fetch('ajax_search_parcours.php?search=' + encodeURIComponent(search) + '&difficulte=' + encodeURIComponent(diff))
            .then(function(r){ return r.json(); })
            .then(function(data){
                tbody.innerHTML = data.html;
                if (badge) badge.textContent = data.count + ' résultat(s)';
            })
            .catch(console.error);
    }

    sP.addEventListener('input', function(){
        clearTimeout(tP);
        tP = setTimeout(fetchParcours, 350);
    });
    dP.addEventListener('change', function(){
        sP.value = '';
        fetchParcours();
    });
})();

function askDelP(id, nom) {
    document.getElementById('confirm-message').textContent = 'Supprimer le parcours "' + nom + '" ?';
    document.getElementById('confirm-yes').onclick = function() {
        window.location.href = 'dashboard.php?tab=parcours&del_p=' + id;
    };
    document.getElementById('confirm-modal').classList.add('active');
}
<?php endif; ?>
document.querySelectorAll('[data-modal-close]').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.getElementById('confirm-modal').classList.remove('active');
    });
});

// Gestion des checkboxes pour les commandes
function updateCommandesCount() {
    var checkboxes = document.querySelectorAll('.selectCommande:checked');
    var count = checkboxes.length;
    var area = document.getElementById('deleteCommandesArea');
    var countSpan = document.getElementById('selectedCommandesCount');
    if (count > 0) {
        area.style.display = 'block';
        countSpan.textContent = count + ' commande(s) sélectionnée(s)';
    } else {
        area.style.display = 'none';
    }
}

function toggleAllCommandes(checkbox) {
    document.querySelectorAll('.selectCommande').forEach(function(cb) {
        cb.checked = checkbox.checked;
    });
    updateCommandesCount();
}

document.querySelectorAll('.selectCommande').forEach(function(checkbox) {
    checkbox.addEventListener('change', updateCommandesCount);
});

function deleteSelectedCommandes() {
    var checkboxes = document.querySelectorAll('.selectCommande:checked');
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins une commande');
        return;
    }
    if (confirm('ÃŠtes-vous sûr de vouloir supprimer ' + checkboxes.length + ' commande(s) ?')) {
        var ids = Array.from(checkboxes).map(cb => cb.value);
        window.location.href = '?tab=commandes&delete_commandes=' + ids.join(',');
    }
}

// Gestion des checkboxes pour les lignes
function updateLignesCount() {
    var checkboxes = document.querySelectorAll('.selectLigne:checked');
    var count = checkboxes.length;
    var area = document.getElementById('deleteLignesArea');
    var countSpan = document.getElementById('selectedLignesCount');
    if (count > 0) {
        area.style.display = 'block';
        countSpan.textContent = count + ' ligne(s) sélectionnée(s)';
    } else {
        area.style.display = 'none';
    }
}

function toggleAllLignes(checkbox) {
    document.querySelectorAll('.selectLigne').forEach(function(cb) {
        cb.checked = checkbox.checked;
    });
    updateLignesCount();
}

document.querySelectorAll('.selectLigne').forEach(function(checkbox) {
    checkbox.addEventListener('change', updateLignesCount);
});

function deleteSelectedLignes() {
    var checkboxes = document.querySelectorAll('.selectLigne:checked');
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins une ligne');
        return;
    }
    if (confirm('ÃŠtes-vous sûr de vouloir supprimer ' + checkboxes.length + ' ligne(s) ?')) {
        var ids = Array.from(checkboxes).map(cb => cb.value);
        window.location.href = '?tab=commandes&delete_lignes=' + ids.join(',');
    }
}

// ==========================
// STAND MANAGEMENT JS
// ==========================
let isEditingStand = false;

function toggleEdit(btn, id) {
    if (isEditingStand) {
        alert("Veuillez enregistrer la ligne en cours d'édition.");
        return;
    }
    const row = document.getElementById('row-' + id);
    const editables = row.querySelectorAll('.editable');
    editables.forEach(td => {
        const text = td.innerText;
        const field = td.getAttribute('data-field');
        const input = document.createElement('input');
        input.type = (field === 'ID_parcours') ? 'number' : 'text';
        input.value = text;
        input.className = 'edit-input';
        input.style.width = '100%';
        td.innerHTML = '';
        td.appendChild(input);
    });
    isEditingStand = true;
    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    btn.className = 'btn-icon save';
    btn.onclick = function() { saveStand(btn, id); };
}

function saveStand(btn, id) {
    const row = document.getElementById('row-' + id);
    const editables = row.querySelectorAll('.editable');
    const formData = new FormData();
    formData.append('ID_stand', id);
    editables.forEach(td => {
        const input = td.querySelector('input');
        formData.append(td.getAttribute('data-field'), input.value);
    });

    fetch('../FrontOffice/updateStandAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(res => {
        if (res === 'SUCCESS') {
            editables.forEach(td => {
                td.innerText = td.querySelector('input').value;
            });
            isEditingStand = false;
            btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>';
            btn.className = 'btn-icon';
            btn.onclick = function() { toggleEdit(btn, id); };
        } else {
            alert("Erreur lors de la sauvegarde : " + res);
        }
    });
}

function filterStands() {
    const input = document.getElementById('standSearchInput');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#stands-table tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function exportTableToPDF(tableId, filename, title) {
    if (!window.jspdf) {
        alert("La bibliothèque PDF n'est pas chargée.");
        return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.text(title, 14, 22);
    doc.setFontSize(11);
    doc.setTextColor(100);
    doc.text("Généré le: " + new Date().toLocaleString(), 14, 30);

    const table = document.getElementById(tableId);
    if (!table) return;

    const headers = [];
    const rows = [];

    const ths = table.querySelectorAll('thead th');
    for (let i = 0; i < ths.length - 1; i++) {
        headers.push(ths[i].innerText.replace('↕', '').trim());
    }

    const trs = table.querySelectorAll('tbody tr');
    trs.forEach(tr => {
        const row = [];
        const tds = tr.querySelectorAll('td');
        if (tds.length > 1) { 
            for (let i = 0; i < tds.length - 1; i++) {
                // Strip emoji symbols like 📍 for PDF compatibility
                let val = tds[i].innerText.replace(/[\u{1F300}-\u{1F9FF}]/gu, '').trim();
                row.push(val);
            }
            rows.push(row);
        }
    });

    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 35,
        theme: 'striped',
        headStyles: { fillColor: [15, 118, 110], textColor: [255, 255, 255] },
        margin: { top: 35 }
    });

    doc.save(filename);
}

function searchByLocation() {
    const overlay = document.getElementById('recoOverlay');
    if (!overlay) return;
    
    overlay.classList.add('active');
    document.getElementById('reco-loading').style.display = 'block';
    document.getElementById('reco-results').style.display = 'none';

    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas supportée par votre navigateur.");
        closeRecoModal();
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                .then(r => r.json())
                .then(addrData => {
                    const addrStr = addrData.display_name || (lat.toFixed(4) + ', ' + lon.toFixed(4));
                    document.getElementById('my-coords-text').innerText = addrStr;
                })
                .catch(() => {
                    document.getElementById('my-coords-text').innerText = lat.toFixed(6) + ', ' + lon.toFixed(6);
                });

            fetchRecommendations(lat, lon);
        },
        (error) => {
            console.warn("Erreur géolocalisation :", error.message);
            const fallbackLat = 36.8065;
            const fallbackLon = 10.1815;
            document.getElementById('my-coords-text').innerText = "Tunis, Tunisie (Position par défaut)";
            fetchRecommendations(fallbackLat, fallbackLon);
        }
    );
}

function fetchRecommendations(lat, lon) {
    fetch(`Stands/api_recommander_admin.php?lat=${lat}&lon=${lon}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('reco-grid-container');
            container.innerHTML = '';
            
            if (data.error) {
                alert(data.error);
                closeRecoModal();
                return;
            }

            data.forEach(stand => {
                let badgeHTML = '';
                if (stand.en_stock == stand.total_produits && stand.total_produits > 0) {
                    badgeHTML = `<div class="reco-badge-status status-success">✅ Tout est disponible</div>`;
                } else if (stand.en_stock >= 5) {
                    badgeHTML = `<div class="reco-badge-status status-stock">🔥 Grand choix en stock</div>`;
                } else if (stand.distance_km < 5) {
                    badgeHTML = `<div class="reco-badge-status status-near">📍 À proximité</div>`;
                } else {
                    badgeHTML = `<div class="reco-badge-status">🏪 Stand</div>`;
                }

                const card = `
                    <div class="reco-card" style="position: relative;">
                        ${badgeHTML}
                        <div class="reco-card-icon" style="font-size: 1.5rem;">🏪</div>
                        <div class="reco-card-info">
                            <h4 style="margin-bottom: 2px; padding-right: 60px;">${stand.nom_stand}</h4>
                            <div class="reco-card-dist" style="display: flex; align-items: center; gap: 4px; font-weight: 600; color: #1e293b;">
                                <span style="color: #e11d48;">📍</span> Dist: <span style="color: #334155;">${stand.distance_km} km de vous</span>
                            </div>
                            <div style="font-size: 0.7rem; color: #94a3b8; font-style: italic; margin-top: 2px;">
                                📍 ${stand.position || 'N/A'}
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <span style="font-size: 0.7rem; color: #64748b;">📦 <strong>${stand.total_produits}</strong> produits</span>
                                <span style="font-size: 0.7rem; color: ${stand.en_stock > 0 ? '#166534' : '#991b1b'}; font-weight: 700;">
                                    ${stand.en_stock > 0 ? '✅ '+stand.en_stock+' en stock' : '❌ Rupture'}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += card;
            });

            setTimeout(() => {
                document.getElementById('reco-loading').style.display = 'none';
                document.getElementById('reco-results').style.display = 'block';
            }, 500);
        })
        .catch(err => {
            alert("Erreur lors de la récupération des recommandations.");
            closeRecoModal();
        });
}

function closeRecoModal() {
    const overlay = document.getElementById('recoOverlay');
    if (overlay) overlay.classList.remove('active');
}

function liveFilterTable(tableId, query) {
    const filter = query.toLowerCase();
    const table = document.getElementById(tableId);
    if (!table) return;
    const trs = table.getElementsByTagName("tr");

    for (let i = 1; i < trs.length; i++) {
        const row = trs[i];
        if (row.cells.length <= 1) continue;
        const textContent = row.textContent || row.innerText;
        row.style.display = (textContent.toLowerCase().indexOf(filter) > -1) ? "" : "none";
    }
}

function toggleIframe(id) {
    const el = document.getElementById(id);
    if (el) {
        if (el.style.display === 'none') {
            el.style.display = 'block';
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            el.style.display = 'none';
        }
    }
}
</script>

<style>
/* MODAL RECOMMANDATION GEOGRAPHIQUE */
.reco-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);
    z-index: 9999; display: none; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.3s ease;
}
.reco-overlay.active { display: flex; opacity: 1; }

.reco-modal {
    background: #ffffff; width: 95%; max-width: 700px;
    border-radius: 24px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    position: relative; transform: translateY(20px); transition: transform 0.3s ease;
    max-height: 90vh; display: flex; flex-direction: column;
}
.reco-overlay.active .reco-modal { transform: translateY(0); }

#reco-results { overflow-y: auto; padding-right: 10px; }
#reco-results::-webkit-scrollbar { width: 6px; }
#reco-results::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.reco-close {
    position: absolute; top: 20px; right: 20px;
    width: 32px; height: 32px; background: #f1f5f9; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: none; color: #64748b; font-weight: bold; transition: 0.2s;
}
.reco-close:hover { background: #e2e8f0; color: #0f172a; }

.reco-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
.reco-header-icon {
    background: #e0f2fe; width: 48px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
}
.reco-header h2 { margin: 0; color: #0f172a; font-size: 1.5rem; font-weight: 800; }

.reco-main-card {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;
    padding: 20px; display: flex; align-items: center; gap: 20px; position: relative; margin-bottom: 30px;
}
.reco-main-badge {
    position: absolute; top: 15px; right: 15px; background: #10b981; color: white;
    padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;
}
.reco-main-avatar {
    background: #1e293b; color: white; width: 60px; height: 60px; border-radius: 16px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; font-weight: 800; line-height: 1.2;
}
.reco-main-avatar span { font-size: 0.7rem; opacity: 0.7; }
.reco-main-info h3 { margin: 0 0 5px 0; color: #0f172a; font-size: 1.2rem; font-weight: 800; }
.reco-main-loc { color: #64748b; font-size: 0.85rem; display: flex; align-items: center; gap: 5px; }
.reco-tag { background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; margin-left: 10px; }

/* NOUVEAUX STYLES POUR LES BADGES IMAGE 2 */
.reco-badge-status {
    position: absolute; top: 10px; right: 10px; font-size: 0.65rem; font-weight: 800;
    padding: 3px 10px; border-radius: 20px; border: 1px solid #e2e8f0;
    background: #f8fafc; color: #64748b;
}
.status-success { background: #f0fdf4; color: #166534; border-color: #dcfce7; }
.status-near { background: #eff6ff; color: #1e40af; border-color: #dbeafe; }
.status-stock { background: #fff7ed; color: #9a3412; border-color: #ffedd5; }

.reco-section-title { display: flex; align-items: center; gap: 10px; color: #334155; font-weight: 800; font-size: 1rem; margin-bottom: 15px; }
.reco-section-icon { background: #eff6ff; color: #3b82f6; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }

.reco-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.reco-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; transition: 0.2s; }
.reco-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.reco-card-icon { background: #f1f5f9; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.reco-card-info h4 { margin: 0 0 4px 0; color: #1e293b; font-size: 1rem; font-weight: 700; }
.reco-card-dist { color: #64748b; font-size: 0.8rem; }
.reco-card-dist strong { color: #334155; }
.reco-card-pos { font-size: 0.75rem; opacity: 0.6; margin-left: 4px; }

#reco-loading { text-align: center; padding: 40px 0; }
.search-anim-icon { font-size: 3rem; margin-bottom: 20px; animation: bounce 1.5s infinite; }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
#reco-loading h3 { color: #0f172a; margin-bottom: 5px; }
#reco-loading p { color: #64748b; font-size: 0.9rem; }
</style>

<div class="reco-overlay" id="recoOverlay">
    <div class="reco-modal">
        <button class="reco-close" onclick="closeRecoModal()">✕</button>
        
        <div id="reco-loading">
            <div class="search-anim-icon">🔍</div>
            <h3>Recherche en cours...</h3>
            <p>Nous trouvons les meilleurs stands pour vous.</p>
        </div>

        <div id="reco-results" style="display: none;">
            <div class="reco-header">
                <div class="reco-header-icon">🔍</div>
                <h2>Résultat de recherche</h2>
            </div>

            <div class="reco-main-card">
                <div class="reco-main-badge">✓ RÉSULTAT PRINCIPAL</div>
                <div class="reco-main-avatar"><span>ID</span>#Moi</div>
                <div class="reco-main-info">
                    <h3>Votre Position</h3>
                    <div class="reco-main-loc">
                        📍 <span id="my-coords-text">Calcul en cours...</span> 
                        <span class="reco-tag">Parcours GPS</span>
                    </div>
                </div>
            </div>

            <div class="reco-section-title">
                <div class="reco-section-icon">🔄</div> Recommandations à proximité
            </div>
            <div class="reco-grid" id="reco-grid-container"></div>
        </div>
    </div>
</div>
<script>
(function(){
    function setRole(){ if(document.body) document.body.dataset.userRole = <?php echo json_encode($user['role'] ?? 'visiteur'); ?>; }
    if(document.body) setRole(); else document.addEventListener('DOMContentLoaded', setRole);
})();


//sponsor
const deleteModal = document.getElementById('deleteModal');
        const deleteMessage = document.getElementById('deleteMessage');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        let deleteHref = null;

        document.querySelectorAll('.delete-sponsor-btn, .delete-sponsoring-btn').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                deleteHref = button.getAttribute('href');
                const itemName = button.dataset.sponsorName || button.dataset.sponsoringName || 'cet élément';
                deleteMessage.textContent = `Êtes-vous sûr de vouloir supprimer ${itemName} ?`;
                deleteModal.classList.add('active');
            });
        });

        cancelDelete.addEventListener('click', () => {
            deleteHref = null;
            deleteModal.classList.remove('active');
        });

        confirmDelete.addEventListener('click', () => {
            if (deleteHref) {
                window.location.href = deleteHref;
            }
        });

        deleteModal.addEventListener('click', event => {
            if (event.target === deleteModal) {
                deleteModal.classList.remove('active');
            }
        });

        // Fonction de recherche en temps réel pour sponsors (Backoffice)
        const searchSponsorBackofficeInput = document.getElementById('searchSponsorBackoffice');
        const sponsorsBackofficeTable = document.getElementById('sponsorsBackofficeTable');
        
        if (searchSponsorBackofficeInput && sponsorsBackofficeTable) {
            searchSponsorBackofficeInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = sponsorsBackofficeTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        // Chercher dans la cellule "Nom" (index 2)
                        const nomCell = cells[2] ? cells[2].textContent.toLowerCase() : '';
                        if (nomCell.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }

        // Fonction de recherche en temps réel pour sponsoring (Backoffice)
        const searchSponsoringBackofficeInput = document.getElementById('searchSponsoringBackoffice');
        const sponsoringBackofficeTable = document.getElementById('sponsoringBackofficeTable');
        
        if (searchSponsoringBackofficeInput && sponsoringBackofficeTable) {
            searchSponsoringBackofficeInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = sponsoringBackofficeTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        // Chercher dans la cellule "Nom Sponsoring" (index 1)
                        const nomCell = cells[1] ? cells[1].textContent.toLowerCase() : '';
                        if (nomCell.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }



        const sortSponsorsBackoffice = document.getElementById('sortSponsorsBackoffice');

        if (sortSponsorsBackoffice) {
            sortSponsorsBackoffice.addEventListener('change', function () {
                const table = document.getElementById('sponsorsBackofficeTable');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const A = a.cells[2].textContent.toLowerCase();
                    const B = b.cells[2].textContent.toLowerCase();
                    return this.value === 'az' ? A.localeCompare(B) : B.localeCompare(A);
                });

                rows.forEach(r => tbody.appendChild(r));
            });
        }

        // ===== SPONSORING FILTRES =====
        const filterEtatBackoffice = document.getElementById('filterEtatBackoffice');
        const sortMontantBackoffice = document.getElementById('sortMontantBackoffice');
        const sortDateFinBackoffice = document.getElementById('sortDateFinBackoffice');
function applyFiltersBackoffice() {
    const table = document.getElementById('sponsoringBackofficeTable');
    const tbody = table.querySelector('tbody');

    let rows = Array.from(tbody.querySelectorAll('tr'));

    // reset affichage
    rows.forEach(r => r.style.display = '');

    // ===== FILTRE ETAT =====
    if (filterEtatBackoffice.value !== 'tout') {
        rows.forEach(row => {
            const etat = row.cells[5].textContent
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

            row.style.display = (etat === filterEtatBackoffice.value) ? '' : 'none';
        });
    }

    // garder seulement visibles pour tri
    rows = rows.filter(r => r.style.display !== 'none');

    // ===== TRI MONTANT =====
    if (sortMontantBackoffice.value) {
        rows.sort((a, b) => {
            const A = parseFloat(a.cells[4].textContent.replace(/[^\d.-]/g, '')) || 0;
            const B = parseFloat(b.cells[4].textContent.replace(/[^\d.-]/g, '')) || 0;
            return sortMontantBackoffice.value === 'asc' ? A - B : B - A;
        });
    }

    // ===== TRI DATE =====
    if (sortDateFinBackoffice.value) {
        rows.sort((a, b) => {
            const A = new Date(a.cells[3].textContent);
            const B = new Date(b.cells[3].textContent);
            return sortDateFinBackoffice.value === 'asc' ? A - B : B - A;
        });
    }

    // réinjection propre
    rows.forEach(r => tbody.appendChild(r));
}

        filterEtatBackoffice?.addEventListener('change', applyFiltersBackoffice);
        sortMontantBackoffice?.addEventListener('change', applyFiltersBackoffice);
        sortDateFinBackoffice?.addEventListener('change', applyFiltersBackoffice);



    function exportSponsorsExcel() {
    const table = document.getElementById("sponsorsBackofficeTable");

    const wb = XLSX.utils.book_new();

    // ⚡ conversion directe propre
    const ws = XLSX.utils.table_to_sheet(table);

    // supprimer colonne Actions proprement
    const range = XLSX.utils.decode_range(ws['!ref']);

    for (let R = range.s.r; R <= range.e.r; R++) {
        const addr = XLSX.utils.encode_cell({ r: R, c: range.e.c });
        delete ws[addr];
    }

    // AUTO WIDTH
    applyAutoWidth(ws);

    XLSX.utils.book_append_sheet(wb, ws, "Sponsors");
    XLSX.writeFile(wb, "sponsors.xlsx");
}
function formatExcelDate(value) {
    if (!value) return "";

    // déjà format ISO
    if (typeof value === "string" && value.includes("-")) {
        return value;
    }

    // Excel number date
    if (typeof value === "number") {
        const date = new Date(Math.round((value - 25569) * 86400 * 1000));

        if (!isNaN(date)) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, "0");
            const d = String(date.getDate()).padStart(2, "0");
            return `${y}-${m}-${d}`;
        }
    }

    return "";
}

function exportSponsoringExcel() {
    const table = document.getElementById("sponsoringBackofficeTable");
    const wb = XLSX.utils.book_new();

    const rows = [];
    const tr = table.querySelectorAll("tr");

    tr.forEach((row, rowIndex) => {
        const cells = row.querySelectorAll("th, td");

        const line = [];

        cells.forEach((cell, colIndex) => {
            const text = cell.innerText.trim();

            // ❌ supprimer colonne ACTIONS (dernière colonne)
            if (colIndex === cells.length - 1) return;

            line.push(text);
        });

        rows.push(line);
    });

    // correction dates si besoin
    for (let i = 1; i < rows.length; i++) {
        rows[i][2] = formatExcelDate(rows[i][2]); // date début
        rows[i][3] = formatExcelDate(rows[i][3]); // date fin
    }

    const ws = XLSX.utils.aoa_to_sheet(rows);

    applyAutoWidth(ws);

    XLSX.utils.book_append_sheet(wb, ws, "Sponsoring");
    XLSX.writeFile(wb, "sponsoring.xlsx");
}
function applyAutoWidth(ws) {
    const range = XLSX.utils.decode_range(ws['!ref']);
    const colWidths = [];

    for (let C = range.s.c; C <= range.e.c; C++) {
        let max = 10;

        for (let R = range.s.r; R <= range.e.r; R++) {
            const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];

            if (cell && cell.v) {
                const len = cell.v.toString().length;
                if (len > max) max = len;
            }
        }

        colWidths.push({ wch: Math.min(max + 2, 60) });
    }

    ws["!cols"] = colWidths;
}







    function addMessage(text, type) {
        let container = document.getElementById("chatMessages");

        let msg = document.createElement("div");
        msg.classList.add("message", type);
        msg.innerText = text;

        container.appendChild(msg);
        container.scrollTop = container.scrollHeight;

        return msg;
    }

    function sendChat() {

        let input = document.getElementById("chatInput");
        let msg = input.value;

        if (!msg.trim()) return;

        // 🔹 Message utilisateur
        addMessage(msg, "user");

        input.value = "";

        // 🔹 Message temporaire "Réflexion..."
        let thinkingMsg = addMessage("🤔 Réflexion...", "bot");

        fetch("chatSponsor_process.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "message=" + encodeURIComponent(msg)
        })
        .then(res => res.text())
        .then(data => {
            // 🔥 remplacer "Réflexion..." par vraie réponse
            thinkingMsg.innerText = data;
        })
        .catch(error => {
            thinkingMsg.innerText = "Erreur : " + error.message;
        });
    }



/*
    const statsByType = <?php echo json_encode($typeStats, JSON_HEX_TAG); ?>;
        const statsBySponsoring = <?php echo json_encode($sponsoringStats, JSON_HEX_TAG); ?>;
        const selectStat = document.getElementById('sponsorStatType');
        const ctx = document.getElementById('sponsorStatsChart').getContext('2d');
        let currentChart;

        function buildDataset(mode) {
            const data = mode === 'type' ? statsByType : statsBySponsoring;
            return {
                labels: data.map(item => item.label),
                values: data.map(item => item.count)
            };
        }

        function createChart(mode) {
            const { labels, values } = buildDataset(mode);
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: labels.map((_, index) => [
                            '#0f766e', '#14b8a6', '#c084fc', '#fb7185', '#f97316', '#4f46e5'
                        ][index % 6]),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 14, padding: 16 } }, tooltip: { callbacks: { label: context => `${context.label}: ${context.parsed}` } } }
                }
            });
        }

        function refreshChart() {
            if (currentChart) { currentChart.destroy(); }
            currentChart = createChart(selectStat.value);
        }

        selectStat.addEventListener('change', refreshChart);
        refreshChart();*/
    //sponsor


</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="../assets/js/voice-nav.js" defer></script>


<!-- ═══════════════════════════════════════════════ -->
<!--  MODAL AFFICHE IA                               -->
<!-- ═══════════════════════════════════════════════ -->
</body>
</html>
