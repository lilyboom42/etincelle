<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class ProductController extends AbstractController
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;
    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route('/shop', name: 'shop_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();

        $deleteForms = [];

        foreach ($products as $product) {
            if ($product->getId()) {
                // Créer un formulaire de suppression pour ce produit
                $deleteForms[$product->getId()] = $this->createDeleteForm($product)->createView();
            } else {
                // Message de débogage si un produit n'a pas d'ID
                dump('Le produit ' . $product->getName() . ' n\'a pas d\'ID.');
            }
        }

        dump($deleteForms); 

        return $this->render('shop/boutique.html.twig', [
            'products' => $products,
            'delete_forms' => $deleteForms,
        ]);
    }
    

    private function createDeleteForm(Product $product): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', ['id' => $product->getId()]))
            ->setMethod('POST')
            ->add('_token', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, [
                'data' => $this->csrfTokenManager->getToken('delete' . $product->getId())->getValue(),
            ])
            ->getForm();
    }

    #[Route('/product/new', name: 'product_new', methods: ['GET', 'POST'])]
    #[Route('/product/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function form(Request $request, Product $product = null): Response
    {
        if (!$product) {
            $product = new Product();
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->get('productImages') as $imageForm) {
                // Vérifie si l'utilisateur a coché la case de suppression
                if ($imageForm->get('delete')->getData()) {
                    $image = $imageForm->getData();
                    $product->removeProductImage($image); // Suppression de l'image dans l'entité
                    $this->entityManager->remove($image); // Suppression de l'image dans la base de données
        
                    // Supprimer physiquement l'image du serveur
                    if ($image->getImagesPath()) {
                        $imagePath = $this->getParameter('images_directory') . '/' . $image->getImagesPath();
                        if (file_exists($imagePath) && is_file($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                }
            }
        
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        
            $this->addFlash('success', 'Produit modifié avec succès.');
        
            return $this->redirectToRoute('shop_index');
        }
        
        

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => $product->getId() !== null,
        ]);
    }

    #[Route('/product/{id}/delete', name: 'product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Product $product): Response
    {
        if (!$product) {
            throw new NotFoundHttpException('Le produit n\'existe pas');
        }

        // Vérification du jeton CSRF
        $submittedToken = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $submittedToken)) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le produit a été supprimé avec succès!');
        } else {
            $this->addFlash('error', 'Token CSRF invalide. La suppression a échoué.');
        }

        return $this->redirectToRoute('shop_index');
    }

    #[Route('/product/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}