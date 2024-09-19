<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class CartController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Méthode privée pour récupérer le panier de la session
     */
    private function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    /**
     * Méthode privée pour enregistrer le panier dans la session
     */
    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }

    #[Route('/panier', name: 'view_cart')]
    public function viewCart(ProductRepository $productRepository): Response
    {
        $cart = $this->getCart();
        $cartData = [];

        foreach ($cart as $productId => $details) {
            $product = $productRepository->find($productId);

            if ($product) {
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                ];
            }
        }

        return $this->render('shop/cart.html.twig', [
            'cart' => $cartData,
        ]);
    }

    #[Route('/ajouter-au-panier/{id}', name: 'add_to_cart')]
    public function addToCart(Product $product): RedirectResponse
    {
        $cart = $this->getCart();
        $productId = $product->getId();
        $currentQuantity = $cart[$productId]['quantity'] ?? 0;
    
        if ($currentQuantity + 1 > $product->getStockQuantity()) {
            // Si la quantité demandée dépasse le stock, afficher un message d'erreur
            $this->addFlash('error', 'Vous avez atteint la quantité maximale disponible pour ce produit.');
        } else {
            // Ajouter ou augmenter la quantité dans le panier
            $cart[$productId]['quantity'] = $currentQuantity + 1;
            $this->saveCart($cart);
            $this->addFlash('success', 'Produit ajouté au panier !');
        }
    
        return $this->redirectToRoute('shop_index');
    }
    

    #[Route('/supprimer-du-panier/{id}', name: 'remove_from_cart')]
    public function removeFromCart(Product $product): RedirectResponse
    {
        $cart = $this->getCart();
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
            $this->addFlash('success', 'Produit supprimé du panier.');
        }

        return $this->redirectToRoute('view_cart');
    }

    #[Route('/modifier-quantite/{id}', name: 'update_cart_quantity', methods: ['POST'])]
    public function updateCartQuantity(Product $product): RedirectResponse
    {
        $cart = $this->getCart();
        $productId = $product->getId();
        $newQuantity = (int)$this->requestStack->getMainRequest()->request->get('quantity');
    
        if ($newQuantity > $product->getStockQuantity()) {
            // Si la quantité modifiée dépasse le stock, afficher un message d'erreur
            $this->addFlash('error', 'Vous avez atteint la quantité maximale disponible pour ce produit.');
        } elseif ($newQuantity > 0) {
            // Mettre à jour la quantité dans le panier
            $cart[$productId]['quantity'] = $newQuantity;
            $this->addFlash('success', 'Quantité mise à jour dans le panier.');
        } else {
            // Supprimer le produit du panier si la quantité est inférieure ou égale à 0
            unset($cart[$productId]);
            $this->addFlash('success', 'Produit supprimé du panier.');
        }
    
        $this->saveCart($cart);
    
        return $this->redirectToRoute('view_cart');
    }
    
}
