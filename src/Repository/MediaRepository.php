<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * Repository for handling operations related to Media entities.
 * Référentiel pour gérer les opérations relatives aux entités Media.
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Finds media associated with a specific blog post.
     * Trouve les médias associés à un article de blog spécifique.
     *
     * @param int $blogPostId The blog post ID
     *                        L'identifiant de l'article de blog
     * @return Media[] An array of Media objects
     *                 Un tableau d'objets Media
     */
    public function findByBlogPost(int $blogPostId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.blogPost = :blogPostId')
            ->setParameter('blogPostId', $blogPostId)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds media by type (image or video).
     * Trouve les médias par type (image ou vidéo).
     *
     * @param string $type The media type (image or video)
     *                     Le type de média (image ou video)
     * @return Media[] An array of Media objects
     *                 Un tableau d'objets Media
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->setParameter('type', $type)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds a media item by its path.
     * Trouve un média par son chemin.
     *
     * @param string $path The path of the media
     *                     Le chemin du média
     * @return Media|null A Media object or null if not found
     *                    Un objet Media ou null si non trouvé
     */
    public function findOneByPath(string $path): ?Media
    {
        return $this->findOneBy(['path' => $path]);
    }
}
