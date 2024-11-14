<?php

namespace App\Service;

use App\Entity\Appointment;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeConfig $stripeConfig;
    private UrlGeneratorInterface $router;
    private StripeClient $stripeClient;

    public function __construct(StripeConfig $stripeConfig, UrlGeneratorInterface $router)
    {
        $this->stripeConfig = $stripeConfig;
        $this->router = $router;
        $this->stripeClient = new StripeClient($this->stripeConfig->getSecretKey());
    }

    public function createCheckoutSession(Appointment $appointment): Session
    {
        return $this->stripeClient->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $appointment->getService()->getName(),
                    ],
                    'unit_amount' => $appointment->getTotal() * 100, // en centimes
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
    }

    public function retrieveSession(string $sessionId): Session
    {
        return $this->stripeClient->checkout->sessions->retrieve($sessionId);
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
