<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<BlogPost>
 * 
 * Repository for handling operations related to BlogPost entities.
 * Référentiel pour gérer les opérations relatives aux entités BlogPost.
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    /**
     * Returns all blog posts ordered by creation date in descending order.
     * Retourne tous les articles de blog ordonnés par date de création décroissante.
     *
     * @return BlogPost[] An array of BlogPost objects
     *                    Un tableau d'objets BlogPost
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds blog posts by a specific author.
     * Trouve les articles de blog par un auteur spécifique.
     *
     * @param int $authorId The author's ID
     *                      L'identifiant de l'auteur
     * @return BlogPost[] An array of BlogPost objects
     *                    Un tableau d'objets BlogPost
     */
    public function findByAuthor(int $authorId): array
    {
        return $this->findBy(['author' => $authorId], ['createdAt' => 'DESC']);
    }

    /**
     * Finds blog posts by a specific category.
     * Trouve les articles de blog par une catégorie spécifique.
     *
     * @param int $categoryId The category ID
     *                        L'identifiant de la catégorie
     * @return BlogPost[] An array of BlogPost objects
     *                    Un tableau d'objets BlogPost
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.blogPostCategoryRelations', 'r')
            ->andWhere('r.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds blog posts with pagination.
     * Trouve les articles de blog avec pagination.
     *
     * @param int $page The current page number
     *                  Le numéro de la page actuelle
     * @param int $limit The number of posts per page
     *                   Le nombre d'articles par page
     * @return Paginator A Paginator object with paginated posts
     *                   Un objet Paginator avec les articles paginés
     */
    public function findPaginated(int $page = 1, int $limit = 10): Paginator
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    /**
     * Finds blog posts published within a specific date range.
     * Trouve les articles de blog publiés dans une plage de dates spécifique.
     *
     * @param \DateTimeInterface $startDate The start date of the range
     *                                      La date de début de la plage
     * @param \DateTimeInterface $endDate The end date of the range
     *                                    La date de fin de la plage
     * @return BlogPost[] An array of BlogPost objects
     *                    Un tableau d'objets BlogPost
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
