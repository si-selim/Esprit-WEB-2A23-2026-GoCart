<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../../Model/Parcours.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    header('Location: ../../FrontOffice/login.php'); exit;
}

$controller = new ParcoursController();
$marathonController = new MarathonController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ../../FrontOffice/listMarathons.php'); exit; }

$data = $controller->showParcours($id);
if (!$data) { header('Location: ../../FrontOffice/listMarathons.php'); exit; }

$marathons = $marathonController->afficherMarathon();
$error = '';
$redirectMarathon = isset($_GET['redirect_marathon']) ? (int)$_GET['redirect_marathon'] : (int)$data['id_marathon'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = new Parcours(
        $id,
        $_POST['nom_parcours'] ?? '',
        $_POST['point_depart'] ?? '',
        $_POST['point_arrivee'] ?? '',
        (float)($_POST['distance'] ?? 0),
        $_POST['difficulte'] ?? 'moyen',
        (int)($_POST['id_marathon'] ?? $data['id_marathon'])
    );

    if ($controller->modifierParcours($p, $id)) {
        $mid = (int)($_POST['id_marathon'] ?? $redirectMarathon);
        header('Location: ../../FrontOffice/detailMarathon.php?id=' . $mid);
        exit;
    } else {
        $error = "Erreur lors de la modification du parcours.";
    }
}

$currentPage = 'catalogue';
$currentUser = getCurrentUser();
$role = $currentUser['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Modifier Parcours #<?php echo $id; ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --sand:#fff8e7; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,var(--sand),var(--bg)); }
        .wrap { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:32px 0 48px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }
        .panel { background:#fff; border-radius:28px; padding:32px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .panel-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; padding-bottom:20px; border-bottom:1px solid #e6edf3; }
        .panel-icon { width:52px; height:52px; border-radius:18px; background:linear-gradient(135deg,#64748b,#94a3b8); display:grid; place-items:center; font-size:1.4rem; }
        h1 { font-size:1.9rem; }
        .error-box { background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:14px; margin-bottom:20px; color:#b42318; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .field { display:grid; gap:6px; }
        .field.full { grid-column:1/-1; }
        label { font-weight:700; font-size:0.95rem; }
        input, select { width:100%; border-radius:14px; border:1px solid #cbd5e1; padding:12px 14px; font:inherit; transition:border .2s; }
        input:focus, select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .feedback { font-size:0.85rem; min-height:18px; margin-top:2px; }
        .feedback.error { color:#dc2626; }
        .feedback.success { color:#16a34a; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 20px; font-weight:700; cursor:pointer; font-size:1rem; }
        .btn-save { background:linear-gradient(135deg,#64748b,#94a3b8); color:white; }
        .btn-secondary { background:#edf2f7; color:var(--ink); }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/../../FrontOffice/partials/topbar.php'; ?>
<div class="wrap">
    <a class="back-link" href="../../FrontOffice/detailMarathon.php?id=<?php echo $redirectMarathon; ?>">← Retour au marathon</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">✏️</div>
            <div><h1>Modifier le Parcours </h1></div>
        </div>

        <?php if ($error): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="grid">
                <div class="field full">
    <label>Marathon associé</label>
    <div style="background-color: #f0fff4; border: 1px solid #c6f6d5; border-radius: 14px; padding: 12px 14px; margin-top: 5px;">
        <span style="color: #0f766e; font-weight: 700; font-size: 1rem;">
            <?php 
                $found = false;
                foreach ($marathons as $m) {
                    // On compare avec l'ID réel stocké dans la table parcours ($data)
                    if ($data['id_marathon'] == $m['id_marathon']) {
                        echo htmlspecialchars($m['id_marathon']) . " - " . htmlspecialchars($m['nom_marathon']);
                        $found = true;
                        break;
                    }
                }
                if (!$found) echo "Marathon introuvable (ID: " . htmlspecialchars($data['id_marathon']) . ")";
            ?>
        </span>
    </div>
    <input type="hidden" name="id_marathon" value="<?php echo htmlspecialchars($data['id_marathon']); ?>">
</div>
                <div class="field full">
                    <label for="nom_parcours">Nom du Parcours</label>
                    <input type="text" id="nom_parcours" name="nom_parcours" value="<?php echo htmlspecialchars($data['nom_parcours']); ?>">
                    <div class="feedback" id="nomFeedback"></div>
                </div>
                <div class="field">
                    <label for="point_depart">Point de Départ</label>
                    <input type="text" id="point_depart" name="point_depart" value="<?php echo htmlspecialchars($data['point_depart']); ?>">
                    <div class="feedback" id="departFeedback"></div>
                </div>
                <div class="field">
                    <label for="point_arrivee">Point d'Arrivée</label>
                    <input type="text" id="point_arrivee" name="point_arrivee" value="<?php echo htmlspecialchars($data['point_arrivee']); ?>">
                    <div class="feedback" id="arriveeFeedback"></div>
                </div>
                <div class="field">
                    <label for="distance">Distance (km)</label>
                    <input type="text" id="distance" name="distance" value="<?php echo htmlspecialchars($data['distance']); ?>" inputmode="decimal">
                    <div class="feedback" id="distanceFeedback"></div>
                </div>
                <div class="field">
                    <label for="difficulte">Difficulté</label>
                    <select id="difficulte" name="difficulte">
                        <?php foreach (['facile','moyen','difficile'] as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo $data['difficulte']===$d?'selected':''; ?>>
                                <?php echo ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$d]; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="feedback" id="difficulteFeedback"></div>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Sauvegarder</button>
                <a href="../../FrontOffice/detailMarathon.php?id=<?php echo $redirectMarathon; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script src="addParcours.js"></script>
</body>
</html>
