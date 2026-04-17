<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    header('Location: ../../FrontOffice/login.php'); exit;
}

$controller = new MarathonController();
$error = '';

$currentUser = getCurrentUser();
$organisateurAuto = $currentUser['nom'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image = "";
    if (!empty($_FILES['image_marathon']['name']) && $_FILES['image_marathon']['error'] === 0) {
        $uploaded = $controller->saveUploadedImage($_FILES['image_marathon']);
        if ($uploaded) { $image = $uploaded; }
    }

    $m = new Marathon(
        null,
        $_POST['nom_marathon'] ?? '',
        $image,
        $organisateurAuto,
        $_POST['region_marathon'] ?? '',
        $_POST['date_marathon'] ?? '',
        (int)($_POST['nb_places_dispo'] ?? 0),
        (float)($_POST['prix_marathon'] ?? 0)
    );

    if ($controller->ajouterMarathon($m)) {
        header('Location: ../../FrontOffice/listMarathons.php');
        exit;
    }
    $error = "Erreur lors de l'ajout du marathon.";
}

$currentPage = 'catalogue';
$role = $currentUser['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Ajouter un Marathon — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --sand:#fff8e7; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,var(--sand),var(--bg)); }
        .wrap { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:32px 0 48px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }
        .panel { background:#fff; border-radius:28px; padding:32px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .panel-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; padding-bottom:20px; border-bottom:1px solid #e6edf3; }
        .panel-icon { width:52px; height:52px; border-radius:18px; background:linear-gradient(135deg,#16a34a,#22c55e); display:grid; place-items:center; font-size:1.4rem; }
        h1 { font-size:1.9rem; }
        .lead { color:#627d98; line-height:1.7; margin-bottom:28px; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .field { display:grid; gap:6px; }
        .field.full { grid-column:1/-1; }
        label { font-weight:700; font-size:0.95rem; }
        input, select { width:100%; border-radius:14px; border:1px solid #cbd5e1; padding:12px 14px; font:inherit; transition:border .2s; }
        input:focus, select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .auto-field { background:#f0fdf4; border-color:#bbf7d0; color:#0f766e; font-weight:700; }
        .auto-note { font-size:0.82rem; color:#059669; margin-top:2px; }
        .feedback { font-size:0.85rem; min-height:18px; margin-top:2px; }
        .feedback.error { color:#dc2626; }
        .feedback.success { color:#16a34a; }
        .error-box { background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:14px; margin-bottom:20px; color:#b42318; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 20px; font-weight:700; cursor:pointer; font-size:1rem; }
        .btn-primary { background:linear-gradient(135deg,#16a34a,#22c55e); color:white; }
        .btn-secondary { background:#edf2f7; color:var(--ink); }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/../../FrontOffice/partials/topbar.php'; ?>
<div class="wrap">
    <a class="back-link" href="../../FrontOffice/listMarathons.php">← Retour au catalogue</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">🏃</div>
            <div><h1>Ajouter un Marathon</h1></div>
        </div>
        <p class="lead">Remplissez les informations du nouveau marathon.</p>

        <?php if ($error): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="grid">
                <!-- 1. Nom -->
                <div class="field">
                    <label for="nom_marathon">Nom du Marathon</label>
                    <input type="text" id="nom_marathon" name="nom_marathon" placeholder="Ex: Marathon de Tunis 2026">
                    <div class="feedback" id="nomFeedback"></div>
                </div>
                <!-- 2. Region -->
                <div class="field">
                    <label for="region_marathon">Région</label>
                    <input type="text" id="region_marathon" name="region_marathon" placeholder="Ex: Tunis, Sousse, Sfax...">
                    <div class="feedback" id="regionFeedback"></div>
                </div>
                <!-- 3. Places dispo -->
                <div class="field">
                    <label for="nb_places_dispo">Places Disponibles</label>
                    <input type="text" id="nb_places_dispo" name="nb_places_dispo" placeholder="Ex: 500" inputmode="numeric">
                    <div class="feedback" id="placesFeedback"></div>
                </div>
                <!-- 4. Prix -->
                <div class="field">
                    <label for="prix_marathon">Prix d'inscription (TND)</label>
                    <input type="text" id="prix_marathon" name="prix_marathon" placeholder="Ex: 30.00" inputmode="decimal">
                    <div class="feedback" id="prixFeedback"></div>
                </div>
                <!-- 5. Date -->
                <div class="field">
                    <label for="date_marathon">Date du Marathon</label>
                    <input type="date" id="date_marathon" name="date_marathon">
                    <div class="feedback" id="dateFeedback"></div>
                </div>
                <!-- 6. Organisateur auto -->
                <div class="field">
                    <label>Organisateur</label>
                    <input type="text" class="auto-field" value="<?php echo htmlspecialchars($organisateurAuto); ?>" readonly>
                    <span class="auto-note">✅ Attribué automatiquement à votre compte</span>
                </div>
                <!-- 7. Image -->
                <div class="field full">
                    <label for="image_marathon">Photo du Marathon</label>
                    <input type="file" id="image_marathon" name="image_marathon" accept=".jpg,.jpeg,.png,.webp">
                    <div class="feedback" id="imageFeedback"></div>
                    <div id="addPreviewContainer" style="display:none; align-items:center; gap:12px; padding:12px; background:#f0fdf4; border-radius:12px; margin-top:6px; border:1px solid #bbf7d0;">
                        <img id="addPreviewImg" src="" alt="" style="width:80px;height:56px;object-fit:cover;border-radius:8px;">
                        <span id="addPreviewLabel" style="color:#0f766e;font-size:0.9rem;font-weight:600;"></span>
                    </div>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary">✅ Enregistrer le Marathon</button>
                <a href="../../FrontOffice/listMarathons.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('image_marathon').addEventListener('change', function() {
    const file = this.files[0];
    const container = document.getElementById('addPreviewContainer');
    const img = document.getElementById('addPreviewImg');
    const label = document.getElementById('addPreviewLabel');
    if (!file) { container.style.display='none'; return; }
    const reader = new FileReader();
    reader.onload = function(e) {
        img.src = e.target.result;
        label.textContent = '📸 ' + file.name;
        container.style.display = 'flex';
    };
    reader.readAsDataURL(file);
});
</script>
<script src="addMarathon.js"></script>
</body>
</html>
