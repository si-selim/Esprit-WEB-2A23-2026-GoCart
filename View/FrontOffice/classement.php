<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../config.php';

$currentPage = 'classement';
$user = getCurrentUser();

$pdo = config::getConnexion();

// Fetch all participants sorted by XP descending
$stmt = $pdo->prepare("SELECT id_user, nom_complet, xp FROM user WHERE role = 'participant' ORDER BY xp DESC");
$stmt->execute();
$participants = $stmt->fetchAll();

// Determine rank thresholds (same as in topbar.php)
function getRankInfo($xp) {
    if ($xp >= 1500) return ['rank' => 'Challenger', 'color' => '#14b8a6'];
    if ($xp >= 1000) return ['rank' => 'Master', 'color' => '#d946ef'];
    if ($xp >= 600)  return ['rank' => 'Platinum', 'color' => '#94a3b8'];
    if ($xp >= 300)  return ['rank' => 'Gold', 'color' => '#f59e0b'];
    if ($xp >= 100)  return ['rank' => 'Silver', 'color' => '#64748b'];
    return ['rank' => 'Bronze', 'color' => '#cd7f32'];
}

$myPosition = null;
$myXp = 0;
foreach ($participants as $index => $p) {
    if ($user && ($p['id_user'] == ($user['id_user'] ?? $user['id'] ?? 0))) {
        $myPosition = $index + 1;
        $myXp = $p['xp'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement Global | BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { margin:0; font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#fefaf0,#f4fbfb); color:#102a43; }
        .page { width:min(900px,calc(100% - 32px)); margin:40px auto; }
        .card { background:#fff; border-radius:24px; padding:30px; box-shadow:0 14px 34px rgba(16,42,67,.08); }
        h1 { text-align:center; color:#0f766e; margin-top:0; }
        .my-rank { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; padding:16px; border-radius:16px; margin-bottom:24px; text-align:center; font-size:1.1rem; box-shadow:0 8px 20px rgba(15,118,110,.2); }
        .my-rank strong { font-size:1.4rem; }
        .leaderboard { width:100%; border-collapse:collapse; }
        .leaderboard th { text-align:left; padding:14px; background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#475569; font-weight:700; }
        .leaderboard td { padding:14px; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
        .leaderboard tr:last-child td { border-bottom:none; }
        .leaderboard tr.me { background:#f0fdfa; }
        .leaderboard tr:hover { background:#f8fafc; }
        .rank-pos { font-size:1.2rem; font-weight:800; color:#94a3b8; width:50px; text-align:center; }
        .rank-pos.top-1 { color:#fbbf24; font-size:1.5rem; }
        .rank-pos.top-2 { color:#94a3b8; font-size:1.4rem; }
        .rank-pos.top-3 { color:#b45309; font-size:1.3rem; }
        .rank-badge { padding:4px 10px; border-radius:999px; font-weight:700; font-size:0.85rem; display:inline-block; }
        .xp-val { font-weight:800; color:#0f766e; }
        .user-name { font-weight:600; font-size:1.05rem; }
        .search-input { padding: 12px 16px; width: 100%; max-width: 400px; border-radius: 12px; border: 1px solid #cbd5e1; outline: none; font-size: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); background: #fff; color: #102a43; transition: border-color 0.2s; }
        .search-input:focus { border-color: #0f766e; }
        html[data-theme="dark"] body { background:#0f172a; color:#e2e8f0; }
        html[data-theme="dark"] .card { background:#1e293b; border:1px solid rgba(255,255,255,0.08); }
        html[data-theme="dark"] h1 { color:#5eead4; }
        html[data-theme="dark"] .leaderboard th { background:#0f172a; color:#94a3b8; border-bottom-color:#334155; }
        html[data-theme="dark"] .leaderboard td { border-bottom-color:#334155; }
        html[data-theme="dark"] .leaderboard tr.me { background:rgba(20,184,166,0.1); }
        html[data-theme="dark"] .leaderboard tr:hover { background:#334155; }
        html[data-theme="dark"] .user-name { color:#f8fafc; }
        html[data-theme="dark"] .xp-val { color:#5eead4; }
        html[data-theme="dark"] .search-input { background: #0f172a; color: #e2e8f0; border-color: #334155; }
        html[data-theme="dark"] .search-input:focus { border-color: #5eead4; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>
    <div class="page">
        <div class="card">
            <h1>🏆 Classement Global</h1>
            
            <?php if ($myPosition): ?>
            <div class="my-rank">
                Vous êtes classé(e) <strong>#<?php echo $myPosition; ?></strong> sur <?php echo count($participants); ?> participants avec <strong><?php echo $myXp; ?> XP</strong> !
            </div>
            <?php endif; ?>

            <div style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
                <input type="text" id="searchInput" class="search-input" placeholder="Rechercher par nom..." style="flex: 1; min-width: 200px;">
                <select id="rankFilter" class="search-input" style="flex: 0 0 auto; width: auto; min-width: 150px; cursor: pointer;">
                    <option value="">Tous les rangs</option>
                    <option value="Challenger">Challenger</option>
                    <option value="Master">Master</option>
                    <option value="Platinum">Platinum</option>
                    <option value="Gold">Gold</option>
                    <option value="Silver">Silver</option>
                    <option value="Bronze">Bronze</option>
                </select>
            </div>

            <table class="leaderboard">
                <thead>
                    <tr>
                        <th style="text-align:center;">#</th>
                        <th>Participant</th>
                        <th>Rang</th>
                        <th>XP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $index => $p): 
                        $pos = $index + 1;
                        $posClass = '';
                        if ($pos == 1) $posClass = 'top-1';
                        elseif ($pos == 2) $posClass = 'top-2';
                        elseif ($pos == 3) $posClass = 'top-3';

                        $isMe = ($user && $p['id_user'] == ($user['id_user'] ?? $user['id'] ?? 0));
                        $rInfo = getRankInfo($p['xp'] ?? 0);
                        $displayName = $p['nom_complet'];
                    ?>
                    <tr class="<?php echo $isMe ? 'me' : ''; ?>">
                        <td class="rank-pos <?php echo $posClass; ?>">
                            <?php if ($pos == 1) echo '🥇'; elseif ($pos == 2) echo '🥈'; elseif ($pos == 3) echo '🥉'; else echo $pos; ?>
                        </td>
                        <td class="user-name">
                            <?php echo htmlspecialchars($displayName); ?>
                            <?php if ($isMe) echo ' <span style="font-size:0.8rem; color:#14b8a6;">(Vous)</span>'; ?>
                        </td>
                        <td>
                            <span class="rank-badge" style="background: <?php echo $rInfo['color']; ?>20; color: <?php echo $rInfo['color']; ?>; border: 1px solid <?php echo $rInfo['color']; ?>80;">
                                <?php echo $rInfo['rank']; ?>
                            </span>
                        </td>
                        <td class="xp-val"><?php echo (int)$p['xp']; ?> XP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        const searchInput = document.getElementById('searchInput');
        const rankFilter = document.getElementById('rankFilter');

        function filterTable() {
            let textFilter = searchInput ? searchInput.value.toLowerCase() : '';
            let rankValue = rankFilter ? rankFilter.value.toLowerCase() : '';
            let rows = document.querySelectorAll('.leaderboard tbody tr');
            
            rows.forEach(row => {
                let nameCell = row.querySelector('.user-name');
                let rankBadge = row.querySelector('.rank-badge');
                
                let nameText = nameCell ? nameCell.innerText.toLowerCase() : '';
                let rankText = rankBadge ? rankBadge.innerText.toLowerCase() : '';
                
                let matchesName = textFilter === '' || nameText.includes(textFilter);
                let matchesRank = rankValue === '' || rankText.includes(rankValue);
                
                if (matchesName && matchesRank) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('keyup', filterTable);
        if (rankFilter) rankFilter.addEventListener('change', filterTable);
    </script>
</body>
</html>
