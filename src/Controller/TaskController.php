<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Task;
use App\Form\TaskStoreType;
use App\Form\TaskUpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'tasks', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $tasks = $entityManager->getRepository(Task::class)->findAll();

        $tasksSerialized = $serializer->normalize($tasks, null,);

        return $this->json([
            'message' => 'Task fetched successfully',
            'tasks' => $tasksSerialized,
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

        $taskSerialized = $serializer->normalize($task, null,);

        return $this->json([
            'message' => 'Task fetched successfully',
            'project' => $taskSerialized,
        ]);
    }

    #[Route('/tasks', name: 'tasks.store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $task = new Task();
        $task->setStatus(0);
        $task->setCreatedAt(new \DateTimeImmutable);
        $task->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(TaskStoreType::class, $task);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {
            $entityManager->persist($task);

            $entityManager->flush();

            $taskSerialized = $serializer->normalize($task, null,);
    
            return $this->json([
                'message' => 'Task stored successfully',
                'task' => $taskSerialized,
            ]);
        }

        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/tasks/{id}', name: 'tasks.update', methods: ['PUT'])]
    public function update(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
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

        $task->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(TaskUpdateType::class, $task);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {        
            $entityManager->flush();

            $taskSerialized = $serializer->normalize($task, null,);
    
            return $this->json([
                'message' => 'Project updated successfully',
                'task' => $taskSerialized,
            ]);
        }
 
        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
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

        $taskSerialized = $serializer->normalize($task, null,);

        return $this->json([
            'message' => 'Task destroyed successfully',
            'task' => $taskSerialized,
        ]);
    }

    private function getErrorsFromForm(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true, true) as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                $childErrors = $this->getErrorsFromForm($childForm);
                $errors = array_merge($errors, $childErrors);
            }
        }

        return $errors;
    }
}
