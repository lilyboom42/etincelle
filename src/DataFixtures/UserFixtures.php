<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserDetails; // Assurez-vous que c'est le bon nom de classe
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        // Création d'un utilisateur administrateur
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $password = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($password);

        // Ajouter les détails de l'utilisateur
        $adminDetail = new UserDetails(); // Assurez-vous que le nom de la classe est correct
        $adminDetail->setCity('Paris');
        $adminDetail->setAddress('1 Rue de l\'Admin');
        $adminDetail->setCountry('France');
        $adminDetail->setPostalCode('75001');
        $adminDetail->setPhoneNumber('0102030405');
        $adminDetail->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        $admin->setUserDetail($adminDetail);

        $manager->persist($adminDetail);
        $manager->persist($admin);

        // Création d'utilisateurs clients
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setFirstName("User$i");
            $user->setLastName("Test$i");
            $user->setRoles(['ROLE_USER']);
            $password = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($password);

            // Détails de l'utilisateur
            $userDetail = new UserDetails(); // Assurez-vous que le nom de la classe est correct
            $userDetail->setCity('Ville' . $i);
            $userDetail->setAddress("Adresse $i");
            $userDetail->setCountry('France');
            $userDetail->setPostalCode('7500' . $i);
            $userDetail->setPhoneNumber('060000000' . $i);
            $userDetail->setBirthDate(new \DateTimeImmutable('1990-01-' . sprintf('%02d', $i)));
            $user->setUserDetail($userDetail);

            $manager->persist($userDetail);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
