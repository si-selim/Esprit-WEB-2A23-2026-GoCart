<?php
include '../../controller/sponsorController.php';
$controller = new sponsorController();
$typeStats = $controller->getSponsorDistributionByType();
$sponsoringStats = $controller->getSponsorsBySponsoringCount(10);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Sponsors</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --ink:#102a43;
            --teal:#0f766e;
            --sun:#ffb703;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --coral:#e76f51;
            --line:#d9e2ec;
        }
        * { box-sizing:border-box; }
        body {
            margin:0;
            font-family:"Segoe UI",sans-serif;
            color:var(--ink);
            background:linear-gradient(180deg,#fefaf0 0%, var(--bg) 100%);
        }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 56px; }
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
        .fo-brand { display:inline-flex; align-items:center; gap:12px; text-decoration:none; color:#102a43; font-weight:900; font-size:1.1rem; flex-shrink:0; }
        .fo-brand img { height:50px; border-radius:10px; object-fit:cover; }
        .fo-nav { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
        .fo-link, .fo-cta, .fo-user {
            text-decoration:none; border-radius:999px; padding:9px 16px;
            font-weight:700; font-size:0.88rem;
            transition:transform .15s,background .15s,box-shadow .15s;
            white-space:nowrap;
        }
        .fo-link { color:#102a43; border:1px solid rgba(16,42,67,0.12); background:transparent; }
        .fo-link:hover { background:rgba(16,42,67,0.05); transform:translateY(-1px); }
        .fo-link.active { color:white; background:#102a43; border-color:#102a43; }
        .fo-cta { color:white; background:linear-gradient(135deg,#0f766e,#14b8a6); border:none; box-shadow:0 5px 16px rgba(15,118,110,.22); }
        .fo-cta:hover { transform:translateY(-1px); }
        .section-card { background:var(--card); border-radius:24px; padding:28px; box-shadow:0 14px 34px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); margin-top:28px; }
        .section-card h1 { margin:0 0 12px; font-size:2rem; }
        .section-card p { color:var(--muted); line-height:1.6; margin:0 0 18px; }
        .toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; gap:16px; align-items:center; margin-bottom:22px; }
        .toolbar-left { display:flex; gap:12px; align-items:center; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:14px; text-decoration:none; font-weight:700; border:0; cursor:pointer; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        .btn-export { background:#102a43; color:#fff; border:1px solid #102a43; }
        .btn-export:hover, .btn-secondary:hover, .fo-cta:hover { transform:translateY(-1px); }
        .select-group { display:flex; flex-wrap:wrap; gap:12px; align-items:center; }
        .select-group label { display:flex; flex-direction:column; gap:6px; font-size:.95rem; color:var(--muted); }
        .select-group select { width:260px; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .chart-card { background:#f8fafb; border-radius:24px; padding:22px; }
        .chart-card canvas { width:100% !important; max-height:420px; }
        .footer-actions { margin-top:22px; display:flex; justify-content:flex-end; }
        @media (max-width: 768px) {
            .fo-topbar-shell, .section-card { width:calc(100% - 24px); }
            .toolbar, .select-group, .footer-actions { flex-direction:column; align-items:flex-start; }
            .select-group select { width:100%; }
            .footer-actions { width:100%; }
        }
    </style>
</head>
<body>
    <div class="fo-topbar">
        <div class="fo-topbar-shell">
            <a class="fo-brand" href="accueil.php">
                <img src="../assets/images/logo_barchathon.jpg" alt="BarchaThon">
                BarchaThon
            </a>
            <nav class="fo-nav">
                <a class="fo-link" href="accueil.php">Accueil</a>
                <a class="fo-link" href="listMarathons.php">Catalogue</a>
                <a class="fo-link active" href="mesSponsors.php">Sponsors</a>
                <a class="fo-link" href="register.php">S'inscrire</a>
                <a class="fo-cta" href="login.php">Se connecter</a>
            </nav>
        </div>
    </div>
    <div class="page">
        <section class="section-card">
            <h1>Statistiques Sponsors</h1>
            <p>Analyse de la répartition des sponsors et des meilleurs sponsors par nombre de sponsoring. Changez le type de statistique sans recharger la page.</p>
            <div class="toolbar">
                <div class="select-group">
                    <label for="sponsorStatType">Type de statistique</label>
                    <select id="sponsorStatType">
                        <option value="type">Répartition par type</option>
                        <option value="sponsoring">Sponsors par nombre de sponsoring</option>
                    </select>
                </div>
            </div>
            <div class="chart-card">
                <canvas id="sponsorStatsChart"></canvas>
            </div>
            <div class="footer-actions">
                <a class="btn btn-secondary" href="mesSponsors.php">Retour</a>
            </div>
        </section>
    </div>
    <script>
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
            const backgroundColors = labels.map((_, index) => [
                '#0f766e', '#14b8a6', '#c084fc', '#fb7185', '#f97316', '#4f46e5'
            ][index % 6]);
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: backgroundColors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 14, padding: 16 } },
                        tooltip: { callbacks: { label: context => `${context.label}: ${context.parsed}` } }
                    }
                }
            });
        }

        function refreshChart() {
            if (currentChart) {
                currentChart.destroy();
            }
            currentChart = createChart(selectStat.value);
        }

        selectStat.addEventListener('change', refreshChart);
        refreshChart();
    </script>
</body>
</html>
