<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings-home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SettingsController.php',
        ]);
    }

    #[Route('/settings/username', name: 'settings-update-username', methods: ['PUT'])]
    public function updateUsername(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SettingsController.php',
        ]);
    }

    #[Route('/settings/email', name: 'settings-update-email', methods: ['PUT'])]
    public function updateEmail(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SettingsController.php',
        ]);
    }
}
