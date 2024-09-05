<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 * Repository for handling operations related to Order entities.
 * Référentiel pour gérer les opérations relatives aux entités Order.
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Finds all orders sorted by descending creation date.
     * Trouve toutes les commandes triées par date de création décroissante.
     *
     * @return Order[] An array of Order objects / Un tableau d'objets Order
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds orders by user.
     * Trouve les commandes par utilisateur.
     *
     * @param User $user The user for whom to find orders / L'utilisateur dont on veut trouver les commandes
     * @return Order[] An array of Order objects / Un tableau d'objets Order
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds orders by status.
     * Trouve les commandes par statut.
     *
     * @param string $status The order status (e.g., 'PENDING', 'COMPLETED') / Le statut de la commande (par exemple 'PENDING', 'COMPLETED')
     * @return Order[] An array of Order objects / Un tableau d'objets Order
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.orderStatus = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds orders created between two dates.
     * Trouve les commandes créées entre deux dates.
     *
     * @param \DateTimeImmutable $startDate The start date / La date de début
     * @param \DateTimeImmutable $endDate The end date / La date de fin
     * @return Order[] An array of Order objects / Un tableau d'objets Order
     */
    public function findOrdersCreatedBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
