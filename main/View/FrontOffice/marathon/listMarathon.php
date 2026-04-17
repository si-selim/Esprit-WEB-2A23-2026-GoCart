<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office — Marathons | BarchaThon</title>
    <style>
        :root {
            --ink: #102a43;
            --teal: #0f766e;
            --sun: #ffb703;
            --bg: #f4fbfb;
            --card: #ffffff;
            --muted: #627d98;
            --coral: #e76f51;
            --line: #d9e2ec;
            --nav: #0b2032;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #fefaf0 0%, var(--bg) 100%);
        }
        .layout { min-height: 100vh; display: grid; grid-template-columns: 280px 1fr; }

        /* SIDEBAR */
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
            overflow-y: auto;
        }
        .brand { display: grid; gap: 10px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,.14); }
        .brand img { width: 64px; height: 64px; border-radius: 18px; object-fit: cover; }
        .brand small { color: rgba(255,255,255,.72); font-size: .8rem; }
        .side-nav { display: grid; gap: 8px; }
        .side-link {
            text-decoration: none; color: #fff;
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.05);
            border-radius: 14px; padding: 11px 14px;
            font-weight: 700; font-size: .93rem;
            display: flex; align-items: center; gap: 8px;
            transition: background .18s;
        }
        .side-link:hover { background: rgba(255,255,255,.12); }
        .side-link.active { background: linear-gradient(135deg, var(--teal), #14b8a6); border: 0; }
        .side-link.cta { background: linear-gradient(135deg, var(--teal), #14b8a6); border: 0; }
        .side-note { color: rgba(255,255,255,.55); font-size: .83rem; line-height: 1.6; margin-top: auto; }

        /* MAIN */
        .content { padding: 30px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 14px; }
        .topbar h1 { font-size: 2rem; }
        .topbar .muted { color: var(--muted); font-size: .95rem; margin-top: 4px; }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: var(--card); border-radius: 20px; padding: 20px; box-shadow: 0 10px 28px rgba(16,42,67,.07); border: 1px solid var(--line); }
        .stat-num { font-size: 2.2rem; font-weight: 900; color: var(--teal); }
        .stat-label { color: var(--muted); font-size: .9rem; margin-top: 4px; }

        /* TOOLBAR */
        .toolbar { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
        .btn { text-decoration: none; padding: 10px 16px; border-radius: 12px; font-weight: 700; border: 0; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; font-size: .93rem; }
        .btn-primary { background: var(--teal); color: #fff; }
        .btn-warning { background: var(--sun); color: var(--ink); }
        .btn-danger { background: var(--coral); color: #fff; }
        .btn-secondary { background: #fff; color: var(--ink); border: 1px solid rgba(16,42,67,.12); }
        .btn-pdf { background: #dc2626; color: #fff; }

        /* SEARCH / FILTER */
        .search-bar { display: flex; gap: 10px; flex-wrap: wrap; flex: 1; }
        .search-bar input, .search-bar select {
            padding: 10px 14px; border-radius: 12px; border: 1px solid var(--line);
            font: inherit; background: #fff; color: var(--ink); min-width: 180px;
        }

        /* TABLE */
        .section-card { background: var(--card); border-radius: 22px; padding: 22px; box-shadow: 0 14px 34px rgba(16,42,67,.07); border: 1px solid var(--line); }
        .section-title { font-size: 1.3rem; font-weight: 800; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
        .table-shell { overflow-x: auto; }
        table { width: 100%; min-width: 900px; border-collapse: collapse; }
        th, td { padding: 13px 12px; text-align: left; border-bottom: 1px solid #e6edf3; vertical-align: middle; }
        th { background: #102a43; color: #fff; font-size: .88rem; text-transform: uppercase; letter-spacing: .03em; }
        th:first-child { border-radius: 10px 0 0 0; }
        th:last-child { border-radius: 0 10px 0 0; }
        tr:hover td { background: #f8fbfd; }
        .table-actions { display: flex; gap: 7px; flex-wrap: wrap; }
        .tag { display: inline-block; padding: 5px 10px; border-radius: 999px; font-weight: 700; font-size: .82rem; }
        .tag-green { background: rgba(15,118,110,.12); color: var(--teal); }
        .tag-red { background: rgba(231,111,81,.12); color: var(--coral); }
        .tag-yellow { background: rgba(255,183,3,.18); color: #92600a; }
        .marathon-img { width: 54px; height: 40px; object-fit: cover; border-radius: 8px; }

        @media (max-width: 980px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <img src="logo.jpg" alt="BarchaThon Logo">
            <div>
                <strong style="font-size:1.1rem;">BarchaThon</strong><br>
                <small>Back Office Admin</small>
            </div>
        </div>
        <nav class="side-nav">
            <a class="side-link active" href="listMarathon.php">🏃 Marathons</a>
            <a class="side-link cta" href="addMarathon.php">➕ Ajouter Marathon</a>
            <a class="side-link" href="../parcours/listParcours.php">🗺️ Parcours</a>
            <a class="side-link" href="../parcours/addParcours.php">➕ Ajouter Parcours</a>
            <a class="side-link" href="../../FrontOffice/listMarathons.php">👁️ Voir Front Office</a>
        </nav>
        <div class="side-note">Gérez vos marathons et parcours depuis ce tableau de bord.</div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content">
        <div class="topbar">
            <div>
                <h1>🏃 Gestion des Marathons</h1>
                <div class="muted">Liste complète — ajout, modification, suppression, stats et export PDF</div>
            </div>
        </div>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-num" id="stat-total">—</div>
                <div class="stat-label">Total marathons</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" id="stat-places">—</div>
                <div class="stat-label">Places disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" id="stat-regions">—</div>
                <div class="stat-label">Régions couvertes</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" id="stat-prix-moy">—</div>
                <div class="stat-label">Prix moyen (TND)</div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <a class="btn btn-primary" href="addMarathon.php">➕ Ajouter un marathon</a>
            <a class="btn btn-pdf" href="exportMarathonPDF.php" target="_blank">📄 Exporter PDF</a>
            <div class="search-bar">
                <input type="text"
           name="search"
           id="searchInput"
           placeholder="🔍 Rechercher par nom marathon"
           oninput="filtrerTable()">

    <select id="filterRegion" onchange="filtrerTable()">
        <option value="">Toutes les régions</option>
        <option value="Tunis">Tunis</option>
        <option value="Sousse">Sousse</option>

        <option value="Monastir">Monastir</option>
        <option value="Bizerte">Bizerte</option>
        <option value="Nabeul">Nabeul</option>
    </select>

</div>
        </div>

        <!-- TABLE -->
        <div class="section-card">
            <div class="section-title">
                <span>Liste des marathons</span>
                <span class="tag tag-green" id="count-label">Chargement...</span>
            </div>
            <div class="table-shell">
                <table id="marathonTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Nom Marathon</th>
                            <th>Organisateur</th>
                            <th>Région</th>
                            <th>Date</th>
                            <th>Places dispo</th>
                            <th>Prix (TND)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="marathonBody">
                        <?php
                        require_once __DIR__ . '/../../../Controller/MarathonController.php';
                        $controller = new MarathonController();
                        $marathons = $controller->listMarathons();
                        if (empty($marathons)):
                        ?>
                        <tr><td colspan="9" style="text-align:center; color:var(--muted); padding:30px;">Aucun marathon trouvé.</td></tr>
                        <?php else: foreach ($marathons as $m): ?>
                        <tr>
                            <td><?= (int)$m->getIdMarathon() ?></td>
                            <td>
                                <img class="marathon-img"
     src="../../FrontOffice/<?= htmlspecialchars($m->getImageMarathon()) ?>"
     onerror="this.src='../../FrontOffice/images/img1.svg'"
     alt="photo">
                            </td>
                            <td><strong><?= htmlspecialchars($m->getNomMarathon()) ?></strong></td>
                            <td><?= htmlspecialchars($m->getOrganisateurMarathon()) ?></td>
                            <td><span class="tag tag-yellow"><?= htmlspecialchars($m->getRegionMarathon()) ?></span></td>
                            <td><?= htmlspecialchars($m->getDateMarathon()) ?></td>
                            <td>
                                <?php $places = (int)$m->getNbPlacesDispo(); ?>
                                <span class="tag <?= $places > 50 ? 'tag-green' : 'tag-red' ?>"><?= $places ?></span>
                            </td>
                            <td><strong><?= number_format((float)$m->getPrixMarathon(), 2) ?> TND</strong></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-warning" href="updateMarathon.php?id=<?= (int)$m->getIdMarathon() ?>">✏️ Modifier</a>
                                    <a class="btn btn-danger" href="deleteMarathon.php?id=<?= (int)$m->getIdMarathon() ?>" onclick="return confirm('Supprimer ce marathon ?')">🗑️ Supprimer</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
// Stats dynamiques
window.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('#marathonBody tr[data-places]');
    // Si PHP a injecté les data-attributes on les lit, sinon on compte les lignes
    const allRows = document.querySelectorAll('#marathonBody tr');
    document.getElementById('stat-total').textContent = allRows.length;
    document.getElementById('count-label').textContent = allRows.length + ' marathon(s)';
});

function filtrerTable() {
    const nom = document.getElementById('searchNom').value.toLowerCase();
    const region = document.getElementById('filterRegion').value.toLowerCase();
    const rows = document.querySelectorAll('#marathonBody tr');
    let visible = 0;
    rows.forEach(row => {
        const texte = row.textContent.toLowerCase();
        const matchNom = texte.includes(nom);
        const matchRegion = region === '' || texte.includes(region);
        if (matchNom && matchRegion) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });
    document.getElementById('count-label').textContent = visible + ' marathon(s)';
}
</script>
</body>
</html>
