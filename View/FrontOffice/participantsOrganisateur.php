<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../config.php';

$currentPage = 'participants';
$user = getCurrentUser();

if (!isOrganisateur()) {
    header('Location: login.php');
    exit;
}

$pdo = config::getConnexion();
$organizerName = $user['nom_complet'] ?? $user['nom'] ?? '';

// Fetch marathons for this organizer
$stmt = $pdo->prepare("SELECT id_marathon, nom_marathon, image_marathon FROM marathon WHERE organisateur_marathon = :nom_complet");
$stmt->execute(['nom_complet' => $organizerName]);
$marathons = $stmt->fetchAll();

// Fetch participants for each marathon
$participantsByMarathon = [];
foreach ($marathons as $m) {
    $stmtP = $pdo->prepare("
        SELECT i.id_inscription, u.id_user, u.nom_complet, p.nom_parcours, p.difficulte, i.statut_course 
        FROM inscription i
        JOIN parcours p ON i.id_parcours = p.id_parcours
        JOIN user u ON i.id_user = u.id_user
        WHERE p.id_marathon = :id_marathon
    ");
    $stmtP->execute(['id_marathon' => $m['id_marathon']]);
    $participantsByMarathon[$m['id_marathon']] = $stmtP->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Participants | BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --card:#ffffff; --muted:#627d98;
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fefaf0,var(--bg)); }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0; }
        .section-card { background:var(--card); border-radius:24px; padding:22px; box-shadow:0 14px 34px rgba(16,42,67,.08); margin-bottom:28px; }
        .marathon-title { font-size:1.5rem; display:flex; align-items:center; gap:12px; margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px; }
        .marathon-img { width:60px; height:40px; border-radius:8px; object-fit:cover; }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        th, td { padding:12px; text-align:left; border-bottom:1px solid #e6edf3; }
        th { background:#102a43; color:#fff; }
        .btn { padding:8px 12px; border-radius:8px; text-decoration:none; cursor:pointer; font-weight:700; border:none; color:#fff; }
        .btn-confirm { background:var(--teal); }
        .btn-confirm:hover { background:#0d635c; }
        .tag-success { background:#dcfce7; color:#166534; padding:4px 8px; border-radius:6px; font-weight:bold; }
        .tag-pending { background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:6px; font-weight:bold; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>
    <div class="page">
        <h1>Mes Participants</h1>
        <p style="color:var(--muted)">Consultez les participants de vos marathons et confirmez leur participation pour leur attribuer de l'XP.</p>

        <div style="margin-bottom: 20px;">
            <input type="text" id="searchInput" placeholder="Rechercher par nom ou ID..." style="padding: 10px; width: 100%; max-width: 400px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        <?php if (empty($marathons)): ?>
            <div class="section-card">
                <p>Vous n'organisez aucun marathon pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($marathons as $m): ?>
                <div class="section-card">
                    <h2 class="marathon-title">
                        <img class="marathon-img" src="<?php echo htmlspecialchars($m['image_marathon'] ?? 'images/default-marathon.jpg'); ?>" onerror="this.src='images/default-marathon.jpg'">
                        <?php echo htmlspecialchars($m['nom_marathon']); ?>
                    </h2>
                    
                    <?php if (empty($participantsByMarathon[$m['id_marathon']])): ?>
                        <p style="color:var(--muted)">Aucun participant inscrit à ce marathon.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Participant</th>
                                    <th>Parcours</th>
                                    <th>Difficulté</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participantsByMarathon[$m['id_marathon']] as $p): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($p['nom_complet']); ?></strong> <span style="color:#94a3b8; font-size:0.8rem;">(#<?php echo $p['id_user']; ?>)</span></td>
                                        <td><?php echo htmlspecialchars($p['nom_parcours']); ?></td>
                                        <td><?php echo htmlspecialchars($p['difficulte']); ?></td>
                                        <td>
                                            <?php if (($p['statut_course'] ?? 'inscrit') === 'terminé'): ?>
                                                <span class="tag-success">Terminé</span>
                                            <?php elseif (($p['statut_course'] ?? 'inscrit') === 'abandon'): ?>
                                                <span style="background:#ffedd5; color:#c2410c; padding:4px 8px; border-radius:6px; font-weight:bold;">Abandon</span>
                                            <?php else: ?>
                                                <span class="tag-pending">Inscrit</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!in_array(($p['statut_course'] ?? 'inscrit'), ['terminé', 'abandon'])): ?>
                                                <form action="confirmerCourse.php" method="POST" style="margin:0; display:flex; gap:8px;">
                                                    <input type="hidden" name="id_inscription" value="<?php echo $p['id_inscription']; ?>">
                                                    <input type="hidden" name="id_user" value="<?php echo $p['id_user']; ?>">
                                                    <input type="hidden" name="difficulte" value="<?php echo $p['difficulte']; ?>">
                                                    <button type="submit" name="action_type" value="arrivee" class="btn btn-confirm">✅ Confirmer arrivée</button>
                                                    <button type="submit" name="action_type" value="depart" class="btn" style="background:#ea580c;">❌ Confirmer départ</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color:#166534;font-size:0.9rem;">✔ XP Attribué</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script>
        document.getElementById('searchInput')?.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let tables = document.querySelectorAll('table');
            
            tables.forEach(table => {
                let rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    if (text.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
