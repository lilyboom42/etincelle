<?php

namespace App\Repository;

use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderLine>
 *
 * Repository for handling operations related to OrderLine entities.
 * Référentiel pour gérer les opérations relatives aux entités OrderLine.
 */
class OrderLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderLine::class);
    }

    /**
     * Finds OrderLine entities by order ID.
     * Trouve les entités OrderLine par l'identifiant de commande.
     *
     * @param int $orderId The ID of the order / L'ID de la commande
     * @return OrderLine[] Returns an array of OrderLine objects / Retourne un tableau d'objets OrderLine
     */
    public function findByOrderId(int $orderId): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.order = :orderId')
            ->setParameter('orderId', $orderId)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds OrderLine entities by product ID.
     * Trouve les entités OrderLine par l'identifiant de produit.
     *
     * @param int $productId The ID of the product / L'ID du produit
     * @return OrderLine[] Returns an array of OrderLine objects / Retourne un tableau d'objets OrderLine
     */
    public function findByProductId(int $productId): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds the total quantity of a specific product ordered.
     * Trouve la quantité totale d'un produit spécifique commandé.
     *
     * @param int $productId The ID of the product / L'ID du produit
     * @return int The total quantity ordered / La quantité totale commandée
     */
    public function findTotalQuantityByProductId(int $productId): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('SUM(o.quantity)')
            ->andWhere('o.product = :productId')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Finds OrderLine entities with a quantity greater than a specific value.
     * Trouve les entités OrderLine avec une quantité supérieure à une valeur spécifique.
     *
     * @param int $minQuantity The minimum quantity / La quantité minimum
     * @return OrderLine[] Returns an array of OrderLine objects / Retourne un tableau d'objets OrderLine
     */
    public function findByQuantityGreaterThan(int $minQuantity): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.quantity > :minQuantity')
            ->setParameter('minQuantity', $minQuantity)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
