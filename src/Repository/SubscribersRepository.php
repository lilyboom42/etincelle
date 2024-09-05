<?php

namespace App\Repository;

use App\Entity\Subscribers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscribers>
 * Repository for managing operations related to the Subscribers entity.
 * Référentiel pour gérer les opérations relatives à l'entité Subscribers.
 */
class SubscribersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscribers::class);
    }

    /**
     * Finds subscribers who have been subscribed for at least one year.
     * Trouve les abonnés qui sont abonnés depuis au moins un an.
     *
     * @param \DateTimeImmutable $oneYearAgo The date one year ago / La date d'il y a un an
     * @return Subscribers[] Returns an array of Subscribers objects / Renvoie un tableau d'objets Subscribers
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
     * Finds subscribers by email.
     * Trouve les abonnés par leur adresse email.
     *
     * @param string $email The email to search by / L'email à rechercher
     * @return Subscribers|null Returns a Subscribers object or null if not found / Renvoie un objet Subscribers ou null si non trouvé
     */
    public function findByEmail(string $email): ?Subscribers
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds all subscribers ordered by subscription date.
     * Trouve tous les abonnés triés par date d'abonnement.
     *
     * @return Subscribers[] Returns an array of Subscribers objects / Renvoie un tableau d'objets Subscribers
     */
    public function findAllOrderedBySubscriptionDate(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.subscribedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * Finds subscribers by a specific field value.
    //     * Trouve les abonnés par la valeur d'un champ spécifique.
    //     *
    //     * @param mixed $value The value to search by / La valeur à rechercher
    //     * @return Subscribers[] Returns an array of Subscribers objects / Renvoie un tableau d'objets Subscribers
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
    //     * @return Subscribers|null Returns a Subscribers object or null if not found / Renvoie un objet Subscribers ou null si non trouvé
    //     */
    //    public function findOneBySomeField($value): ?Subscribers
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult();
    //    }
}
