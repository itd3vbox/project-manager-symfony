<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Test;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class TestController extends AbstractController
{
    #[Route('/tests', name: 'tests', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $tests = $entityManager->getRepository(Test::class)->findAll();

        $testsSerialized = $serializer->serialize($tests, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $testsJSON = json_decode($testsSerialized, true);

        return $this->json([
            'message' => 'Test fetched successfully',
            'tests' => $testsJSON,
        ]);
    }

    #[Route('/tests/{id}', name: 'tests.show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $test = $entityManager->getRepository(Test::class)->find($id);

        if (!$test) 
        {
            return $this->json([
                'errors' => 'No test found for id ' . $id,
            ], 404);
        }

        $testSerialized = $serializer->serialize($test, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $testJSON = json_decode($testSerialized, true);

        return $this->json([
            'message' => 'Test fetched successfully',
            'test' => $testJSON,
        ]);
    }

    #[Route('/tests', name: 'tests.store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $test = new Test();

        $test->setTitle($data['title']);
        $test->setStatus(0);

        $project = $entityManager->getRepository(Project::class)
            ->find($data['project_id']);
        if (!$project) 
        {
            return $this->json([
                'error' => 'Project not found'
            ], 404);
        }
        $test->setProject($project);

        $date = new \DateTimeImmutable;
        $test->setCreatedAt($date);
        $test->setUpdatedAt($date);

        $entityManager->persist($test);
        $entityManager->flush();

        $testSerialized = $serializer->serialize($test, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $testJSON = json_decode($testSerialized, true);

        return $this->json([
            'message' => 'Test stored successfully',
            'test' => $testJSON,
        ]);
    }

    #[Route('/tests/{id}', name: 'tests.update', methods: ['PUT'])]
    public function update(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $test = $entityManager->getRepository(Test::class)->find($id);

        if (!$test) 
        {
            return $this->json([
                'errors' => 'No test found for id ' . $id,
            ], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        $test->setTitle($data['title']);
        $test->setStatus($data['status']);
        $test->setUpdatedAt(new \DateTimeImmutable);

        $entityManager->flush();

        $testSerialized = $serializer->serialize($test, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $testJSON = json_decode($testSerialized, true);

        return $this->json([
            'message' => 'Test updated successfully',
            'test' => $testJSON,
        ]);
    }

    #[Route('/tests/{id}', name: 'tests.destroy', methods: ['DELETE'])]
    public function destroy(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $test = $entityManager->getRepository(Test::class)->find($id);

        if (!$test) 
        {
            return $this->json([
                'errors' => 'No test found for id ' . $id,
            ], 404);
        }

        $entityManager->remove($test);
        $entityManager->flush();

        $testSerialized = $serializer->serialize($test, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $testJSON = json_decode($testSerialized, true);

        return $this->json([
            'message' => 'Test destroyed successfully',
            'test' => $testJSON,
        ]);
    }
}
