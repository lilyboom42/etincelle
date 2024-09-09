<?php

namespace App\Controller;

use App\Entity\ProductCustomization;
use App\Form\ProductCustomizationType;
use App\Repository\ProductCustomizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductCustomizationController extends AbstractController
{
    private ProductCustomizationRepository $customizationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductCustomizationRepository $customizationRepository, EntityManagerInterface $entityManager)
    {
        $this->customizationRepository = $customizationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Displays the details of a specific customization.
     */
    #[Route('/customization/{id<\d+>}', name: 'customization_detail')]
    public function show(int $id): Response
    {
        $customization = $this->customizationRepository->find($id);

        if (!$customization) {
            throw $this->createNotFoundException('No customization found for id ' . $id);
        }

        return $this->render('customization/detail.html.twig', [
            'customization' => $customization,
        ]);
    }

    /**
     * Lists all customizations.
     */
    #[Route('/customizations', name: 'customizations_list')]
    public function list(): Response
    {
        $customizations = $this->customizationRepository->findAll();

        return $this->render('customization/list.html.twig', [
            'customizations' => $customizations,
        ]);
    }

    /**
     * Creates a new customization.
     */
    #[Route('/customization/new', name: 'customization_new')]
    public function new(Request $request): Response
    {
        $customization = new ProductCustomization();
        $form = $this->createForm(ProductCustomizationType::class, $customization);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customization);
            $this->entityManager->flush();

            return $this->redirectToRoute('customizations_list');
        }

        return $this->render('customization/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edits an existing customization.
     */
    #[Route('/customization/edit/{id<\d+>}', name: 'customization_edit')]
    public function edit(Request $request, int $id): Response
    {
        $customization = $this->customizationRepository->find($id);

        if (!$customization) {
            throw $this->createNotFoundException('No customization found for id ' . $id);
        }

        $form = $this->createForm(ProductCustomizationType::class, $customization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('customizations_list');
        }

        return $this->render('customization/edit.html.twig', [
            'form' => $form->createView(),
            'customization' => $customization,
        ]);
    }

    /**
     * Deletes a customization.
     */
    #[Route('/customization/delete/{id<\d+>}', name: 'customization_delete')]
    public function delete(int $id): Response
    {
        $customization = $this->customizationRepository->find($id);

        if (!$customization) {
            throw $this->createNotFoundException('No customization found for id ' . $id);
        }

        $this->entityManager->remove($customization);
        $this->entityManager->flush();

        return $this->redirectToRoute('customizations_list');
    }
}
