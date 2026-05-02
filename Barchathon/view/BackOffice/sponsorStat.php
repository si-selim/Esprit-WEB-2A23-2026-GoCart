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
    <title>Stats Sponsors - BackOffice</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fefaf0 0%, var(--bg) 100%); }
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .sidebar { background: linear-gradient(180deg, #0b2032 0%, #12314a 100%); color:#fff; padding:28px 22px; position:sticky; top:0; height:100vh; display:flex; flex-direction:column; gap:24px; }
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
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="../assets/images/logo_barchathon.jpg" alt="Logo Barchathon">
                <div><strong>Admin Back Office</strong><br><small>Statistiques sponsors</small></div>
            </div>
            <nav class="side-nav">
                <a class="side-link" href="dashboard.php">Dashboard</a>
                <a class="side-link active" href="backoffice_Sponsor.php">Sponsors</a>
                <a class="side-link" href="#">Marathons</a>
                <a class="side-link" href="#">Participants</a>
                <a class="side-link" href="#">Paramètres</a>
            </nav>
            <div class="side-note">Vue de statistiques sponsor. Les données sont chargées depuis le contrôleur.</div>
        </aside>
        <main class="content">
            <div class="head">
                <div>
                    <h1>Statistiques Sponsors</h1>
                    <div class="muted">Utilisez cette page pour visualiser la répartition des sponsors par type ou par nombre de parrainages.</div>
                </div>
            </div>
            <section class="section-card">
                <div class="toolbar" style="margin-bottom:18px;">
                    <div class="select-group">
                        <label for="sponsorStatType">Type de statistique</label>
                        <select id="sponsorStatType" style="width:280px; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink);">
                            <option value="type">Répartition par type</option>
                            <option value="sponsoring">Sponsors par nombre de sponsoring</option>
                        </select>
                    </div>
                </div>
                <div class="chart-card">
                    <canvas id="sponsorStatsChart"></canvas>
                </div>
                <div class="footer-actions">
                    <a class="btn btn-secondary" href="backoffice_Sponsor.php">Retour</a>
                </div>
            </section>
        </main>
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
        refreshChart();
    </script>
</body>
</html>
