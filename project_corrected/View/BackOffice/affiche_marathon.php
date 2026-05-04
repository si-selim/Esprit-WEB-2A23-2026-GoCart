<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: dashboard.php?tab=marathons'); exit; }

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();

$allMarathons = $mCtrl->afficherMarathon();
$marathon = null;
foreach ($allMarathons as $m) {
    if ((int)$m['id_marathon'] === $id) { $marathon = $m; break; }
}
if (!$marathon) { header('Location: dashboard.php?tab=marathons'); exit; }

$tousParcours = $pCtrl->afficherParcours();
$parcours = [];
foreach ($tousParcours as $p) {
    if ((int)$p['id_marathon'] === $id) $parcours[] = $p;
}

$imgPath     = '../FrontOffice/' . htmlspecialchars($marathon['image_marathon']);
$imgFallback = '../FrontOffice/marathon/images/hero_runner.png';

function diffColor($d) {
    if ($d === 'facile')    return ['#16a34a','#dcfce7'];
    if ($d === 'difficile') return ['#dc2626','#fee2e2'];
    return ['#d97706','#fef3c7'];
}
function formatHeure($h) {
    if (!$h) return '';
    return substr($h, 0, 5);
}

$date_formatee = date('d/m/Y', strtotime($marathon['date_marathon']));
$region        = htmlspecialchars($marathon['region_marathon']);

// ── Météo ──────────────────────────────────────────────────────────
$meteo_emoji = '🌡️';
$meteo_label = 'Indisponible';
$meteo_temp  = '';
$meteo_ok    = false;

