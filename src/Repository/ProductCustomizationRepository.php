<?php

namespace App\Repository;

use App\Entity\ProductCustomization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCustomization>
 * Repository for handling operations related to ProductCustomization entities.
 * Référentiel pour gérer les opérations relatives aux entités ProductCustomization.
 */
class ProductCustomizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCustomization::class);
    }

    /**
     * Finds customizations by name.
     * Trouve les personnalisations par nom.
     *
     * @param string $name The name or part of the name of the customization / Le nom ou une partie du nom de la personnalisation
     * @return ProductCustomization[] An array of ProductCustomization objects / Un tableau d'objets ProductCustomization
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.customizationName LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('p.customizationName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds customizations by product.
     * Trouve les personnalisations par produit.
     *
     * @param int $productId The ID of the product / L'identifiant du produit
     * @return ProductCustomization[] An array of ProductCustomization objects / Un tableau d'objets ProductCustomization
     */
    public function findByProduct(int $productId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('p.customizationName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
