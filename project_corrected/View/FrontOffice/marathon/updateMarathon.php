<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    header('Location: ../../FrontOffice/login.php'); exit;
}

$controller = new MarathonController();
$error = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ../../FrontOffice/listMarathons.php'); exit; }

$data = $controller->showMarathon($id);
if (!$data) { header('Location: ../../FrontOffice/listMarathons.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $TUNISIA_REGIONS_PHP = ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Kef','Mahdia','Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'];
    $nom    = trim($_POST['nom_marathon'] ?? '');
    $org    = trim($_POST['organisateur_marathon'] ?? $data['organisateur_marathon']);
    $region = trim($_POST['region_marathon'] ?? '');
    $date   = $_POST['date_marathon'] ?? '';
    $places = (int)($_POST['nb_places_dispo'] ?? 0);
    $prix   = (float)($_POST['prix_marathon'] ?? 0);

    // Support plusieurs régions séparées par tiret ou virgule
    $regionParts = array_filter(array_map('trim', preg_split('/[-,]/', $region)));
    $validParts = []; $invalidPart = null;
    foreach ($regionParts as $part) {
        $found = null;
        foreach ($TUNISIA_REGIONS_PHP as $r) { if (mb_strtolower($r) === mb_strtolower($part)) { $found = $r; break; } }
        if ($found) { $validParts[] = $found; } else { $invalidPart = $part; break; }
    }
    $regionValid = (!empty($validParts) && $invalidPart === null) ? implode('-', $validParts) : null;

    if ($nom !== '' && $regionValid && $date !== '') {
        $image = $data['image_marathon'];
        if (!empty($_FILES['image_marathon']['name']) && $_FILES['image_marathon']['error'] === 0) {
            $newImage = $controller->saveUploadedImage($_FILES['image_marathon']);
            if ($newImage) { $image = $newImage; }
        }

        $m = new Marathon($id, $nom, $image, $org, $regionValid, $date, $places, $prix);
        $controller->modifierMarathon($m, $id);
        header('Location: ../../FrontOffice/listMarathons.php');
        exit;
    } else {
        if (!$regionValid && $region !== '') {
            $error = "La région \"" . htmlspecialchars($region) . "\" n'est pas un gouvernorat valide de Tunisie.";
        } else {
            $error = "Veuillez remplir tous les champs obligatoires.";
        }
    }
}

