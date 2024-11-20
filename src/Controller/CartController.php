<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\StripeConfig;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    private CartService $cartService;
    private StripeConfig $stripeConfig;

    public function __construct(CartService $cartService, StripeConfig $stripeConfig)
    {
        $this->cartService = $cartService;
        $this->stripeConfig = $stripeConfig;
    }

    #[Route('/', name: 'view_cart', methods: ['GET'])]
    public function viewCart(ProductRepository $productRepository): Response
    {
        $cart = $this->cartService->getCart();
        $cartData = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);

            if ($product) {
                $subtotal = $product->getPrice() * $quantity;
                $total += $subtotal;

                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return $this->render('shop/cart.html.twig', [
            'cart' => $cartData,
            'total' => $total,
        ]);
    }

    #[Route('/ajouter/{id}', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart(Product $product, Request $request): RedirectResponse
    {
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity < 1) {
            $this->addFlash('error', 'La quantité doit être au moins de 1.');
            return $this->redirectToRoute('view_cart');
        }

        if ($quantity > $product->getStockQuantity()) {
            $this->addFlash('error', 'Vous avez atteint la quantité maximale disponible pour ce produit.');
        } else {
            $this->cartService->addToCart($product->getId(), $quantity);
            $this->addFlash('success', 'Produit ajouté au panier !');
        }

        return $this->redirectToRoute('view_cart');
    }

    #[Route('/supprimer/{id}', name: 'remove_from_cart', methods: ['POST'])]
    public function removeFromCart(Product $product): RedirectResponse
    {
        $this->cartService->removeFromCart($product->getId());
        $this->addFlash('success', 'Produit supprimé du panier.');

        return $this->redirectToRoute('view_cart');
    }

    #[Route('/confirmation', name: 'cart_checkout', methods: ['GET'])]
    public function checkout(ProductRepository $productRepository): Response
    {
        $cart = $this->cartService->getCart();

        // Vérifiez si le panier est vide
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('view_cart');
        }

        $cartData = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product) {
                $subtotal = $product->getPrice() * $quantity;
                $total += $subtotal;

                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cartData,
            'total' => $total,
        ]);
    }

    #[Route('/modifier-quantite/{id}', name: 'update_cart_quantity', methods: ['POST'])]
    public function updateCartQuantity(Product $product, Request $request): RedirectResponse
    {
        $productId = $product->getId();
        $newQuantity = (int) $request->request->get('quantity');

        if ($newQuantity < 0) {
            $this->addFlash('error', 'La quantité ne peut pas être négative.');
            return $this->redirectToRoute('view_cart');
        }

        if ($newQuantity > $product->getStockQuantity()) {
            $this->addFlash('error', 'Vous avez atteint la quantité maximale disponible pour ce produit.');
        } elseif ($newQuantity === 0) {
            $this->cartService->removeFromCart($productId);
            $this->addFlash('success', 'Produit supprimé du panier.');
        } else {
            $this->cartService->updateCartItemQuantity($productId, $newQuantity);
            $this->addFlash('success', 'Quantité mise à jour dans le panier.');
        }

        return $this->redirectToRoute('view_cart');
    }
}
