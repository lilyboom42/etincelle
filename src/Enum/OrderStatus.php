<?php

namespace App\Enum;

/**
 * Represents the different statuses an order can have.
 * Représente les différents statuts qu'une commande peut avoir.
 */
enum OrderStatus: string
{
    case Pending = 'pending';           // Order is pending
                                        // La commande est en attente

    case Processing = 'processing';     // Order is being processed
                                        // La commande est en cours de traitement

    case Completed = 'completed';       // Order is completed
                                        // La commande est terminée

    case Canceled = 'canceled';         // Order is canceled
                                        // La commande est annulée
}
