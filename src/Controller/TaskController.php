<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Task;
use App\Entity\Project;
use App\Form\TaskStoreType;
use App\Form\TaskUpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'tasks', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $tasks = $entityManager->getRepository(Task::class)->findAll();

        $tasksSerialized = $serializer->serialize($tasks, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $tasksJSON = json_decode($tasksSerialized, true);

        return $this->json([
            'message' => 'Task fetched successfully',
            'tasks' => $tasksJSON,
        ]);
    }

    #[Route('/tasks/{id}', name: 'tasks.show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) 
        {
            return $this->json([
                'errors' => 'No task found for id ' . $id,
            ], 404);
        }

        $taskSerialized = $serializer->serialize($task, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $taskJSON = json_decode($taskSerialized, true);

        return $this->json([
            'message' => 'Task fetched successfully',
            'task' => $taskJSON,
        ]);
    }

    #[Route('/tasks', name: 'tasks.store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task = new Task();

        $task->setTitle($data['title']);
        $task->setDescriptionShort($data['description_short']);
        $task->setDescription($data['description']);
        $task->setStatus(0);

        $project = $entityManager->getRepository(Project::class)
            ->find($data['project_id']);
        if (!$project) 
        {
            return $this->json([
                'error' => 'Project not found'
            ], 404);
        }
        $task->setProject($project);

        $date = new \DateTimeImmutable;
        $task->setCreatedAt($date);
        $task->setUpdatedAt($date);

        $entityManager->persist($task);
        $entityManager->flush();

        $taskSerialized = $serializer->serialize($task, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $taskJSON = json_decode($taskSerialized, true);

        return $this->json([
            'message' => 'Task stored successfully',
            'task' => $taskJSON,
        ]);
    }

    #[Route('/tasks/{id}', name: 'tasks.update', methods: ['PUT'])]
    public function update(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) 
        {
            return $this->json([
                'errors' => 'No task found for id ' . $id,
            ], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        $task->setTitle($data['title']);
        $task->setDescriptionShort($data['description_short']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        $task->setUpdatedAt(new \DateTimeImmutable);

        $entityManager->flush();

        $taskSerialized = $serializer->serialize($task, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $taskJSON = json_decode($taskSerialized, true);

        return $this->json([
            'message' => 'Task updated successfully',
            'task' => $taskJSON,
        ]);
    }

    #[Route('/tasks/{id}', name: 'tasks.destroy', methods: ['DELETE'])]
    public function destroy(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) 
        {
            return $this->json([
                'errors' => 'No task found for id ' . $id,
            ], 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $taskSerialized = $serializer->serialize($task, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $taskJSON = json_decode($taskSerialized, true);

        return $this->json([
            'message' => 'Task destroyed successfully',
            'task' => $taskJSON,
        ]);
    }
}
