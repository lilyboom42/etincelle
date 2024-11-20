<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/calendar')]
#[IsGranted('ROLE_ADMIN')]
class CalendarController extends AbstractController
{
    #[Route('/', name: 'admin_calendar', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $appointments = $entityManager->getRepository(Appointment::class)
            ->findBy(['status' => OrderStatus::PAID]);

        return $this->render('admin/calendar/index.html.twig', [
            'appointments' => $appointments,
        ]);
    }
}
