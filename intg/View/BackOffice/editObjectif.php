<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../FrontOffice/lang.php';
require_once __DIR__ . '/../../Controller/ObjectifController.php';

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

$objCtrl = new ObjectifController();

if (!isset($_GET['id'])) {
    header('Location: objectifs.php');
    exit;
}

$id = (int)$_GET['id'];
$objectif = $objCtrl->showObjectif($id);

if (!$objectif) {
    header('Location: objectifs.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $obj = new Objectif(
        $_POST['titre'],
        $_POST['description'],
        $_POST['type_objectif'],
        (int)$_POST['target_value'],
        $_POST['recompense'],
        $_POST['description_recompense'] ?? '',
        $objectif['etat']
    );
    $objCtrl->updateObjectif($obj, $id);
    header('Location: objectifs.php');
    exit;
}
$activeTab = 'objectifs';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Objectif - BarchaThon</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .content { padding:28px; background:#f4fbfb; }
        .section-card { background:#fff; border-radius:24px; padding:28px; box-shadow:0 14px 34px rgba(16,42,67,.08); max-width:600px; margin:0 auto; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-weight:bold; margin-bottom:8px; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; }
        .btn { padding:10px 16px; border-radius:8px; border:none; cursor:pointer; color:#fff; font-weight:bold; }
        .btn-primary { background:#0f766e; }
        .btn-secondary { background:#64748b; text-decoration:none; display:inline-block; margin-left:8px; }
    </style>
</head>
<body>
<div class="layout">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
<main class="content">
    <div class="section-card">
        <h1>Modifier un Objectif</h1>
        <form method="POST">
            <div class="form-group">
                <label>Titre de l'objectif</label>
                <input type="text" name="titre" value="<?php echo htmlspecialchars($objectif['titre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" required><?php echo htmlspecialchars($objectif['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Type d'objectif</label>
                <select name="type_objectif" required>
                    <option value="marathons" <?php echo $objectif['type_objectif'] === 'marathons' ? 'selected' : ''; ?>>Participations aux marathons</option>
                    <option value="commandes" <?php echo $objectif['type_objectif'] === 'commandes' ? 'selected' : ''; ?>>Nombre de commandes</option>
                    <option value="logins" <?php echo $objectif['type_objectif'] === 'logins' ? 'selected' : ''; ?>>Connexions consécutives (jours)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Valeur cible (Nombre)</label>
                <input type="number" name="target_value" value="<?php echo $objectif['target_value']; ?>" min="1" required>
            </div>
            <div class="form-group">
                <label>Titre de la récompense</label>
                <input type="text" name="recompense" value="<?php echo htmlspecialchars($objectif['recompense']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description de la récompense (Optionnel)</label>
                <textarea name="description_recompense" rows="2"><?php echo htmlspecialchars($objectif['description_recompense']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="objectifs.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</main>
</div>
</body>
</html>
