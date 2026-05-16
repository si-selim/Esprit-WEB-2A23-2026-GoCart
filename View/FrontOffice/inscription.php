<!DOCTYPE html>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once "../../Controller/InscriptionController.php";
require_once "../../Controller/ParcoursController.php"; // ✅ AJOUT

$controller = new InscriptionController();
$liste = $controller->getAll();

// ✅ CORRECTION : charger les vrais parcours depuis la base
$parcoursController = new ParcoursController();
$listeParcours = $parcoursController->afficherParcours();

// ✅ Auto-sélection du parcours depuis l'URL
$preselected_parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;
$preselected_marathon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preselected_parcours_nom = '';
if ($preselected_parcours_id > 0) {
    foreach ($listeParcours as $p) {
        if ((int)$p['id_parcours'] === $preselected_parcours_id) {
            $preselected_parcours_nom = $p['nom_parcours'];
            break;
        }
    }
}

$currentPage = 'inscription';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 48px; }

        /* Back link */
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }

        /* Page header */
        .page-header { margin-bottom:28px; }
        .page-header h1 { font-size:1.85rem; font-weight:900; line-height:1.2; margin-bottom:6px; }
        .page-header p { color:#627d98; font-size:0.97rem; }

        /* Cards */
        .card { background:white; border-radius:24px; box-shadow:0 8px 32px rgba(16,42,67,.09); padding:28px; border:1px solid rgba(16,42,67,.06); }
        .card + .card { margin-top:24px; }

        .card-title { display:flex; align-items:center; gap:12px; margin-bottom:22px; }
        .card-title h2 { font-size:1.25rem; font-weight:900; }
        .card-title .icon-badge { width:40px; height:40px; border-radius:12px; background:rgba(15,118,110,.1); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }

        /* Form grid */
        .form-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:16px; margin-bottom:20px; }
        .field-group { display:flex; flex-direction:column; gap:6px; }
        .field-group label { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#627d98; }
        .field-group input,
        .field-group select { border:1.5px solid #e2e8f0; border-radius:11px; padding:10px 14px; font:inherit; font-size:0.93rem; color:var(--ink); transition:border-color .15s, box-shadow .15s; background:white; }
        .field-group input:focus,
        .field-group select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.1); }
        .field-group input[readonly] { background:#f8fafc; color:#64748b; }
        .field-group small { color:#e76f51; font-size:0.78rem; min-height:16px; }

        /* Buttons */
        .btn { text-decoration:none; padding:10px 18px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:0.9rem; transition:opacity .15s,transform .15s,box-shadow .15s; }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; box-shadow:0 4px 14px rgba(15,118,110,.3); }
        .btn-primary:hover { box-shadow:0 6px 18px rgba(15,118,110,.4); }
        .btn-outlined { background:white; color:var(--teal); border:2px solid var(--teal); }
        .btn-secondary { background:linear-gradient(135deg,#cbd5e1,#e2e8f0); color:#475569; box-shadow:0 3px 10px rgba(203,213,225,.4); }
        .btn-danger { background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; box-shadow:0 3px 10px rgba(220,38,38,.3); }
        .btn-small { padding:6px 12px; font-size:0.82rem; }

        .form-actions { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-top:4px; }

        /* Prix total */
        .prix-display { background:linear-gradient(135deg,#102a43,#1e3a5f); color:white; border-radius:16px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; margin-top:16px; }
        .prix-display .label { font-size:0.85rem; opacity:.75; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
        .prix-display .value { font-size:1.7rem; font-weight:900; color:#ffb703; }

        /* Search / filter bar */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .filter-bar input,
        .filter-bar select { border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 13px; font:inherit; font-size:0.88rem; color:var(--ink); background:white; flex:1 1 140px; min-width:0; }
        .filter-bar input:focus,
        .filter-bar select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.1); }

        /* Table */
        .table-wrapper { overflow-x:auto; border-radius:14px; border:1px solid #e5e7eb; }
        table { width:100%; border-collapse:collapse; font-size:0.89rem; }
        thead tr { background:linear-gradient(135deg,#f0fdfa,#e0f2fe); }
        thead th { padding:12px 14px; text-align:left; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#0369a1; white-space:nowrap; }
        tbody tr { border-top:1px solid #f1f5f9; transition:background .12s; }
        tbody tr:hover { background:#f8fafc; }
        td { padding:11px 14px; vertical-align:middle; }

        .badge { display:inline-block; border-radius:999px; padding:4px 11px; font-size:0.78rem; font-weight:700; }
        .badge-paid { background:rgba(16,185,129,.12); color:#059669; }
        .badge-unpaid { background:rgba(231,111,81,.12); color:#e76f51; }

        .table-actions { display:flex; gap:6px; flex-wrap:wrap; }

        /* Toast */
        .toast-notification { position:fixed; top:20px; right:20px; padding:16px 20px; border-radius:12px; color:white; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,.2); animation:slideIn .3s ease-out; z-index:10000; max-width:400px; }
        .toast-success { background:linear-gradient(135deg,#10b981,#059669); }
        .toast-error { background:linear-gradient(135deg,#ef4444,#dc2626); }
        @keyframes slideIn { from{transform:translateX(400px);opacity:0} to{transform:translateX(0);opacity:1} }
        @keyframes slideOut { from{transform:translateX(0);opacity:1} to{transform:translateX(400px);opacity:0} }

        /* ✅ Alerte erreur session */
        .alert-error { background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:12px 16px; color:#b42318; font-size:0.9rem; margin-bottom:18px; display:flex; align-items:center; gap:10px; }

        @media(max-width:680px){ .form-grid{grid-template-columns:1fr;} }

        /* ── Dark mode ── */
        html[data-theme="dark"] body { background:#0f172a; color:#e2e8f0; }
        html[data-theme="dark"] .card { background:#1e293b; border-color:rgba(255,255,255,0.07); box-shadow:0 8px 32px rgba(0,0,0,.35); }
        html[data-theme="dark"] .back-link { background:#1e293b; color:#5eead4; box-shadow:0 4px 12px rgba(0,0,0,.3); }
        html[data-theme="dark"] .page-header p { color:#94a3b8; }
        html[data-theme="dark"] .card-title h2 { color:#e2e8f0; }
        html[data-theme="dark"] .field-group label { color:#94a3b8; }
        html[data-theme="dark"] .field-group input,
        html[data-theme="dark"] .field-group select { background:#0f172a; color:#e2e8f0; border-color:rgba(255,255,255,0.1); }
        html[data-theme="dark"] .field-group input[readonly] { background:#1e293b; color:#64748b; }
        html[data-theme="dark"] .filter-bar input,
        html[data-theme="dark"] .filter-bar select { background:#0f172a; color:#e2e8f0; border-color:rgba(255,255,255,0.1); }
        html[data-theme="dark"] .table-wrapper { border-color:rgba(255,255,255,0.08); }
        html[data-theme="dark"] thead tr { background:linear-gradient(135deg,#162032,#1e3a5f); }
        html[data-theme="dark"] thead th { color:#5eead4; }
        html[data-theme="dark"] tbody tr { border-top-color:rgba(255,255,255,0.06); }
        html[data-theme="dark"] tbody tr:hover { background:rgba(20,184,166,0.06); }
        html[data-theme="dark"] td { color:#e2e8f0; }
        html[data-theme="dark"] .badge-unpaid { background:rgba(231,111,81,.2); color:#fb923c; }
        html[data-theme="dark"] .badge-paid { background:rgba(16,185,129,.2); color:#34d399; }
        html[data-theme="dark"] .btn-outlined { background:#1e293b; color:#5eead4; border-color:#5eead4; }
        html[data-theme="dark"] .btn-secondary { background:linear-gradient(135deg,#334155,#475569); color:#cbd5e1; }
    </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
    <script>
        window.addEventListener('load', function() {
            showToast('<?php echo $_GET['success'] === 'add' ? 'Inscription ajoutée avec succès !' : 'Inscription modifiée avec succès !'; ?>', 'success');
        });
    </script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <script>
        window.addEventListener('load', function() {
            const msgs = {
                'missing_fields'    : 'Veuillez remplir tous les champs obligatoires.',
                'invalid_number'    : 'Le nombre de personnes est invalide.',
                'invalid_parcours'  : 'Le parcours sélectionné est invalide.',
                'not_logged_in'     : 'Vous devez être connecté pour vous inscrire.'
            };
            showToast(msgs['<?php echo htmlspecialchars($_GET['error']); ?>'] || 'Une erreur est survenue.', 'error');
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/partials/topbar.php'; ?>

<div class="page">
    <a class="back-link" href="listMarathons.php">← Retour aux marathons</a>

    <div class="page-header">
        <h1>🏃‍♂️ Inscription au Marathon</h1>
        <?php if ($preselected_parcours_nom): ?>
            <div style="display:inline-flex;align-items:center;gap:10px;margin-top:8px;background:rgba(15,118,110,.08);border:1px solid rgba(15,118,110,.18);border-radius:12px;padding:9px 18px;">
                <span style="font-size:1.1rem;">🗺️</span>
                <span style="font-weight:800;color:#0f766e;font-size:1rem;">Parcours sélectionné : <?php echo htmlspecialchars($preselected_parcours_nom); ?></span>
            </div>
        <?php endif; ?>
        <p style="margin-top:8px;">Remplissez le formulaire ci-dessous pour valider votre inscription.</p>
    </div>

    <!-- ✅ Vérification session côté affichage -->
    <?php if (!isset($_SESSION['user']) && !isset($_SESSION['id_user'])): ?>
        <div class="alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Vous devez être <a href="login.php" style="color:var(--coral);font-weight:700;">connecté</a> pour vous inscrire.
        </div>
    <?php endif; ?>

    <!-- FORMULAIRE -->
    <div class="card">
        <div class="card-title">
            <div class="icon-badge">📋</div>
            <div>
                <h2>Nouvelle inscription</h2>
            </div>
        </div>

        <form method="post" action="../../Controller/process_inscription.php">
            <input type="hidden" id="id_inscription" name="id_inscription">

            <div class="form-grid">
                <div class="field-group">
                    <label>Nombre de personnes</label>
                    <input id="nb_personnes" type="number" name="nb_personnes" placeholder="Ex : 1" min="1">
                    <small id="error-nb_personnes"></small>
                </div>

                <!-- ✅ Parcours : auto-sélectionné depuis URL ou choix manuel -->
                <div class="field-group">
                    <label>Parcours</label>
                    <?php if ($preselected_parcours_id > 0 && $preselected_parcours_nom): ?>
                        <input type="text" value="<?php echo htmlspecialchars($preselected_parcours_nom); ?>" readonly style="background:#f0fdf4;color:#0f766e;font-weight:700;border-color:#6ee7b7;">
                        <input type="hidden" id="circuit" name="circuit" value="<?php echo $preselected_parcours_id; ?>">
                    <?php else: ?>
                        <select id="circuit" name="circuit">
                            <option value="">Choisir un parcours</option>
                            <?php foreach ($listeParcours as $p): ?>
                                <option value="<?php echo (int)$p['id_parcours']; ?>">
                                    <?php echo htmlspecialchars($p['nom_parcours']); ?>
                                    (<?php echo number_format((float)$p['distance'], 2); ?> km
                                    — <?php echo htmlspecialchars($p['difficulte']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <small id="error-circuit"></small>
                </div>

                <div class="field-group">
                    <label>Mode de paiement</label>
                    <select id="mode_paiement" name="mode_paiement">
                        <option value="">Choisir un mode</option>
                        <option value="cash">Espèces</option>
                        <option value="card">Carte bancaire</option>
                        <option value="transfer">Virement</option>
                    </select>
                    <small id="error-mode_paiement"></small>
                </div>

                
            </div>

            <div class="prix-display">
                <span class="label">💰 Prix total</span>
                <span class="value" id="prix_total">0 TND</span>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button class="btn btn-primary" type="submit" name="action" value="add">
                    <i class="fa-solid fa-plus"></i> Ajouter l'inscription
                </button>
                <button class="btn btn-outlined" type="submit" name="action" value="update">
                    <i class="fa-solid fa-pen-to-square"></i> Modifier
                </button>
                <a href="export_all_inscriptions.php" class="btn btn-secondary">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </form>
    </div>

    <!-- LISTE DES INSCRIPTIONS -->
    <div class="card" style="margin-top:28px;">
        <div class="card-title">
            <div class="icon-badge">📋</div>
            <h2>Inscriptions récentes</h2>
        </div>

        <!-- ✅ CORRECTION : filtres dynamiques aussi -->
        <div class="filter-bar">
            <input type="number" id="search_id" placeholder="🔍 Chercher par ID">
            <select id="filter_statut">
                <option value="">Tous les statuts</option>
                <option value="paid">Payé</option>
                <option value="unpaid">Non payé</option>
            </select>
            <select id="filter_circuit">
                <option value="">Tous les parcours</option>
                <?php foreach ($listeParcours as $p): ?>
                    <option value="<?php echo (int)$p['id_parcours']; ?>">
                        <?php echo htmlspecialchars($p['nom_parcours']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="filter_nb">
                <option value="">Nb personnes</option>
                <option value="1">1</option>
                <option value="2-4">2 – 4</option>
                <option value="5+">5+</option>
            </select>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date inscription</th>
                        <th>Mode paiement</th>
                        <th>Parcours</th>
                        <th>Nb personnes</th>
                        <th>Date paiement</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php if (empty($liste)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#94a3b8; padding:28px;">
                                <i class="fa-solid fa-inbox" style="font-size:1.5rem; display:block; margin-bottom:8px;"></i>
                                Aucune inscription trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        // ✅ Construire un index nom_parcours par id pour affichage lisible
                        $parcoursIndex = [];
                        foreach ($listeParcours as $p) {
                            $parcoursIndex[$p['id_parcours']] = $p['nom_parcours'];
                        }
                        ?>
                        <?php foreach ($liste as $row): ?>
                            <tr data-id="<?php echo $row['id_inscription']; ?>"
                                data-statut="<?php echo $row['statut_paiement']; ?>"
                                data-circuit="<?php echo $row['id_parcours']; ?>"
                                data-nb="<?php echo $row['nb_personnes']; ?>">

                                <td><?php echo $row['date_inscription']; ?></td>
                                <td><?php echo htmlspecialchars($row['mode_de_paiement']); ?></td>
                                <!-- ✅ Afficher le nom du parcours au lieu de l'ID brut -->
                                <td><?php echo htmlspecialchars($parcoursIndex[$row['id_parcours']] ?? 'Parcours #' . $row['id_parcours']); ?></td>
                                <td><?php echo $row['nb_personnes']; ?></td>
                                <td>
                                <?php
                                if (!empty($row['date_paiement'])) {
                                    echo date("Y-m-d", strtotime($row['date_paiement']));
                                } else {
                                    echo "—";
                                }
                                ?>
                                </td>
                                <td>
                                    <?php if ($row['statut_paiement'] == 'paid'): ?>
                                        <span class="badge badge-paid"><i class="fa-solid fa-circle-check"></i> Payé</span>
                                    <?php else: ?>
                                        <span class="badge badge-unpaid"><i class="fa-solid fa-clock"></i> Non payé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-secondary btn-small"
                                            onclick="fillForm(
                                                <?php echo $row['id_inscription']; ?>,
                                                <?php echo $row['nb_personnes']; ?>,
                                                '<?php echo $row['mode_de_paiement']; ?>',
                                                '<?php echo date("Y-m-d", strtotime($row['date_paiement'])); ?>',
                                                <?php echo $row['id_parcours']; ?>
                                            )">
                                            <i class="fa-solid fa-pen"></i> Sélectionner
                                        </button>

                                        <a href="../FrontOffice/voirDossard.php?id_inscription=<?php echo $row['id_inscription']; ?>"
                                           class="btn btn-secondary btn-small">
                                            <i class="fa-solid fa-eye"></i> Voir
                                        </a>

                                        <a href="../../Controller/InscriptionController.php?delete=<?php echo $row['id_inscription']; ?>&redirect=front_inscription"
   class="btn btn-danger btn-small"
   onclick="return confirm('Supprimer cette inscription ?')">
   <i class="fa-solid fa-trash"></i> Supprimer
</a>

                                        <?php if ($row['statut_paiement'] != 'paid'): ?>
                                            <button class="btn btn-primary btn-small btn-pay-trigger"
                                                onclick="openPayModal(
                                                    <?php echo $row['id_inscription']; ?>,
                                                    <?php echo $row['nb_personnes']; ?>,
                                                    <?php echo $row['id_parcours']; ?>
                                                )">
                                                <i class="fa-solid fa-credit-card"></i> Payer
                                            </button>
                                        <?php else: ?>
                                            <span style="color:#059669;font-weight:700;font-size:0.85rem;"><i class="fa-solid fa-circle-check"></i> OK</span>
                                        <?php endif; ?>
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
document.addEventListener("DOMContentLoaded", function() {
    // Données parcours embarquées pour calcul prix
    var parcoursData = <?php
        $pd = [];
        foreach ($listeParcours as $p) {
            $pd[(int)$p['id_parcours']] = (float)$p['distance'];
        }
        echo json_encode($pd);
    ?>;

    window.calculerPrix = function() {
        var nb      = document.getElementById("nb_personnes");
        var circuit = document.getElementById("circuit");
        var champ   = document.getElementById("prix_total");
        if (!nb || !circuit || !champ) return;
        var nombre = parseInt(nb.value);
        if (isNaN(nombre) || nombre <= 0) { champ.textContent = "0 TND"; return; }

        var idCircuit = parseInt(circuit.value);
        var dist = 0;
        
        // Essayer depuis les données JSON (hidden input ou select)
        if (idCircuit && parcoursData[idCircuit]) {
            dist = parcoursData[idCircuit];
        } else if (circuit.options && circuit.selectedIndex >= 0) {
            var optionText = circuit.options[circuit.selectedIndex]?.text || "";
            var distMatch  = optionText.match(/([\d.]+)\s*km/);
            if (distMatch) dist = parseFloat(distMatch[1]);
        }
        
        if (!dist) { champ.textContent = "0 TND"; return; }

        var pu = dist < 15 ? 20 : dist < 25 ? 40 : 60;
        var total = pu * nombre;
        if (nombre >= 5)      total *= 0.8;
        else if (nombre >= 3) total *= 0.9;
        champ.textContent = total.toFixed(2) + " TND";
    };

    document.getElementById("nb_personnes")?.addEventListener("input", window.calculerPrix);
    document.getElementById("circuit")?.addEventListener("change", window.calculerPrix);
    
    // Trigger initial calculation if parcours is preselected
    <?php if ($preselected_parcours_id > 0): ?>
    setTimeout(window.calculerPrix, 100);
    <?php endif; ?>
});
</script>

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

<script>
// ✅ Filtrage tableau côté client
(function(){
    const searchId     = document.getElementById('search_id');
    const filterStatut = document.getElementById('filter_statut');
    const filterCircuit= document.getElementById('filter_circuit');
    const filterNb     = document.getElementById('filter_nb');
    const tbody        = document.getElementById('table-body');

    function applyFilters() {
        const id      = searchId?.value.trim();
        const statut  = filterStatut?.value;
        const circuit = filterCircuit?.value;
        const nb      = filterNb?.value;

        tbody.querySelectorAll('tr[data-id]').forEach(row => {
            let show = true;
            if (id      && row.dataset.id      !== id)     show = false;
            if (statut  && row.dataset.statut  !== statut) show = false;
            if (circuit && row.dataset.circuit !== circuit) show = false;
            if (nb) {
                const n = parseInt(row.dataset.nb);
                if (nb === '1'   && n !== 1)        show = false;
                if (nb === '2-4' && (n < 2||n > 4)) show = false;
                if (nb === '5+'  && n < 5)           show = false;
            }
            row.style.display = show ? '' : 'none';
        });
    }

    searchId?.addEventListener('input', applyFilters);
    filterStatut?.addEventListener('change', applyFilters);
    filterCircuit?.addEventListener('change', applyFilters);
    filterNb?.addEventListener('change', applyFilters);
})();
</script>

<script src="inscription.js?v=<?php echo time(); ?>"></script>
<script src="inscription_ai.js?v=<?php echo time(); ?>"></script>
</body>
</html>