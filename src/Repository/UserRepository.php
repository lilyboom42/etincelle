<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $entityManager = $this->getEntityManager();
        $entityManager->persist($user);
        $entityManager->flush();
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
}
