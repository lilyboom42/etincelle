<?php

namespace App\Repository;

use App\Entity\Subscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<subscriber>
 * Repository for managing operations related to the subscriber entity.
 * Référentiel pour gérer les opérations relatives à l'entité subscriber.
 */
class SubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscriber::class);
    }

    /**
     * Finds subscriber who have been subscribed for at least one year.
     * Trouve les abonnés qui sont abonnés depuis au moins un an.
     *
     * @param \DateTimeImmutable $oneYearAgo The date one year ago / La date d'il y a un an
     * @return subscriber[] Returns an array of subscriber objects / Renvoie un tableau d'objets subscriber
     */
    public function findSubscribedForOneYear(\DateTimeImmutable $oneYearAgo): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.subscribedAt <= :oneYearAgo')
            ->setParameter('oneYearAgo', $oneYearAgo)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds subscriber by email.
     * Trouve les abonnés par leur adresse email.
     *
     * @param string $email The email to search by / L'email à rechercher
     * @return Subscriber|null Returns a subscriber object or null if not found / Renvoie un objet subscriber ou null si non trouvé
     */
    public function findByEmail(string $email): ?Subscriber
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds all subscriber ordered by subscription date.
     * Trouve tous les abonnés triés par date d'abonnement.
     *
     * @return Subscriber[] Returns an array of subscriber objects / Renvoie un tableau d'objets subscriber
     */
    public function findAllOrderedBySubscriptionDate(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.subscribedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * Finds subscriber by a specific field value.
    //     * Trouve les abonnés par la valeur d'un champ spécifique.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return subscriber[] Returns an array of subscriber objects / Renvoie un tableau d'objets subscriber
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult();
    //    }

    //    /**
    //     * Finds a single subscriber by a specific field value.
    //     * Trouve un seul abonné par la valeur d'un champ spécifique.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return subscriber|null Returns a subscriber object or null if not found / Renvoie un objet subscriber ou null si non trouvé
    //     */
    //    public function findOneBySomeField($value): ?subscriber
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult();
    //    }
}
