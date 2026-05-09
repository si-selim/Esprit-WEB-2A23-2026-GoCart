<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once "../../Controller/InscriptionController.php";

$controller = new InscriptionController();
$stats = $controller->getStats();
$pay   = $controller->getPaidStats();

$currentPage = 'stats';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 48px; }

        .page-header { margin-bottom:28px; }
        .page-header h1 { font-size:1.85rem; font-weight:900; line-height:1.2; margin-bottom:6px; }
        .page-header p { color:#627d98; font-size:0.97rem; }

        /* KPI Cards */
        .kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:16px; margin-bottom:24px; }
        .kpi-card { background:white; border-radius:20px; padding:22px 24px; box-shadow:0 6px 24px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.06); display:flex; align-items:center; gap:16px; }
        .kpi-icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
        .kpi-icon-teal   { background:rgba(15,118,110,.1); }
        .kpi-icon-sun    { background:rgba(255,183,3,.15); }
        .kpi-icon-green  { background:rgba(16,185,129,.12); }
        .kpi-icon-coral  { background:rgba(231,111,81,.12); }
        .kpi-label { font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#627d98; margin-bottom:4px; }
        .kpi-value { font-size:2rem; font-weight:900; line-height:1; }
        .kpi-value-teal  { color:var(--teal); }
        .kpi-value-sun   { color:#d97706; }
        .kpi-value-green { color:#059669; }
        .kpi-value-coral { color:var(--coral); }

        /* Chart Cards */
        .charts-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(460px,1fr)); gap:20px; margin-bottom:24px; }
        .chart-card { background:white; border-radius:20px; padding:24px; box-shadow:0 6px 24px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.06); }
        .chart-card-title { display:flex; align-items:center; gap:10px; margin-bottom:20px; }
        .chart-card-title .icon-badge { width:36px; height:36px; border-radius:10px; background:rgba(15,118,110,.1); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .chart-card-title h2 { font-size:1.05rem; font-weight:800; }
        .chart-container { position:relative; height:260px; }

        @media(max-width:700px){ .charts-grid{ grid-template-columns:1fr; } }
    </style>
</head>
<body>

<?php require __DIR__ . '/partials/topbar.php'; ?>

<div class="page">
    <div class="page-header">
        <h1>📊 Dashboard Organisateur</h1>
        <p>Analyse des inscriptions et des paiements</p>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-teal">🏃</div>
            <div>
                <div class="kpi-label">Total Inscriptions</div>
                <div class="kpi-value kpi-value-teal"><?php echo $stats['totalInscriptions']; ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-sun">👥</div>
            <div>
                <div class="kpi-label">Total Participants</div>
                <div class="kpi-value kpi-value-sun"><?php echo $stats['totalParticipants']; ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-green">✅</div>
            <div>
                <div class="kpi-label">Paiements reçus</div>
                <div class="kpi-value kpi-value-green"><?php echo $pay['paid']; ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-coral">⏳</div>
            <div>
                <div class="kpi-label">En attente</div>
                <div class="kpi-value kpi-value-coral"><?php echo $pay['unpaid']; ?></div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-title">
                <div class="icon-badge">🗺️</div>
                <h2>Répartition des parcours</h2>
            </div>
            <div class="chart-container">
                <canvas id="chartParcours"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-title">
                <div class="icon-badge">💳</div>
                <h2>Modes de paiement</h2>
            </div>
            <div class="chart-container">
                <canvas id="chartPaiement"></canvas>
            </div>
        </div>
    </div>
</div>

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
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { family: 'Segoe UI', weight: '700' }, padding: 16 } }
        }
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
            borderRadius: 10,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font: { family: 'Segoe UI' } } },
            x: { grid: { display: false }, ticks: { font: { family: 'Segoe UI', weight: '700' } } }
        }
    }
});
</script>
</body>
</html>