try {
    $geo_url = "https://geocoding-api.open-meteo.com/v1/search?name=" . urlencode($marathon['region_marathon']) . "&count=1&language=fr&format=json";
    $ctx     = stream_context_create(['http' => ['timeout' => 5]]);
    $geo_raw = @file_get_contents($geo_url, false, $ctx);
    if ($geo_raw) {
        $geo = json_decode($geo_raw, true);
        if (!empty($geo['results'][0])) {
            $lat      = $geo['results'][0]['latitude'];
            $lon      = $geo['results'][0]['longitude'];
            $date_iso = date('Y-m-d', strtotime($marathon['date_marathon']));
            $today     = date('Y-m-d');
            $diff_days = (strtotime($date_iso) - strtotime($today)) / 86400;

            if ($diff_days >= 0 && $diff_days <= 15) {
                $wx_url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&daily=weathercode,temperature_2m_max,temperature_2m_min&timezone=auto&start_date={$date_iso}&end_date={$date_iso}";
            } elseif ($diff_days < 0) {
                $wx_url = "https://archive-api.open-meteo.com/v1/archive?latitude={$lat}&longitude={$lon}&daily=weathercode,temperature_2m_max,temperature_2m_min&timezone=auto&start_date={$date_iso}&end_date={$date_iso}";
            } else {
                $wx_url = null;
            }

            if ($wx_url) {
                $wx_raw = @file_get_contents($wx_url, false, $ctx);
                if ($wx_raw) {
                    $wx = json_decode($wx_raw, true);
                    if (isset($wx['daily']['weathercode'][0])) {
                        $wcode = (int)$wx['daily']['weathercode'][0];
                        $tmax  = round($wx['daily']['temperature_2m_max'][0]);
                        $tmin  = round($wx['daily']['temperature_2m_min'][0]);
                        $wmap  = [0=>'☀️',1=>'🌤️',2=>'⛅',3=>'☁️',45=>'🌫️',48=>'🌫️',51=>'🌦️',53=>'🌦️',55=>'🌧️',61=>'🌧️',63=>'🌧️',65=>'🌧️',71=>'🌨️',73=>'🌨️',75=>'❄️',77=>'🌨️',80=>'🌦️',81=>'🌧️',82=>'⛈️',85=>'🌨️',86=>'❄️',95=>'⛈️',96=>'⛈️',99=>'⛈️'];
                        $lmap  = [0=>'Ensoleillé',1=>'Peu nuageux',2=>'Partiellement nuageux',3=>'Couvert',45=>'Brouillard',48=>'Brouillard givrant',51=>'Bruine légère',53=>'Bruine modérée',55=>'Bruine forte',61=>'Pluie légère',63=>'Pluie modérée',65=>'Pluie forte',71=>'Neige légère',73=>'Neige modérée',75=>'Neige forte',80=>'Averses légères',81=>'Averses modérées',82=>'Averses fortes',95=>'Orage',99=>'Orage + grêle'];
                        $meteo_emoji = $wmap[$wcode] ?? '🌡️';
                        $meteo_label = $lmap[$wcode] ?? 'Variable';
                        $meteo_temp  = $tmin . '°–' . $tmax . '°C';
                        $meteo_ok    = true;
                    }
                }
            } else {
                $meteo_label = 'Trop lointain';
            }
        }
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Affiche — <?php echo htmlspecialchars($marathon['nom_marathon']); ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'Segoe UI',Arial,sans-serif;
    background:#0f172a;
    min-height:100vh;
    display:flex; flex-direction:column; align-items:center;
    justify-content:flex-start;
    padding:30px 20px 60px;
}

/* Toolbar */
.toolbar {
    width:100%; max-width:820px;
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:24px;
    flex-shrink:0;
}
.toolbar a { color:#94a3b8; text-decoration:none; font-size:.9rem; }
.toolbar a:hover { color:#fff; }
.btn-dl {
    background:#1565c0; color:#fff; border:none;
    padding:10px 22px; border-radius:10px;
    font-size:.95rem; font-weight:600; cursor:pointer;
    transition:background .2s;
}
.btn-dl:hover { background:#0d47a1; }
.btn-dl.loading { opacity:.7; cursor:wait; pointer-events:none; }

/* ══ AFFICHE ══ */
.affiche {
    width:100%; max-width:820px;
    flex-shrink:0;
    border-radius:20px; overflow:hidden;
    box-shadow:0 30px 80px rgba(0,0,0,.7);
    position:relative;
    background-color:#0f172a;
}

@media print {
    body { background:#fff; padding:0; display:block; }
    .toolbar { display:none; }
    .affiche { max-width:100%; border-radius:0; box-shadow:none; page-break-inside:avoid; }
}

/* Image de fond globale sur TOUT le poster */
.affiche-full-bg {
    position:absolute; inset:0; z-index:0;
    background-size:cover; background-position:center;
    opacity:.45;          /* bien visible, sans flou */
    pointer-events:none;
}
/* Overlay sombre global pour lisibilité */
.affiche-full-bg::after {
    content:''; position:absolute; inset:0;
    background:rgba(8,16,35,.50);
}
/* Tout le contenu au-dessus */
.affiche > *:not(.affiche-full-bg) { position:relative; z-index:1; }

/* ── HERO : image visible en haut ── */
.hero {
    position:relative;
    height:300px;
    overflow:hidden;
    background:transparent; /* laisse voir le fond global */
}
.hero-img {
    position:absolute; inset:0;
    width:100%; height:100%;
    object-fit:cover; object-position:center;
    opacity:.6;
    display:block;
}
.hero-overlay {
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(10,20,40,.05) 0%, rgba(10,20,40,.75) 100%);
    display:flex; flex-direction:column;
    justify-content:flex-end;
    padding:36px 40px;
}

/* ── BODY : transparent, laisse voir le fond global ── */
.body {
    position:relative;
    padding:28px 40px 32px;
    background:transparent;
}
.badge-event {
    display:inline-block;
    background:#0ea5e9; color:#fff;
    font-size:.72rem; font-weight:700;
    letter-spacing:2px; text-transform:uppercase;
    padding:4px 14px; border-radius:20px;
    margin-bottom:14px; width:fit-content;
}
.hero h1 {
    font-size:2.8rem; font-weight:900; color:#fff;
    line-height:1.1; letter-spacing:-1px;
    text-shadow:0 2px 20px rgba(0,0,0,.7);
}
.hero-sub {
    margin-top:8px; color:#7dd3fc;
    font-size:.97rem; font-style:italic;
}

/* ── INFO RECTANGLE — fond bleu solide, SANS lignes séparatrices ── */
.infos-rect {
    background:#0f2744;
    border:1px solid rgba(255,255,255,.15);
    border-radius:16px;
    padding:28px 32px;
    margin-bottom:28px;
    display:flex;
    flex-direction:column;
    gap:18px;          /* espace entre les lignes, mais pas de bordure */
}
.info-row {
    display:flex;
    align-items:center;
    gap:16px;
}
.info-icon { font-size:1.5rem; width:36px; text-align:center; flex-shrink:0; }
.info-label {
    font-size:.7rem; text-transform:uppercase;
    letter-spacing:1.5px; color:#7dd3fc; font-weight:700;
    width:90px; flex-shrink:0;
}
.info-val {
    font-size:1.2rem;   /* plus grand */
    font-weight:800;
    color:#fff;
}
.info-sub {
    font-size:.85rem; color:#7dd3fc; margin-left:10px;
    font-weight:600;
}

/* ── SECTION TITLE ── */
.section-title {
    color:#38bdf8; font-size:.7rem;
    text-transform:uppercase; letter-spacing:3px; font-weight:700;
    margin-bottom:16px; padding-bottom:10px;
    border-bottom:1px solid rgba(255,255,255,.12);
    display:flex; align-items:center; gap:8px;
}

/* ── PARCOURS — grille de carrés compacts ── */
.parcours-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
}
.parcours-card {
    background:#0f2744;
    border:1px solid rgba(255,255,255,.13);
    border-radius:14px;
    padding:18px 16px 16px;
    display:flex;
    flex-direction:column;
    gap:8px;
    border-top:4px solid;
    /* PAS de aspect-ratio — hauteur auto selon contenu */
}

/* Nom en haut */
.p-nom {
    font-weight:800; color:#fff;
    font-size:.97rem; line-height:1.3;
    margin-bottom:4px;
}

/* Détails compacts */
.p-details {
    font-size:.78rem; color:#94a3b8;
    display:flex; flex-direction:column; gap:5px;
}
.p-details span { display:flex; align-items:center; gap:5px; }

/* Badge juste après les détails, sans margin-top:auto */
.diff-badge {
    font-size:.7rem; font-weight:700;
    padding:5px 14px; border-radius:20px; text-transform:capitalize;
    align-self:flex-start;
    margin-top:6px;
}

.empty-parcours {
    color:#64748b; font-style:italic;
    text-align:center; padding:24px;
    grid-column:1/-1;
}

/* ── FOOTER ── */
.footer {
    background:rgba(0,0,0,.45);
    padding:16px 40px;
    display:flex; justify-content:space-between; align-items:center;
    border-top:1px solid rgba(255,255,255,.07);
}
.footer-brand { color:#38bdf8; font-weight:800; font-size:1rem; }
.footer-note  { color:#64748b; font-size:.78rem; }
</style>
</head>
<body>

<div class="toolbar">
    <a href="dashboard.php?tab=marathons">← Retour aux marathons</a>
    <button class="btn-dl" id="btn-pdf" onclick="telechargerPDF()">Télécharger PDF</button>
</div>

<div class="affiche" id="affiche">

    <!-- IMAGE EN FOND SUR TOUT LE POSTER (sans flou) -->
    <div class="affiche-full-bg"
         style="background-image:url('<?php echo $imgPath; ?>');"></div>

    <!-- HERO : image visible en haut avec titre -->
    <div class="hero">
        <img class="hero-img"
             src="<?php echo $imgPath; ?>"
             onerror="this.onerror=null;this.src='<?php echo $imgFallback; ?>';"
             alt="<?php echo htmlspecialchars($marathon['nom_marathon']); ?>">
        <div class="hero-overlay">
            <span class="badge-event">🏅 Événement Sportif</span>
            <h1><?php echo htmlspecialchars($marathon['nom_marathon']); ?></h1>
            <p class="hero-sub">Organisé par <?php echo htmlspecialchars($marathon['organisateur_marathon']); ?> · <?php echo $region; ?></p>
        </div>
    </div>

    <!-- BODY -->
    <div class="body">
        <!-- RECTANGLE INFOS — fond bleu, sans séparateurs -->
        <div class="infos-rect">
            <div class="info-row">
                <div class="info-icon">📍</div>
                <div class="info-label">Région</div>
                <div class="info-val"><?php echo $region; ?></div>
            </div>
            <div class="info-row">
                <div class="info-icon">📅</div>
                <div class="info-label">Date</div>
                <div class="info-val"><?php echo $date_formatee; ?></div>
            </div>
            <div class="info-row">
                <div class="info-icon"><?php echo $meteo_emoji; ?></div>
                <div class="info-label">Météo</div>
                <div class="info-val">
                    <?php echo $meteo_label; ?>
                    <?php if ($meteo_ok && $meteo_temp): ?>
                    <span class="info-sub"><?php echo $meteo_temp; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon">🎟️</div>
                <div class="info-label">Places</div>
                <div class="info-val"><?php echo $marathon['nb_places_dispo']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-icon">💰</div>
                <div class="info-label">Prix</div>
                <div class="info-val"><?php echo number_format($marathon['prix_marathon'], 2); ?> TND</div>
            </div>
        </div>

        <!-- SECTION PARCOURS -->
        <div class="section-title">🗺️ Parcours du marathon</div>
        <div class="parcours-grid">
            <?php if (empty($parcours)): ?>
                <p class="empty-parcours">Aucun parcours défini pour ce marathon.</p>
            <?php else: foreach ($parcours as $p):
                [$cl, $bg] = diffColor($p['difficulte'] ?? '');
            ?>
            <div class="parcours-card" style="border-top-color:<?php echo $cl; ?>;">
                <!-- Nom — sans emoji cercle -->
                <div class="p-nom"><?php echo htmlspecialchars($p['nom_parcours']); ?></div>

                <!-- Détails : départ → arrivée → distance → heure -->
                <div class="p-details">
                    <span>🚩 <?php echo htmlspecialchars($p['point_depart']); ?></span>
                    <span>🏁 <?php echo htmlspecialchars($p['point_arrivee']); ?></span>
                    <span>📏 <?php echo htmlspecialchars($p['distance']); ?> km</span>
                    <?php if (!empty($p['heure_depart'])): ?>
                    <span>⏰ <?php echo formatHeure($p['heure_depart']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Badge difficulté -->
                <span class="diff-badge" style="background:<?php echo $bg; ?>;color:<?php echo $cl; ?>;">
                    <?php echo ucfirst(htmlspecialchars($p['difficulte'] ?? '')); ?>
                </span>
            </div>
            <?php endforeach; endif; ?>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        <span class="footer-brand">BarchaThon</span>
        <span class="footer-note">Administration · Généré le <?php echo date('d/m/Y'); ?></span>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function telechargerPDF() {
    const btn = document.getElementById('btn-pdf');
    btn.textContent = 'Génération...';
    btn.classList.add('loading');
    const el = document.getElementById('affiche');
    html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#0f172a' }).then(canvas => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW = pdf.internal.pageSize.getWidth();
        const pageH = pdf.internal.pageSize.getHeight();
        const ratio = Math.min(pageW / canvas.width, pageH / canvas.height);
        const imgW  = canvas.width  * ratio;
        const imgH  = canvas.height * ratio;
        pdf.addImage(canvas.toDataURL('image/jpeg', 0.92), 'JPEG',
            (pageW-imgW)/2, (pageH-imgH)/2, imgW, imgH);
        pdf.save('affiche_<?php echo preg_replace("/[^a-z0-9]/i","_",$marathon["nom_marathon"]); ?>.pdf');
        btn.textContent = 'Télécharger PDF';
        btn.classList.remove('loading');
    });
}
</script>
</body>
</html>
