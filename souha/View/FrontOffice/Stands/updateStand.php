<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/StandController.php';
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Model/Stand.php';
require_once __DIR__ . '/../partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    header('Location: ../login.php'); exit;
}

$controller = new StandController();
$parcoursController = new ParcoursController();

$id = isset($_GET['searchVal']) ? (int)$_GET['searchVal'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id <= 0) { header('Location: listStandsFront.php'); exit; }

$data = $controller->showStand($id);
if (!$data) { header('Location: listStandsFront.php'); exit; }

$error = '';
$redirectParcours = isset($_GET['redirect_parcours']) ? (int)$_GET['redirect_parcours'] : (int)($data['ID_parcours'] ?? $data['id_parcours'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = new Stand(
        $id,
        (int)($_POST['id_parcours'] ?? $redirectParcours),
        $_POST['nom_stand'] ?? '',
        $_POST['position'] ?? '',
        $_POST['description'] ?? ''
    );

    if ($controller->updateStand($s, $id)) {
        header('Location: ../detailParcours.php?id=' . $redirectParcours);
        exit;
    } else {
        $error = "Erreur lors de la modification du stand.";
    }
}

$currentPage = 'catalogue';
$currentUser = getCurrentUser();
$role = $currentUser['role'] ?? 'visiteur';

// Fetch parcours list if needed
$tousParcours = $parcoursController->afficherParcours();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Modifier Stand #<?php echo $id; ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --sand:#fff8e7; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,var(--sand),var(--bg)); }
        .wrap { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:32px 0 48px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }
        .panel { background:#fff; border-radius:28px; padding:32px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .panel-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; padding-bottom:20px; border-bottom:1px solid #e6edf3; }
        .panel-icon { width:52px; height:52px; border-radius:18px; background:linear-gradient(135deg,#64748b,#94a3b8); display:grid; place-items:center; font-size:1.4rem; color:white; }
        h1 { font-size:1.9rem; }
        .error-box { background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:14px; margin-bottom:20px; color:#b42318; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .field { display:grid; gap:6px; }
        .field.full { grid-column:1/-1; }
        label { font-weight:700; font-size:0.95rem; }
        input, select, textarea { width:100%; border-radius:14px; border:1px solid #cbd5e1; padding:12px 14px; font:inherit; transition:border .2s; }
        input:focus, select:focus, textarea:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        textarea { resize:vertical; min-height:100px; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 20px; font-weight:700; cursor:pointer; font-size:1rem; transition:opacity .15s; }
        .btn:hover { opacity:0.9; }
        .btn-save { background:linear-gradient(135deg,#64748b,#94a3b8); color:white; }
        .btn-secondary { background:#edf2f7; color:var(--ink); display:inline-flex; align-items:center; }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/topbar.php'; ?>
<div class="wrap">
    <a class="back-link" href="../detailParcours.php?id=<?php echo $redirectParcours; ?>">← Retour au parcours</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">🏪</div>
            <div><h1>Modifier le Stand </h1></div>
        </div>

        <?php if ($error): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="grid">
                <div class="field full">
                    <label>Parcours associé</label>
                    <div style="background-color: #f0fff4; border: 1px solid #c6f6d5; border-radius: 14px; padding: 12px 14px; margin-top: 5px;">
                        <span style="color: #0f766e; font-weight: 700; font-size: 1rem;">
                            <?php 
                                $idParcours = $data['ID_parcours'] ?? $data['id_parcours'] ?? 0;
                                $found = false;
                                foreach ($tousParcours as $p) {
                                    if ($idParcours == $p['id_parcours']) {
                                        echo htmlspecialchars($p['id_parcours']) . " - " . htmlspecialchars($p['nom_parcours']);
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) echo "Parcours introuvable (ID: " . htmlspecialchars($idParcours) . ")";
                            ?>
                        </span>
                    </div>
                    <input type="hidden" name="id_parcours" value="<?php echo htmlspecialchars($idParcours); ?>">
                </div>
                
                <div class="field full">
                    <label for="nom_stand">Nom du Stand</label>
                    <input type="text" id="nom_stand" name="nom_stand" value="<?php echo htmlspecialchars($data['nom_stand'] ?? $data['Nom_stand'] ?? ''); ?>" required>
                </div>
                
                <div class="field">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($data['position'] ?? $data['Position'] ?? ''); ?>" required>
                </div>

                <div class="field full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($data['description'] ?? $data['Description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Sauvegarder</button>
                <a href="../detailParcours.php?id=<?php echo $redirectParcours; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
