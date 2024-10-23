<?php

namespace App\Repository;

use App\Entity\ProductImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage>
 * Repository for handling operations related to ProductImage entities.
 * Référentiel pour gérer les opérations relatives aux entités ProductImage.
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    /**
     * Finds ProductImage entities by product ID.
     * Trouve les entités ProductImage par identifiant de produit.
     *
     * @param int $productId The ID of the product / L'identifiant du produit
     * @return ProductImage[] Returns an array of ProductImage objects / Retourne un tableau d'objets ProductImage
     */
    public function findByProductId(int $productId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds the main image for a specific product.
     * Trouve l'image principale d'un produit spécifique.
     *
     * @param int $productId The ID of the product / L'identifiant du produit
     * @return ProductImage|null Returns the main ProductImage or null / Retourne l'image principale ou null
     */
    public function findMainImageByProductId(int $productId): ?ProductImage
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.product = :productId')
            ->andWhere('p.isMain = true')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds all ProductImages sorted by creation date.
     * Trouve toutes les ProductImages triées par date de création.
     *
     * @return ProductImage[] Returns an array of ProductImage objects / Retourne un tableau d'objets ProductImage
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * Finds ProductImage entities by example field.
    //     * Trouve les entités ProductImage par champ exemple.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return ProductImage[] Returns an array of ProductImage objects / Retourne un tableau d'objets ProductImage
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult();
    //    }

    //    /**
    //     * Finds a single ProductImage entity by example field.
    //     * Trouve une seule entité ProductImage par champ exemple.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return ProductImage|null Returns a ProductImage object or null / Retourne un objet ProductImage ou null
    //     */
    //    public function findOneBySomeField($value): ?ProductImage
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult();
    //    }
}