<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/ParcoursController.php';
require_once __DIR__ . '/partials/session.php';

header('Content-Type: application/json');

$pCtrl   = new ParcoursController();
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$search  = trim($_GET['search'] ?? '');
$diff    = trim($_GET['difficulte'] ?? '');
$mode    = $_GET['mode'] ?? 'cards';

if ($id <= 0) { echo json_encode(['html'=>'','count'=>0]); exit; }

$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';

// Get all parcours for this marathon
$tous = $pCtrl->afficherParcours();
$parcours = array_values(array_filter($tous, fn($p) => (int)$p['id_marathon'] === $id));

// Apply search & filter
if ($search !== '') {
    $parcours = array_values(array_filter($parcours, fn($p) =>
        stripos($p['nom_parcours'], $search) !== false ||
        stripos($p['point_depart'], $search) !== false ||
        stripos($p['point_arrivee'], $search) !== false
    ));
}
if ($diff !== '') {
    $parcours = array_values(array_filter($parcours, fn($p) => $p['difficulte'] === $diff));
}

// MODE SUGGESTIONS
if ($mode === 'suggestions') {
    if ($search === '') { echo json_encode([]); exit; }
    $names = array_unique(array_map(fn($p) => $p['nom_parcours'], $parcours));
    echo json_encode(array_values($names));
    exit;
}

// MODE CARDS
ob_start();
if (empty($parcours)): ?>
<div class="empty-box">🗺️ Aucun parcours trouvé pour ce marathon.</div>
<?php else:
    foreach ($parcours as $p):
        $dc = ['facile'=>'diff-facile','moyen'=>'diff-moyen','difficile'=>'diff-difficile'][$p['difficulte']] ?? 'diff-moyen';
        $dl = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$p['difficulte']] ?? $p['difficulte'];
?>
<div class="p-card">
    <div class="diff-band <?php echo $dc; ?>"><?php echo $dl; ?></div>
    <div class="p-body">
        <h3><?php echo htmlspecialchars($p['nom_parcours']); ?></h3>
        <div class="p-route">
            <span>📍 <strong>Départ :</strong> <?php echo htmlspecialchars($p['point_depart']); ?></span>
            <span>🏁 <strong>Arrivée :</strong> <?php echo htmlspecialchars($p['point_arrivee']); ?></span>
        </div>
        <div class="dist-row">
            <div>
                <div class="dist-val"><?php echo number_format((float)$p['distance'], 2); ?> km</div>
            </div>
            <div style="display:flex;justify-content:flex-end;flex-grow:1;margin-top:10px;">
                <a href="details.php?id=<?php echo $p['id_parcours']; ?>"
                   style="background:linear-gradient(135deg,#149184,#0eb19d);color:white;padding:6px 15px;border-radius:20px;text-decoration:none;font-weight:bold;font-size:0.85rem;display:flex;align-items:center;gap:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                   Voir détail <span style="font-size:1.1rem;">→</span>
                </a>
            </div>
        </div>
    </div>
    <?php if ($role === 'organisateur' || $role === 'admin'): ?>
    <div class="p-actions" style="padding:12px 16px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
        <?php if ($role === 'organisateur'): ?>
        <a href="parcours/updateParcours.php?id=<?php echo $p['id_parcours']; ?>&redirect_marathon=<?php echo $id; ?>" class="btn-mod" style="flex:1;text-align:center;padding:9px 8px;"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>
        <?php endif; ?>
        <button class="btn-del-card" style="flex:1;padding:9px 8px;" onclick="confirmDeleteParcours(<?php echo $p['id_parcours']; ?>, '<?php echo addslashes($p['nom_parcours']); ?>')"><i class="fa-solid fa-trash"></i> Supprimer</button>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; endif;
$html = ob_get_clean();
echo json_encode(['html' => $html, 'count' => count($parcours)]);
exit;
