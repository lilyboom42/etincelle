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

    /**
     * Constructor to initialize dependencies.
     * Constructeur pour initialiser les dépendances.
     */
    public function __construct(ProductCustomizationRepository $customizationRepository, EntityManagerInterface $entityManager)
    {
        $this->customizationRepository = $customizationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Displays the details of a specific customization.
     * Affiche les détails d'une personnalisation spécifique.
     */
    #[Route('/customization/{id}', name: 'customization_detail')]
    public function show(int $id): Response
    {
        $customization = $this->customizationRepository->find($id);

        if (!$customization) {
            // Throw an exception if the customization is not found.
            // Lancer une exception si la personnalisation n'est pas trouvée.
            throw $this->createNotFoundException('No customization found for id ' . $id);
        }

        return $this->render('customization/detail.html.twig', [
            'customization' => $customization,
        ]);
    }

    /**
     * Lists all customizations.
     * Liste toutes les personnalisations.
     */
    #[Route('/customizations', name: 'customizations_list')]
    public function list(): Response
    {
        // Fetch all customizations from the repository.
        // Récupérer toutes les personnalisations du repository.
        $customizations = $this->customizationRepository->findAll();

        return $this->render('customization/list.html.twig', [
            'customizations' => $customizations,
        ]);
    }

    /**
     * Creates a new customization.
     * Crée une nouvelle personnalisation.
     */
    #[Route('/customization/new', name: 'customization_new')]
    public function new(Request $request): Response
    {
        $customization = new ProductCustomization();
        $form = $this->createForm(ProductCustomizationType::class, $customization);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new customization to the database.
            // Persiste la nouvelle personnalisation dans la base de données.
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
     * Modifie une personnalisation existante.
     */
    #[Route('/customization/edit/{id}', name: 'customization_edit')]
    public function edit(Request $request, int $id): Response
    {
        $customization = $this->customizationRepository->find($id);

        if (!$customization) {
            // Throw an exception if the customization is not found.
            // Lancer une exception si la personnalisation n'est pas trouvée.
            throw $this->createNotFoundException('No customization found for id ' . $id);
        }

        $form = $this->createForm(ProductCustomizationType::class, $customization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated customization to the database.
            // Enregistre la personnalisation mise à jour dans la base de données.
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
     * Supprime une personnalisation.
     */
    #[Route('/customization/delete/{id}', name: 'customization_delete')]
    public function delete(int $id): Response
    {
        $customization = $this->customizationRepository->find($id);

        if (!$customization) {
            // Throw an exception if the customization is not found.
            // Lancer une exception si la personnalisation n'est pas trouvée.
            throw $this->createNotFoundException('No customization found for id ' . $id);
        }

        // Remove the customization from the database.
        // Supprime la personnalisation de la base de données.
        $this->entityManager->remove($customization);
        $this->entityManager->flush();

        return $this->redirectToRoute('customizations_list');
    }
}
