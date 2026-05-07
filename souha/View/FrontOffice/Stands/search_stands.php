<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/standcontroller.php';
require_once __DIR__ . '/../partials/session.php';

header('Content-Type: application/json');

$controller = new StandController();
$search = $_GET['search'] ?? '';
$parcours = $_GET['parcours'] ?? '';
$mode   = $_GET['mode']   ?? 'cards';
$lat    = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$lon    = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

function geocodeInternal($address) {
    if (empty($address)) return [36.8065, 10.1815];
    $localCache = [
        'ariana' => [36.8625, 10.1956], 'menzah' => [36.8465, 10.1706],
        'nasser' => [36.8580, 10.1600], 'medina' => [36.7992, 10.1706],
        'zaghouan' => [36.4022, 10.1425], 'lac' => [36.8333, 10.2333], 'marsa' => [36.8778, 10.3222]
    ];
    $addrClean = strtolower(trim($address));
    foreach ($localCache as $key => $coords) { 
        if (strpos($addrClean, $key) !== false) return $coords; 
    }
    return [36.8065, 10.1815];
}

function haversineInternal($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1); 
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    return $earthRadius * 2 * atan2(sqrt($a), sqrt(1-$a));
}

// ---- MODE SUGGESTIONS ----
if ($mode === 'suggestions') {
    if ($search === '') { echo json_encode([]); exit; }
    $results = $controller->searchStands($search);
    $names = array_map(fn($s) => $s['nom_stand'], $results);
    echo json_encode(array_values(array_unique($names)));
    exit;
}

// ---- MODE CARDS ----
if ($search !== '') {
    $listStands = $controller->searchStands($search);
} elseif ($parcours !== '') {
    $listStands = $controller->getStandsByParcours((int)$parcours);
} else {
    $listStands = $controller->listStands();
}

// Calculate distances if location is provided
if ($lat !== null && $lon !== null) {
    foreach ($listStands as &$st) {
        $coords = geocodeInternal($st['position']);
        $st['distance_km'] = haversineInternal($lat, $lon, $coords[0], $coords[1]);
    }
    unset($st);
}

$sort = $_GET['sort'] ?? '';
if ($sort !== '') {
    usort($listStands, function($a, $b) use ($sort) {
        if ($sort === 'id_asc') {
            return $a['ID_stand'] <=> $b['ID_stand'];
        } elseif ($sort === 'id_desc') {
            return $b['ID_stand'] <=> $a['ID_stand'];
        } elseif ($sort === 'nom_asc') {
            return strcasecmp($a['nom_stand'], $b['nom_stand']);
        } elseif ($sort === 'nom_desc') {
            return strcasecmp($b['nom_stand'], $a['nom_stand']);
        }
        return 0;
    });
} elseif ($lat !== null && $lon !== null) {
    usort($listStands, function($a, $b) {
        return $a['distance_km'] <=> $b['distance_km'];
    });
}

$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';

ob_start();
if (empty($listStands)): ?>
<div class="empty-state">
    <div style="font-size:3rem;margin-bottom:14px;">🏪</div>
    <h3>Aucun stand trouvé</h3>
</div>
<?php else: ?>
<section class="catalog" id="standGrid">
<?php foreach ($listStands as $s): ?>
<div class="card" style="cursor:default;">
    <div class="card-img-wrap">
        <img src="../images/LOGO.jpg?v=2" alt="<?php echo htmlspecialchars($s['nom_stand']); ?>" onerror="this.src='../logo.png'">
        <span class="card-id">#<?php echo $s['ID_stand']; ?></span>
    </div>
    <div class="card-body">
        <div class="pill-row">
            <?php if (isset($s['distance_km'])): ?>
                <span class="pill" style="background:rgba(16,185,129,.1); color:#059669;">📍 À <?php echo round($s['distance_km'], 1); ?> km</span>
            <?php else: ?>
                <span class="pill">📍 <?php echo htmlspecialchars($s['position']); ?></span>
            <?php endif; ?>
            <span class="pill pill-sun">🚩 Parcours #<?php echo $s['ID_parcours']; ?></span>
        </div>
        <h3><?php echo htmlspecialchars($s['nom_stand']); ?></h3>
        <div class="meta">
            <p class="description-cell"><?php echo htmlspecialchars($s['description']); ?></p>
        </div>
    </div>
    <?php if ($role === 'organisateur' || $role === 'admin'): ?>
    <div class="card-actions" style="padding:12px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
        <a href="updateStand.php?id=<?php echo $s['ID_stand']; ?>" class="btn-mod-card">✏️ Modifier</a>
        <button class="btn-del-card" style="flex:1;padding:8px;font-size:0.85rem;"
            onclick="confirmDelete(<?php echo $s['ID_stand']; ?>, '<?php echo addslashes($s['nom_stand']); ?>')">
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
    'count' => count($listStands)
]);
