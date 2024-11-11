<?php

namespace App\Enum;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case CONFIRMED = 'confirmed';
    case APPROVED = 'approved';
    case PAID = 'paid';

    // Méthodes spécifiques (facultatif)
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::REJECTED => 'Rejeté',
            self::CANCELLED => 'Annulé',
            self::CONFIRMED => 'Confirmé',
            self::APPROVED => 'Approuvé',
            self::PAID => 'Payé',
        };
    }
}