$currentPage = 'catalogue';
$currentUser = getCurrentUser();
$role = $currentUser['role'] ?? 'visiteur';
$todayStr = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Modifier Marathon #<?php echo $id; ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --sand:#fff8e7; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,var(--sand),var(--bg)); }
        .wrap { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:32px 0 48px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }
        .panel { background:#fff; border-radius:28px; padding:32px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .panel-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; padding-bottom:20px; border-bottom:1px solid #e6edf3; }
        .panel-icon { width:52px; height:52px; border-radius:18px; background:linear-gradient(135deg,#64748b,#94a3b8); display:grid; place-items:center; font-size:1.4rem; flex-shrink:0; }
        h1 { font-size:1.9rem; }
        .error-box { background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:14px; margin-bottom:20px; color:#b42318; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
        .field { display:grid; gap:8px; }
        .field.full { grid-column:1/-1; }
        label { font-weight:700; font-size:0.95rem; display:flex; align-items:center; gap:6px; }
        input, select { width:100%; border-radius:14px; border:1px solid #cbd5e1; padding:12px 14px; font:inherit; font-size:0.97rem; outline:none !important; transition:border .15s; background:#fff !important; }
        input:focus, select:focus { outline:none !important; border-color:#cbd5e1 !important; box-shadow:none !important; }
        input:focus-visible, select:focus-visible { outline:none !important; box-shadow:none !important; }
        *:focus { outline:none !important; }
        .feedback { font-size:0.85rem; min-height:18px; margin-top:2px; }
        .feedback.error { color:#dc2626; }
        .feedback.success { color:#16a34a; }

        /* ─── Preview image ─── */
        .img-preview-wrap {
            display:flex; align-items:center; gap:16px;
            padding:16px; background:#f8fafc; border-radius:14px;
            margin-top:8px; border:1px solid #e2e8f0;
        }
        .img-preview-wrap img {
            width:160px; height:100px; object-fit:cover;
            border-radius:12px; border:2px solid #e2e8f0;
            flex-shrink:0; background:#f1f5f9;
        }
        .img-preview-info { display:flex; flex-direction:column; gap:4px; }
        .img-preview-info .img-name { font-size:0.9rem; color:#627d98; }
        .img-preview-info .img-badge { display:inline-flex; align-items:center; gap:4px; font-size:0.82rem; font-weight:700; color:#0f766e; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:3px 10px; width:fit-content; }

        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 26px; font-weight:700; cursor:pointer; font-size:1rem; display:inline-flex; align-items:center; gap:7px; transition:transform .12s, box-shadow .12s, opacity .12s; }
        .btn:active { transform:scale(0.96); }
        .btn-save { background:linear-gradient(135deg,#0f766e,#14b8a6); color:white; box-shadow:0 4px 14px rgba(15,118,110,.28); }
        .btn-save:hover { box-shadow:0 6px 20px rgba(15,118,110,.38); transform:translateY(-1px); }
        .btn-secondary { background:#edf2f7; color:var(--ink); box-shadow:0 2px 8px rgba(16,42,67,.08); }
        .btn-secondary:hover { background:#e2e8f0; transform:translateY(-1px); }

        /* ─── MÉTÉO STYLES ─── */
        #meteoZone { display:none; margin-top:24px; }
        .meteo-loading { display:flex; align-items:center; gap:10px; padding:16px; background:#f8fafc; border-radius:16px; color:#64748b; font-weight:600; }
        .spinner { width:20px; height:20px; border:3px solid #e2e8f0; border-top-color:var(--teal); border-radius:50%; animation:spin 0.8s linear infinite; flex-shrink:0; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .meteo-result { border:1px solid #b7e3f5; border-radius:20px; padding:20px; }
        .meteo-header { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
        .meteo-icon-big { font-size:2.4rem; }
        .meteo-title { font-size:1.1rem; font-weight:800; color:#0f766e; }
        .meteo-subtitle { font-size:0.85rem; color:#64748b; }
        .meteo-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(110px,1fr)); gap:10px; margin-bottom:14px; }
        .meteo-cell { background:white; border-radius:14px; padding:12px 10px; text-align:center; box-shadow:0 4px 12px rgba(16,42,67,.06); border:1px solid rgba(16,42,67,.06); }
        .meteo-cell .val { font-size:1.3rem; font-weight:900; color:#0f766e; }
        .meteo-cell .lbl { font-size:0.72rem; color:#94a3b8; font-weight:700; text-transform:uppercase; margin-top:3px; }
        .meteo-alert { background:linear-gradient(135deg,#fff7ed,#fef2f2); border:1px solid #fca5a5; border-radius:16px; padding:16px 18px; }
        .meteo-alert-title { font-weight:800; color:#b91c1c; font-size:1rem; margin-bottom:8px; }
        .meteo-alert p { font-size:0.92rem; color:#7f1d1d; line-height:1.6; }
        .meteo-alert-reasons { list-style:none; margin:8px 0; }
        .meteo-alert-reasons li::before { content:"⚠️ "; }
        .meteo-alert-reasons li { font-size:0.92rem; color:#9a3412; margin-bottom:4px; }
        .suggested-date-btn { display:inline-flex; align-items:center; gap:6px; margin-top:10px; padding:10px 18px; background:var(--teal); color:white; border-radius:10px; cursor:pointer; font-weight:700; font-size:0.92rem; border:none; }
        .suggested-date-btn:hover { background:#0a5e57; }
        .estimate-note { background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:12px 16px; margin-bottom:14px; font-size:0.92rem; color:#92400e; line-height:1.6; }
        .best-dates-panel { background:linear-gradient(135deg,#f0fdf9,#fff9ef); border:1px solid #a7f3d0; border-radius:20px; padding:20px; margin-top:12px; }
        .best-dates-title { font-weight:800; color:#065f46; font-size:1rem; margin-bottom:14px; }
        .best-dates-list { display:grid; gap:10px; max-height:520px; overflow-y:auto; padding-right:4px; }
        .best-dates-list::-webkit-scrollbar { width:6px; }
        .best-dates-list::-webkit-scrollbar-thumb { background:#a7f3d0; border-radius:6px; }
        .date-option { background:white; border-radius:14px; padding:14px 16px; display:flex; align-items:center; gap:12px; justify-content:space-between; box-shadow:0 4px 12px rgba(16,42,67,.06); border:1px solid rgba(16,42,67,.07); cursor:pointer; transition:transform .15s, box-shadow .15s; }
        .date-option:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(16,42,67,.12); }
        .date-option .date-label { font-weight:700; color:var(--ink); font-size:0.95rem; }
        .date-option .date-weather { display:flex; align-items:center; gap:8px; font-size:0.88rem; color:#64748b; flex-wrap:wrap; margin-top:4px; }
        .date-option .date-icon { font-size:1.5rem; flex-shrink:0; }
        .date-option .btn-choose { background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; border:none; border-radius:10px; padding:8px 16px; font-weight:700; font-size:0.85rem; cursor:pointer; white-space:nowrap; flex-shrink:0; }
        .date-option .btn-choose:hover { opacity:0.88; }

        @media(max-width:700px){ .grid{grid-template-columns:1fr;} .meteo-grid{grid-template-columns:repeat(2,1fr);} .img-preview-wrap{flex-direction:column;align-items:flex-start;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/../../FrontOffice/partials/topbar.php'; ?>
<div class="wrap">
    <a class="back-link" href="../../FrontOffice/listMarathons.php">← Retour au catalogue</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">✏️</div>
            <div><h1>Modifier le Marathon</h1></div>
        </div>

        <?php if ($error): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate id="marathonForm">
            <div class="grid">
                <!-- 1. Nom -->
                <div class="field full">
                    <label for="nom_marathon">Nom du Marathon</label>
                    <input type="text" id="nom_marathon" name="nom_marathon" value="<?php echo htmlspecialchars($data['nom_marathon']); ?>">
                    <div class="feedback" id="nomFeedback"></div>
                </div>
                <!-- 2. Image -->
                <div class="field full">
                    <label for="image_marathon">📸 Changer la Photo du Marathon</label>
                    <input type="file" id="image_marathon" name="image_marathon" accept=".jpg,.jpeg,.png,.webp">
                    <div class="feedback" id="imageFeedback"></div>
                    <!-- Preview : affiche la photo actuelle, remplacée en live si on en choisit une nouvelle -->
                    <div class="img-preview-wrap" id="previewContainer">
                        <img id="previewImg"
                             src="../../FrontOffice/<?php echo htmlspecialchars($data['image_marathon']); ?>"
                             alt="Photo du marathon"
                             onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'160\' height=\'100\'><rect width=\'160\' height=\'100\' fill=\'%23f1f5f9\'/><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'14\' fill=\'%2394a3b8\'>📸 No image</text></svg>'">
                        <div class="img-preview-info">
                            <span class="img-badge" id="imgBadge">📌 Image actuelle</span>
                            <span class="img-name" id="previewLabel"><?php echo htmlspecialchars(basename($data['image_marathon'])); ?></span>
                            <span style="font-size:0.82rem;color:#94a3b8;margin-top:4px;">Choisissez un fichier pour remplacer la photo</span>
                        </div>
                    </div>
                </div>
                <!-- 3. Places -->
                <div class="field">
                    <label for="nb_places_dispo">Places Disponibles</label>
                    <input type="text" id="nb_places_dispo" name="nb_places_dispo" value="<?php echo (int)$data['nb_places_dispo']; ?>" inputmode="numeric">
                    <div class="feedback" id="placesFeedback"></div>
                </div>
                <!-- 4. Prix -->
                <div class="field">
                    <label for="prix_marathon">Prix d'inscription (TND)</label>
                    <input type="text" id="prix_marathon" name="prix_marathon" value="<?php echo htmlspecialchars($data['prix_marathon']); ?>" inputmode="decimal">
                    <div class="feedback" id="prixFeedback"></div>
                </div>
                <!-- 5. Région -->
                <div class="field" style="position:relative;">
                    <label for="region_marathon"><i class="fa-solid fa-location-dot" style="color:var(--teal);"></i> Région(s)</label>
                    <input type="text" id="region_marathon" name="region_marathon" value="<?php echo htmlspecialchars($data['region_marathon']); ?>" placeholder="Ex: Tunis — ou Tunis-Sousse" autocomplete="off">
                    <div id="regionDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #cbd5e1;border-radius:12px;box-shadow:0 8px 24px rgba(16,42,67,.12);z-index:999;max-height:220px;overflow-y:auto;margin-top:4px;"></div>
                    <div class="feedback" id="regionFeedback"></div>
                </div>
                <!-- 6. Date -->
                <div class="field">
                    <label for="date_marathon">📅 Date du Marathon</label>
                    <input type="date" id="date_marathon" name="date_marathon" value="<?php echo htmlspecialchars($data['date_marathon']); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" data-today="<?php echo $todayStr; ?>">
                    <div class="feedback" id="dateFeedback"></div>
                </div>
            </div>

            <!-- ZONE MÉTÉO -->
            <div id="meteoZone">
                <div class="meteo-loading" id="meteoLoading" style="display:none;">
                    <div class="spinner"></div><span>Analyse météo en cours…</span>
                </div>
                <div id="meteoResult" style="display:none;"></div>
                <div id="bestDatesPanel" style="display:none;"></div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-save">✅ Modifier</button>
                <a href="../../FrontOffice/listMarathons.php" class="btn btn-secondary">✖ Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
// ─── Autocomplete gouvernorats ─────────────────────────────────────────────────
const TUNISIA_REGIONS = ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Kef','Mahdia','Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'];
const regionInput    = document.getElementById('region_marathon');
const regionDropdown = document.getElementById('regionDropdown');

function normalizeStr(s) { return s.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }

function validateRegionValue(val) {
    const parts = val.split(/[-,]/).map(s => s.trim()).filter(Boolean);
    if (parts.length === 0) return null;
    const resolved = parts.map(part => TUNISIA_REGIONS.find(r => normalizeStr(r) === normalizeStr(part)) || null);
    if (resolved.includes(null)) return null;
    return resolved.join('-');
}

regionInput.addEventListener('input', function () {
    const fullVal = this.value;
    const lastSep = Math.max(fullVal.lastIndexOf('-'), fullVal.lastIndexOf(','));
    const typing = lastSep >= 0 ? fullVal.slice(lastSep + 1).trim() : fullVal.trim();
    regionDropdown.innerHTML = '';
    const fb = document.getElementById('regionFeedback');
    if (!typing) { regionDropdown.style.display='none'; fb.textContent=''; fb.className='feedback'; return; }
    const matches = TUNISIA_REGIONS.filter(r => r.toLowerCase().includes(typing.toLowerCase()));
    const allValid = validateRegionValue(fullVal);
    if (allValid) { fb.textContent='✅ Région(s) valide(s)'; fb.className='feedback success'; }
    else { fb.textContent=''; fb.className='feedback'; }
    if (!matches.length) { regionDropdown.style.display='none'; return; }
    matches.forEach(region => {
        const item = document.createElement('div');
        item.textContent = region;
        item.style.cssText = 'padding:10px 14px;cursor:pointer;font-size:0.95rem;border-bottom:1px solid #f1f5f9;transition:background .15s;';
        item.addEventListener('mouseenter', () => item.style.background='#f0fdf4');
        item.addEventListener('mouseleave', () => item.style.background='');
        item.addEventListener('mousedown', e => {
            e.preventDefault();
            const fv = regionInput.value;
            const lc = Math.max(fv.lastIndexOf('-'), fv.lastIndexOf(','));
            regionInput.value = lc >= 0 ? fv.slice(0, lc + 1) + region : region;
            regionDropdown.style.display = 'none';
            fb.textContent = '✅ Région(s) valide(s)'; fb.className = 'feedback success';
            const dateVal = document.getElementById('date_marathon').value;
            if (dateVal) { clearTimeout(window._meteoTimer); window._meteoTimer = setTimeout(handleDateChange, 300); }
        });
        regionDropdown.appendChild(item);
    });
    regionDropdown.style.display = 'block';
});

regionInput.addEventListener('blur', () => {
    setTimeout(() => {
        regionDropdown.style.display = 'none';
        const val = regionInput.value.trim();
        const fb = document.getElementById('regionFeedback');
        if (val) {
            const valid = validateRegionValue(val);
            if (valid) { regionInput.value = valid; fb.textContent='✅ Région(s) valide(s)'; fb.className='feedback success'; }
            else { fb.textContent='❌ Vérifiez les gouvernorats saisis'; fb.className='feedback error'; }
        }
    }, 200);
});

regionInput.addEventListener('focus', function () {
    if (this.value.trim()) this.dispatchEvent(new Event('input'));
});

// ─── Preview image live ───────────────────────────────────────────────────────
document.getElementById('image_marathon').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewLabel').textContent = file.name;
        document.getElementById('imgBadge').textContent = '🆕 Nouvelle image';
        document.getElementById('imgBadge').style.background = '#f0fdf4';
        document.getElementById('imgBadge').style.color = '#0f766e';
    };
    reader.readAsDataURL(file);
});

// ─── Submit validation ────────────────────────────────────────────────────────
document.getElementById('marathonForm').addEventListener('submit', function (e) {
    const val = regionInput.value.trim();
    if (!val) {
        e.preventDefault();
        document.getElementById('regionFeedback').textContent = '❌ La région est obligatoire.';
        document.getElementById('regionFeedback').className = 'feedback error';
        regionInput.focus(); return;
    }
    const valid = validateRegionValue(val);
    if (!valid) {
        e.preventDefault();
        document.getElementById('regionFeedback').textContent = '❌ Vérifiez les gouvernorats saisis. Séparez plusieurs régions par un tiret (-).';
        document.getElementById('regionFeedback').className = 'feedback error';
        regionInput.focus(); return;
    }
    regionInput.value = valid;
});
</script>
<!-- Module météo partagé -->
<script src="meteo.js"></script>
<!-- Validation de formulaire -->
<script src="addMarathon.js"></script>
</body>
</html>
