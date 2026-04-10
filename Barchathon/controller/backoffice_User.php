<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/read_users.php';

$paysStmt = $pdo->query("SELECT DISTINCT pays FROM `user` WHERE pays IS NOT NULL AND pays != '' ORDER BY pays");
$paysList = $paysStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Utilisateurs</title>
    <link rel="stylesheet" href="../view/assets/css/style.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="../view/assets/images/logo_barchathon.jpg" alt="Logo Barchathon">
                <div>
                    <strong>Admin Back Office</strong><br>
                    <small>Gestion des utilisateurs</small>
                </div>
            </div>
            <nav class="side-nav">
                <a class="side-link" href="dashboard.php">Dashboard</a>
                <a class="side-link active" href="backoffice_User.php">Utilisateurs</a>
                <a class="side-link" href="#">Marathons</a>
                <a class="side-link" href="#">Parcours</a>
                <a class="side-link" href="#">Parametres</a>
                <a class="side-link" href="logout.php">Deconnexion</a>
            </nav>
            <div class="side-note">
                Gestion des utilisateurs : recherche, filtres et suppression.
            </div>
        </aside>
        <main class="content">
            <div class="mobile-nav">
                <a class="btn btn-secondary" href="dashboard.php">Dashboard</a>
                <a class="btn btn-primary" href="backoffice_User.php">Utilisateurs</a>
            </div>
            <div class="head">
                <div>
                    <h1>Section utilisateurs</h1>
                    <div class="muted">Vue administrative pour consulter et gerer les utilisateurs. <?= $totalUsers ?> utilisateur(s) au total.</div>
                </div>
                <div class="actions">
                    <span class="tag"><?= $totalUsers ?> utilisateurs</span>
                    <span class="tag">Backoffice</span>
                </div>
            </div>
            <section class="section-card fade-in">
                <h2 class="section-title">Utilisateurs</h2>
                <form method="GET" action="">
                    <div class="toolbar">
                        <div class="search-box">
                            <input type="search" name="search" placeholder="Rechercher un utilisateur, un email ou un pays" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="filter-group">
                            <label>
                                Filtrer par role
                                <select name="role" onchange="this.form.submit()">
                                    <option value="">Tout</option>
                                    <option value="participant" <?= $roleFilter === 'participant' ? 'selected' : '' ?>>Participant</option>
                                    <option value="organisateur" <?= $roleFilter === 'organisateur' ? 'selected' : '' ?>>Organisateur</option>
                                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </label>
                            <label>
                                Filtrer par pays
                                <select name="pays" onchange="this.form.submit()">
                                    <option value="">Tout</option>
                                    <?php foreach ($paysList as $p): ?>
                                        <option value="<?= htmlspecialchars($p) ?>" <?= $paysFilter === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </div>
                </form>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Nom</th>
                                <th>Nom utilisateur</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Pays</th>
                                <th>Ville / zone</th>
                                <th>Telephone</th>
                                <th>Occupation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="11" style="text-align:center;color:var(--muted);">Aucun utilisateur trouve.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= $u['id_user'] ?></td>
                                        <td>
                                            <?php if ($u['profile_picture'] && file_exists(__DIR__ . '/../uploads/' . $u['profile_picture'])): ?>
                                                <img class="user-thumb" src="../uploads/<?= htmlspecialchars($u['profile_picture']) ?>" alt="">
                                            <?php else: ?>
                                                <span class="user-thumb" style="display:inline-grid;place-items:center;color:#fff;font-weight:900;font-size:.8rem;background:linear-gradient(135deg,var(--teal),var(--sun));"><?= mb_strtoupper(mb_substr($u['nom_complet'], 0, 1)) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($u['nom_complet']) ?></td>
                                        <td><?= htmlspecialchars($u['nom_user']) ?></td>
                                        <td><span class="tag"><?= htmlspecialchars($u['role']) ?></span></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><?= htmlspecialchars($u['pays'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($u['ville'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($u['tel'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($u['occupation'] ?? '-') ?></td>
                                        <td>
                                            <button class="btn btn-danger" style="padding:8px 12px;font-size:.85rem;" onclick="showConfirm('Supprimer l\'utilisateur <?= htmlspecialchars(addslashes($u['nom_complet'])) ?> ?', function(){ document.getElementById('del-<?= $u['id_user'] ?>').submit(); });">Supprimer</button>
                                            <form id="del-<?= $u['id_user'] ?>" method="POST" action="delete_user.php" style="display:none;">
                                                <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&pays=<?= urlencode($paysFilter) ?>">Precedent</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&pays=<?= urlencode($paysFilter) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&pays=<?= urlencode($paysFilter) ?>">Suivant</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="section-note">Total : <?= $totalUsers ?> utilisateur(s).</div>
            </section>
        </main>
    </div>

    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirmation</h3>
            <p id="confirm-message"></p>
            <div class="modal-actions">
                <button id="confirm-yes" class="btn btn-danger">Oui, supprimer</button>
                <button class="btn btn-secondary" data-modal-close>Annuler</button>
            </div>
        </div>
    </div>

    <div id="feedback-modal" class="modal-overlay">
        <div class="modal-box">
            <div id="feedback-icon" class="feedback-icon success"></div>
            <p id="feedback-message"></p>
        </div>
    </div>

    <script src="../view/assets/js/app.js"></script>
</body>
</html>
