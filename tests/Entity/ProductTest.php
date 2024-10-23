<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testDecrementStockQuantitySuccessfully()
    {
        $product = new Product();
        $product->setStockQuantity(10);

        $product->decrementStockQuantity(5);

        $this->assertEquals(5, $product->getStockQuantity());
    }

    public function testDecrementStockQuantityInsufficientStock()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuffisant pour le produit: Test Product');

        $product = new Product();
        $product->setName('Test Product');
        $product->setStockQuantity(3);

        $product->decrementStockQuantity(5);
    }

    public function testDecrementStockQuantityNegativeQuantity()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La quantité à décrémenter doit être positive.');

        $product = new Product();
        $product->setStockQuantity(10);

        $product->decrementStockQuantity(-2);
    }
}
