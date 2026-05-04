<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/InscriptionMarathonController.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../Controller/UserController.php';

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();
$inscCtrl = new InscriptionMarathonController();
$uCtrl = new UserController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: listMarathons.php'); exit; }
$m = $mCtrl->showMarathon($id);
if (!$m) { header('Location: listMarathons.php'); exit; }

$tousParcours = $pCtrl->afficherParcours();
$parcoursDuMarathon = array_values(array_filter($tousParcours, fn($p) => $p['id_marathon'] == $id));

// Search & filter for parcours
$searchParcours = $_GET['search_parcours'] ?? '';
$filterDiff = $_GET['difficulte'] ?? '';
if ($searchParcours !== '') {
    $parcoursDuMarathon = array_values(array_filter($parcoursDuMarathon, fn($p) => stripos($p['nom_parcours'], $searchParcours) !== false));
}
if ($filterDiff !== '') {
    $parcoursDuMarathon = array_values(array_filter($parcoursDuMarathon, fn($p) => $p['difficulte'] === $filterDiff));
}

$standsDemo = [
    ['id_stand'=>1,'nom_stand'=>'Stand Ravitaillement','position'=>'Km 5','description'=>'Eau, boissons énergétiques et fruits'],
    ['id_stand'=>2,'nom_stand'=>'Stand Médical','position'=>'Km 10','description'=>'Premiers secours et assistance médicale'],
    ['id_stand'=>3,'nom_stand'=>'Stand Sponsors','position'=>'Arrivée','description'=>'Stands partenaires et remise des médailles'],
];

$currentPage = 'catalogue';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
if (is_array($user)) {
    $userId = $user['id_user'] ?? $user['id'] ?? null;
} else {
    $userId = null;
}

// ── Refresh physical data from DB (age/poids/taille may be missing from old sessions) ──
if ($userId && $role === 'participant') {
    $freshUser = $uCtrl->showUser($userId);
    if ($freshUser) {
        $user['age']    = $freshUser['age']    ?? $user['age']    ?? 0;
        $user['poids']  = $freshUser['poids']  ?? $user['poids']  ?? 0;
        $user['taille'] = $freshUser['taille'] ?? $user['taille'] ?? 0;
        $user['nom_complet'] = $freshUser['nom_complet'] ?? $user['nom_complet'] ?? ($user['nom'] ?? '');
        // Update session with fresh data
        $_SESSION['user']['age']    = $user['age'];
        $_SESSION['user']['poids']  = $user['poids'];
        $_SESSION['user']['taille'] = $user['taille'];
    }
}

