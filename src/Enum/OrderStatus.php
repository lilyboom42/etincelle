<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    // Méthode pour obtenir un libellé convivial pour chaque statut (facultatif)
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payé',
            self::SHIPPED => 'Expédié',
            self::CANCELLED => 'Annulé',
            self::COMPLETED => 'Complété',
        };
    }
}
