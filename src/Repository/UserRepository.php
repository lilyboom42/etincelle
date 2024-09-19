<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 * Repository for managing operations related to the User entity.
 * Référentiel pour gérer les opérations relatives à l'entité User.
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * Utilisé pour mettre à jour (rehasher) automatiquement le mot de passe de l'utilisateur au fil du temps.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Finds a user by email.
     * Trouve un utilisateur par son adresse e-mail.
     *
     * @param string $email The email to search for / L'adresse e-mail à rechercher
     * @return User|null Returns a User object or null if not found / Renvoie un objet User ou null si non trouvé
     */
    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds users by role.
     * Trouve les utilisateurs par rôle.
     *
     * @param string $role The role to search for / Le rôle à rechercher
     * @return User[] Returns an array of User objects / Renvoie un tableau d'objets User
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode($role))
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds active users.
     * Trouve les utilisateurs actifs.
     *
     * @return User[] Returns an array of active User objects / Renvoie un tableau d'objets User actifs
     */
    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds inactive users.
     * Trouve les utilisateurs inactifs.
     *
     * @return User[] Returns an array of inactive User objects / Renvoie un tableau d'objets User inactifs
     */
    public function findInactiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :inactive')
            ->setParameter('inactive', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds active users with pagination.
     * Trouve les utilisateurs actifs avec pagination.
     *
     * @param int $page Le numéro de la page
     * @param int $limit Le nombre d'éléments par page
     * @return Paginator Returns a Paginator object with active User objects / Renvoie un objet Paginator avec des objets User actifs
     */
    public function findActiveUsersPaginated(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    /**
     * Searches users by multiple criteria.
     * Recherche les utilisateurs par plusieurs critères.
     *
     * @param string|null $email Optional email criteria / Critère optionnel d'e-mail
     * @param string|null $role Optional role criteria / Critère optionnel de rôle
     * @param bool|null $isActive Optional active status criteria / Critère optionnel de statut actif
     * @return User[] Returns an array of User objects / Renvoie un tableau d'objets User
     */
    public function searchUsers(?string $email = null, ?string $role = null, ?bool $isActive = null): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($email) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%'.$email.'%');
        }

        if ($role) {
            $qb->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
                ->setParameter('role', json_encode($role));
        }

        if ($isActive !== null) {
            $qb->andWhere('u.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }
}
