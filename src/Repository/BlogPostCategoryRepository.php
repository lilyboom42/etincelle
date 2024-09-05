<?php

namespace App\Repository;

use App\Entity\BlogPostCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPostCategory>
 * 
 * Repository for handling operations related to BlogPostCategory entities.
 * Référentiel pour gérer les opérations relatives aux entités BlogPostCategory.
 */
class BlogPostCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPostCategory::class);
    }

    /**
     * Returns all blog post categories ordered by name.
     * Retourne toutes les catégories d'articles de blog triées par nom.
     *
     * @return BlogPostCategory[] An array of BlogPostCategory objects
     *                            Un tableau d'objets BlogPostCategory
     */
    public function findAllOrderedByName(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    /**
     * Finds a category by its name.
     * Trouve une catégorie par son nom.
     *
     * @param string $name The name of the category
     *                     Le nom de la catégorie
     * @return BlogPostCategory|null A BlogPostCategory object or null if not found
     *                               Un objet BlogPostCategory ou null si non trouvé
     */
    public function findOneByName(string $name): ?BlogPostCategory
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Finds categories that have at least one associated blog post.
     * Trouve les catégories qui ont au moins un article de blog associé.
     *
     * @return BlogPostCategory[] An array of BlogPostCategory objects
     *                            Un tableau d'objets BlogPostCategory
     */
    public function findCategoriesWithBlogPosts(): array
    {
        return $this->createQueryBuilder('category')
            ->innerJoin('category.blogPostCategoryRelations', 'relation')
            ->groupBy('category.id')
            ->having('COUNT(relation.id) > 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds categories associated with a minimum number of blog posts.
     * Trouve les catégories associées à un nombre minimum d'articles de blog.
     *
     * @param int $minPosts The minimum number of associated blog posts
     *                      Le nombre minimum d'articles de blog associés
     * @return BlogPostCategory[] An array of BlogPostCategory objects
     *                            Un tableau d'objets BlogPostCategory
     */
    public function findCategoriesWithMinimumPosts(int $minPosts): array
    {
        return $this->createQueryBuilder('category')
            ->innerJoin('category.blogPostCategoryRelations', 'relation')
            ->groupBy('category.id')
            ->having('COUNT(relation.id) >= :minPosts')
            ->setParameter('minPosts', $minPosts)
            ->getQuery()
            ->getResult();
    }
}
