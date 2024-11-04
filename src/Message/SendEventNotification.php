<?php
// src/Message/SendEventNotification.php

namespace App\Message;

class SendEventNotification
{
    private int $eventId;
    private string $subscriberEmail;

    public function __construct(int $eventId, string $subscriberEmail)
    {
        $this->eventId = $eventId;
        $this->subscriberEmail = $subscriberEmail;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getSubscriberEmail(): string
    {
        return $this->subscriberEmail;
    }
}
