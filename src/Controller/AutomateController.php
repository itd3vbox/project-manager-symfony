<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Automate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class AutomateController extends AbstractController
{
    #[Route('/automates', name: 'automates', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $automates = $entityManager->getRepository(Automate::class)->findAll();

        $automatesSerialized = $serializer->serialize($automates, 'json', []);

        $automatesJSON = json_decode($automatesSerialized, true);

        return $this->json([
            'message' => 'Automates fetched successfully',
            'automates' => $automatesJSON,
        ]);
    }

    #[Route('/automates/{id}', name: 'automates.show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $automate = $entityManager->getRepository(Automate::class)->find($id);

        if (!$automate) 
        {
            return $this->json([
                'errors' => 'No automate found for id ' . $id,
            ], 404);
        }

        $automateSerialized = $serializer->serialize($automate, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $automateJSON = json_decode($automateSerialized, true);

        return $this->json([
            'message' => 'Automate fetched successfully',
            'automate' => $automateJSON,
        ]);
    }

    #[Route('/automates', name: 'automates.store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $automate = new Automate();

        $automate->setCommand($data['command']);
        $date = new \DateTimeImmutable;
        $automate->setCreatedAt($date);
        $automate->setUpdatedAt($date);

        $entityManager->persist($automate);
        $entityManager->flush();

        $automateSerialized = $serializer->serialize($automate, 'json', []);

        $automateJSON = json_decode($automateSerialized, true);

        return $this->json([
            'message' => 'Automate stored successfully',
            'automate' => $automateJSON,
        ]);
    }

    #[Route('/automates/{id}', name: 'automates.update', methods: ['PUT'])]
    public function update(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $automate = $entityManager->getRepository(Automate::class)->find($id);

        if (!$automate) 
        {
            return $this->json([
                'errors' => 'No automate found for id ' . $id,
            ], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        $automate->setCommand($data['command']);
        $automate->setUpdatedAt(new \DateTimeImmutable);

        $entityManager->flush();

        $automateSerialized = $serializer->serialize($automate, 'json', []);

        $automateJSON = json_decode($automateSerialized, true);

        return $this->json([
            'message' => 'Automate updated successfully',
            'automate' => $automateJSON,
        ]);
    }

    #[Route('/automates/{id}', name: 'automates.destroy', methods: ['DELETE'])]
    public function destroy(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $automate = $entityManager->getRepository(Automate::class)->find($id);

        if (!$automate) 
        {
            return $this->json([
                'errors' => 'No automate found for id ' . $id,
            ], 404);
        }

        $entityManager->remove($automate);
        $entityManager->flush();

        $automateSerialized = $serializer->serialize($automate, 'json', []);

        $automateJSON = json_decode($automateSerialized, true);

        return $this->json([
            'message' => 'Automate destroyed successfully',
            'automate' => $automateJSON,
        ]);
    }
}
