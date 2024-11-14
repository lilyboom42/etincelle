<?php

namespace App\DataFixtures;

use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ServiceFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $services = [
            [
                'name' => 'Massage Relaxant',
                'description' => 'Un massage pour détendre les muscles et l\'esprit.',
                'price' => 60.00,
            ],
            [
                'name' => 'Massage Énergétique',
                'description' => 'Un massage pour rééquilibrer les énergies du corps.',
                'price' => 75.00,
            ],
            [
                'name' => 'Réflexologie Plantaire',
                'description' => 'Un massage des pieds pour stimuler les zones réflexes.',
                'price' => 50.00,
            ],
            // Ajoutez d'autres services si nécessaire
        ];

        foreach ($services as $serviceData) {
            $service = new Service();
            $service->setName($serviceData['name']);
            $service->setDescription($serviceData['description']);
            $service->setPrice($serviceData['price']);

            $manager->persist($service);
        }

        $manager->flush();
    }
}
