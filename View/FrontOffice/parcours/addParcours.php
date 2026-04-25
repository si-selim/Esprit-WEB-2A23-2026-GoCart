<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../../Model/Parcours.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    header('Location: ../../FrontOffice/login.php'); exit;
}

$parcoursController = new ParcoursController();
$marathonController = new MarathonController();

$marathons = $marathonController->afficherMarathon();
$error = '';
$preselectedMarathon = isset($_GET['marathon_id']) ? (int)$_GET['marathon_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom_parcours'] ?? '';
    $depart = $_POST['point_depart'] ?? '';
    $arrivee = $_POST['point_arrivee'] ?? '';
    $distance = (float)($_POST['distance'] ?? 0);
    $difficulte = $_POST['difficulte'] ?? '';
    $id_marathon = (int)($_POST['id_marathon'] ?? 0);

    if ($nom !== '' && $depart !== '' && $arrivee !== '' && $distance > 0 && $id_marathon > 0) {
        $p = new Parcours(null, $nom, $depart, $arrivee, $distance, $difficulte, $id_marathon);

        if ($parcoursController->ajouterParcours($p)) {
            // Redirect back to detail page if came from there
            header('Location: ../../FrontOffice/detailMarathon.php?id=' . $id_marathon);
            exit;
        } else {
            $error = "Erreur lors de l'ajout du parcours.";
        }
    } else {
        $error = "Données invalides. Veuillez remplir tous les champs.";
    }
}

$currentPage = 'catalogue';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Ajouter un Parcours — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --sand:#fff8e7; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,var(--sand),var(--bg)); }
        .wrap { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:32px 0 48px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }
        .panel { background:#fff; border-radius:28px; padding:32px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .panel-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; padding-bottom:20px; border-bottom:1px solid #e6edf3; }
        .panel-icon { width:52px; height:52px; border-radius:18px; background:linear-gradient(135deg,#059669,#10b981); display:grid; place-items:center; font-size:1.4rem; }
        h1 { font-size:1.9rem; }
        .lead { color:#627d98; line-height:1.7; margin-bottom:28px; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .field { display:grid; gap:8px; }
        .field.full { grid-column:1/-1; }
        label { font-weight:700; font-size:0.95rem; }
        input, select { width:100%; border-radius:14px; border:1px solid #cbd5e1; padding:12px 14px; font:inherit; transition:border .2s; }
        input:focus, select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .feedback { font-size:0.88rem; min-height:18px; }
        .error-box { background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:14px; margin-bottom:20px; color:#b42318; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 20px; font-weight:700; cursor:pointer; font-size:1rem; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; }
        .btn-secondary { background:#edf2f7; color:var(--ink); }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/../../FrontOffice/partials/topbar.php'; ?>
<div class="wrap">
    <a class="back-link" href="<?php echo $preselectedMarathon ? '../../FrontOffice/detailMarathon.php?id='.$preselectedMarathon : '../../FrontOffice/listMarathons.php'; ?>">← Retour</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">🗺️</div>
            <div>
                <h1>Ajouter un Parcours</h1>
            </div>
        </div>
        <p class="lead">Définissez les informations du parcours et associez-le à un marathon existant.</p>

        <?php if ($error): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="grid">
                <div class="field full">
                    <label>Marathon associé</label>
                    <?php if ($preselectedMarathon > 0):
                        $marathonNom = '';
                        foreach ($marathons as $m) {
                            if ($m['id_marathon'] == $preselectedMarathon) { $marathonNom = $m['nom_marathon']; break; }
                        }
                    ?>
                    <div style="background-color:#f0fff4;border:1px solid #c6f6d5;border-radius:14px;padding:12px 14px;margin-top:5px;">
                        <span style="color:#0f766e;font-weight:700;font-size:1rem;">
                            <?php echo htmlspecialchars($preselectedMarathon) . ' - ' . htmlspecialchars($marathonNom); ?>
                        </span>
                    </div>
                    <input type="hidden" id="id_marathon" name="id_marathon" value="<?php echo $preselectedMarathon; ?>">
                    <?php else: ?>
                    <select id="id_marathon" name="id_marathon">
                        <option value="">— Choisir un marathon —</option>
                        <?php foreach ($marathons as $m): ?>
                            <option value="<?php echo $m['id_marathon']; ?>">
                                <?php echo htmlspecialchars($m['id_marathon'] . ' - ' . $m['nom_marathon']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="feedback" id="marathonFeedback"></div>
                    <?php endif; ?>
                </div>
                <div class="field full">
                    <label for="nom_parcours">Nom du Parcours</label>
                    <input type="text" id="nom_parcours" name="nom_parcours" placeholder="Ex: Parcours Nord de Tunis">
                    <div class="feedback" id="nomFeedback"></div>
                </div>
                <div class="field">
                    <label for="point_depart">Point de Départ</label>
                    <input type="text" id="point_depart" name="point_depart" placeholder="Ex: Place du Gouvernorat">
                    <div class="feedback" id="departFeedback"></div>
                </div>
                <div class="field">
                    <label for="point_arrivee">Point d'Arrivée</label>
                    <input type="text" id="point_arrivee" name="point_arrivee" placeholder="Ex: Avenue Habib Bourguiba">
                    <div class="feedback" id="arriveeFeedback"></div>
                </div>
                <div class="field">
                    <label for="distance">Distance (km)</label>
                    <input type="number" id="distance" name="distance" min="0.1" step="0.01" placeholder="Ex: 10.5">
                    <div class="feedback" id="distanceFeedback"></div>
                </div>
                <div class="field">
                    <label for="difficulte">Difficulté</label>
                    <select id="difficulte" name="difficulte">
                        <option value="">— Choisir —</option>
                        <option value="facile">🟢 Facile</option>
                        <option value="moyen">🟡 Moyen</option>
                        <option value="difficile">🔴 Difficile</option>
                    </select>
                    <div class="feedback" id="difficulteFeedback"></div>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary">✅ Enregistrer le Parcours</button>
                <a href="<?php echo $preselectedMarathon ? '../../FrontOffice/detailMarathon.php?id='.$preselectedMarathon : '../../FrontOffice/listMarathons.php'; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script src="addParcours.js"></script>
</body>
</html>
