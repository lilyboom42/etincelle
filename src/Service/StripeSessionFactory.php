<?php
// src/Service/StripeSessionFactory.php

namespace App\Service;

use Stripe\Checkout\Session as StripeSession;

class StripeSessionFactory
{
    /**
     * Crée une session de paiement Stripe.
     *
     * @param array $params Les paramètres pour créer la session Stripe.
     * @return StripeSession
     */
    public function create(array $params): StripeSession
    {
        return StripeSession::create($params);
    }
}
