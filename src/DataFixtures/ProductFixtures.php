<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $products = [
            [
                'name' => 'Huile de Massage Relaxante',
                'description' => 'Une huile de massage pour une relaxation profonde.',
                'price' => 20.00,
                'stockQuantity' => 100,
                'category' => 'electronics', 
            ],
            [
                'name' => 'Bougies Aromatiques',
                'description' => 'Bougies parfumées pour une ambiance apaisante.',
                'price' => 15.00,
                'stockQuantity' => 200,
                'category' => 'electronics', 
            ],
            [
                'name' => 'Thé Détox',
                'description' => 'Un mélange de thés pour purifier votre organisme.',
                'price' => 12.50,
                'stockQuantity' => 150,
                'category' => 'books',
            ],
            
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setStockQuantity($productData['stockQuantity']);

            // Récupérer la catégorie par référence
            $categoryReference = CategoryFixtures::CATEGORY_REFERENCE_PREFIX . $productData['category'];
            /** @var \App\Entity\Category $category */
            $categoryProduct = $this->getReference($categoryReference);
            $product->setCategoryProduct($categoryProduct);

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
