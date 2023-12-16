<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Project;
use App\Form\ProjectStoreType;
use App\Form\ProjectUpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'projects', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();

        $projectsSerialized = $serializer->normalize($projects, null,);

        return $this->json([
            'message' => 'Project fetched successfully',
            'projects' => $projectsSerialized,
        ]);
    }

    #[Route('/projects/{id}', name: 'projects.show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) 
        {
            return $this->json([
                'errors' => 'No project found for id ' . $id,
            ], 404);
        }

        $projectSerialized = $serializer->normalize($project, null,);

        return $this->json([
            'message' => 'Project fetched successfully',
            'project' => $projectSerialized,
        ]);
    }

    #[Route('/projects', name: 'projects.store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $project = new Project();
        $project->setStatus(0);
        $project->setCreatedAt(new \DateTimeImmutable);
        $project->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(ProjectStoreType::class, $project);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {
            $entityManager->persist($project);

            $entityManager->flush();

            $projectSerialized = $serializer->normalize($project, null,);
    
            return $this->json([
                'message' => 'Project stored successfully',
                'project' => $projectSerialized,
            ]);
        }

        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/projects/{id}', name: 'projects.update', methods: ['PUT'])]
    public function update(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {        
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) 
        {
            return $this->json([
                'errors' => 'No project found for id ' . $id,
            ], 404);
        }

        $project->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(ProjectUpdateType::class, $project);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {        
            $entityManager->flush();

            $projectSerialized = $serializer->normalize($project, null,);
    
            return $this->json([
                'message' => 'Project updated successfully',
                'project' => $projectSerialized,
            ]);
        }
 
        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/projects/{id}', name: 'projects.destroy', methods: ['DELETE'])]
    public function destroy(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) 
        {
            return $this->json([
                'errors' => 'No project found for id ' . $id,
            ], 404);
        }

        $entityManager->remove($project);
        $entityManager->flush();

        $serializedProjects = $serializer->normalize($project, null,);

        return $this->json([
            'message' => 'Project destroyed successfully',
            'project' => $serializedProjects,
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
