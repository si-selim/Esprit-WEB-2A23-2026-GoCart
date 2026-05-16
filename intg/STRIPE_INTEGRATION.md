# Intégration Stripe - Guide d'utilisation

## Configuration Stripe

L'intégration Stripe est maintenant complète sur votre application. Voici comment l'utiliser.

### Fichiers créés/modifiés:

1. **[paiement.php](View/FrontOffice/paiement.php)** - Page de paiement avec Stripe Elements
2. **[process_stripe_payment.php](View/FrontOffice/process_stripe_payment.php)** - Traitement des paiements Stripe
3. **[StripePayment.php](StripePayment.php)** - Classe pour gérer l'API Stripe
4. **[config_stripe.php](config_stripe.php)** - Configuration des clés API Stripe

### Clés Stripe utilisées:

- **Clé publique**: `pk_test_51TRyxUDGCRF098Y0BS4HGplC209NZAdp7eQkNaaL0olm7YyaSda8dxMd0A5eUhk75T46wUutnnUbDh0AUTiMTYgH00oqWYY9rE`
- **Clé secrète**: 

## Fonctionnalités

### 1. Formulaire de paiement
- Interface Stripe Elements pour saisie de carte bancaire
- Plusieurs méthodes de paiement disponibles:
  - **Stripe** (Carte bancaire)
  - D17
  - PayPal

### 2. Processus de paiement

Quand l'utilisateur:
1. Sélectionne une méthode de paiement
2. Remplit les informations de la carte (si Stripe)
3. Clique sur "Payer maintenant"

Le système:
1. Crée un PaymentMethod via Stripe.js (côté client)
2. Envoie au serveur (process_stripe_payment.php)
3. Crée un PaymentIntent et le confirme
4. Traite l'inscription/commande si paiement réussi
5. Redirige vers la page de confirmation avec notification

### 3. Notifications

- Toast notifications élégantes lors des erreurs/succès
- Messages d'erreur dans les champs Stripe
- Affichage du spinner pendant le traitement

## Tester les paiements

### Cartes de test Stripe

Utilisez les numéros de carte suivants en mode test:

**Paiement réussi:**
- Numéro: `4242 4242 4242 4242`
- Expiration: N'importe quelle date future (ex: 12/25)
- CVC: N'importe quel code (ex: 123)

**Paiement échoué:**
- Numéro: `4000 0000 0000 0002`
- Expiration: N'importe quelle date future
- CVC: N'importe quel code

**Authentification requise (3D Secure):**
- Numéro: `4000 0025 0000 3155`
- Expiration: N'importe quelle date future
- CVC: N'importe quel code

## Architecture du flux de paiement

```
paiement.php (Frontend)
    ↓
[Sélectionner Stripe]
    ↓
stripe.createPaymentMethod() [Côté client]
    ↓
Envoyer FormData + payment_method_id
    ↓
process_stripe_payment.php (Backend)
    ↓
StripePayment::createAndConfirmPaymentIntent()
    ↓
API Stripe (créer + confirmer le paiement)
    ↓
Retour JSON (success/requires_action/error)
    ↓
Si succès:
    - Créer inscription/commande en BD
    - Rediriger vers page de confirmation
    - Afficher notification toast
```

## Gestion des erreurs

Les erreurs Stripe sont affichées:
1. **En temps réel**: Dans le champ `#stripe-errors` du formulaire
2. **Toast notification**: Alerte flottante en haut à droite
3. **URL de redirection**: Paramètre `error` si problème du côté serveur

## Sécurité

### ✅ Points de sécurité implémentés:

1. **Clé publique Stripe** utilisée uniquement côté client
2. **Clé secrète** stockée dans PHP (serveur)
3. **PaymentMethod** créé côté client par Stripe (pas d'accès direct aux cartes)
4. **Confirmation du paiement** côté serveur avec clé secrète
5. **Validation des sessions** avant traitement
6. **Métadonnées Stripe** pour traçabilité

### ⚠️ À faire en production:

1. Migrer vers des clés de production
2. Activer HTTPS obligatoire
3. Placer `config_stripe.php` hors du web root
4. Ajouter webhook Stripe pour écouter les événements
5. Implémenter un système de logs pour les paiements
6. Tester avec PCI Compliance Scanner

## Modifications au code existant

1. **process_payment.php**: Reste inchangé pour les autres méthodes (D17, PayPal)
2. **detailParcours.php**: Système de notification toast ajouté (réutilisé par d'autres pages)
3. **paiement.php**: 
   - Ajout Stripe Elements
   - Formulaire AJAX pour Stripe
   - Gestion erreurs en temps réel

## Support Stripe

- Documentation: https://stripe.com/docs
- Dashboard test: https://dashboard.stripe.com/test/dashboard
- API Reference: https://stripe.com/docs/api
