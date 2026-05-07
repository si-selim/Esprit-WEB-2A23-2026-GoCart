<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../../Model/Parcours.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    header('Location: ../../FrontOffice/login.php'); exit;
}

$parcoursController = new ParcoursController();
$marathonController = new MarathonController();
$marathons = $marathonController->afficherMarathon();
$error = '';
$preselectedMarathon = isset($_GET['marathon_id']) ? (int)$_GET['marathon_id'] : 0;

$marathonRegion = 'Tunis';
if ($preselectedMarathon > 0) {
    foreach ($marathons as $m) {
        if ($m['id_marathon'] == $preselectedMarathon) {
            $marathonRegion = $m['region_marathon'] ?? 'Tunis';
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = $_POST['nom_parcours'] ?? '';
    $depart      = $_POST['point_depart'] ?? '';
    $arrivee     = $_POST['point_arrivee'] ?? '';
    $distance    = (float)($_POST['distance'] ?? 0);
    $difficulte  = $_POST['difficulte'] ?? '';
    $id_marathon = (int)($_POST['id_marathon'] ?? 0);
    $heure_depart = $_POST['heure_depart'] ?? null;
    if ($nom !== '' && $depart !== '' && $arrivee !== '' && $distance > 0 && $id_marathon > 0 && $heure_depart !== null && $heure_depart !== '') {
        $p = new Parcours(null, $nom, $depart, $arrivee, $distance, $difficulte, $id_marathon, $heure_depart ?: null);
        if ($parcoursController->ajouterParcours($p)) {
            header('Location: ../../FrontOffice/detailMarathon.php?id=' . $id_marathon); exit;
        } else { $error = "Erreur lors de l'ajout du parcours."; }
    } else { $error = "Données invalides. Veuillez remplir tous les champs obligatoires."; }
}

$currentPage = 'catalogue';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Ajouter un Parcours — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <style>
        :root{--ink:#102a43;--teal:#0f766e;--sun:#ffb703;--bg:#f4fbfb;--sand:#fff8e7}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:"Segoe UI",sans-serif;color:var(--ink);background:linear-gradient(180deg,var(--sand),var(--bg));min-height:100vh;font-size:1rem}
        .wrap{width:min(1700px,calc(100% - 32px));margin:0 auto;padding:22px 0 60px}
        .back-link{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--teal);font-weight:700;margin-bottom:16px;padding:9px 16px;background:white;border-radius:12px;box-shadow:0 4px 12px rgba(16,42,67,.07);font-size:.95rem}

        /* ── LAYOUT ── */
        .split-layout{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:stretch;min-height:800px}
        @media(max-width:1000px){.split-layout{grid-template-columns:1fr}}
        .panel{background:#fff;border-radius:24px;padding:30px 32px;box-shadow:0 12px 36px rgba(16,42,67,.08);border:1px solid rgba(16,42,67,.08);display:flex;flex-direction:column}

        /* ── FORM PANEL ── */
        .panel-header{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #e6edf3}
        .panel-icon{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#059669,#10b981);display:grid;place-items:center;font-size:1.3rem;flex-shrink:0}
        h1{font-size:1.5rem}
        .lead{color:#627d98;line-height:1.6;margin-bottom:18px;font-size:.95rem}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .field{display:grid;gap:5px;position:relative}
        .field.full{grid-column:1/-1}
        label{font-weight:700;font-size:.97rem}
        input,select{width:100%;border-radius:11px;border:1px solid #cbd5e1;padding:11px 14px;font:inherit;outline:none!important;font-size:.97rem;background:#fff!important;color:var(--ink)!important;transition:border .15s;}
        input:focus,select:focus{outline:none!important;border-color:#cbd5e1!important;box-shadow:none!important;}
        input:focus-visible,select:focus-visible{outline:none!important;box-shadow:none!important;}
        *:focus{outline:none!important;}
        input:-webkit-autofill,input:-webkit-autofill:hover,input:-webkit-autofill:focus{-webkit-box-shadow:0 0 0 40px white inset!important;-webkit-text-fill-color:var(--ink)!important;}
        /* Désactiver l'icône interdit sur les champs auto */
        input[readonly],select:disabled{cursor:default!important}
        .feedback{font-size:.88rem;min-height:16px}
        .feedback.success{color:#16a34a}
        .feedback.error{color:#dc2626}
        .error-box{background:#fef2f2;border:1px solid #fecaca;border-radius:11px;padding:11px 13px;margin-bottom:14px;color:#b42318;font-size:.95rem}
        .marathon-badge{background:#f0fff4;border:1px solid #c6f6d5;border-radius:11px;padding:10px 13px;color:#0f766e;font-weight:700;font-size:.97rem}

        .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:auto;padding-top:18px;align-items:center}
        .btn{text-decoration:none;border:0;border-radius:11px;padding:13px 26px;font-weight:700;cursor:pointer;font-size:1rem;display:inline-flex;align-items:center;gap:7px;transition:transform .12s,box-shadow .12s,opacity .12s;}
        .btn:active{transform:scale(0.96);}
        .btn-primary{background:linear-gradient(135deg,var(--teal),#14b8a6);color:white;box-shadow:0 4px 14px rgba(15,118,110,.28);}
        .btn-primary:hover{box-shadow:0 6px 20px rgba(15,118,110,.38);transform:translateY(-1px);}
        .btn-secondary{background:#edf2f7;color:var(--ink);box-shadow:0 2px 8px rgba(16,42,67,.08);}
        .btn-secondary:hover{background:#e2e8f0;transform:translateY(-1px);}

        /* ── MAP PANEL ── */
        .map-panel-header{display:flex;align-items:center;gap:12px;margin-bottom:8px}
        .map-panel-icon{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#0f766e,#14b8a6);display:grid;place-items:center;font-size:1.3rem;flex-shrink:0}
        .city-badge{display:inline-flex;align-items:center;gap:6px;background:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:4px 11px;font-size:.85rem;font-weight:700;color:#0f766e;margin-bottom:9px}
        .map-toolbar{display:flex;gap:8px;margin-bottom:9px;flex-wrap:wrap;align-items:center;justify-content:flex-start;}
        .map-toolbar .map-btn-nav{margin-left:auto !important;}
        .map-btn{border:none;border-radius:9px;padding:8px 14px;font-size:.88rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:opacity .15s}
        .map-btn:hover{opacity:.85}
        .map-btn-undo{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
        .map-btn-reset{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}

        /* Temps estimé — EN BAS du panneau carte */
        .map-time-bar{display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#f0fdfa,#e0fdf4);border:1px solid #a7f3d0;border-radius:12px;padding:11px 16px;margin-top:12px;}
        .map-time-bar .time-label{font-size:.88rem;color:#0f766e;font-weight:700;display:flex;align-items:center;gap:6px}
        .map-time-value{font-size:1.3rem;font-weight:900;color:#065f46;letter-spacing:.02em}
        .map-time-waiting{font-size:.88rem;color:#94a3b8;font-style:italic}

        #parcours-map{width:100%;flex:1;min-height:480px;border-radius:14px;border:1px solid #e2e8f0}
        .map-body{display:flex;flex-direction:column;flex:1;gap:0}
        #map-instructions{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:9px 13px;font-size:.9rem;margin-top:9px;min-height:36px}
        #smart-panel{display:none;margin-top:10px}

        /* Difficulty color */
        select#difficulte.diff-facile { background:#f0fdf4!important; border-color:#86efac!important; color:#15803d!important; font-weight:700!important; }
        select#difficulte.diff-moyen  { background:#fefce8!important; border-color:#fde047!important; color:#a16207!important; font-weight:700!important; }
        select#difficulte.diff-difficile { background:#fef2f2!important; border-color:#fca5a5!important; color:#b91c1c!important; font-weight:700!important; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../../FrontOffice/partials/topbar.php'; ?>
<div class="wrap">
    <a class="back-link" href="<?php echo $preselectedMarathon ? '../../FrontOffice/detailMarathon.php?id='.$preselectedMarathon : '../../FrontOffice/listMarathons.php'; ?>">← Retour</a>
    <div class="split-layout">

      <!-- ══ PANNEAU FORMULAIRE ══ -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-icon">🗺️</div>
          <div><h1>Ajouter un Parcours</h1></div>
        </div>
        <p class="lead">Tapez un lieu dans <strong>Point de Départ</strong> ou <strong>Point d'Arrivée</strong> — les suggestions apparaissent selon la région du marathon. Sélectionnez un lieu pour le placer sur la carte.</p>
        <?php if ($error): ?><div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST" novalidate>
          <div class="grid">
            <div class="field full">
              <label>Marathon associé</label>
              <?php if ($preselectedMarathon > 0):
                $mNom='';
                foreach ($marathons as $m) { if ($m['id_marathon']==$preselectedMarathon){$mNom=$m['nom_marathon'];break;} }
              ?>
              <div class="marathon-badge"><?php echo htmlspecialchars($preselectedMarathon.' - '.$mNom); ?></div>
              <input type="hidden" id="id_marathon" name="id_marathon" value="<?php echo $preselectedMarathon; ?>">
              <?php else: ?>
              <select id="id_marathon" name="id_marathon" onchange="onMarathonChange(this)">
                <option value="">— Choisir un marathon —</option>
                <?php foreach ($marathons as $m): ?>
                  <option value="<?php echo $m['id_marathon']; ?>" data-region="<?php echo htmlspecialchars($m['region_marathon']??'Tunis'); ?>">
                    <?php echo htmlspecialchars($m['id_marathon'].' - '.$m['nom_marathon']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="feedback" id="marathonFeedback"></div>
              <?php endif; ?>
            </div>

            <div class="field">
              <label for="nom_parcours">Nom du Parcours</label>
              <input type="text" id="nom_parcours" name="nom_parcours" placeholder="Ex: Parcours Nord de Tunis">
              <div class="feedback" id="nomFeedback"></div>
            </div>
            <div class="field">
              <label for="heure_depart">Heure de Départ</label>
              <input type="time" id="heure_depart" name="heure_depart" required>
              <div class="feedback" id="heureFeedback"></div>
            </div>

            <div class="field">
              <label for="point_depart">📍 Point de Départ</label>
              <input type="text" id="point_depart" name="point_depart" placeholder="Tapez un lieu…" autocomplete="off">
              <div class="feedback" id="departFeedback"></div>
            </div>
            <div class="field">
              <label for="point_arrivee">🏁 Point d'Arrivée</label>
              <input type="text" id="point_arrivee" name="point_arrivee" placeholder="Tapez un lieu…" autocomplete="off">
              <div class="feedback" id="arriveeFeedback"></div>
            </div>

            <div class="field">
              <label for="distance">Distance (km)</label>
              <input type="number" id="distance" name="distance" min="0.1" step="0.01" placeholder="Ex: 10.5">
              <div class="feedback" id="distanceFeedback"></div>
            </div>
            <div class="field">
              <label for="difficulte">Difficulté</label>
              <select id="difficulte" name="difficulte">
                <option value="">— Choisir —</option>
                <option value="facile">🟢 Facile</option>
                <option value="moyen">🟡 Moyen</option>
                <option value="difficile">🔴 Difficile</option>
              </select>
              <div class="feedback" id="difficulteFeedback"></div>
            </div>
          </div>


          <!-- BOUTON IA : GÉNÉRER TRAJET -->
          <div style="margin-top:18px;margin-bottom:14px;">
            <button type="button" id="btn-generer-trajet" onclick="lancerGenerationTrajet()" style="
              display:inline-flex;align-items:center;gap:9px;
              background:linear-gradient(135deg,#1d4ed8,#3b82f6);
              color:#fff;border:none;border-radius:11px;
              padding:13px 24px;font-weight:800;font-size:1rem;cursor:pointer;
              box-shadow:0 4px 16px rgba(59,130,246,.4);
              transition:transform .12s,box-shadow .15s;
              letter-spacing:.01em;
            "
            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 22px rgba(59,130,246,.5)';"
            onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(59,130,246,.4)';">
              <span style="font-size:1.1em">✨</span> Générer trajet
            </button>
          </div>

          <!-- BOUTONS ENREGISTRER / ANNULER EN BAS -->
          <hr style="border:none;border-top:1px solid #e2e8f0;margin:4px 0 14px;">
          <div class="actions" style="justify-content:flex-start;">
              <button type="submit" class="btn btn-primary">✅ Enregistrer</button>
              <a href="<?php echo $preselectedMarathon ? '../../FrontOffice/detailMarathon.php?id='.$preselectedMarathon : '../../FrontOffice/listMarathons.php'; ?>" class="btn btn-secondary">✖ Annuler</a>
          </div>
        </form>
      </div>

      <!-- ══ PANNEAU CARTE ══ -->
      <div class="panel">
        <div class="map-panel-header">
          <div class="map-panel-icon">🗺️</div>
          <div>
            <div style="font-size:1.08rem;font-weight:800;color:var(--ink);">Carte du Parcours</div>
            <div style="font-size:.85rem;color:#64748b;margin-top:1px;">Synchronisation bidirectionnelle · formulaire ↔ carte</div>
          </div>
        </div>

        <div class="city-badge" id="city-badge">
          <i class="fa-solid fa-location-dot"></i>
          <span id="city-name"><?php echo htmlspecialchars($marathonRegion); ?></span>
        </div>
        <div class="map-toolbar">
          <button type="button" class="map-btn map-btn-undo" onclick="undoLastPoint()">↩️ Annuler dernier</button>
          <button type="button" class="map-btn map-btn-reset" onclick="resetMap()">🗑️ Réinitialiser</button>
        </div>
        <div class="map-body">
          <div id="parcours-map"></div>
          <div id="map-instructions"></div>
          <div id="smart-panel"></div>
        </div>

        <!-- TEMPS ESTIMÉ — en bas du panneau carte -->
        <div class="map-time-bar">
          <div class="time-label"><i class="fa-regular fa-clock"></i> Temps estimé (10 km/h)</div>
          <div>
            <span class="map-time-value" id="map-time-display">—</span>
            <span class="map-time-waiting" id="map-time-waiting"> En attente du tracé…</span>
          </div>
        </div>
      </div>

    </div><!-- /split-layout -->
</div>


<script>
const MARATHON_REGION = <?php echo json_encode($marathonRegion); ?>;
function onMarathonChange(sel){
    const opt=sel.options[sel.selectedIndex];
    const region=opt.getAttribute('data-region')||'Tunis';
    document.getElementById('city-name').textContent=region;
    resetMap();
    initMap(region);
}
document.addEventListener('DOMContentLoaded',function(){ initMap(MARATHON_REGION); });
</script>
<script src="parcours_map.js"></script>
<script src="addParcours.js"></script>
<script src="generateTrajet.js"></script>
<script>
(function(){
    const diffSel = document.getElementById('difficulte');
    if (!diffSel) return;
    function updateDiffColor() {
        diffSel.className = diffSel.className.replace(/\bdiff-\S+/g,'').trim();
        const v = diffSel.value;
        if (v === 'facile') diffSel.classList.add('diff-facile');
        else if (v === 'moyen') diffSel.classList.add('diff-moyen');
        else if (v === 'difficile') diffSel.classList.add('diff-difficile');
    }
    diffSel.addEventListener('change', updateDiffColor);
    updateDiffColor();
})();
</script>
</body>
</html>
