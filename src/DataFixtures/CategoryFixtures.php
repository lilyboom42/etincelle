<?php

namespace App\DataFixtures;

use App\Entity\CategoryProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_REFERENCE_PREFIX = 'category_';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            'electronics' => 'Électronique',
            'books' => 'Livres',
            'clothing' => 'Vêtements',
            // Ajoutez d'autres catégories si nécessaire
        ];

        foreach ($categories as $key => $name) {
            $category = new CategoryProduct();
            $category->setName($name);

            $manager->persist($category);

            // Ajouter une référence pour utiliser dans ProductFixtures
            $this->addReference(self::CATEGORY_REFERENCE_PREFIX . $key, $category);
        }

        $manager->flush();
    }
}
