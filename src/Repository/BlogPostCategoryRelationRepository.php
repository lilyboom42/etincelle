<?php

namespace App\Repository;

use App\Entity\BlogPostCategoryRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPostCategoryRelation>
 * 
 * Repository for handling operations related to BlogPostCategoryRelation entities.
 * Référentiel pour gérer les opérations relatives aux entités BlogPostCategoryRelation.
 */
class BlogPostCategoryRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPostCategoryRelation::class);
    }

    /**
     * Finds all relations for a specific BlogPost.
     * Trouve toutes les relations pour un BlogPost spécifique.
     *
     * @param int $blogPostId The ID of the blog post
     *                        L'identifiant de l'article de blog
     * @return BlogPostCategoryRelation[] An array of BlogPostCategoryRelation objects
     *                                    Un tableau d'objets BlogPostCategoryRelation
     */
    public function findByBlogPost(int $blogPostId): array
    {
        return $this->findBy(['blogPost' => $blogPostId]);
    }

    /**
     * Finds all relations for a specific category.
     * Trouve toutes les relations pour une catégorie spécifique.
     *
     * @param int $categoryId The ID of the category
     *                        L'identifiant de la catégorie
     * @return BlogPostCategoryRelation[] An array of BlogPostCategoryRelation objects
     *                                    Un tableau d'objets BlogPostCategoryRelation
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->findBy(['category' => $categoryId]);
    }

    /**
     * Checks if a relation between a BlogPost and a category exists.
     * Vérifie l'existence d'une relation entre un BlogPost et une catégorie.
     *
     * @param int $blogPostId The ID of the blog post
     *                        L'identifiant de l'article de blog
     * @param int $categoryId The ID of the category
     *                        L'identifiant de la catégorie
     * @return bool True if the relation exists, False otherwise
     *              True si la relation existe, False sinon
     */
    public function relationExists(int $blogPostId, int $categoryId): bool
    {
        $result = $this->createQueryBuilder('r')
            ->andWhere('r.blogPost = :blogPostId')
            ->andWhere('r.category = :categoryId')
            ->setParameter('blogPostId', $blogPostId)
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}
