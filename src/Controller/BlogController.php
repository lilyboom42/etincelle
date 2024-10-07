<?php

// src/Controller/BlogController.php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\BlogType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $blogs = $entityManager->getRepository(BlogPost::class)->findAll();

        return $this->render('blog/index.html.twig', [
            'blogs' => $blogs,
        ]);
    }

    #[Route('/blog/new', name: 'blog_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')] // Restreindre l'accès aux administrateurs
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $blog = new BlogPost();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Plus besoin d'utiliser $this->getDoctrine()->getManager() car EntityManagerInterface est injecté
            $entityManager->persist($blog);
            $entityManager->flush();

            $this->addFlash('success', 'Blog créé avec succès.');
            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/new.html.twig', [
            'form' => $form->createView(),
            'blogPost' => $blog,
        ]);
    }
}
