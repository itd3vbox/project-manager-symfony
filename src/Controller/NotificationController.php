<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Notification;
use App\Form\NotificationStoreType;
use App\Form\NotificationUpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;


class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $notifications = $entityManager->getRepository(Notification::class)->findAll();

        $notificationsSerialized = $serializer->normalize($notifications, null,);

        return $this->json([
            'message' => 'Notification fetched successfully',
            'notifications' => $notificationsSerialized,
        ]);
    }

    #[Route('/notifications/{id}', name: 'notifications.show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $notification = $entityManager->getRepository(Notification::class)->find($id);

        if (!$notification) 
        {
            return $this->json([
                'errors' => 'No notification found for id ' . $id,
            ], 404);
        }

        $notificationSerialized = $serializer->normalize($notification, null,);

        return $this->json([
            'message' => 'Notification fetched successfully',
            'notification' => $notificationSerialized,
        ]);
    }

    #[Route('/notifications', name: 'notifications.store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $notification = new Notification();
        $notification->setStatus(0);
        $date = new \DateTimeImmutable;
        $notification->setCreatedAt($date);
        $notification->setUpdatedAt($date);

        $form = $formFactory->create(NotificationStoreType::class, $notification);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {
            $entityManager->persist($notification);

            $entityManager->flush();

            $notificationSerialized = $serializer->normalize($notification, null,);
    
            return $this->json([
                'message' => 'Notification stored successfully',
                'notification' => $notificationSerialized,
            ]);
        }

        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/notifications/{id}', name: 'notifications.update', methods: ['PUT'])]
    public function update(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $notification = $entityManager->getRepository(Notification::class)->find($id);

        if (!$notification) 
        {
            return $this->json([
                'errors' => 'No task found for id ' . $id,
            ], 404);
        }

        $notification->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(NotificationUpdateType::class, $notification);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {        
            $entityManager->flush();

            $notificationSerialized = $serializer->normalize($notification, null,);
    
            return $this->json([
                'message' => 'Notification updated successfully',
                'notification' => $notificationSerialized,
            ]);
        }
 
        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/notifications/{id}', name: 'notifications.destroy', methods: ['DELETE'])]
    public function destroy(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $notification = $entityManager->getRepository(Notification::class)->find($id);

        if (!$notification) 
        {
            return $this->json([
                'errors' => 'No notification found for id ' . $id,
            ], 404);
        }

        $entityManager->remove($notification);
        $entityManager->flush();

        $notificationSerialized = $serializer->normalize($notification, null,);

        return $this->json([
            'message' => 'Notification destroyed successfully',
            'notification' => $notificationSerialized,
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
