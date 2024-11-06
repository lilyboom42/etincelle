<?php

namespace App\Service;

use App\Entity\Appointment;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeConfig $stripeConfig;
    private UrlGeneratorInterface $router;

    public function __construct(StripeConfig $stripeConfig, UrlGeneratorInterface $router)
    {
        $this->stripeConfig = $stripeConfig;
        $this->router = $router;
    }

    public function createCheckoutSession(Appointment $appointment): Session
    {
        Stripe::setApiKey($this->stripeConfig->getSecretKey());

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $appointment->getServiceType(),
                    ],
                    'unit_amount' => 5000, // Montant en centimes (par exemple 50,00 €)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->router->generate('appointment_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->router->generate('appointment_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'appointment_id' => $appointment->getId(),
            ],
        ]);

        return $session;
    }

    public function getPublicKey(): string
    {
        return $this->stripeConfig->getPublicKey();
    }

    public function getSecretKey(): string
    {
        return $this->stripeConfig->getSecretKey();
    }
}
