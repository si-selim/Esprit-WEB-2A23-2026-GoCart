<?php
$currentPage = $currentPage ?? '';
$currentUser = $currentUser ?? $controller->getCurrentUser();
$logoutUrl = $logoutUrl ?? 'index.php?action=logout';
?>
<style>
    .fo-topbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(16px);
        background: rgba(255, 255, 255, 0.82);
        border-bottom: 1px solid rgba(16, 42, 67, 0.08);
        box-shadow: 0 10px 30px rgba(16, 42, 67, 0.06);
    }

    .fo-topbar-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        min-height: 78px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .fo-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: #102a43;
        font-weight: 900;
        letter-spacing: 0.04em;
    }

    .fo-brand-mark {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: white;
        background: linear-gradient(135deg, #0f766e, #ffb703);
        box-shadow: 0 12px 24px rgba(15, 118, 110, 0.22);
        font-size: 0.92rem;
    }

    .fo-brand-text {
        display: grid;
        line-height: 1.05;
    }

    .fo-brand-text small {
        color: #627d98;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .fo-nav {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .fo-link,
    .fo-profile,
    .fo-cta {
        text-decoration: none;
        border-radius: 999px;
        padding: 10px 14px;
        font-weight: 700;
        transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
    }

    .fo-link {
        color: #102a43;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(16, 42, 67, 0.08);
    }

    .fo-link:hover,
    .fo-profile:hover,
    .fo-cta:hover {
        transform: translateY(-1px);
    }

    .fo-link.active {
        color: white;
        background: linear-gradient(135deg, #102a43, #0f766e);
        box-shadow: 0 10px 24px rgba(16, 42, 67, 0.18);
    }

    .fo-cta {
        color: white;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
    }

    .fo-profile {
        color: #102a43;
        background: linear-gradient(135deg, #fff7ed, #ffffff);
        border: 1px solid rgba(255, 183, 3, 0.28);
    }

    .fo-profile-role {
        display: inline-block;
        margin-left: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.1);
        color: #0f766e;
        font-size: 0.78rem;
    }

    @media (max-width: 860px) {
        .fo-topbar-shell {
            padding: 10px 0;
            flex-direction: column;
            align-items: flex-start;
        }

        .fo-nav {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<div class="fo-topbar">
    <div class="fo-topbar-shell">
        <a class="fo-brand" href="index.php">
            <span class="fo-brand-mark">BT</span>
            <span class="fo-brand-text">
                <span>BarchaThon</span>
                <small>Front Office</small>
            </span>
        </a>

        <nav class="fo-nav">
            <a class="fo-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php">Accueil</a>
            <a class="fo-link <?php echo $currentPage === 'catalog' ? 'active' : ''; ?>" href="listMarathons.php">Catalogue</a>
            <?php if ($controller->isParticipant()): ?>
                <a class="fo-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>" href="orderHistory.php">Voir mes commandes</a>
            <?php elseif ($controller->isOrganizer()): ?>
                <a class="fo-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>" href="organizerOrders.php">Voir mes commandes</a>
            <?php endif; ?>

            <?php if ($controller->isAdmin()): ?>
                <a class="fo-link" href="../BackOffice/listMarathons.php">Dashboard</a>
            <?php elseif ($controller->isOrganizer()): ?>
                <a class="fo-link" href="../BackOffice/listMarathons.php">Mes marathons</a>
            <?php endif; ?>

            <?php if ($controller->isVisitor()): ?>
                <a class="fo-link" href="register.php">S inscrire</a>
                <a class="fo-cta" href="login.php">Se connecter</a>
            <?php else: ?>
                <a class="fo-profile" href="profile.php">
                    <?php echo htmlspecialchars($currentUser['name']); ?>
                    <span class="fo-profile-role"><?php echo htmlspecialchars($currentUser['role']); ?></span>
                </a>
                <a class="fo-link" href="<?php echo htmlspecialchars($logoutUrl); ?>">Se deconnecter</a>
            <?php endif; ?>
        </nav>
    </div>
</div>
