<?php

namespace App\Service;

class StripeConfig
{
    private string $stripePublicKey;
    private string $stripeSecretKey;
    private string $stripeWebhookSecret;

    public function __construct(
        string $stripePublicKey,
        string $stripeSecretKey,
        string $stripeWebhookSecret
    ) {
        $this->stripePublicKey = $stripePublicKey;
        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripeWebhookSecret = $stripeWebhookSecret;
    }

    public function getPublicKey(): string
    {
        return $this->stripePublicKey;
    }

    public function getSecretKey(): string
    {
        return $this->stripeSecretKey;
    }

    public function getWebhookSecret(): string
    {
        return $this->stripeWebhookSecret;
    }
}
