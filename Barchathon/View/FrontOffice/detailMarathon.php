<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();

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

        .section-h { display:flex; align-items:center; gap:12px; margin:0 0 16px; flex-wrap:wrap; }
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
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="listMarathons.php">← Retour au catalogue</a>

    <!-- INSCRIPTION BANNER - TOP -->
    <?php if ($role === 'visiteur'): ?>
    <div class="inscription-banner visitor">
        <div class="insc-text">
            <h3>🏆 Prêt(e) à participer ?</h3>
            <p>Connectez-vous pour vous inscrire à <?php echo htmlspecialchars($m['nom_marathon']); ?>.</p>
        </div>
        <a href="#p" class="btn-login-insc">Participer</a>
    </div>
    <?php elseif ($role === 'participant'): ?>
    <div class="inscription-banner participant">
        <div class="insc-text">
            <h3>🏆 Prêt(e) à participer ?</h3>
            <p>Rejoignez les coureurs et inscrivez-vous dès maintenant à <?php echo htmlspecialchars($m['nom_marathon']); ?>.</p>
        </div>
        <?php if ($m['nb_places_dispo'] > 0): ?>
            <a href="register.php" class="btn-inscription">Participer</a>
        <?php else: ?>
            <span class="btn-inscription-disabled">❌ Marathon complet</span>
        <?php endif; ?>
    </div>
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
                        <div><div class="label">Date</div><div class="value"><?php echo date('d/m/Y', strtotime($m['date_marathon'])); ?></div></div>
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
            <img src="<?php echo htmlspecialchars($m['image_marathon']); ?>" alt="<?php echo htmlspecialchars($m['nom_marathon']); ?>" onerror="this.src='images/img1.svg'">
            <span class="img-id">#<?php echo $m['id_marathon']; ?></span>
            <span class="places-badge <?php echo $m['nb_places_dispo']>0?'places-ok':'places-no'; ?>">
                <?php echo $m['nb_places_dispo']>0 ? '✅ '.$m['nb_places_dispo'].' places' : '❌ Complet'; ?>
            </span>
        </div>
    </div>

    <!-- PARCOURS SECTION -->
    <div class="section-h">
        <h2>🗺️ Parcours</h2>
        <span class="count" id="parcoursCount"><?php
            $allParcoursDuMarathon = array_values(array_filter($pCtrl->afficherParcours(), fn($p) => $p['id_marathon'] == $id));
            echo count($allParcoursDuMarathon);
        ?></span>
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
                $dc = ['facile'=>'diff-facile','moyen'=>'diff-moyen','difficile'=>'diff-difficile'][$p['difficulte']]??'diff-moyen';
                $dl = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$p['difficulte']]??$p['difficulte'];
        ?>
        <div class="p-card">
            <div class="diff-band <?php echo $dc; ?>"><?php echo $dl; ?></div>
            <div class="p-body">
                <h3><?php echo htmlspecialchars($p['nom_parcours']); ?></h3>
                <div class="p-route">
                    <span>📍 <strong>Départ :</strong> <?php echo htmlspecialchars($p['point_depart']); ?></span>
                    <span>🏁 <strong>Arrivée :</strong> <?php echo htmlspecialchars($p['point_arrivee']); ?></span>
                </div>
                <div class="dist-row">
                    <div>
                        <div class="dist-val"><?php echo number_format((float)$p['distance'], 2); ?> km</div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;flex-grow:1;margin-top:10px;">
                        <a href="details.php?id=<?php echo $p['id_parcours']; ?>"
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
</script>
</body>
</html>
