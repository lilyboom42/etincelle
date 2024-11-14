<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $statuses = ['demandé', 'en attente', 'payé', 'fait', 'approuvé', 'rejeté', 'annulé', 'confirmé'];

        foreach ($statuses as $statusName) {
            $status = new Status();
            $status->setName($statusName);
            $manager->persist($status);
        }

        $manager->flush();
    }
}
