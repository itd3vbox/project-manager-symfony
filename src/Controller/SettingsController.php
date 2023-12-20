<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserUpdateUsernameType;
use App\Form\UserUpdateEmailType;
use App\Form\UserUpdatePasswordType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        $usersSerialized = $serializer->normalize($users, null,);

        return $this->json([
            'message' => 'User fetched successfully',
            'users' => $usersSerialized,
        ]);
    }

    #[Route('/settings/{id}/username', name: 'settings.update.username', methods: ['PUT'])]
    public function updateUsername(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) 
        {
            return $this->json([
                'errors' => 'No user found for id ' . $id,
            ], 404);
        }

        $user->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(UserUpdateUsernameType::class, $user);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {        
            $entityManager->flush();

            $userSerialized = $serializer->normalize($user, null,);
    
            return $this->json([
                'message' => 'User updated successfully',
                'user' => $userSerialized,
            ]);
        }
 
        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/settings/{id}/email', name: 'settings.update.email', methods: ['PUT'])]
    public function updateEmail(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        int $id,
    ): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) 
        {
            return $this->json([
                'errors' => 'No user found for id ' . $id,
            ], 404);
        }

        $user->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(UserUpdateEmailType::class, $user);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {        
            $entityManager->flush();

            $userSerialized = $serializer->normalize($user, null,);
    
            return $this->json([
                'message' => 'User updated successfully',
                'user' => $userSerialized,
            ]);
        }
 
        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
    }

    #[Route('/settings/{id}/password', name: 'settings.update.password', methods: ['PUT'])]
    public function updatePassword(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        int $id,
    ): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) 
        {
            return $this->json([
                'errors' => 'No user found for id ' . $id,
            ], 404);
        }

        $user->setUpdatedAt(new \DateTimeImmutable);

        $form = $formFactory->create(UserUpdatePasswordType::class, $user);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) 
        {        
            $requestData = json_decode($request->getContent(), true);
            $encodedPassword = $passwordHasher->hashPassword($user, $requestData['password']['first']);
            $user->setPassword($encodedPassword);
        
            $entityManager->flush();

            $userSerialized = $serializer->normalize($user, null,);
    
            return $this->json([
                'message' => 'User updated successfully',
                'user' => $userSerialized,
            ]);
        }
 
        $errors = $this->getErrorsFromForm($form);

        return $this->json([
            'errors' => $this->getErrorsFromForm($form),
        ], 400);
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
