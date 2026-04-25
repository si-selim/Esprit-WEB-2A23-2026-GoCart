<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/ParcoursController.php';

header('Content-Type: application/json');

$controller = new ParcoursController();
$search = $_GET['search']    ?? '';
$diff   = $_GET['difficulte'] ?? '';
$mode   = $_GET['mode']       ?? 'cards'; // 'suggestions' | 'cards'

// ---- MODE SUGGESTIONS : retourne seulement les noms ----
if ($mode === 'suggestions') {
    if ($search === '') { echo json_encode([]); exit; }
    $all = $controller->afficherParcours();
    $names = [];
    foreach ($all as $p) {
        if (stripos($p['nom_parcours'], $search) !== false) {
            $names[] = $p['nom_parcours'];
        }
    }
    echo json_encode(array_values(array_unique($names)));
    exit;
}

// ---- MODE CARDS : retourne les cartes HTML ----
$all = $controller->afficherParcours();

// Filtrer
if ($search !== '') {
    $all = array_values(array_filter($all, fn($p) => stripos($p['nom_parcours'], $search) !== false));
}
if ($diff !== '') {
    $all = array_values(array_filter($all, fn($p) => $p['difficulte'] === $diff));
}

ob_start();
if (empty($all)): ?>
<div class="empty-state">
    <div style="font-size:3rem;margin-bottom:14px;">🗺️</div>
    <h3>Aucun parcours trouvé</h3>
</div>
<?php else: ?>
<section class="catalog">
<?php foreach ($all as $p):
    $dc = ['facile'=>'diff-facile','moyen'=>'diff-moyen','difficile'=>'diff-difficile'][$p['difficulte']] ?? 'diff-moyen';
    $dl = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$p['difficulte']] ?? $p['difficulte'];
?>
<article class="card">
    <div class="diff-banner <?php echo $dc; ?>"><?php echo $dl; ?></div>
    <div class="card-body">
        <span class="marathon-tag">🏃 <?php echo htmlspecialchars($p['nom_marathon']); ?></span>
        <h3><?php echo htmlspecialchars($p['nom_parcours']); ?></h3>
        <div class="route-info">
            <div class="route-row">📍 <span><strong>Départ :</strong> <?php echo htmlspecialchars($p['point_depart']); ?></span></div>
            <div class="route-row">🏁 <span><strong>Arrivée :</strong> <?php echo htmlspecialchars($p['point_arrivee']); ?></span></div>
        </div>
        <div class="dist-row">
            <div>
                <div class="dist-val"><?php echo number_format((float)$p['distance'],2); ?> km</div>
                <div style="color:#627d98;font-size:.82rem;">Distance totale</div>
            </div>
            <span style="font-size:2rem;">🏅</span>
        </div>
    </div>
</article>
<?php endforeach; ?>
</section>
<?php endif;

$html = ob_get_clean();
echo json_encode([
    'html'  => $html,
    'count' => count($all)
]);
