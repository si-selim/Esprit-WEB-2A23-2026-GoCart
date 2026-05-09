<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

header('Content-Type: application/json');

if (!isAdmin()) { echo json_encode(['html'=>'','count'=>0]); exit; }

$pCtrl      = new ParcoursController();
$id_marathon = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$search      = trim($_GET['search'] ?? '');
$diff        = trim($_GET['difficulte'] ?? '');

if ($id_marathon <= 0) { echo json_encode(['html'=>'','count'=>0]); exit; }

// Get all parcours and filter by marathon
$tous = $pCtrl->afficherParcours();
$parcours = array_values(array_filter($tous, fn($p) => (int)$p['id_marathon'] === $id_marathon));

// Apply search
if ($search !== '') {
    $parcours = array_values(array_filter($parcours, fn($p) =>
        stripos($p['nom_parcours'], $search) !== false ||
        stripos($p['point_depart'], $search) !== false ||
        stripos($p['point_arrivee'], $search) !== false
    ));
}
// Apply difficulty filter
if ($diff !== '') {
    $parcours = array_values(array_filter($parcours, fn($p) =>
        strtolower(trim($p['difficulte'] ?? '')) === strtolower($diff)
    ));
}

ob_start();
if (empty($parcours)): ?>
<tr><td colspan="8" style="text-align:center;padding:30px;color:#64748b;">Aucun parcours trouvé pour ce marathon.</td></tr>
<?php else: foreach ($parcours as $p2):
    $diffRaw = strtolower(trim($p2['difficulte'] ?? ''));
    $dc = ['facile'=>'tag-easy','moyen'=>'tag-med','difficile'=>'tag-hard'][$diffRaw] ?? '';
    $diffLabel = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$diffRaw] ?? htmlspecialchars($p2['difficulte'] ?? '—');
?>
<tr>
    <td><strong>#<?php echo (int)$p2['id_parcours']; ?></strong></td>
    <td><strong><?php echo htmlspecialchars($p2['nom_parcours']); ?></strong></td>
    <td><?php echo !empty($p2['heure_depart']) ? '<strong>'.htmlspecialchars(substr($p2['heure_depart'],0,5)).'</strong>' : '<span style="color:#94a3b8;">—</span>'; ?></td>
    <td><?php echo htmlspecialchars($p2['point_depart']); ?></td>
    <td><?php echo htmlspecialchars($p2['point_arrivee']); ?></td>
    <td><strong><?php echo number_format((float)$p2['distance'], 2); ?> km</strong></td>
    <td><span class="tag <?php echo $dc; ?>"><?php echo $diffLabel; ?></span></td>
    <td>
        <div class="table-actions">
            <button class="btn btn-danger btn-sm"
                onclick="askDel(<?php echo (int)$p2['id_parcours']; ?>, '<?php echo htmlspecialchars($p2['nom_parcours'], ENT_QUOTES); ?>')">
                Supprimer
            </button>
        </div>
    </td>
</tr>
<?php endforeach; endif;

$html = ob_get_clean();
echo json_encode(['html' => $html, 'count' => count($parcours)]);
