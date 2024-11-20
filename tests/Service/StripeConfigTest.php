<?php
// tests/Service/StripeConfigTest.php

namespace App\Tests\Service;

use App\Service\StripeConfig;
use PHPUnit\Framework\TestCase;

class StripeConfigTest extends TestCase
{
    public function testGetPublicKey()
    {
        $publicKey = 'pk_test_example';
        $secretKey = 'sk_test_example';
        $webhookSecret = 'whsec_example';

        $stripeConfig = new StripeConfig($publicKey, $secretKey, $webhookSecret);

        $this->assertEquals($publicKey, $stripeConfig->getPublicKey());
    }

    public function testGetSecretKey()
    {
        $publicKey = 'pk_test_example';
        $secretKey = 'sk_test_example';
        $webhookSecret = 'whsec_example';

        $stripeConfig = new StripeConfig($publicKey, $secretKey, $webhookSecret);

        $this->assertEquals($secretKey, $stripeConfig->getSecretKey());
    }

    public function testGetWebhookSecret()
    {
        $publicKey = 'pk_test_example';
        $secretKey = 'sk_test_example';
        $webhookSecret = 'whsec_example';

        $stripeConfig = new StripeConfig($publicKey, $secretKey, $webhookSecret);

        $this->assertEquals($webhookSecret, $stripeConfig->getWebhookSecret());
    }
}
