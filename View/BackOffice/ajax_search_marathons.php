<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';

header('Content-Type: application/json');

if (!isAdmin()) { echo json_encode(['html'=>'','count'=>0]); exit; }

$mCtrl  = new MarathonController();
$search = trim($_GET['search'] ?? '');
$region = trim($_GET['region'] ?? '');

if ($search !== '') {
    $marathons = $mCtrl->rechercherMarathon($search);
} elseif ($region !== '') {
    $marathons = $mCtrl->filtrerMarathon($region);
} else {
    $marathons = $mCtrl->afficherMarathon();
}

ob_start();
if (empty($marathons)): ?>
<tr><td colspan="9" style="text-align:center;color:#627d98;padding:28px;">Aucun marathon trouvé.</td></tr>
<?php else: foreach ($marathons as $m2):
    $imgSrc = (!empty($m2['image_marathon'])) ? '../FrontOffice/' . htmlspecialchars($m2['image_marathon']) : '../FrontOffice/marathon/images/hero_runner.png';
?>
<tr>
    <td><strong><?php echo (int)$m2['id_marathon']; ?></strong></td>
    <td>
        <img class="marathon-img"
             src="<?php echo $imgSrc; ?>"
             onerror="this.onerror=null;this.src='../FrontOffice/marathon/images/hero_runner.png';" alt="">
    </td>
    <td><strong><?php echo htmlspecialchars($m2['nom_marathon']); ?></strong></td>
    <td><?php echo htmlspecialchars($m2['organisateur_marathon']); ?></td>
    <td><span class="tag">📍 <?php echo htmlspecialchars($m2['region_marathon']); ?></span></td>
    <td><?php echo date('d/m/Y', strtotime($m2['date_marathon'])); ?></td>
    <td>
        <?php if ($m2['nb_places_dispo'] > 0): ?>
            <span class="tag"><?php echo $m2['nb_places_dispo']; ?></span>
        <?php else: ?>
            <span class="tag tag-no">Complet</span>
        <?php endif; ?>
    </td>
    <td><strong><?php echo number_format($m2['prix_marathon'], 2); ?></strong></td>
    <td>
        <div class="table-actions">
            <a class="btn btn-secondary btn-sm" href="voirdetailsparcours.php?id=<?php echo (int)$m2['id_marathon']; ?>">Voir details</a>
            <a class="btn btn-affiche btn-sm" href="affiche_marathon.php?id=<?php echo (int)$m2['id_marathon']; ?>" target="_blank">Affiche</a>
            <button class="btn btn-danger btn-sm" onclick="askDelM(<?php echo (int)$m2['id_marathon']; ?>, '<?php echo htmlspecialchars($m2['nom_marathon'], ENT_QUOTES); ?>')">Supprimer</button>
        </div>
    </td>
</tr>
<?php endforeach; endif;

$html = ob_get_clean();
echo json_encode(['html' => $html, 'count' => count($marathons)]);
