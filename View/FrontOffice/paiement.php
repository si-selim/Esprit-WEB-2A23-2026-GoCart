<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'marathon'; // 'marathon' ou 'commande'
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$montant = isset($_GET['montant']) ? (float)$_GET['montant'] : 0;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : 0;
$stand_id = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : 0;

if ($type === 'marathon' && $id <= 0) {
    header('Location: listMarathons.php');
    exit;
}

$currentPage = 'paiement';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }

        .payment-container { background:white; border-radius:28px; padding:32px; box-shadow:0 16px 44px rgba(16,42,67,.1); margin-bottom:28px; }
        .payment-header { text-align:center; margin-bottom:32px; }
        .payment-header h1 { font-size:1.8rem; margin-bottom:8px; }
        .payment-header p { color:#627d98; font-size:1rem; }

        .amount-display { background:linear-gradient(135deg,#f0f9ff,#e0f2fe); border:2px solid #0ea5e9; border-radius:16px; padding:20px; text-align:center; margin-bottom:24px; }
        .amount-label { font-size:0.9rem; color:#0c4a6e; font-weight:700; text-transform:uppercase; margin-bottom:8px; }
        .amount-value { font-size:2.5rem; font-weight:900; color:#0ea5e9; }

        .payment-methods { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:16px; }
        .payment-method { border:2px solid #e5e7eb; border-radius:16px; padding:24px; text-align:center; cursor:pointer; transition:all .2s; background:white; }
        .payment-method:hover { border-color:var(--teal); transform:translateY(-2px); box-shadow:0 8px 24px rgba(15,118,110,.15); }
        .payment-method.selected { border-color:var(--teal); background:#f0fdf9; }
        .method-icon { font-size:3rem; margin-bottom:12px; }
        .method-name { font-size:1.2rem; font-weight:700; margin-bottom:8px; }
        .method-desc { color:#627d98; font-size:0.9rem; }

        .payment-form { margin-top:32px; text-align:center; }
        .btn-pay { background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; border:none; border-radius:12px; padding:16px 32px; font-size:1.1rem; font-weight:700; cursor:pointer; transition:transform .15s; }
        .btn-pay:hover { transform:translateY(-2px); }

        .message { padding:16px; border-radius:18px; margin-bottom:18px; }
        .message.success { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .message.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="<?php echo $type === 'marathon' ? 'detailMarathon.php?id=' . $id : ($stand_id > 0 ? 'produit.php?stand_id=' . $stand_id : 'Mes commandes.php'); ?>">
        ← Retour
    </a>

    <div class="payment-container">
        <div class="payment-header">
            <h1>💳 Paiement sécurisé</h1>
            <p><?php echo $type === 'marathon' ? 'Finalisez votre inscription au marathon' : 'Payez votre commande'; ?></p>
        </div>

        <div class="amount-display">
            <div class="amount-label">Montant à payer</div>
            <div class="amount-value"><?php echo number_format($montant, 2, ',', ' '); ?> TND</div>
        </div>

        <form method="post" action="process_payment.php" class="payment-form">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="montant" value="<?php echo $montant; ?>">
            <input type="hidden" name="parcours_id" value="<?php echo $parcours_id; ?>">
            <input type="hidden" name="stand_id" value="<?php echo $stand_id; ?>">

            <div class="payment-methods">
                <div class="payment-method" data-method="d17">
                    <img src="images/d17.png" alt="D17" style="width: 60px; height: auto; margin-bottom: 12px;">
                    <div class="method-icon">💳</div>
                    <div class="method-name">D17</div>
                    <div class="method-desc">Paiement par carte bancaire D17</div>
                </div>
                <div class="payment-method" data-method="paypal">
                    <img src="images/paypal.png" alt="PayPal" style="width: 60px; height: auto; margin-bottom: 12px;">
                    <div class="method-icon">🅿️</div>
                    <div class="method-name">PayPal</div>
                    <div class="method-desc">Paiement via PayPal</div>
                </div>
                <div class="payment-method" data-method="card">
                    <img src="images/mastercard.png" alt="MasterCard" style="width: 60px; height: auto; margin-bottom: 12px;">
                    <div class="method-icon">💳</div>
                    <div class="method-name">Carte bancaire</div>
                    <div class="method-desc">Visa, MasterCard, etc.</div>
                </div>
            </div>

            <input type="hidden" name="methode_paiement" id="selectedMethod" required>

            <div style="margin-top:32px;">
                <button type="submit" class="btn-pay" id="payBtn" disabled>
                    <i class="fas fa-lock"></i> Payer maintenant
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methods = document.querySelectorAll('.payment-method');
    const selectedInput = document.getElementById('selectedMethod');
    const payBtn = document.getElementById('payBtn');

    methods.forEach(method => {
        method.addEventListener('click', function() {
            methods.forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            selectedInput.value = this.dataset.method;
            payBtn.disabled = false;
        });
    });
});
</script>
</body>
</html>