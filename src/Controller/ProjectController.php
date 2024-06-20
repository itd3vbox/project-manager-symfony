<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Project;
use App\Form\ProjectStoreType;
use App\Form\ProjectUpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Filesystem\Filesystem;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'projects', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();

        $projectsSerialized = $serializer->serialize($projects, 'json', [
            'groups' => ['project:read']
        ]);

        $projectsJSON = json_decode($projectsSerialized, true);

        return $this->json([
            'message' => 'Project fetched successfully',
            'projects' => $projectsJSON,
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

        $projectSerialized = $serializer->serialize($project, 'json', [
            'groups' => ['project:read']
        ]);

        $projectJSON = json_decode($projectSerialized, true);

        return $this->json([
            'message' => 'Project fetched successfully',
            'project' => $projectJSON,
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
        $date = new \DateTimeImmutable;
        $project->setCreatedAt($date);
        $project->setUpdatedAt($date);

        $data = [];
        $files = $request->files->all();

        if ($request->headers->get('Content-Type') === 'application/json') 
            $data = json_decode($request->getContent(), true);
        else 
            $data = $request->request->all();
        
        foreach ($files as $key => $file)
            $data[$key] = $file;

        //dd($data);
        
        $form = $formFactory->create(ProjectStoreType::class, $project);

        $form->submit($data);

        if ($form->isValid()) 
        {
            $entityManager->persist($project);

            $entityManager->flush();

            // Create folder
            $folderName = 'projects/' . $project->getId() . $date->format('YmdHis');
            $publicPath = $this->getParameter('kernel.project_dir') . '/public/' . $folderName;

            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0777, true);
            }

            $project->setFolderPath($folderName);

            if (isset($data['image']) && $data['image'] instanceof UploadedFile)
            {
                //dd('test');
                $ext = $data['image']->guessExtension();
                $image_main = $folderName . '/image-main.' . $ext;
                $data['image']->move($publicPath, 'image-main.' . $ext);
                $project->setImageMain($image_main);
            }

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

        $data = [];
        $files = $request->files->all();

        if ($request->headers->get('Content-Type') === 'application/json') 
            $data = json_decode($request->getContent(), true);
        else
            $data = $request->request->all();
        
        foreach ($files as $key => $file)
            $data[$key] = $file;

        $date = new \DateTimeImmutable;
        $project->setUpdatedAt($date);

        $form = $formFactory->create(ProjectUpdateType::class, $project);

        $form->submit($data);

        if ($form->isValid()) 
        {        
            $entityManager->flush();

            // Create folder
            $folderName = 'projects/' . $project->getId() . $date->format('YmdHis');
            $publicPath = $this->getParameter('kernel.project_dir') . '/public/' . $folderName;

            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0777, true);
            }

            $project->setFolderPath($folderName);

            if (isset($data['image']) && $data['image'] instanceof UploadedFile)
            {
                //dd('test');
                $ext = $data['image']->guessExtension();
                $image_main = $folderName . '/image-main.' . $ext;
                $data['image']->move($publicPath, 'image-main.' . $ext);
                $project->setImageMain($image_main);
            }

            $entityManager->persist($project);
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
        Filesystem $filesystem,
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

        $folderPath = $project->getFolderPath();
        if ($folderPath) {
            $publicPath = $this->getParameter('kernel.project_dir') . '/public/' . $folderPath;
            if ($filesystem->exists($publicPath)) {
                $filesystem->remove($publicPath);
            }
        }

        $entityManager->remove($project);
        $entityManager->flush();

        $serializedProjects = $serializer->normalize($project, null, [
            'ignored_attributes' => ['automates'],
        ]);

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
