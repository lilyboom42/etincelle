<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Form\ServiceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/service')]
#[IsGranted('ROLE_ADMIN')]
class ServiceController extends AbstractController
{
    #[Route('/', name: 'service_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
 // Filtrer les services actifs uniquement
 $services = $entityManager->getRepository(Service::class)->findBy(['isActive' => true]);
 return $this->render('admin/service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/new', name: 'service_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'service_edit', methods: ['GET', 'POST'])]
    public function form(Request $request, EntityManagerInterface $entityManager, Service $service = null): Response
    {
        // Créer une nouvelle instance si le service n'existe pas (pour l'ajout)
        if (!$service) {
            $service = new Service();
        }

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', $service->getId() ? 'Service modifié avec succès.' : 'Service ajouté avec succès.');
            return $this->redirectToRoute('service_index');
        }

        return $this->render('admin/service/new.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }

    #[Route('/{id}/delete', name: 'service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            // Marquer le service comme inactif
            $service->setIsActive(false);
            $entityManager->flush();
    
            $this->addFlash('success', 'Service désactivé avec succès.');
        }
    
        return $this->redirectToRoute('service_index');
    }
    
}
