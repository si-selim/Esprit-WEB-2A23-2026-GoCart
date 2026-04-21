<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/partials/session.php';

header('Content-Type: application/json');

$controller = new MarathonController();
$search  = $_GET['search']  ?? '';
$region  = $_GET['region']  ?? '';
$mode    = $_GET['mode']    ?? 'cards';

// ---- MODE SUGGESTIONS ----
if ($mode === 'suggestions') {
    if ($search === '') { echo json_encode([]); exit; }
    $results = $controller->rechercherMarathon($search);
    $names = array_map(fn($m) => $m['nom_marathon'], $results);
    echo json_encode(array_values(array_unique($names)));
    exit;
}

// ---- MODE CARDS ----
if ($search !== '') {
    $marathons = $controller->rechercherMarathon($search);
} elseif ($region !== '') {
    $marathons = $controller->filtrerMarathon($region);
} else {
    $marathons = $controller->afficherMarathon();
}

$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';

ob_start();
if (empty($marathons)): ?>
<div class="empty-state">
    <div style="font-size:3rem;margin-bottom:14px;">🏃</div>
    <h3>Aucun marathon trouvé</h3>
</div>
<?php else: ?>
<section class="catalog" id="marathonGrid">
<?php foreach ($marathons as $m): ?>
<div class="card" style="cursor:default;">
    <a href="detailMarathon.php?id=<?php echo $m['id_marathon']; ?>" style="text-decoration:none;color:inherit;display:block;">
        <div class="card-img-wrap">
            <img src="<?php echo htmlspecialchars($m['image_marathon']); ?>" alt="<?php echo htmlspecialchars($m['nom_marathon']); ?>" onerror="this.src='images/img1.svg'">
            <span class="card-id">#<?php echo $m['id_marathon']; ?></span>
        </div>
        <div class="card-body">
            <div class="pill-row">
                <span class="pill">📍 <?php echo htmlspecialchars($m['region_marathon']); ?></span>
                <span class="pill pill-sun"><?php echo $m['nb_places_dispo']>0?'✅ '.$m['nb_places_dispo'].' places':'❌ Complet'; ?></span>
            </div>
            <h3><?php echo htmlspecialchars($m['nom_marathon']); ?></h3>
            <div class="meta">
                <span>👤 <?php echo htmlspecialchars($m['organisateur_marathon']); ?></span>
                <span>📅 <?php echo date('d/m/Y',strtotime($m['date_marathon'])); ?></span>
            </div>
            <div class="card-footer">
                <span class="price"><?php echo number_format($m['prix_marathon'],2); ?> TND</span>
                <span class="btn-detail">Voir détail →</span>
            </div>
        </div>
    </a>
    <?php if ($role === 'organisateur' || $role === 'admin'): ?>
    <div class="card-actions" style="padding:12px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
        <?php if ($role === 'organisateur'): ?>
        <a href="marathon/updateMarathon.php?id=<?php echo $m['id_marathon']; ?>" class="btn-mod-card">✏️ Modifier</a>
        <?php endif; ?>
        <button class="btn-del-card" style="flex:1;padding:8px;font-size:0.85rem;"
            onclick="confirmDelete(<?php echo $m['id_marathon']; ?>, '<?php echo addslashes($m['nom_marathon']); ?>')">
            🗑️ Supprimer
        </button>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</section>
<?php endif;

$html = ob_get_clean();
echo json_encode([
    'html'  => $html,
    'count' => count($marathons)
]);
