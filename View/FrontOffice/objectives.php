<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/../../Controller/ObjectifController.php';

$user = getCurrentUser();
if (!$user || $user['role'] !== 'participant') {
    header('Location: login.php');
    exit;
}

$currentPage = 'objectives';
$objCtrl = new ObjectifController();
$userId = $user['id_user'] ?? $user['id'];

// Handle claim request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'])) {
    $claimId = (int)$_POST['claim_id'];
    $objectif = $objCtrl->showObjectif($claimId);
    
    if ($objectif && $objectif['etat'] == 1) {
        $progress = $objCtrl->getUserProgress($userId, $objectif['type_objectif']);
        $claims = $objCtrl->getUserClaims($userId);
        
        if ($progress >= $objectif['target_value'] && !in_array($claimId, $claims)) {
            $objCtrl->claimRecompense($userId, $claimId);
            $success_message = "Récompense réclamée avec succès !";
            // Update session XP for immediate topbar reflection
            $xpEarned = (int)$objectif['target_value'] * 10;
            $_SESSION['user']['xp'] = ($_SESSION['user']['xp'] ?? 0) + $xpEarned;
            $user['xp'] = $_SESSION['user']['xp']; // update local var for this request
        } else {
            $error_message = "Vous ne remplissez pas les conditions ou vous avez déjà réclamé cette récompense.";
        }
    }
}

$objectifs = $objCtrl->listActiveObjectifs();
$claims = $objCtrl->getUserClaims($userId);

// Cache user progress to avoid duplicate queries
$progressCache = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objectifs & Récompenses — BarchaThon</title>
    <style>
        body { margin:0; font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#fff8e7,#eef8f8); color:#102a43; }
        .wrap { width:min(1200px, calc(100% - 32px)); margin:24px auto 40px; }
        .hero { background:linear-gradient(135deg,#102a43,#0f766e); color:#fff; border-radius:28px; padding:34px 32px; box-shadow:0 20px 40px rgba(16,42,67,.16); }
        .hero h1 { font-size:2.5rem; margin:0 0 10px; }
        .hero p { opacity:0.9; font-size:1.1rem; margin:0; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px; margin-top:24px; }
        .card { background:#fff; border-radius:24px; padding:24px; box-shadow:0 14px 30px rgba(16,42,67,.06); position:relative; overflow:hidden; }
        .card.claimed::before { content:''; position:absolute; inset:0; background:rgba(255,255,255,0.6); z-index:1; }
        .card-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; }
        .card-title { font-size:1.25rem; font-weight:800; color:#102a43; margin:0 0 4px; }
        .badge { background:#e2e8f0; color:#475569; padding:4px 10px; border-radius:999px; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; }
        .badge-marathons { background:#dbeafe; color:#1e40af; }
        .badge-commandes { background:#fef3c7; color:#92400e; }
        .badge-logins { background:#dcfce7; color:#166534; }
        .card-desc { color:#475569; font-size:0.95rem; margin-bottom:20px; line-height:1.5; }
        .progress-container { background:#e2e8f0; border-radius:999px; height:8px; width:100%; margin-bottom:8px; overflow:hidden; }
        .progress-bar { background:linear-gradient(90deg,#0f766e,#14b8a6); height:100%; border-radius:999px; transition:width 0.4s ease; }
        .progress-text { display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; color:#64748b; margin-bottom:20px; }
        .reward-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:16px; display:flex; flex-direction:column; gap:12px; }
        .reward-title { font-weight:700; color:#0f766e; font-size:1.05rem; }
        .reward-desc { font-size:0.85rem; color:#64748b; }
        .btn { display:inline-block; padding:10px 16px; border-radius:12px; font-weight:700; border:none; cursor:pointer; text-align:center; transition:transform 0.2s, box-shadow 0.2s; font-size:0.95rem; text-decoration:none; width:100%; }
        .btn:hover { transform:translateY(-1px); box-shadow:0 8px 16px rgba(0,0,0,0.1); }
        .btn-claim { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; }
        .btn-disabled { background:#e2e8f0; color:#94a3b8; cursor:not-allowed; box-shadow:none !important; transform:none !important; }
        .btn-claimed { background:#dcfce7; color:#166534; cursor:default; pointer-events:none; }
        .alert { padding:14px 20px; border-radius:16px; margin-bottom:20px; font-weight:600; }
        .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .alert-error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .empty-state { text-align:center; padding:40px; background:#fff; border-radius:24px; color:#64748b; }
        .z-2 { position:relative; z-index:2; }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>

<div class="wrap">
    <section class="hero">
        <h1>Objectifs & Récompenses</h1>
        <p>Accomplissez des défis pour débloquer des récompenses exclusives.</p>
    </section>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success" style="margin-top:20px;"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error" style="margin-top:20px;"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (empty($objectifs)): ?>
        <div class="empty-state" style="margin-top:24px;">
            <h3>Aucun objectif disponible pour le moment.</h3>
            <p>Revenez plus tard pour découvrir de nouveaux défis !</p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($objectifs as $obj): 
                $type = $obj['type_objectif'];
                if (!isset($progressCache[$type])) {
                    $progressCache[$type] = $objCtrl->getUserProgress($userId, $type);
                }
                $progress = $progressCache[$type];
                $target = $obj['target_value'];
                $percentage = min(100, ($progress / $target) * 100);
                
                $isClaimed = in_array($obj['id_objectif'], $claims);
                $canClaim = $progress >= $target && !$isClaimed;
            ?>
            <div class="card <?php echo $isClaimed ? 'claimed' : ''; ?>">
                <div class="card-header z-2">
                    <h3 class="card-title"><?php echo htmlspecialchars($obj['titre']); ?></h3>
                    <span class="badge badge-<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></span>
                </div>
                <p class="card-desc z-2"><?php echo htmlspecialchars($obj['description']); ?></p>
                
                <div class="progress-container z-2">
                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%;"></div>
                </div>
                <div class="progress-text z-2">
                    <span>Progression</span>
                    <span><?php echo $progress; ?> / <?php echo $target; ?></span>
                </div>

                <div class="reward-box z-2">
                    <div class="reward-title">🎁 <?php echo htmlspecialchars($obj['recompense']); ?></div>
                    <div class="reward-desc" style="color: #0f766e; font-weight: bold;">⭐ +<?php echo (int)$target * 10; ?> XP</div>
                    <?php if ($obj['description_recompense']): ?>
                        <div class="reward-desc"><?php echo htmlspecialchars($obj['description_recompense']); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($isClaimed): ?>
                        <div class="btn btn-claimed">✔ Réclamé</div>
                    <?php elseif ($canClaim): ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="claim_id" value="<?php echo $obj['id_objectif']; ?>">
                            <button type="submit" class="btn btn-claim">Réclamer</button>
                        </form>
                    <?php else: ?>
                        <div class="btn btn-disabled">En cours...</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
