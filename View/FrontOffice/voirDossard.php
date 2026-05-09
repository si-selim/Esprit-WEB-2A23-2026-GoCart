<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

$id = $_GET['id_inscription'] ?? 0;

$dossardController     = new DossardController();
$inscriptionController = new InscriptionController();

$data  = $inscriptionController->getById($id);
$nb    = is_array($data) ? ($data['nb_personnes'] ?? 0) : 0;
$liste = $dossardController->getByInscription($id);

// Auto-regenerate QR codes that are missing (e.g. created before GD was enabled)
foreach ($liste as &$row) {
    if (empty($row['qr_code'])) {
        try {
            $row['qr_code'] = $dossardController->regenerateQR($row);
        } catch (\Throwable $e) { /* silently skip if GD not available */ }
    }
}
unset($row);

$total = max($nb, count($liste));

$currentPage = 'dossard';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir Dossards #<?php echo $id; ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 48px; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }

        .page-header { margin-bottom:28px; }
        .page-header h1 { font-size:1.85rem; font-weight:900; line-height:1.2; margin-bottom:6px; }
        .page-header p { color:#627d98; font-size:0.97rem; }

        .card { background:white; border-radius:24px; box-shadow:0 8px 32px rgba(16,42,67,.09); padding:28px; border:1px solid rgba(16,42,67,.06); }

        .card-title { display:flex; align-items:center; gap:12px; margin-bottom:22px; }
        .card-title h2 { font-size:1.25rem; font-weight:900; }
        .card-title .icon-badge { width:40px; height:40px; border-radius:12px; background:rgba(15,118,110,.1); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }

        .btn { text-decoration:none; padding:10px 18px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:0.9rem; transition:opacity .15s,transform .15s,box-shadow .15s; }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary  { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; box-shadow:0 4px 14px rgba(15,118,110,.3); }
        .btn-outlined { background:white; color:var(--teal); border:2px solid var(--teal); }
        .btn-secondary { background:linear-gradient(135deg,#cbd5e1,#e2e8f0); color:#475569; }
        .btn-small { padding:6px 12px; font-size:0.82rem; }

        .form-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:20px; }

        .alert { border-radius:12px; padding:14px 18px; font-weight:600; font-size:0.93rem; margin-bottom:18px; display:flex; align-items:center; gap:10px; }
        .alert-error { background:rgba(231,111,81,.1); color:var(--coral); border:1.5px solid rgba(231,111,81,.3); }

        .table-wrapper { overflow-x:auto; border-radius:14px; border:1px solid #e5e7eb; }
        table { width:100%; border-collapse:collapse; font-size:0.9rem; }
        thead tr { background:linear-gradient(135deg,#f0fdfa,#e0f2fe); }
        thead th { padding:12px 16px; text-align:left; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#0369a1; white-space:nowrap; }
        tbody tr { border-top:1px solid #f1f5f9; transition:background .12s; }
        tbody tr:hover { background:#f8fafc; }
        td { padding:12px 16px; vertical-align:middle; }

        .num-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:8px; padding:4px 10px; font-weight:800; font-size:0.88rem; display:inline-block; }
        .check-badge { background:rgba(16,185,129,.12); color:#059669; border-radius:8px; padding:4px 10px; font-weight:700; font-size:0.85rem; }
        .couleur-swatch { width:24px; height:24px; border-radius:6px; display:inline-block; border:2px solid rgba(0,0,0,.08); vertical-align:middle; margin-right:6px; }
        .qr-img { width:72px; height:72px; border-radius:8px; border:1.5px solid #e2e8f0; }

        .toast-notification { position:fixed; top:20px; right:20px; padding:16px 20px; border-radius:12px; color:white; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,.2); animation:slideIn .3s ease-out; z-index:10000; max-width:400px; }
        .toast-success { background:linear-gradient(135deg,#10b981,#059669); }
        .toast-error   { background:linear-gradient(135deg,#ef4444,#dc2626); }
        @keyframes slideIn { from{transform:translateX(400px);opacity:0} to{transform:translateX(0);opacity:1} }
        @keyframes slideOut { from{transform:translateX(0);opacity:1} to{transform:translateX(400px);opacity:0} }

        /* ── Dark mode ── */
        html[data-theme="dark"] body { background:#0f172a; color:#e2e8f0; }
        html[data-theme="dark"] .card { background:#1e293b; border-color:rgba(255,255,255,0.07); box-shadow:0 8px 32px rgba(0,0,0,.35); }
        html[data-theme="dark"] .back-link { background:#1e293b; color:#5eead4; box-shadow:0 4px 12px rgba(0,0,0,.3); }
        html[data-theme="dark"] .page-header h1 { color:#f1f5f9; }
        html[data-theme="dark"] .page-header p { color:#94a3b8; }
        html[data-theme="dark"] .page-header strong { color:#5eead4; }
        html[data-theme="dark"] .card-title h2 { color:#f1f5f9; }
        html[data-theme="dark"] .card-title .icon-badge { background:rgba(20,184,166,.15); }
        html[data-theme="dark"] .table-wrapper { border-color:rgba(255,255,255,0.08); }
        html[data-theme="dark"] thead tr { background:linear-gradient(135deg,#162032,#1e3a5f); }
        html[data-theme="dark"] thead th { color:#5eead4; }
        html[data-theme="dark"] tbody tr { border-top-color:rgba(255,255,255,0.06); }
        html[data-theme="dark"] tbody tr:hover { background:rgba(20,184,166,0.06); }
        html[data-theme="dark"] td { color:#e2e8f0; }
        html[data-theme="dark"] .num-badge { background:rgba(20,184,166,.18); color:#5eead4; }
        html[data-theme="dark"] .check-badge { background:rgba(16,185,129,.2); color:#34d399; }
        html[data-theme="dark"] .couleur-swatch { border-color:rgba(255,255,255,.18); }
        html[data-theme="dark"] td small { color:#cbd5e1; }
        html[data-theme="dark"] .qr-img { background:#fff; border-color:rgba(255,255,255,.15); }
        html[data-theme="dark"] .btn-outlined { background:#1e293b; color:#5eead4; border-color:#5eead4; }
        html[data-theme="dark"] .btn-secondary { background:linear-gradient(135deg,#334155,#475569); color:#cbd5e1; }
        html[data-theme="dark"] .alert-error { background:rgba(231,111,81,.15); color:#fb923c; border-color:rgba(231,111,81,.35); }
    </style>
</head>
<body>

<?php require __DIR__ . '/partials/topbar.php'; ?>

<div class="page">
    <a class="back-link" href="inscription.php">← Retour aux inscriptions</a>

    <div class="page-header">
        <h1>🎽 Dossards de l'inscription</h1>
        <p>Liste des dossards associés à l'inscription <strong>#<?php echo $id; ?></strong></p>
    </div>

    <div class="card">
        <div class="card-title">
            <div class="icon-badge">🎽</div>
            <h2>Inscription #<?php echo $id; ?> — <?php echo $total; ?> dossard(s)</h2>
        </div>

        <?php if ($nb == 0): ?>
            <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> Aucune inscription trouvée pour cet ID.</div>
        <?php else: ?>

            <?php if (count($liste) == 0): ?>
                <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> Aucun dossard trouvé pour cette inscription.</div>
            <?php endif; ?>

            <div class="table-wrapper">
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
                                    <span class="check-badge"><i class="fa-solid fa-circle-check"></i> Complété</span>
                                <?php else: ?>
                                    <a href="dossard.php?id_inscription=<?php echo $id; ?>" class="btn btn-secondary btn-small">
                                        <i class="fa-solid fa-plus"></i> Compléter
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($liste[$i]['nom'] ?? '—'); ?></td>
                            <td><span class="num-badge">#<?php echo htmlspecialchars($liste[$i]['numero'] ?? ($i + 1)); ?></span></td>
                            <td><?php echo htmlspecialchars($liste[$i]['taille'] ?? '—'); ?></td>
                            <td>
                                <?php if (isset($liste[$i]['couleur'])): ?>
                                    <span class="couleur-swatch" style="background:<?php echo htmlspecialchars($liste[$i]['couleur']); ?>;"></span>
                                    <small><?php echo htmlspecialchars($liste[$i]['couleur']); ?></small>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($liste[$i]['qr_code'])): ?>
                                    <img class="qr-img" src="../../qr/<?php echo htmlspecialchars($liste[$i]['qr_code']); ?>" alt="QR Code">
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-size:0.85rem;"><i class="fa-solid fa-ban"></i> Pas de QR</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

        <div class="form-actions">
            <a href="export_pdf.php?id_inscription=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
            <a href="inscription.php" class="btn btn-outlined">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<script>
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>
</body>
</html>