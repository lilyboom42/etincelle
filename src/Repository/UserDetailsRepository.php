<?php

namespace App\Repository;

use App\Entity\UserDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserDetails>
 * Repository for managing operations related to the UserDetails entity.
 * Référentiel pour gérer les opérations relatives à l'entité UserDetails.
 */
class UserDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDetails::class);
    }

    /**
     * Finds UserDetails by country.
     * Trouve les UserDetails par pays.
     *
     * @param string $country The country to search for / Le pays à rechercher
     * @return UserDetails[] Returns an array of UserDetails objects / Renvoie un tableau d'objets UserDetails
     */
    public function findByCountry(string $country): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.country = :country')
            ->setParameter('country', $country)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds a single UserDetails by phone number.
     * Trouve un seul UserDetails par numéro de téléphone.
     *
     * @param int $phoneNumber The phone number to search for / Le numéro de téléphone à rechercher
     * @return UserDetails|null Returns a UserDetails object or null if not found / Renvoie un objet UserDetails ou null si non trouvé
     */
    public function findOneByPhoneNumber(int $phoneNumber): ?UserDetails
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.phoneNumber = :phoneNumber')
            ->setParameter('phoneNumber', $phoneNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds UserDetails by birth date.
     * Trouve les UserDetails par date de naissance.
     *
     * @param \DateTimeImmutable $birthDate The birth date to search for / La date de naissance à rechercher
     * @return UserDetails[] Returns an array of UserDetails objects / Renvoie un tableau d'objets UserDetails
     */
    public function findByBirthDate(\DateTimeImmutable $birthDate): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.birthDate = :birthDate')
            ->setParameter('birthDate', $birthDate)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