// Vérifier si l'utilisateur est déjà inscrit à ce marathon
$estDejaInscrit = false;
if ($role === 'participant' && $userId) {
    $estDejaInscrit = $inscCtrl->estDejaInscrit($userId, $id);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo htmlspecialchars($m['nom_marathon']); ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }

        .message { padding:16px; border-radius:18px; margin-bottom:18px; }
        .message.success { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .message.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

        /* INSCRIPTION BANNER - top */
        .inscription-banner { border-radius:20px; padding:22px 28px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; }
        .inscription-banner.visitor { background:linear-gradient(135deg,#102a43,#1e3a5f); color:white; }
        .inscription-banner.participant { background:linear-gradient(135deg,#102a43,#1e3a5f); color:white; }
        .insc-text h3 { font-size:1.2rem; margin-bottom:4px; }
        .insc-text p { opacity:.88; font-size:0.9rem; }
        .btn-inscription, .btn-login-insc { display:inline-block; background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; padding:12px 28px; border-radius:12px; font-weight:900; font-size:0.97rem; text-decoration:none; box-shadow:0 6px 18px rgba(15,118,110,.3); transition:transform .15s; white-space:nowrap; border:2px solid rgba(255,255,255,.3); }
        .btn-inscription:hover, .btn-login-insc:hover { transform:translateY(-2px); }
        .btn-inscription-disabled { display:inline-block; background:rgba(255,255,255,.2); color:rgba(255,255,255,.7); padding:12px 28px; border-radius:12px; font-weight:900; font-size:0.97rem; cursor:not-allowed; white-space:nowrap; }

        .detail-hero {
            display:grid; grid-template-columns:1fr 1fr; gap:0;
            background:white; border-radius:28px; overflow:hidden;
            box-shadow:0 16px 44px rgba(16,42,67,.1); margin-bottom:28px;
        }
        .detail-info { padding:32px; display:flex; flex-direction:column; justify-content:space-between; }
        .marathon-badge { display:inline-block; background:rgba(16,42,67,.08); color:var(--ink); border-radius:8px; padding:5px 12px; font-size:0.82rem; font-weight:700; margin-bottom:14px; }
        .detail-info h1 { font-size:1.85rem; line-height:1.2; margin-bottom:18px; }
        .meta-list { display:grid; gap:10px; margin-bottom:22px; }
        .meta-row { display:flex; align-items:center; gap:10px; font-size:0.93rem; }
        .meta-row .icon { width:32px; height:32px; border-radius:10px; background:rgba(15,118,110,.1); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .meta-row .label { color:#627d98; font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .meta-row .value { font-weight:700; color:var(--ink); }
        .price-block {
  background: linear-gradient(135deg, #fff9ef, #fff);
  border: 1px solid rgba(255, 183, 3, .2);
  border-radius: 5px;
  padding: 2px 5px;
  font-size: 0.75rem;
  line-height: 1;
}
        .price-label { font-size:0.8rem; color:#627d98; font-weight:700; text-transform:uppercase; margin-bottom:4px; }
        .price-val { font-size:2.2rem; font-weight:900; color:var(--coral); }
        .detail-img { position:relative; max-height:380px; overflow:hidden; }
        .detail-img img { width:100%; height:100%; max-height:380px; object-fit:cover; display:block; }
        .img-id { position:absolute; top:16px; left:16px; background:rgba(16,42,67,.82); color:white; border-radius:9px; padding:6px 14px; font-weight:700; font-size:0.88rem; backdrop-filter:blur(6px); }
        .places-badge { position:absolute; bottom:16px; right:16px; border-radius:12px; padding:8px 16px; font-weight:700; font-size:0.88rem; backdrop-filter:blur(6px); }
        .places-ok { background:rgba(16,185,129,.85); color:white; }
        .places-no { background:rgba(231,111,81,.85); color:white; }

        .section-h { display:flex; align-items:center; gap:12px; margin:0 0 16px; flex-wrap:wrap; justify-content:space-between; }
        .section-h h2 { font-size:1.4rem; font-weight:900; }
        .section-h .count { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:4px 13px; font-size:0.88rem; font-weight:700; }

        /* Parcours filter bar */
        .parcours-filter { background:white; border-radius:14px; padding:14px 16px; margin-bottom:18px; box-shadow:0 4px 14px rgba(16,42,67,.06); display:flex; gap:10px; flex-wrap:wrap; }
        .parcours-filter select { border-radius:10px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; flex:1 1 130px; min-width:0; max-width:200px; font-size:0.9rem; }
        .parcours-filter select:focus { outline:none; border-color:var(--teal); }
        /* Autocomplete for parcours */
        .p-search-wrap { position:relative; flex:2 1 200px; min-width:0; }
        .p-search-wrap input { width:100%; border-radius:10px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; font-size:0.9rem; }
        .p-search-wrap input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.1); }
        .p-autocomplete-list { position:absolute; top:calc(100% + 4px); left:0; right:0; background:white; border:1px solid #cbd5e1; border-radius:11px; box-shadow:0 8px 20px rgba(16,42,67,.1); z-index:999; max-height:200px; overflow-y:auto; display:none; }
        .p-autocomplete-list.open { display:block; }
        .p-auto-item { padding:9px 14px; cursor:pointer; font-size:0.9rem; border-bottom:1px solid #f1f5f9; }
        .p-auto-item:last-child { border-bottom:none; }
        .p-auto-item:hover, .p-auto-item.selected { background:#f0fdf9; color:var(--teal); font-weight:700; }

        .cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-bottom:36px; }

        .p-card { background:white; border-radius:18px; border:1px solid rgba(16,42,67,.07); box-shadow:0 6px 20px rgba(16,42,67,.07); overflow:hidden; transition:transform .2s; }
        .p-card:hover { transform:translateY(-3px); }
        .diff-band { padding:9px 16px; font-weight:800; font-size:0.83rem; letter-spacing:.04em; }
        .diff-facile { background:linear-gradient(90deg,#d1fae5,#a7f3d0); color:#065f46; }
        .diff-moyen  { background:linear-gradient(90deg,#fef9c3,#fde68a); color:#92400e; }
        .diff-difficile { background:linear-gradient(90deg,#fee2e2,#fecaca); color:#991b1b; }
        .p-body { padding:16px; }
        .p-body h3 { font-size:1rem; margin-bottom:10px; }
        .p-route { display:grid; gap:6px; font-size:0.87rem; color:#486581; background:#f8fafc; border-radius:11px; padding:11px; margin-bottom:10px; }
        .dist-row { display:flex; justify-content:space-between; align-items:center; }
        .dist-val { font-size:1.35rem; font-weight:900; color:var(--teal); }

        .s-card { background:white; border-radius:18px; border:1px solid rgba(16,42,67,.07); box-shadow:0 6px 20px rgba(16,42,67,.07); overflow:hidden; transition:transform .2s; }
        .s-card:hover { transform:translateY(-3px); }
        .stand-header { background:linear-gradient(135deg,#102a43,#1e3a5f); padding:14px 16px; color:white; }
        .stand-header h3 { font-size:1rem; margin-bottom:3px; }
        .stand-pos { font-size:0.82rem; opacity:.8; }
        .s-body { padding:16px; }
        .s-desc { color:#486581; font-size:0.88rem; line-height:1.6; margin-bottom:14px; }
        .btn-produits { display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; border:none; border-radius:10px; padding:9px 14px; font-weight:700; font-size:0.85rem; cursor:pointer; text-decoration:none; transition:transform .15s; }
        .btn-produits:hover { transform:translateY(-1px); }

        /* ── Boutons généraux (manquants dans le fichier original) ── */
        .btn { text-decoration:none; padding:10px 15px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:0.9rem; transition:opacity .15s,transform .15s,box-shadow .15s; }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary { background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; box-shadow:0 4px 14px rgba(22,163,74,.3); }
        .btn-primary:hover { box-shadow:0 6px 18px rgba(22,163,74,.4); }
        .btn-outline { background:linear-gradient(135deg,#1a1a1a,#374151); color:#fff; box-shadow:0 4px 14px rgba(0,0,0,.25); }
        .btn-outline:hover { box-shadow:0 6px 18px rgba(0,0,0,.35); }
        /* Boutons dans les cartes Parcours */
        .btn-mod { display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; background:linear-gradient(135deg,#cbd5e1,#e2e8f0); color:#475569; border-radius:10px; font-weight:700; font-size:0.85rem; border:0; cursor:pointer; transition:opacity .15s,transform .15s,box-shadow .15s; box-shadow:0 3px 10px rgba(203,213,225,.4); }
        .btn-mod:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 5px 14px rgba(203,213,225,.5); }
        .btn-del-card { display:inline-flex; align-items:center; justify-content:center; gap:6px; background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; border-radius:10px; font-weight:700; font-size:0.85rem; border:0; cursor:pointer; transition:opacity .15s,transform .15s,box-shadow .15s; box-shadow:0 3px 10px rgba(220,38,38,.3); }
        .btn-del-card:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 5px 14px rgba(220,38,38,.4); }

        /* MODAL */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(16,42,67,.5); z-index:2000; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
        .modal-overlay.open { display:flex; }
        .modal { background:white; border-radius:24px; padding:28px; width:min(540px,calc(100% - 32px)); max-height:80vh; overflow-y:auto; box-shadow:0 24px 60px rgba(16,42,67,.2); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .modal-header h3 { font-size:1.25rem; font-weight:900; }
        .modal-close { background:#f1f5f9; border:none; border-radius:8px; width:32px; height:32px; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; }
        .prod-table { width:100%; border-collapse:collapse; font-size:0.9rem; }
        .prod-table th { background:#102a43; color:white; padding:10px 8px; text-align:left; font-size:0.83rem; }
        .prod-table td { padding:10px 8px; border-bottom:1px solid #e6edf3; }
        .prod-table tr:hover td { background:#f8fafc; }
        .stock-ok { color:#059669; font-weight:700; }
        .stock-no { color:var(--coral); font-weight:700; }

        .empty-box { background:white; border-radius:16px; padding:28px; text-align:center; color:#627d98; font-size:0.93rem; grid-column:1/-1; }
        /* Modal box */
        .modal-box { background:#fff; border-radius:20px; padding:32px 28px; width:min(420px,calc(100% - 32px)); text-align:center; box-shadow:0 24px 60px rgba(16,42,67,.2); }
        .modal-icon { font-size:2.8rem; margin-bottom:12px; }
        .modal-box h3 { font-size:1.25rem; font-weight:800; color:#102a43; margin-bottom:8px; }
        .modal-box p { color:#627d98; margin-bottom:4px; font-size:0.95rem; }
        /* Modal buttons */
        .modal-btns { display:flex; gap:10px; margin-top:22px; }
        .btn-confirm-del { flex:1; background:#e76f51; color:#fff; border:0; border-radius:10px; padding:11px; font-weight:700; font-size:0.92rem; cursor:pointer; transition:opacity .15s; }
        .btn-confirm-del:hover { opacity:.9; }
        .btn-cancel { flex:1; background:#f1f5f9; color:#102a43; border:0; border-radius:10px; padding:11px; font-weight:700; font-size:0.92rem; cursor:pointer; transition:opacity .15s; }
        .btn-cancel:hover { opacity:.8; }
        @media(max-width:860px){ .detail-hero{grid-template-columns:1fr;} .detail-img{max-height:250px;} }
        @media(max-width:600px){ .inscription-banner{flex-direction:column;} }

        /* ── IA RECOMMENDATION BUTTON ─────────────────────────────────────── */
        .ia-reco-bar {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(15,23,42,.2);
        }
        .ia-reco-bar .ia-text h3 {
            color: white;
            font-size: .92rem;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .ia-reco-bar .ia-text p {
            color: rgba(255,255,255,.6);
            font-size: .78rem;
        }
        .btn-ia-reco {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-weight: 800;
            font-size: .85rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59,130,246,.4);
            transition: transform .15s, box-shadow .15s;
            white-space: nowrap;
        }
        .btn-ia-reco:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(59,130,246,.5); }
        .btn-ia-reco .ia-icon { font-size: 1rem; }

        /* ── IA MODAL ─────────────────────────────────────────────────────── */
        .ia-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10,20,40,.65);
            z-index: 3000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
        }
        .ia-modal-overlay.open { display: flex; }
        .ia-modal {
            background: white;
            border-radius: 28px;
            width: min(780px, calc(100% - 24px));
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 32px 80px rgba(10,20,40,.3);
            animation: iaSlideIn .25s ease;
        }
        @keyframes iaSlideIn { from { transform:translateY(24px); opacity:0; } to { transform:translateY(0); opacity:1; } }
        .ia-modal-head {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            padding: 24px 28px;
            border-radius: 28px 28px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ia-modal-head h2 { color: white; font-size: 1.25rem; font-weight: 900; }
        .ia-modal-head span { color: rgba(255,255,255,.6); font-size: 0.85rem; margin-top: 3px; display: block; }
        .ia-close-btn {
            background: rgba(255,255,255,.15);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 1.15rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ia-modal-body { padding: 28px; }

        /* Steps */
        .ia-step-label {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #627d98;
            margin-bottom: 8px;
        }
        .ia-cards-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 22px; }
        .ia-stat-card {
            background: #f8fafc;
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .ia-stat-card .stat-icon { font-size: 1.6rem; margin-bottom: 6px; }
        .ia-stat-card .stat-val { font-size: 1.3rem; font-weight: 900; color: #102a43; }
        .ia-stat-card .stat-lbl { font-size: 0.76rem; color: #627d98; font-weight: 700; }

        /* Endurance badge */
        .ia-endurance-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }
        .ia-endurance-badge.good  { background: #dcfce7; color: #166534; }
        .ia-endurance-badge.mid   { background: #fef9c3; color: #92400e; }
        .ia-endurance-badge.low   { background: #fee2e2; color: #991b1b; }

        /* Score bar */
        .ia-score-wrap { margin-bottom: 22px; }
        .ia-score-bar-bg { background: #e2e8f0; border-radius: 999px; height: 12px; overflow: hidden; }
        .ia-score-bar-fill { height: 100%; border-radius: 999px; transition: width .6s ease; }
        .ia-score-nums { display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: #627d98; margin-top: 4px; }

        /* Recommended parcours card */
        .ia-result-card {
            border-radius: 18px;
            border: 2.5px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4, #fff);
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
        }
        .ia-result-card .ia-badge {
            position: absolute;
            top: -12px;
            left: 20px;
            background: #10b981;
            color: white;
            border-radius: 999px;
            padding: 4px 14px;
            font-size: 0.8rem;
            font-weight: 900;
        }
        .ia-result-card h3 { font-size: 1.1rem; font-weight: 900; color: #102a43; margin-bottom: 10px; margin-top: 6px; }
        .ia-result-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .ia-result-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(16,185,129,.1);
            color: #065f46;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 0.84rem;
            font-weight: 700;
        }

        /* Compatibility pills */
        .ia-compat { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
        .ia-compat-dot {
            width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0;
        }
        .ia-compat.green .ia-compat-dot { background: #10b981; }
        .ia-compat.yellow .ia-compat-dot { background: #f59e0b; }
        .ia-compat.red .ia-compat-dot { background: #ef4444; }
        .ia-compat-text { font-size: 0.88rem; font-weight: 700; }

        /* Simulation box */
        .ia-sim-box {
            background: linear-gradient(135deg, #fff9ef, #fff);
            border: 1px solid rgba(245,158,11,.2);
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 18px;
        }
        .ia-sim-box h4 { font-size: 0.92rem; font-weight: 800; color: #102a43; margin-bottom: 12px; }
        .ia-sim-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 0.88rem; }
        .ia-sim-row .sim-icon { font-size: 1.2rem; width: 30px; text-align: center; }
        .ia-sim-row .sim-val { font-weight: 800; color: #102a43; }
        .ia-sim-row .sim-lbl { color: #627d98; }

        /* All parcours table */
        .ia-all-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 10px; }
        .ia-all-table th { background: #f1f5f9; color: #475569; font-weight: 800; padding: 9px 10px; text-align: left; border-radius: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: .04em; }
        .ia-all-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .ia-all-table tr:last-child td { border-bottom: none; }
        .ia-all-table tr:hover td { background: #f8fafc; }
        .compat-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 3px 12px;
            font-size: 0.8rem;
            font-weight: 800;
        }
        .compat-pill.green  { background: #dcfce7; color: #166534; }
        .compat-pill.yellow { background: #fef9c3; color: #92400e; }
        .compat-pill.red    { background: #fee2e2; color: #991b1b; }

        /* Loading spinner */
        .ia-loading { text-align: center; padding: 40px 20px; }
        .ia-spinner { width: 52px; height: 52px; border: 5px solid #e2e8f0; border-top-color: #f59e0b; border-radius: 50%; animation: spin .8s linear infinite; margin: 0 auto 16px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media(max-width:520px) {
            .ia-cards-row { grid-template-columns: repeat(3,1fr); gap: 8px; }
            .ia-modal-body { padding: 18px; }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="listMarathons.php">← Retour au catalogue</a>

    <?php
    $success = $_GET['success'] ?? '';
    $error = $_GET['error'] ?? '';
    if ($success !== ''): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>



    <!-- DETAIL HERO -->
    <div class="detail-hero">
        <div class="detail-info">
            <div>
                <span class="marathon-badge">#<?php echo $m['id_marathon']; ?></span>
                <h1><?php echo htmlspecialchars($m['nom_marathon']); ?></h1>
                <div class="meta-list">
                    <div class="meta-row">
                        <div class="icon">👤</div>
                        <div><div class="label">Organisateur</div><div class="value"><?php echo htmlspecialchars($m['organisateur_marathon']); ?></div></div>
                    </div>
                    <div class="meta-row">
                        <div class="icon">📍</div>
                        <div><div class="label">Région</div><div class="value"><?php echo htmlspecialchars($m['region_marathon']); ?></div></div>
                    </div>
                    <div class="meta-row">
                        <div class="icon">📅</div>
                        <div><div class="label">Date</div><div class="value"><?php echo date('d/m/Y', strtotime($m['date_marathon'])); ?></div>
                        <div id="detailMeteo" style="margin-top:4px;font-size:0.82rem;font-weight:700;color:#0f766e;" data-date="<?php echo htmlspecialchars($m['date_marathon']); ?>" data-city="<?php echo htmlspecialchars($m['region_marathon']); ?>">⏳</div></div>
                    </div>
                    <div class="meta-row">
                        <div class="icon">🎟️</div>
                        <div>
                            <div class="label">Places disponibles</div>
                            <div class="value" style="color:<?php echo $m['nb_places_dispo']>0?'#0f766e':'#e76f51'; ?>">
                                <?php echo $m['nb_places_dispo']>0 ? '✅ '.$m['nb_places_dispo'].' places' : '❌ Complet'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="price-block">
                <div class="price-label">Prix d'inscription</div>
                <div class="price-val"><?php echo number_format($m['prix_marathon'],2); ?> TND</div>
            </div>
        </div>
        <div class="detail-img">
            <img src="images/hero_runner.png" alt="<?php echo htmlspecialchars($m['nom_marathon']); ?>" onerror="this.src='images/img1.svg'">
            <span class="img-id">#<?php echo $m['id_marathon']; ?></span>
            <span class="places-badge <?php echo $m['nb_places_dispo']>0?'places-ok':'places-no'; ?>">
                <?php echo $m['nb_places_dispo']>0 ? '✅ '.$m['nb_places_dispo'].' places' : '❌ Complet'; ?>
            </span>
        </div>
    </div>

    <!-- PARCOURS SECTION -->
    <div class="section-h" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:12px;">
            <h2>🗺️ Parcours</h2>
            <span class="count" id="parcoursCount"><?php
                $allParcoursDuMarathon = array_values(array_filter($pCtrl->afficherParcours(), fn($p) => $p['id_marathon'] == $id));
                echo count($allParcoursDuMarathon);
            ?></span>
        </div>
        <?php if ($role === 'participant' && $userId && !empty($allParcoursDuMarathon)): ?>
        <button class="btn-ia-reco" onclick="openIaModal()" style="font-size:.83rem;padding:8px 16px;">
            ⚡ Meilleur parcours pour moi
        </button>
        <?php endif; ?>
    </div>

    <!-- Parcours search & filter — AJAX like marathons -->
    <div class="parcours-filter" id="parcoursFilterBar">
        <div class="p-search-wrap">
            <input type="text" id="searchParcours" placeholder="🔍 Rechercher par nom parcours" autocomplete="off">
            <div class="p-autocomplete-list" id="pAutocompleteList"></div>
        </div>
        <select id="diffSelect">
            <option value="">Toutes les difficultés</option>
            <option value="facile">🟢 Facile</option>
            <option value="moyen">🟡 Moyen</option>
            <option value="difficile">🔴 Difficile</option>
        </select>
    </div>

    <?php if ($role === 'organisateur'): ?>
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;justify-content:flex-end;">
        <a href="parcours/addParcours.php?marathon_id=<?php echo $id; ?>" class="btn btn-primary" style="padding:11px 20px;"><i class="fa-solid fa-plus"></i> Ajouter un parcours</a>
    </div>
    <?php endif; ?>

    <div class="cards-grid" id="parcoursGrid">
        <?php
        // Initial render — same as AJAX response
        if (empty($parcoursDuMarathon)): ?>
            <div class="empty-box">🗺️ Aucun parcours trouvé pour ce marathon.</div>
        <?php else:
            foreach ($parcoursDuMarathon as $p):
                $diffRaw = strtolower(trim($p['difficulte'] ?? ''));
                $dc = ['facile'=>'diff-facile','moyen'=>'diff-moyen','difficile'=>'diff-difficile'][$diffRaw] ?? 'diff-moyen';
                $dl = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$diffRaw] ?? (!empty($p['difficulte']) ? htmlspecialchars($p['difficulte']) : 'Non défini');
        ?>
        <div class="p-card">
            <div class="diff-band <?php echo $dc; ?>"><?php echo $dl; ?></div>
            <div class="p-body">
                <h3><?php echo htmlspecialchars($p['nom_parcours']); ?></h3>
                <div class="p-route">
                    <span>📍 <strong>Départ :</strong> <?php echo htmlspecialchars($p['point_depart']); ?></span>
                    <span>🏁 <strong>Arrivée :</strong> <?php echo htmlspecialchars($p['point_arrivee']); ?></span>
                    <?php if (!empty($p['heure_depart'])): ?>
                    <span>🕐 <strong>Heure de départ :</strong> <?php echo htmlspecialchars(substr($p['heure_depart'], 0, 5)); ?></span>
                    <?php endif; ?>
                </div>
                <div class="dist-row">
                    <div>
                        <div class="dist-val"><?php echo number_format((float)$p['distance'], 2); ?> km</div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;flex-grow:1;margin-top:10px;">
                        <a href="detailParcours.php?id=<?php echo $p['id_parcours']; ?>"
                           style="background:linear-gradient(135deg,#149184,#0eb19d);color:white;padding:6px 15px;border-radius:20px;text-decoration:none;font-weight:bold;font-size:0.85rem;display:flex;align-items:center;gap:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                           Voir détail <span style="font-size:1.1rem;">→</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php if ($role === 'organisateur' || $role === 'admin'): ?>
            <div class="p-actions" style="padding:12px 16px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
                <?php if ($role === 'organisateur'): ?>
                <a href="parcours/updateParcours.php?id=<?php echo $p['id_parcours']; ?>&redirect_marathon=<?php echo $id; ?>" class="btn-mod" style="flex:1;text-align:center;padding:9px 8px;"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>
                <?php endif; ?>
                <button class="btn-del-card" style="flex:1;padding:9px 8px;" onclick="confirmDeleteParcours(<?php echo $p['id_parcours']; ?>, '<?php echo addslashes($p['nom_parcours']); ?>')"><i class="fa-solid fa-trash"></i> Supprimer</button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <?php // IA button is now inline with Parcours heading above ?>
</div>

<!-- MODAL SUPPRESSION PARCOURS -->
<div class="modal-overlay" id="delParcoursModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <h3>Confirmer la suppression</h3>
        <p id="delParcoursMsg"></p>
        <div class="modal-btns">
            <button class="btn-confirm-del" id="delParcoursConfirm">Oui, supprimer</button>
            <button class="btn-cancel" onclick="document.getElementById('delParcoursModal').classList.remove('open')">Annuler</button>
        </div>
    </div>
</div>

<script>
// ── AJAX parcours search + autocomplete ──────────────────────────────────────
(function(){
    var MARATHON_ID = <?php echo (int)$id; ?>;
    var searchInput  = document.getElementById('searchParcours');
    var diffSelect   = document.getElementById('diffSelect');
    var grid         = document.getElementById('parcoursGrid');
    var countBadge   = document.getElementById('parcoursCount');
    var autoList     = document.getElementById('pAutocompleteList');

    var debounceTimer = null;
    var selectedIndex = -1;
    var currentSuggestions = [];

    function fetchCards() {
        var search = searchInput.value.trim();
        var diff   = diffSelect.value;
        fetch('search_parcours.php?mode=cards&id=' + MARATHON_ID +
              '&search=' + encodeURIComponent(search) +
              '&difficulte=' + encodeURIComponent(diff))
            .then(function(r){ return r.json(); })
            .then(function(data){
                grid.innerHTML = data.html;
                countBadge.textContent = data.count;
            })
            .catch(console.error);
    }

    function fetchSuggestions(val) {
        if (!val) { closeAuto(); return; }
        fetch('search_parcours.php?mode=suggestions&id=' + MARATHON_ID +
              '&search=' + encodeURIComponent(val))
            .then(function(r){ return r.json(); })
            .then(function(names){
                currentSuggestions = names;
                selectedIndex = -1;
                if (!names.length) { closeAuto(); return; }
                autoList.innerHTML = names.map(function(n, i){
                    return '<div class="p-auto-item" data-i="' + i + '">' + esc(n) + '</div>';
                }).join('');
                autoList.classList.add('open');
                autoList.querySelectorAll('.p-auto-item').forEach(function(el){
                    el.addEventListener('mousedown', function(e){
                        e.preventDefault();
                        searchInput.value = currentSuggestions[+this.dataset.i];
                        closeAuto();
                        fetchCards();
                    });
                });
            })
            .catch(console.error);
    }

    function closeAuto() {
        autoList.classList.remove('open');
        autoList.innerHTML = '';
        currentSuggestions = [];
        selectedIndex = -1;
    }

    function esc(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    searchInput.addEventListener('input', function(){
        var val = this.value.trim();
        clearTimeout(debounceTimer);
        clearTimeout(searchInput._sugTimer);
        searchInput._sugTimer = setTimeout(function(){ fetchSuggestions(val); }, 200);
        debounceTimer = setTimeout(fetchCards, 450);
    });

    searchInput.addEventListener('keydown', function(e){
        var items = autoList.querySelectorAll('.p-auto-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0) {
                e.preventDefault();
                searchInput.value = currentSuggestions[selectedIndex];
                closeAuto(); fetchCards(); return;
            }
            clearTimeout(debounceTimer); closeAuto(); fetchCards(); return;
        } else if (e.key === 'Escape') { closeAuto(); return; }
        items.forEach(function(el, i){ el.classList.toggle('selected', i === selectedIndex); });
    });

    searchInput.addEventListener('blur', function(){ setTimeout(closeAuto, 150); });

    diffSelect.addEventListener('change', function(){
        searchInput.value = '';
        closeAuto();
        fetchCards();
    });
})();

// ── Delete parcours modal ─────────────────────────────────────────────────────
function confirmDeleteParcours(id, nom) {
    document.getElementById('delParcoursMsg').textContent = 'Supprimer le parcours "' + nom + '" ?';
    document.getElementById('delParcoursConfirm').onclick = function() {
        window.location.href = 'parcours/deleteParcours.php?id=' + id + '&marathon_id=<?php echo $id; ?>';
    };
    document.getElementById('delParcoursModal').classList.add('open');
}
document.getElementById('delParcoursModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
// ── Météo du marathon ─────────────────────────────────────────────────────────
(async function() {
    const el = document.getElementById('detailMeteo');
    if (!el) return;
    const city = el.dataset.city;
    const dateStr = el.dataset.date;
    if (!city || !dateStr) { el.textContent=''; return; }
    try {
        // Pour les marathons multi-régions (ex: "Tunis-Ariana"), prendre la première région
        const cityFirst = city.split('-')[0].trim();
        const geoResp = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(cityFirst)}&count=1&language=fr&format=json`);
        const geoData = await geoResp.json();
        if (!geoData.results?.length) { el.textContent=''; return; }
        const {latitude:lat, longitude:lon} = geoData.results[0];
        const wResp = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,precipitation_sum,windspeed_10m_max&timezone=auto&start_date=${dateStr}&end_date=${dateStr}`);
        const wData = await wResp.json();
        if (!wData.daily?.time?.length) { el.textContent=''; return; }
        const temp = Math.round(wData.daily.temperature_2m_max[0]);
        const rain = wData.daily.precipitation_sum[0] || 0;
        const wind = wData.daily.windspeed_10m_max[0] || 0;
        const rainIcon = rain > 20 ? '🌧️' : rain > 5 ? '🌦️' : '☀️';
        let desc;
        if (rain > 20) desc = 'Pluie forte';
        else if (rain > 5) desc = 'Pluie légère';
        else if (temp > 34) desc = 'Très chaud';
        else if (temp > 28) desc = 'Ensoleillé';
        else if (temp < 10) desc = 'Froid vif';
        else desc = 'Beau temps';
        el.textContent = rainIcon + ' ' + temp + '°C — ' + desc;
        el.title = `Température: ${temp}°C | Pluie: ${rain.toFixed(1)}mm | Vent: ${wind.toFixed(0)}km/h`;
        if (rain > 10 || temp > 34) { el.style.color='#b91c1c'; }
        else if (rain > 5 || temp > 28) { el.style.color='#92400e'; }
    } catch(e) { el.textContent=''; }
})();
</script>
<!-- ══════════════════════════════════════════════════════════════════════
     IA MODAL — MEILLEUR PARCOURS POUR MOI
     ══════════════════════════════════════════════════════════════════════ -->
<?php if ($role === 'participant' && $userId && !empty($parcoursDuMarathon)): ?>
<div class="ia-modal-overlay" id="iaModalOverlay" onclick="if(event.target===this)closeIaModal()">
    <div class="ia-modal" id="iaModal">
        <div class="ia-modal-head">
            <div>
                <h2>⚡ Recommandation IA</h2>
                <span>Meilleur parcours pour ton profil</span>
            </div>
            <button class="ia-close-btn" onclick="closeIaModal()">✕</button>
        </div>
        <div class="ia-modal-body" id="iaModalBody">
            <div class="ia-loading">
                <div class="ia-spinner"></div>
                <p style="color:#627d98;font-weight:700;">Analyse en cours...</p>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // ── Participant data from PHP session ─────────────────────────────────
    const USER = {
        age:    <?php echo (int)($user['age']   ?? 0); ?>,
        poids:  <?php echo (float)($user['poids']?? 0); ?>,
        taille: <?php echo (float)($user['taille']??0); ?>,
        nom:    <?php echo json_encode($user['nom_complet'] ?? $user['nom_user'] ?? 'Participant'); ?>
    };

    // ── Parcours data from PHP ────────────────────────────────────────────
    const PARCOURS = <?php
        $parcoursJson = [];
        foreach ($allParcoursDuMarathon as $p) {
            $parcoursJson[] = [
                'id'         => (int)$p['id_parcours'],
                'nom'        => $p['nom_parcours'],
                'depart'     => $p['point_depart'],
                'arrivee'    => $p['point_arrivee'],
                'distance'   => (float)$p['distance'],
                'difficulte' => $p['difficulte'],
            ];
        }
        echo json_encode($parcoursJson);
    ?>;

    // ── AI Scoring Engine ─────────────────────────────────────────────────
    function calcIMC(poids, tailleM) {
        return tailleM > 0 ? poids / (tailleM * tailleM) : 0;
    }

    function imcScore(imc) {
        // IMC → score 0-100 (peak around 22, penalise extremes)
        if (imc < 15)   return 20;
        if (imc < 18.5) return 55;
        if (imc < 25)   return 100;
        if (imc < 30)   return 60;
        return 30;
    }

    function imcLabel(imc) {
        if (imc < 18.5) return { text:'Faible forme', cls:'low' };
        if (imc < 25)   return { text:'Forme normale', cls:'good' };
        if (imc < 30)   return { text:'Effort plus difficile', cls:'mid' };
        return           { text:'Effort très difficile', cls:'low' };
    }

    function enduranceScore(age, imc) {
        let s = 100;
        if (age > 50) s -= 30;
        else if (age > 40) s -= 15;
        else if (age > 30) s -= 5;
        if (imc < 18.5 || imc >= 30) s -= 30;
        else if (imc >= 25) s -= 15;
        return Math.max(10, Math.min(100, s));
    }

    function enduranceLabel(score) {
        if (score >= 70) return { text:'🟢 Bonne endurance',   cls:'good' };
        if (score >= 45) return { text:'🟡 Endurance moyenne', cls:'mid' };
        return            { text:'🔴 Faible endurance',   cls:'low' };
    }

    function participantScore(imcS, endS) {
        return Math.round(0.5 * imcS + 0.5 * endS);
    }

    function difficulteScore(diff) {
        return { facile: 20, moyen: 55, difficile: 90 }[diff] ?? 55;
    }

    function distanceScore(km) {
        // 0-100: 5km→15, 10km→30, 21km→55, 42km→90, >60→100
        return Math.min(100, Math.round(km * 1.8));
    }

    function parcoursScore(p) {
        return Math.round(0.5 * distanceScore(p.distance) + 0.5 * difficulteScore(p.difficulte));
    }

    function compatibilite(partScore, parcScore) {
        return partScore - parcScore;
    }

    function compatLabel(diff) {
        if (diff >= 10)  return { text:'🟢 Recommandé',    cls:'green' };
        if (diff >= -10) return { text:'🟡 Possible',      cls:'yellow' };
        return            { text:'🔴 Déconseillé', cls:'red' };
    }

    function fatigueLabel(pScore, parcScore) {
        const d = pScore - parcScore;
        if (d >= 20) return { text:'Faible 🔋', icon:'💚', vitesse:'Vitesse normale — vous pouvez maintenir le rythme.' };
        if (d >= 0)  return { text:'Moyenne ⚡', icon:'💛', vitesse:'Légèrement ralentir si vous sentez la fatigue augmenter.' };
        return        { text:'Élevée 🔥', icon:'❤️', vitesse:'Ralentir dès le départ, faire des pauses régulières.' };
    }

    function vitesse(partScore) {
        if (partScore >= 70) return '10–12 km/h recommandés';
        if (partScore >= 45) return '7–9 km/h recommandés';
        return '5–6 km/h recommandés';
    }

    // ── Build Result HTML ─────────────────────────────────────────────────
    function buildResult() {
        const taille_m = USER.taille / 100;
        const imc = calcIMC(USER.poids, taille_m);
        const imcS = imcScore(imc);
        const endS = enduranceScore(USER.age, imc);
        const pScore = participantScore(imcS, endS);
        const endLbl = enduranceLabel(endS);
        const imcLbl = imcLabel(imc);
        const imcBarColor = imc < 18.5 ? '#3b82f6' : imc < 25 ? '#10b981' : imc < 30 ? '#f59e0b' : '#ef4444';
        const pScoreColor = pScore >= 70 ? '#10b981' : pScore >= 45 ? '#f59e0b' : '#ef4444';

        const scored = PARCOURS.map(p => {
            const pS = parcoursScore(p);
            const compat = compatibilite(pScore, pS);
            return { ...p, pS, compat };
        });
        scored.sort((a,b) => b.compat - a.compat);
        const best = scored[0];
        const fatigue = fatigueLabel(pScore, best.pS);

        const diffName = { facile:'Facile', moyen:'Moyen', difficile:'Difficile' };

        // Parcours table rows — difficulty always shown, no separator circles
        const tableRows = scored.map((p, i) => {
            const cl = compatLabel(p.compat);
            const rawDiff = (p.difficulte || '').toLowerCase().trim();
            const dName = diffName[rawDiff] ?? (p.difficulte && p.difficulte.trim() !== '' ? p.difficulte : '—');
            const diffColors = {facile:'background:#dcfce7;color:#166534;', moyen:'background:#fef9c3;color:#92400e;', difficile:'background:#fee2e2;color:#991b1b;'};
            const dStyle = diffColors[rawDiff] ?? 'background:#f1f5f9;color:#475569;';
            return `<tr>
                <td style="padding:10px;"><strong>${p.nom}</strong>${i===0?' <span style="font-size:.73rem;background:#dcfce7;color:#166534;border-radius:6px;padding:2px 8px;font-weight:800;margin-left:6px;">⭐ Meilleur</span>':''}</td>
                <td style="padding:10px;">${p.distance} km</td>
                <td style="padding:10px;"><span style="display:inline-block;border-radius:6px;padding:3px 10px;font-size:.8rem;font-weight:800;${dStyle}">${dName}</span></td>
                <td style="padding:10px;"><span class="compat-pill ${cl.cls}">${cl.text}</span></td>
            </tr>`;
        }).join('');

        const bestDiffRaw = (best.difficulte || '').toLowerCase().trim();

        return `
        <!-- ① MEILLEUR PARCOURS -->
        <div style="margin-bottom:20px;">
            <div class="ia-step-label">🏆 PARCOURS RECOMMANDÉ POUR TOI</div>
            <div class="ia-result-card">
                <span class="ia-badge">⭐ Meilleur choix</span>
                <h3>${best.nom}</h3>
                <div class="ia-result-meta">
                    <span class="ia-result-tag">📍 ${best.depart}</span>
                    <span class="ia-result-tag">🏁 ${best.arrivee}</span>
                    <span class="ia-result-tag">📏 ${best.distance} km</span>
                    <span class="ia-result-tag">${diffName[bestDiffRaw] ?? best.difficulte}</span>
                </div>
                <div style="background:#f0fdf4;border-radius:10px;padding:12px 14px;font-size:.87rem;color:#065f46;line-height:1.7;">
                    ✅ <strong>Distance adaptée</strong> à ta condition physique<br>
                    ✅ <strong>Difficulté compatible</strong> avec ton profil
                </div>
            </div>
        </div>

        <!-- ② PROFIL PHYSIQUE — compact, même dimension que simulation -->
        <div style="margin-bottom:20px;">
            <div class="ia-step-label">📊 ANALYSE DE TON PROFIL PHYSIQUE</div>
            <div style="background:#f8fafc;border-radius:16px;border:1px solid #e2e8f0;padding:18px 20px;">
                <!-- 2 valeurs côte à côte : IMC + Endurance -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <!-- IMC -->
                    <div style="text-align:center;background:white;border-radius:12px;padding:14px 10px;border:2px solid ${imcBarColor};">
                        <div style="font-size:1.7rem;font-weight:900;color:${imcBarColor};">${imc.toFixed(1)}</div>
                        <div style="font-size:.72rem;font-weight:800;color:#627d98;text-transform:uppercase;margin-top:3px;">IMC</div>
                        <div style="font-size:.72rem;font-weight:700;color:${imcBarColor};margin-top:4px;">${imcLbl.text}</div>
                    </div>
                    <!-- Endurance -->
                    <div style="text-align:center;background:white;border-radius:12px;padding:14px 10px;border:2px solid ${endS>=70?'#10b981':endS>=45?'#f59e0b':'#ef4444'};">
                        <div style="font-size:1.7rem;font-weight:900;color:${endS>=70?'#10b981':endS>=45?'#f59e0b':'#ef4444'};">${endS}</div>
                        <div style="font-size:.72rem;font-weight:800;color:#627d98;text-transform:uppercase;margin-top:3px;">Endurance</div>
                        <div style="font-size:.72rem;font-weight:700;color:${endS>=70?'#166534':endS>=45?'#92400e':'#991b1b'};margin-top:4px;">${endLbl.text.replace(/🟢|🟡|🔴/g,'').trim()}</div>
                    </div>
                </div>
                <!-- Barre Score IA -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <span style="font-size:.78rem;font-weight:800;color:#102a43;text-transform:uppercase;letter-spacing:.04em;">⚡ Score IA</span>
                    <span style="font-size:1.1rem;font-weight:900;color:${pScoreColor};">${pScore}<span style="font-size:.75rem;color:#94a3b8;font-weight:700;">/100</span></span>
                </div>
                <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
                    <div style="width:${pScore}%;height:100%;border-radius:999px;background:${pScore>=70?'linear-gradient(90deg,#10b981,#34d399)':pScore>=45?'linear-gradient(90deg,#f59e0b,#fbbf24)':'linear-gradient(90deg,#ef4444,#f87171)'};transition:width .6s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:#94a3b8;font-weight:700;margin-top:4px;"><span>0</span><span>50</span><span>100</span></div>
            </div>
        </div>

        <!-- ③ SIMULATION AVANT COURSE -->
        <div style="margin-bottom:20px;">
            <div class="ia-step-label">⚡ SIMULATION AVANT COURSE</div>
            <div class="ia-sim-box" style="margin-bottom:0;">
                <div class="ia-sim-row">
                    <span class="sim-icon">${fatigue.icon}</span>
                    <span class="sim-lbl">Fatigue estimée :</span>
                    <span class="sim-val">${fatigue.text}</span>
                </div>
                <div class="ia-sim-row">
                    <span class="sim-icon">🏃</span>
                    <span class="sim-lbl">Vitesse conseillée :</span>
                    <span class="sim-val">${vitesse(pScore)}</span>
                </div>
                <div class="ia-sim-row">
                    <span class="sim-icon">💧</span>
                    <span class="sim-lbl">Hydratation :</span>
                    <span class="sim-val">Toutes les 15–20 min</span>
                </div>
                <div style="margin-top:12px;padding:10px 14px;background:rgba(245,158,11,.1);border-radius:10px;font-size:.84rem;color:#92400e;font-weight:700;">
                    💡 ${fatigue.vitesse}
                </div>
            </div>
        </div>

        <!-- ④ TOUS LES PARCOURS -->
        <div>
            <div class="ia-step-label">📊 TOUS LES PARCOURS CLASSÉS POUR TOI</div>
            <div style="overflow-x:auto;border-radius:14px;border:1px solid #e2e8f0;">
                <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
                    <thead><tr style="background:#f1f5f9;">
                        <th style="padding:10px;text-align:left;font-size:.8rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Parcours</th>
                        <th style="padding:10px;text-align:left;font-size:.8rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Distance</th>
                        <th style="padding:10px;text-align:left;font-size:.8rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Difficulté</th>
                        <th style="padding:10px;text-align:left;font-size:.8rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Compatibilité</th>
                    </tr></thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </div>
        </div>
        `;
    }

    // ── Modal open/close ──────────────────────────────────────────────────
    window.openIaModal = function() {
        const overlay = document.getElementById('iaModalOverlay');
        overlay.classList.add('open');
        // Show result (computed client-side, no spinner delay needed)
        setTimeout(function() {
            document.getElementById('iaModalBody').innerHTML = buildResult();
        }, 400);
    };

    window.closeIaModal = function() {
        document.getElementById('iaModalOverlay').classList.remove('open');
        // Reset to loader for next open
        setTimeout(function() {
            document.getElementById('iaModalBody').innerHTML =
                '<div class="ia-loading"><div class="ia-spinner"></div><p style="color:#627d98;font-weight:700;">Analyse en cours...</p></div>';
        }, 300);
    };

})();
</script>
<?php endif; ?>

</body>
</html>
