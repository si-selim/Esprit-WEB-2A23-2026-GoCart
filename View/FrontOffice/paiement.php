<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/lang.php';

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

require_once __DIR__ . '/../../Controller/UserController.php';
$userCtrl = new UserController();
$dbUser = $userCtrl->showUser($user['id_user'] ?? $user['id']);
$nbreCommande = isset($dbUser['nbre_commande']) ? (int)$dbUser['nbre_commande'] : 0;
$pendingDiscount = isset($dbUser['pending_discount']) ? (int)$dbUser['pending_discount'] : 0;

$isFirstOrder = ($nbreCommande === 0 && $type === 'commande');
$totalDiscountPercent = 0;

if ($isFirstOrder) {
    $totalDiscountPercent += 10;
}
if ($type === 'commande' && $pendingDiscount > 0) {
    $totalDiscountPercent += $pendingDiscount;
}

$currentPage = 'paiement';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('pay_title'); ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
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
        .btn-secondary { background:#e2e8f0; color:#102a43; border:1px solid #cbd5e1; border-radius:12px; padding:16px 24px; font-size:1rem; font-weight:700; cursor:pointer; transition:transform .15s, background-color .15s; }
        .btn-secondary:hover { background:#cbd5e1; transform:translateY(-2px); }

        .message { padding:16px; border-radius:18px; margin-bottom:18px; }
        .message.success { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .message.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

        #stripe-card-element { border: 2px solid #e5e7eb; border-radius: 16px; padding: 18px; background: #f9fafb; margin-bottom: 20px; transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
        #stripe-card-element.stripe-focus { border-color: var(--teal); box-shadow: 0 0 0 4px rgba(15,118,110,0.15); background: white; }
        
        .stripe-form { display: block; margin-top: 24px; }
        
        .secure-payment-info {
            display: flex; align-items: center; justify-content: center; gap: 16px; 
            background: linear-gradient(135deg, #f0fdf9, #ccfbf1); padding: 24px; 
            border-radius: 20px; border: 1px solid #99f6e4; color: #0f766e; 
            margin-bottom: 28px; box-shadow: 0 8px 24px rgba(20, 184, 166, 0.15);
            transition: transform 0.2s ease;
        }
        .secure-payment-info:hover { transform: translateY(-2px); }
        .secure-payment-info i { font-size: 2.2rem; background: linear-gradient(135deg, #0d9488, #0f766e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .secure-info-text { text-align: left; }
        .secure-info-title { font-weight: 800; font-size: 1.15rem; letter-spacing: -0.02em; }
        .secure-info-desc { font-size: 0.9rem; color: #115e59; margin-top: 4px; opacity: 0.9; }

        .loading-spinner { display: none; }
        .loading-spinner.show { display: inline-block; margin-right: 8px; }

        /* Toast Notifications */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease-out;
            z-index: 10000;
            max-width: 400px;
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="<?php echo $type === 'marathon' ? 'detailMarathon.php?id=' . $id : ($stand_id > 0 ? 'Produits/produit.php?stand_id=' . $stand_id : 'Mes commandes.php'); ?>">
        ← Retour
    </a>

    <div class="payment-container">
        <div class="payment-header">
            <h1>💳 <?php echo t('pay_title'); ?></h1>
            <p><?php echo $type === 'marathon' ? 'Finalisez votre inscription au marathon' : 'Payez votre commande'; ?></p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="amount-display">
            <?php if ($totalDiscountPercent > 0 && $montant > 0): ?>
                <div class="amount-label">Montant initial</div>
                <div class="amount-value" style="font-size: 1.5rem; color: #64748b; text-decoration: line-through;"><?php echo number_format($montant, 2, ',', ' '); ?> TND</div>
                
                <div class="amount-label" style="color: #10b981; margin-top: 15px;">Montant à payer (Remise -<?php echo $totalDiscountPercent; ?>%)</div>
                <div class="amount-value" style="color: #10b981;"><?php echo number_format($montant * (1 - ($totalDiscountPercent / 100)), 2, ',', ' '); ?> TND</div>
            <?php else: ?>
                <div class="amount-label">Montant à payer</div>
                <div class="amount-value"><?php echo number_format($montant, 2, ',', ' '); ?> TND</div>
            <?php endif; ?>
        </div>

        <form method="post" action="process_payment.php" class="payment-form" id="paymentForm">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="montant" value="<?php echo $montant; ?>">
            <input type="hidden" name="parcours_id" value="<?php echo $parcours_id; ?>">
            <input type="hidden" name="stand_id" value="<?php echo $stand_id; ?>">
            <input type="hidden" name="action" id="paymentAction" value="now">
            <input type="hidden" name="methode_paiement" id="selectedMethod" value="stripe">
            <input type="hidden" name="payment_method_id" id="paymentMethodId">

            <div class="secure-payment-info">
                <i class="fas fa-shield-alt"></i>
                <div class="secure-info-text">
                    <div class="secure-info-title">Paiement sécurisé par carte</div>
                    <div class="secure-info-desc">Vos données sont cryptées par Stripe avec une sécurité de niveau bancaire.</div>
                </div>
                <div style="margin-left: auto;">
                    <img src="images/mastercard.png" alt="MasterCard" style="height: 35px; object-fit: contain;">
                </div>
            </div>

            <!-- Formulaire Stripe -->
            <div class="stripe-form" id="stripeForm">
                <div id="stripe-card-element"></div>
                <div id="stripe-errors" role="alert" style="color: #dc2626; margin-top: 10px; font-weight: 600;"></div>
            </div>

            <div style="margin-top:32px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; justify-content:center;">
                <button type="submit" class="btn-pay" id="payBtn">
                    <span class="loading-spinner" id="spinner"><i class="fas fa-spinner fa-spin"></i></span>
                    <i class="fas fa-lock"></i> Payer maintenant
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '" style="margin-right: 8px;"></i>' + message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('pk_test_51TRyxUDGCRF098Y0BS4HGplC209NZAdp7eQkNaaL0olm7YyaSda8dxMd0A5eUhk75T46wUutnnUbDh0AUTiMTYgH00oqWYY9rE');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    
    const paymentActionInput = document.getElementById('paymentAction');
    const payBtn = document.getElementById('payBtn');
    const paymentForm = document.getElementById('paymentForm');
    const stripeErrors = document.getElementById('stripe-errors');
    const spinner = document.getElementById('spinner');

    // Initialiser Stripe Card Element
    cardElement.mount('#stripe-card-element');
    
    cardElement.on('focus', () => { document.getElementById('stripe-card-element').classList.add('stripe-focus'); });
    cardElement.on('blur', () => { document.getElementById('stripe-card-element').classList.remove('stripe-focus'); });

    cardElement.addEventListener('change', function(event) {
        if (event.error) {
            stripeErrors.textContent = event.error.message;
        } else {
            stripeErrors.textContent = '';
        }
    });

    // Gérer la soumission du formulaire
    paymentForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Désactiver le bouton pendant le traitement
        payBtn.disabled = true;
        spinner.classList.add('show');
        paymentActionInput.value = 'now';
        
        try {
            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement
            });

            if (error) {
                stripeErrors.textContent = error.message;
                showToast('❌ ' + error.message, 'error');
                payBtn.disabled = false;
                spinner.classList.remove('show');
                return;
            }

            const formData = new FormData(paymentForm);
            formData.set('payment_method_id', paymentMethod.id);
            formData.set('methode_paiement', 'stripe');

            const response = await fetch('process_stripe_payment.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast('✅ Paiement réussi!', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1200);
                return;
            }

            const errorMsg = data.error || 'Erreur lors du paiement';
            stripeErrors.textContent = errorMsg;
            showToast('❌ ' + errorMsg, 'error');
            payBtn.disabled = false;
            spinner.classList.remove('show');
        } catch (err) {
            const message = err.message || 'Erreur lors du traitement du paiement';
            stripeErrors.textContent = message;
            showToast('❌ ' + message, 'error');
            payBtn.disabled = false;
            spinner.classList.remove('show');
        }
    });
});
</script>
</body>
</html>
