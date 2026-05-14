<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../FrontOffice/lang.php';
require_once __DIR__ . '/../../Controller/ObjectifController.php';

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

$objCtrl = new ObjectifController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $objCtrl->deleteObjectif($_POST['delete_id']);
    header('Location: objectifs.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $etat = (int)$_POST['new_etat'];
    $objCtrl->toggleEtat($_POST['toggle_id'], $etat);
    header('Location: objectifs.php');
    exit;
}

$objectifs = $objCtrl->listObjectifs();
$activeTab = 'objectifs';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Objectifs - BarchaThon</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .content { padding:28px; background:#f4fbfb; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; }
        .section-card { background:#fff; border-radius:24px; padding:28px; box-shadow:0 14px 34px rgba(16,42,67,.08); }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #e6edf3; }
        th { background:#102a43; color:#fff; }
        .btn { padding:8px 14px; border-radius:8px; text-decoration:none; color:#fff; border:none; cursor:pointer; }
        .btn-primary { background:#0f766e; }
        .btn-edit { background:#d97706; }
        .btn-delete { background:#d92d20; }
        .btn-toggle { background:#475569; }
        .badge { padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold; }
        .badge-active { background:#dcfce7; color:#166534; }
        .badge-inactive { background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>
<div class="layout">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
<main class="content">
    <div class="head">
        <div>
            <h1>Gestion des Objectifs & Récompenses</h1>
            <p>Créez et gérez les objectifs que les participants peuvent accomplir.</p>
        </div>
        <div>
            <a href="addObjectif.php" class="btn btn-primary">Créer un objectif</a>
        </div>
    </div>
    
    <div class="section-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Cible</th>
                    <th>Récompense</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($objectifs as $obj): ?>
                <tr>
                    <td><?php echo $obj['id_objectif']; ?></td>
                    <td><?php echo htmlspecialchars($obj['titre']); ?></td>
                    <td><?php echo htmlspecialchars($obj['type_objectif']); ?></td>
                    <td><?php echo htmlspecialchars($obj['target_value']); ?></td>
                    <td><?php echo htmlspecialchars($obj['recompense']); ?></td>
                    <td>
                        <span class="badge <?php echo $obj['etat'] == 1 ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $obj['etat'] == 1 ? 'Actif' : 'Inactif'; ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="toggle_id" value="<?php echo $obj['id_objectif']; ?>">
                                <input type="hidden" name="new_etat" value="<?php echo $obj['etat'] == 1 ? 0 : 1; ?>">
                                <button type="submit" class="btn btn-toggle"><?php echo $obj['etat'] == 1 ? 'Désactiver' : 'Activer'; ?></button>
                            </form>
                            <a href="editObjectif.php?id=<?php echo $obj['id_objectif']; ?>" class="btn btn-edit">Modifier</a>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer cet objectif ?');">
                                <input type="hidden" name="delete_id" value="<?php echo $obj['id_objectif']; ?>">
                                <button type="submit" class="btn btn-delete">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</div>
</body>
</html>
