<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once "../../Controller/InscriptionController.php";

$controller = new InscriptionController();
$liste = $controller->getAll();

$currentPage = 'afficher';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Inscriptions — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 48px; }

        .page-header { margin-bottom:28px; }
        .page-header h1 { font-size:1.85rem; font-weight:900; line-height:1.2; margin-bottom:6px; }
        .page-header p { color:#627d98; font-size:0.97rem; }

        .card { background:white; border-radius:24px; box-shadow:0 8px 32px rgba(16,42,67,.09); padding:28px; border:1px solid rgba(16,42,67,.06); }

        .card-title { display:flex; align-items:center; gap:12px; margin-bottom:22px; }
        .card-title h2 { font-size:1.25rem; font-weight:900; }
        .card-title .icon-badge { width:40px; height:40px; border-radius:12px; background:rgba(15,118,110,.1); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
        .card-title .count { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:4px 13px; font-size:0.88rem; font-weight:700; margin-left:4px; }

        /* Filter bar */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .filter-bar input, .filter-bar select { border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 13px; font:inherit; font-size:0.88rem; color:var(--ink); background:white; flex:1 1 160px; min-width:0; }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.1); }

        /* Table */
        .table-wrapper { overflow-x:auto; border-radius:14px; border:1px solid #e5e7eb; }
        table { width:100%; border-collapse:collapse; font-size:0.9rem; }
        thead tr { background:linear-gradient(135deg,#f0fdfa,#e0f2fe); }
        thead th { padding:12px 16px; text-align:left; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#0369a1; white-space:nowrap; }
        tbody tr { border-top:1px solid #f1f5f9; transition:background .12s; }
        tbody tr:hover { background:#f8fafc; }
        td { padding:12px 16px; vertical-align:middle; }

        .circuit-badge { display:inline-block; border-radius:8px; padding:4px 11px; font-size:0.82rem; font-weight:700; }
        .circuit-10  { background:rgba(15,118,110,.1);  color:var(--teal); }
        .circuit-21  { background:rgba(255,183,3,.15);  color:#b45309; }
        .circuit-42  { background:rgba(231,111,81,.12); color:var(--coral); }

        .btn { text-decoration:none; padding:7px 14px; border-radius:10px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-size:0.83rem; transition:opacity .15s,transform .15s; }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-secondary { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; box-shadow:0 3px 10px rgba(15,118,110,.25); }
        .btn-danger    { background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; box-shadow:0 3px 10px rgba(220,38,38,.25); }

        .table-actions { display:flex; gap:8px; flex-wrap:wrap; }

        .empty-row td { text-align:center; color:#94a3b8; padding:32px; }
        .empty-row i  { font-size:1.5rem; display:block; margin-bottom:8px; }
    </style>
</head>
<body>

<?php require __DIR__ . '/partials/topbar.php'; ?>

<div class="page">
    <div class="page-header">
        <h1>📋 Gestion des Inscriptions</h1>
        <p>Liste complète de toutes les inscriptions enregistrées.</p>
    </div>

    <div class="card">
        <div class="card-title">
            <div class="icon-badge">📋</div>
            <h2>Toutes les inscriptions</h2>
            <span class="count"><?php echo count($liste); ?></span>
        </div>

        <!-- Filtres -->
        <div class="filter-bar">
            <input type="text" id="search_mode" placeholder="🔍 Mode de paiement...">
            <select id="filter_circuit">
                <option value="">Tous les circuits</option>
                <option value="1">10 km</option>
                <option value="2">21 km</option>
                <option value="3">42 km</option>
            </select>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mode paiement</th>
                        <th>Circuit</th>
                        <th>Nb personnes</th>
                        <th>Date paiement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php if (empty($liste)): ?>
                        <tr class="empty-row">
                            <td colspan="5">
                                <i class="fa-solid fa-inbox"></i>
                                Aucune inscription trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($liste as $row): ?>
                            <tr data-circuit="<?php echo $row['id_parcours']; ?>" data-mode="<?php echo strtolower($row['mode_de_paiement']); ?>">
                                <td><?php echo htmlspecialchars($row['mode_de_paiement']); ?></td>
                                <td>
                                    <?php if ($row['id_parcours'] == 1): ?>
                                        <span class="circuit-badge circuit-10">10 km</span>
                                    <?php elseif ($row['id_parcours'] == 2): ?>
                                        <span class="circuit-badge circuit-21">21 km</span>
                                    <?php else: ?>
                                        <span class="circuit-badge circuit-42">42 km</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['nb_personnes']; ?></td>
                                <td><?php echo htmlspecialchars($row['date_paiement']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="../FrontOffice/voirDossard.php?id_inscription=<?php echo $row['id_inscription']; ?>"
                                           class="btn btn-secondary">
                                            <i class="fa-solid fa-eye"></i> Voir
                                        </a>
                                        <a href="../../Controller/InscriptionController.php?delete=<?php echo $row['id_inscription']; ?>&redirect=front_afficher"
                                           class="btn btn-danger"
                                           onclick="return confirm('Supprimer cette inscription ?')">
                                            <i class="fa-solid fa-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const mode    = document.getElementById('search_mode').value.toLowerCase();
    const circuit = document.getElementById('filter_circuit').value;
    document.querySelectorAll('#table-body tr[data-circuit]').forEach(row => {
        const matchMode    = row.dataset.mode.includes(mode);
        const matchCircuit = circuit === '' || row.dataset.circuit === circuit;
        row.style.display  = matchMode && matchCircuit ? '' : 'none';
    });
}
document.getElementById('search_mode').addEventListener('input', applyFilters);
document.getElementById('filter_circuit').addEventListener('change', applyFilters);
</script>
</body>
</html>