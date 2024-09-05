<?php

namespace App\Repository;

use App\Entity\Reviews;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reviews>
 * Repository for managing operations related to the Reviews entity.
 * Référentiel pour gérer les opérations relatives à l'entité Reviews.
 */
class ReviewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reviews::class);
    }

    /**
     * Finds reviews by product.
     * Trouve des avis par produit.
     *
     * @param Product $product The product to search reviews for / Le produit pour lequel rechercher des avis
     * @return Reviews[] Returns an array of Reviews objects / Renvoie un tableau d'objets Reviews
     */
    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.product = :product')
            ->setParameter('product', $product)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds reviews by user.
     * Trouve des avis par utilisateur.
     *
     * @param User $user The user to search reviews for / L'utilisateur pour lequel rechercher des avis
     * @return Reviews[] Returns an array of Reviews objects / Renvoie un tableau d'objets Reviews
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds reviews with a specific rating.
     * Trouve des avis avec une note spécifique.
     *
     * @param int $rating The rating value to filter by / La valeur de la note pour filtrer
     * @return Reviews[] Returns an array of Reviews objects / Renvoie un tableau d'objets Reviews
     */
    public function findByRating(int $rating): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.rating = :rating')
            ->setParameter('rating', $rating)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds the average rating for a specific product.
     * Trouve la note moyenne pour un produit spécifique.
     *
     * @param Product $product The product to calculate the average rating for / Le produit pour lequel calculer la note moyenne
     * @return float|null Returns the average rating or null if no reviews found / Renvoie la note moyenne ou null si aucun avis n'est trouvé
     */
    public function findAverageRatingByProduct(Product $product): ?float
    {
        return $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->andWhere('r.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * Finds reviews by a specific field value.
    //     * Trouve des avis par la valeur d'un champ spécifique.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return Reviews[] Returns an array of Reviews objects / Renvoie un tableau d'objets Reviews
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult();
    //    }

    //    /**
    //     * Finds a single review by a specific field value.
    //     * Trouve un seul avis par la valeur d'un champ spécifique.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return Reviews|null Returns a Reviews object or null if not found / Renvoie un objet Reviews ou null si non trouvé
    //     */
    //    public function findOneBySomeField($value): ?Reviews
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult();
    //    }
}
