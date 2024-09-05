<?php
namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 * Repository for handling operations related to Product entities.
 * Référentiel pour gérer les opérations relatives aux entités Product.
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Finds products by name using a partial match.
     * Trouve des produits par nom en utilisant une correspondance partielle.
     *
     * @param string $name The name to search for / Le nom à rechercher
     * @return Product[] Returns an array of Product objects / Renvoie un tableau d'objets Product
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds products within a specific price range.
     * Trouve des produits dans une gamme de prix spécifique.
     *
     * @param float $minPrice The minimum price / Le prix minimum
     * @param float $maxPrice The maximum price / Le prix maximum
     * @return Product[] Returns an array of Product objects / Renvoie un tableau d'objets Product
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.price BETWEEN :minPrice AND :maxPrice')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all available products (products with stock quantity greater than 0).
     * Trouve tous les produits disponibles (produits avec une quantité en stock supérieure à 0).
     *
     * @return Product[] Returns an array of Product objects / Renvoie un tableau d'objets Product
     */
    public function findAvailableProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stockQuantity > 0')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds products that are favorited by a specific user.
     * Trouve les produits qui figurent dans les favoris d'un utilisateur spécifique.
     *
     * @param User $user The user whose favorites to find / L'utilisateur dont on veut trouver les favoris
     * @return Product[] Returns an array of Product objects that are in the user's favorites / Renvoie un tableau d'objets Product qui figurent dans les favoris de l'utilisateur
     */
    public function findFavoritesByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.favoritedBy', 'u')
            ->andWhere('u = :user')
            ->setParameter('user', $user)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
