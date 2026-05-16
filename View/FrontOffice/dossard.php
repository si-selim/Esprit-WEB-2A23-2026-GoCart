<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

$id = $_GET['id_inscription'] ?? 0;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;
$back_url = $parcours_id > 0 ? "inscription.php?parcours_id=" . $parcours_id : "inscription.php";

$inscriptionController = new InscriptionController();
$dossardController     = new DossardController();

$data               = $inscriptionController->getById($id);
$nbFromInscription  = $data['nb_personnes'] ?? 1;
$dossardsExistants  = $dossardController->getByInscription($id);
$nbExistants        = count($dossardsExistants);
$total              = max($nbFromInscription, $nbExistants);
$nom_global         = $dossardsExistants[0]['nom'] ?? "";
$nextNumero         = $dossardController->getLastNumero();

$currentPage = 'dossard';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossards #<?php echo $id; ?> — BarchaThon</title>
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

        .form-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; margin-bottom:22px; }
        .field-group { display:flex; flex-direction:column; gap:6px; }
        .field-group label { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#627d98; }
        .field-group input, .field-group select { border:1.5px solid #e2e8f0; border-radius:11px; padding:10px 14px; font:inherit; font-size:0.93rem; color:var(--ink); transition:border-color .15s,box-shadow .15s; background:white; }
        .field-group input:focus, .field-group select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.1); }
        .field-group small { font-size:0.78rem; min-height:16px; }

        .btn { text-decoration:none; padding:10px 18px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:0.9rem; transition:opacity .15s,transform .15s,box-shadow .15s; }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; box-shadow:0 4px 14px rgba(15,118,110,.3); }
        .btn-outlined { background:white; color:var(--teal); border:2px solid var(--teal); }

        .table-wrapper { overflow-x:auto; border-radius:14px; border:1px solid #e5e7eb; margin-bottom:22px; }
        table { width:100%; border-collapse:collapse; font-size:0.9rem; }
        thead tr { background:linear-gradient(135deg,#f0fdfa,#e0f2fe); }
        thead th { padding:12px 16px; text-align:left; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#0369a1; white-space:nowrap; }
        tbody tr { border-top:1px solid #f1f5f9; transition:background .12s; }
        tbody tr:hover { background:#f8fafc; }
        td { padding:12px 16px; vertical-align:middle; }

        .taille-select { border:1.5px solid #e2e8f0; border-radius:9px; padding:7px 11px; font:inherit; font-size:0.88rem; background:white; width:100%; max-width:110px; }
        .taille-select:focus { outline:none; border-color:var(--teal); }
        .couleur-input { width:44px; height:36px; border-radius:9px; border:1.5px solid #e2e8f0; cursor:pointer; padding:2px; }
        .num-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:8px; padding:4px 10px; font-weight:800; font-size:0.88rem; display:inline-block; }

        .form-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:8px; }

        .toast-notification { position:fixed; top:20px; right:20px; padding:16px 20px; border-radius:12px; color:white; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,.2); animation:slideIn .3s ease-out; z-index:10000; max-width:400px; }
        .toast-success { background:linear-gradient(135deg,#10b981,#059669); }
        .toast-error   { background:linear-gradient(135deg,#ef4444,#dc2626); }
        @keyframes slideIn { from{transform:translateX(400px);opacity:0} to{transform:translateX(0);opacity:1} }
        @keyframes slideOut { from{transform:translateX(0);opacity:1} to{transform:translateX(400px);opacity:0} }
    </style>
</head>
<body>

<?php require __DIR__ . '/partials/topbar.php'; ?>

<div class="page">
    <a class="back-link" href="<?php echo $back_url; ?>">← Retour aux inscriptions</a>

    <div class="page-header">
        <h1>🎽 Dossards</h1>
        <p>Gestion des dossards liés à l'inscription <strong>#<?php echo $id; ?></strong></p>
    </div>

    <div class="card">
        <div class="card-title">
            <div class="icon-badge">🎽</div>
            <h2>Inscription #<?php echo $id; ?> — <?php echo $total; ?> dossard(s)</h2>
        </div>

        <form method="post" action="../../Controller/dossard_process.php">
            <input type="hidden" name="id_inscription" value="<?php echo $id; ?>">

            <div class="form-grid">
                <div class="field-group">
                    <label>Nom / Équipe</label>
                    <input type="text" name="nom_global" id="nom_global" value="<?php echo htmlspecialchars($nom_global); ?>" placeholder="Ex : Équipe Carthage">
                    <small id="error-nom_global"></small>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Taille</th>
                            <th>Couleur</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < $total; $i++):
                        if (isset($dossardsExistants[$i])) {
                            $numero = $dossardsExistants[$i]['numero'];
                        } else {
                            $nextNumero++;
                            $numero = $nextNumero;
                        }
                    ?>
                    <tr>
                        <td>
                            <span class="num-badge">#<?php echo $numero; ?></span>
                            <input type="hidden" name="numero[]" value="<?php echo $numero; ?>">
                        </td>
                        <td>
                            <select name="taille[]" class="taille taille-select">
                                <option value="">— Taille —</option>
                                <?php foreach (['S','M','L','XL'] as $t): ?>
                                    <option value="<?php echo $t; ?>" <?php if (($dossardsExistants[$i]['taille'] ?? '') == $t) echo 'selected'; ?>><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="error-taille" style="display:block;font-size:0.75rem;margin-top:3px;"></small>
                        </td>
                        <td>
                            <input type="color" name="couleur[]" class="couleur couleur-input"
                                   value="<?php echo htmlspecialchars($dossardsExistants[$i]['couleur'] ?? '#0f766e'); ?>">
                            <small class="error-couleur" style="display:block;font-size:0.75rem;margin-top:3px;"></small>
                        </td>
                    </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer les dossards
                </button>
                <a href="<?php echo $back_url; ?>" class="btn btn-outlined">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
            <input type="hidden" name="parcours_id" value="<?php echo $parcours_id; ?>">
        </form>
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
<script src="dossard.js"></script>
</body>
</html>