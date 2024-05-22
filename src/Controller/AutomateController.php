<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Automate;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Process\Process;

class AutomateController extends AbstractController
{
    #[Route('/automates', name: 'automates', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $automates = $entityManager->getRepository(Automate::class)->findAll();

        $automatesSerialized = $serializer->serialize($automates, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

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

        $automate->setName($data['name']);
        $automate->setType($data['type']);
        $automate->setDescriptionShort($data['description_short']);
        $automate->setDescription($data['description']);
        $automate->setCommand($data['command']);
        $automate->setStatus(0);

        $project = $entityManager->getRepository(Project::class)
            ->find($data['project_id']);
        if (!$project) 
        {
            return $this->json([
                'error' => 'Project not found'
            ], 404);
        }
        $automate->setProject($project);
        
        $date = new \DateTimeImmutable;
        $automate->setCreatedAt($date);
        $automate->setUpdatedAt($date);

        $entityManager->persist($automate);
        $entityManager->flush();

        // Create folder
        $folderName = 'automates/' . $automate->getId() . $date->format('YmdHis');
        $publicPath = $this->getParameter('kernel.project_dir') . '/public/' . $folderName;

        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $automate->setFolderPath($folderName);

        $entityManager->persist($automate);
        $entityManager->flush();

        $automateSerialized = $serializer->serialize($automate, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

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
        
        $automate->setName($data['name']);
        $automate->setCommand($data['type']);
        $automate->setDescriptionShort($data['description_short']);
        $automate->setDescription($data['description']);
        $automate->setCommand($data['command']);
        $automate->setStatus($data['status']);
        $automate->setUpdatedAt(new \DateTimeImmutable);

        $entityManager->flush();

        $automateSerialized = $serializer->serialize($automate, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

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

        // Define the folder path
        $folderPath = $this->getParameter('kernel.project_dir') . '/public/automates/' . $id;

        // Verify that the folder path is specific and valid
        if (strpos(realpath($folderPath), realpath($this->getParameter('kernel.project_dir') . '/public/automates')) === 0) {
            if (is_dir($folderPath)) {
                $this->deleteDirectory($folderPath, $logger);
            }
        } 
        else 
        {
            $logger->error("Invalid folder path: " . $folderPath);
            return $this->json([
                'errors' => 'Invalid folder path'
            ], 400);
        }

        $entityManager->remove($automate);
        $entityManager->flush();

        $automateSerialized = $serializer->serialize($automate, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $automateJSON = json_decode($automateSerialized, true);

        return $this->json([
            'message' => 'Automate destroyed successfully',
            'automate' => $automateJSON,
        ]);
    }

    #[Route('/automates/{id}/run', name: 'automates.run', methods: ['GET'])]
    public function run(
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

        $process = new Process([$automate->getCommand()]);
        $process->run();

        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();

        $logDirectory = $this->getParameter('kernel.project_dir') . '/public/' . $automate->getFolderPath();
        $logFilePath = $logDirectory . '/execution_' . date('YmdHis') . '.log';

        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        file_put_contents($logFilePath, "Output:\n" . $output . "\nError Output:\n" . $errorOutput);
       
        $automateSerialized = $serializer->serialize($automate, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['project'],
        ]);

        $automateJSON = json_decode($automateSerialized, true);

        return $this->json([
            'message' => 'Automate updated successfully',
            'automate' => $automateJSON,
        ]);
    }

    // --

    private function deleteDirectory($dir, LoggerInterface $logger)
    {
        if (!is_dir($dir)) {
            return;
        }

        // Log the directory to be deleted
        $logger->info("Attempting to delete directory: " . $dir);

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->deleteDirectory($path, $logger);
            } else {
                // Log each file to be deleted
                $logger->info("Deleting file: " . $path);
                unlink($path);
            }
        }
        // Log the removal of the directory
        $logger->info("Removing directory: " . $dir);
        rmdir($dir);
    }
}
