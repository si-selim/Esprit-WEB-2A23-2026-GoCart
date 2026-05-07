<?php
/**
 * Classe simple pour gérer les paiements Stripe
 * Sans dépendre de la bibliothèque Stripe PHP
 */
class StripePayment {
    private $secretKey;
    private $apiUrl = 'https://api.stripe.com/v1';

    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
    }

    /**
     * Créer un PaymentIntent et le confirmer
     */
    public function createAndConfirmPaymentIntent($amount, $currency, $paymentMethodId, $metadata = []) {
        $params = [
            'amount' => (int)($amount * 100), // En centimes
            'currency' => strtolower($currency),
            'payment_method' => $paymentMethodId,
            'payment_method_types[0]' => 'card',
            'confirm' => 'true'
        ];

        // Ajouter les métadonnées
        foreach ($metadata as $key => $value) {
            $params['metadata[' . $key . ']'] = $value;
        }

        $ch = curl_init($this->apiUrl . '/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Erreur cURL: ' . $error);
        }

        $result = json_decode($response, true);

        if (!$result) {
            throw new Exception('Réponse Stripe invalide');
        }

        if (isset($result['error'])) {
            throw new Exception($result['error']['message'] ?? 'Erreur Stripe');
        }

        return $result;
    }

    /**
     * Récupérer un PaymentIntent
     */
    public function getPaymentIntent($paymentIntentId) {
        $ch = curl_init($this->apiUrl . '/payment_intents/' . $paymentIntentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Erreur cURL: ' . $error);
        }

        $result = json_decode($response, true);

        if (!$result) {
            throw new Exception('Réponse Stripe invalide');
        }

        if (isset($result['error'])) {
            throw new Exception($result['error']['message'] ?? 'Erreur Stripe');
        }

        return $result;
    }
}
