<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$marathon_id = isset($_GET['marathon_id']) ? (int)$_GET['marathon_id'] : 0;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;
$inscription_id = isset($_GET['inscription_id']) ? (int)$_GET['inscription_id'] : 0;

if ($marathon_id <= 0) {
    header('Location: listMarathons.php');
    exit;
}

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();

$marathon = $mCtrl->showMarathon($marathon_id);
$parcours = $parcours_id ? $pCtrl->showParcours($parcours_id) : null;

if (!$marathon) {
    header('Location: listMarathons.php');
    exit;
}

$formattedDate = date('d/m/Y', strtotime($marathon['date_marathon']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription — BarchaThon</title>
    <style>
        :root {
            --bg: #f7f6f3;
            --paper: #fffef7;
            --ink: #102a43;
            --accent: #0f766e;
            --accent-soft: #d8f3ee;
            --border: #d1d5db;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #eef6f2, #f7f6f3);
            color: var(--ink);
        }
        .sheet {
            width: 100%;
            max-width: 1200px;
            margin: 24px auto;
            padding: 36px;
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 24px 70px rgba(16, 42, 67, 0.12);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo {
            width: 78px;
            height: 78px;
            background: radial-gradient(circle at 30% 30%, #14b8a6, #0f766e 55%);
            border-radius: 20px;
            display: grid;
            place-items: center;
            color: white;
            font-size: 1.6rem;
            font-weight: 900;
        }
        .brand-text h1 {
            margin: 0;
            font-size: 1.7rem;
            letter-spacing: 1px;
        }
        .brand-text p {
            margin: 4px 0 0;
            color: #475569;
            font-size: 0.95rem;
        }
        .title {
            margin-top: 24px;
            font-size: 2.6rem;
            line-height: 1.05;
            letter-spacing: -0.03em;
            color: var(--ink);
        }
        .subtitle {
            margin: 12px 0 0;
            color: #475569;
            font-size: 1rem;
        }
        .content {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 28px;
            margin-top: 36px;
        }
        .letter {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 30px;
            min-height: 620px;
        }
        .letter h2 {
            margin-top: 0;
            font-size: 1.45rem;
            color: var(--accent);
        }
        .letter p {
            line-height: 1.75;
            color: #334155;
            margin-bottom: 18px;
            font-size: 1rem;
        }
        .letter strong {
            color: var(--ink);
        }
        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 20px 0;
        }
        .meta-item {
            background: var(--accent-soft);
            border-radius: 12px;
            padding: 12px 16px;
        }
        .meta-item span {
            display: block;
            color: #475569;
            font-size: 0.78rem;
            margin-bottom: 4px;
        }
        .meta-item strong {
            color: var(--ink);
            font-size: 0.95rem;
        }
        .signature {
            margin-top: 42px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .signature-line {
            flex: 1;
            border-bottom: 1px solid #cbd5e1;
            min-height: 1px;
        }
        .signature-text {
            font-weight: 700;
            color: var(--ink);
            font-size: 0.95rem;
        }
        .qr-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 24px;
            display: grid;
            gap: 18px;
            align-items: center;
            justify-items: center;
        }
        .qr-box {
            width: 220px;
            height: 220px;
            background: radial-gradient(circle at 20% 20%, #0f766e 0%, #14b8a6 18%, #0f766e 18%, #0f766e 22%, #f8fafc 22%, #f8fafc 24%, #0f766e 24%);
            border-radius: 18px;
            position: relative;
            box-shadow: inset 0 0 0 4px #fff;
        }
        .qr-box::after {
            content: "QR";
            color: white;
            position: absolute;
            bottom: 10px;
            right: 12px;
            font-size: 0.8rem;
            opacity: 0.8;
        }
        .qr-title {
            margin: 0;
            font-size: 1rem;
            color: #475569;
            text-align: center;
        }
        .print-button {
            margin-top: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 22px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .print-button:hover { opacity: 0.95; }
        @media (max-width: 960px) {
            .content { grid-template-columns: 1fr; }
        }
        @media print {
            body > *:not(.sheet) {
                display: none;
            }
            .sheet {
                margin: 0;
                padding: 20px;
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page" style="width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 16px;">
        <a class="back-link" href="<?php echo $parcours_id ? 'detailParcours.php?id=' . $parcours_id : 'listMarathons.php'; ?>" style="display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:#0f766e; font-weight:700; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem;">← Retour au parcours</a>
        <button class="print-button" onclick="window.print();" style="margin: 0;">Imprimer la confirmation</button>
    </div>
</div>
<div class="sheet">
    <div class="header">
        <div class="brand">
            <div class="logo"><img src="images/LOGO.jpg" alt="BarchaThon" style="width:100%; height:100%; object-fit:cover; border-radius:20px;"></div>
            <div class="brand-text">
                <h1>BarchaThon</h1>
                <p>Votre course, notre passion.</p>
            </div>
        </div>
        <div style="text-align:right; color:#475569;">
            <div>Confirmation d'inscription</div>
            <div style="margin-top:6px; font-weight:700;">Réf. <?php echo $inscription_id ? sprintf('%06d', $inscription_id) : 'N/A'; ?></div>
        </div>
    </div>

    <div class="title">Lettre de confirmation</div>
    <div class="subtitle">Merci d’avoir choisi BarchaThon pour participer à nos marathons.</div>

    <div class="content">
        <div class="letter">
            <h2>Bonjour <?php echo htmlspecialchars($user['prenom'] ?? $user['nom'] ?? 'Coureur'); ?>,</h2>
            <p>Nous confirmons votre participation officielle au marathon <strong><?php echo htmlspecialchars($marathon['nom_marathon']); ?></strong>, organisé par <strong><?php echo htmlspecialchars($marathon['organisateur_marathon']); ?></strong>, qui aura lieu le <strong><?php echo $formattedDate; ?></strong> dans la région de <strong><?php echo htmlspecialchars($marathon['region_marathon']); ?></strong>.</p>
            <p>Vous avez choisi le parcours : <strong><?php echo $parcours ? htmlspecialchars($parcours['nom_parcours']) : 'Non précisé'; ?></strong>.</p>
            <p>Nous sommes ravis de vous compter parmi nos participants. Merci d’avoir utilisé notre site pour gérer votre inscription et vos courses.</p>
            <p>Cet avis vous est délivré comme preuve officielle de votre participation. Présentez-le le jour du marathon si nécessaire.</p>

            <div class="meta">
                <div class="meta-item">
                    <span>Participant</span>
                    <strong><?php echo htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')); ?></strong>
                </div>
                <div class="meta-item">
                    <span>Marathon</span>
                    <strong><?php echo htmlspecialchars($marathon['nom_marathon']); ?></strong>
                </div>
                <div class="meta-item">
                    <span>Organisé par</span>
                    <strong><?php echo htmlspecialchars($marathon['organisateur_marathon']); ?></strong>
                </div>
                <div class="meta-item">
                    <span>Date</span>
                    <strong><?php echo $formattedDate; ?></strong>
                </div>
            </div>

            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-text">Signature BarchaThon</div>
            </div>
            
            <div style="display: flex; gap: 24px; justify-content: space-between; align-items: flex-end; margin-top: 32px;">
                <div style="text-align: center;">
                    <img src="images/signature.png" alt="Signature" style="height: 80px; object-fit: contain; margin-bottom: 8px;">
                    <div style="color:#475569; font-size:0.85rem;">Signature BarchaThon</div>
                </div>
                <div style="text-align: center;">
                    <img src="images/caché.png" alt="Cachet officiel" style="height: 100px; object-fit: contain;">
                    <div style="color:#475569; font-size:0.85rem;">Cachet officiel</div>
                </div>
            </div>
            
            <div style="margin-top:28px; color:#475569; font-size:0.92rem;">BarchaThon - Ensemble vers la ligne d'arrivée.</div>
        </div>

        <div>
            <div class="qr-card">
                <div class="qr-box"></div>
                <p class="qr-title">Code QR associé à cette confirmation</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
