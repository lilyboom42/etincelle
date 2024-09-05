<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * Repository for handling operations related to Event entities.
 * Référentiel pour gérer les opérations relatives aux entités Event.
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Returns all events ordered by the closest event date.
     * Retourne tous les événements triés par date de l'événement la plus proche.
     *
     * @return Event[] An array of Event objects
     *                 Un tableau d'objets Event
     */
    public function findAllOrderedByEventDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns events that will take place after a specific date.
     * Retourne les événements qui auront lieu après une date spécifique.
     *
     * @param \DateTimeImmutable $date The reference date
     *                                 La date de référence
     * @return Event[] An array of Event objects
     *                 Un tableau d'objets Event
     */
    public function findUpcomingEvents(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.eventDate >= :date')
            ->setParameter('date', $date)
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns events created between two dates.
     * Retourne les événements créés entre deux dates.
     *
     * @param \DateTimeImmutable $startDate The start date
     *                                      La date de début
     * @param \DateTimeImmutable $endDate The end date
     *                                    La date de fin
     * @return Event[] An array of Event objects
     *                 Un tableau d'objets Event
     */
    public function findEventsCreatedBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns events matching the title, either partially or completely.
     * Retourne les événements par titre, partiellement ou complètement correspondants.
     *
     * @param string $title The title or part of the title
     *                      Le titre ou une partie du titre
     * @return Event[] An array of Event objects
     *                 Un tableau d'objets Event
     */
    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.title LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
