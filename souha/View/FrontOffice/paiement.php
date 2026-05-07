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

        #stripe-card-element { border: 2px solid #e5e7eb; border-radius: 12px; padding: 15px; background: #f9fafb; margin-bottom: 20px; }
        #stripe-card-element.stripe-focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(15,118,110,0.1); }
        
        .stripe-form { display: none; margin-top: 24px; }
        .stripe-form.active { display: block; }

        .payment-details { display: none; margin-top: 18px; background: #f8fafb; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; }
        .payment-details.active { display: block; }
        .payment-details label { display: block; font-weight: 700; margin-bottom: 10px; color: #102a43; }
        .payment-details input { width: 100%; padding: 14px 16px; border: 1.5px solid #d9e2ec; border-radius: 12px; font-size: 0.95rem; margin-bottom: 12px; background: white; }
        .payment-details small { color: #64748b; font-size: 0.88rem; }
        .payment-details .info { color: #0f766e; font-size: 0.94rem; margin-top: 8px; }

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
            <h1>💳 Paiement sécurisé</h1>
            <p><?php echo $type === 'marathon' ? 'Finalisez votre inscription au marathon' : 'Payez votre commande'; ?></p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="amount-display">
            <div class="amount-label">Montant à payer</div>
            <div class="amount-value"><?php echo number_format($montant, 2, ',', ' '); ?> TND</div>
        </div>

        <form method="post" action="process_payment.php" class="payment-form" id="paymentForm">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="montant" value="<?php echo $montant; ?>">
            <input type="hidden" name="parcours_id" value="<?php echo $parcours_id; ?>">
            <input type="hidden" name="stand_id" value="<?php echo $stand_id; ?>">
            <input type="hidden" name="action" id="paymentAction" value="now">
            <input type="hidden" name="methode_paiement" id="selectedMethod">
            <input type="hidden" name="payment_method_id" id="paymentMethodId">

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
                <div class="payment-method" data-method="stripe">
                    <img src="images/mastercard.png" alt="MasterCard" style="width: 60px; height: auto; margin-bottom: 12px;">
                    <div class="method-icon">💳</div>
                    <div class="method-name">Carte bancaire</div>
                    <div class="method-desc">Visa, MasterCard, etc. (Stripe)</div>
                </div>
            </div>

            <!-- Formulaire Stripe (affiché seulement quand Stripe est sélectionné) -->
            <div class="stripe-form" id="stripeForm">
                <div id="stripe-card-element"></div>
                <div id="stripe-errors" role="alert" style="color: #dc2626; margin-top: 10px; font-weight: 600;"></div>
            </div>

            <div class="payment-details" id="d17Details">
                <label for="d17Reference">Référence D17</label>
                <input type="text" name="d17_reference" id="d17Reference" placeholder="Ex: D17-12345" autocomplete="off">
                <small>Entrez la référence D17 fournie par votre banque ou terminal.</small>
            </div>

            <div class="payment-details" id="paypalDetails">
                <label for="paypalEmail">Email PayPal</label>
                <input type="email" name="paypal_email" id="paypalEmail" placeholder="votre.email@exemple.com" autocomplete="email">
                <small>Entrez l'adresse email liée à votre compte PayPal.</small>
            </div>

            <div style="margin-top:32px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; justify-content:center;">
                <?php if ($type === 'commande'): ?>
                    <button type="button" class="btn btn-secondary" id="payLaterBtn">
                        Payer ultérieurement
                    </button>
                <?php endif; ?>
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
    
    const methods = document.querySelectorAll('.payment-method');
    const selectedInput = document.getElementById('selectedMethod');
    const paymentActionInput = document.getElementById('paymentAction');
    const payBtn = document.getElementById('payBtn');
    const payLaterBtn = document.getElementById('payLaterBtn');
    const paymentForm = document.getElementById('paymentForm');
    const stripeForm = document.getElementById('stripeForm');
    const d17Details = document.getElementById('d17Details');
    const paypalDetails = document.getElementById('paypalDetails');
    const d17Reference = document.getElementById('d17Reference');
    const paypalEmail = document.getElementById('paypalEmail');
    const stripeErrors = document.getElementById('stripe-errors');
    const spinner = document.getElementById('spinner');
    
    let selectedMethod = null;
    let paymentAction = 'now';

    // Initialiser Stripe Card Element
    cardElement.mount('#stripe-card-element');
    
    cardElement.addEventListener('change', function(event) {
        if (event.error) {
            stripeErrors.textContent = event.error.message;
        } else {
            stripeErrors.textContent = '';
        }
    });

    // Gérer la sélection des méthodes de paiement
    methods.forEach(method => {
        method.addEventListener('click', function() {
            methods.forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            selectedMethod = this.dataset.method;
            selectedInput.value = selectedMethod;
            payBtn.disabled = false;
            paymentActionInput.value = 'now';
            
            // Afficher/masquer le formulaire Stripe et les champs spécifiques
            stripeForm.classList.toggle('active', selectedMethod === 'stripe');
            d17Details.classList.toggle('active', selectedMethod === 'd17');
            paypalDetails.classList.toggle('active', selectedMethod === 'paypal');
            stripeErrors.textContent = '';
        });
    });

    const isValidEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const isValidD17 = value => /^[A-Za-z0-9\-]{5,30}$/.test(value.trim());

    if (payLaterBtn) {
        payLaterBtn.addEventListener('click', function() {
            paymentAction = 'later';
            paymentActionInput.value = 'later';
            selectedInput.value = '';
            paymentForm.submit();
        });
    }

    // Gérer la soumission du formulaire
    paymentForm.addEventListener('submit', async function(e) {
        if (paymentAction === 'later') {
            paymentActionInput.value = 'later';
            return;
        }
        e.preventDefault();

        if (!selectedMethod) {
            showToast('Sélectionnez une méthode de paiement.', 'error');
            return;
        }

        if (selectedMethod === 'd17') {
            if (!d17Reference.value.trim()) {
                showToast('Veuillez entrer la référence D17.', 'error');
                return;
            }
            if (!isValidD17(d17Reference.value)) {
                showToast('Référence D17 invalide. Utilisez lettres, chiffres et tirets.', 'error');
                return;
            }
        }

        if (selectedMethod === 'paypal') {
            if (!paypalEmail.value.trim()) {
                showToast('Veuillez entrer votre adresse PayPal.', 'error');
                return;
            }
            if (!isValidEmail(paypalEmail.value)) {
                showToast('Adresse PayPal invalide.', 'error');
                return;
            }
        }
        
        // Désactiver le bouton pendant le traitement
        payBtn.disabled = true;
        spinner.classList.add('show');
        selectedInput.value = selectedMethod;
        paymentActionInput.value = 'now';
        
        if (selectedMethod === 'stripe') {
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
        } else {
            paymentForm.submit();
        }
    });
});
</script>
</body>
</html>
