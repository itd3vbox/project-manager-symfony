<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HomeController.php',
        ]);
    }

    #[Route('/info', name: 'home.info')]
    public function info(
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        $totalProjects = $entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Project p')->getSingleScalarResult();
        $totalTasks = $entityManager->createQuery('SELECT COUNT(t) FROM App\Entity\Task t')->getSingleScalarResult();
        $totalTests = $entityManager->createQuery('SELECT COUNT(t) FROM App\Entity\Test t')->getSingleScalarResult();
        $totalNotifications = $entityManager->createQuery('SELECT COUNT(n) FROM App\Entity\Notification n')->getSingleScalarResult();

        return $this->json([
            'data' => [
                'total_projects' => $totalProjects,
                'total_tasks' => $totalTasks,
                'total_tests' => $totalTests,
                'total_notifications' => $totalNotifications,
            ]
        ]);
    }
}
