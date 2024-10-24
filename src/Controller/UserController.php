<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_user_')]
class UserController extends AbstractController
{
    #[Route('/user', name: 'add', methods: "POST")]
    public function add(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator) :JsonResponse
    {
        $json = $request->getContent();
        $user = $serializer->deserialize(data: $json, type: User::class, format: 'json');
        
        $user->setCreatedAt(new DateTimeImmutable());
        
        $errorReadable = [];
        $errors = $validator->validate($user);
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }

        if (count($errorReadable) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['success' => 'User added successfully.'], 200);
    }

    #[Route('/user/{id}', name: 'edit', methods: "PUT")]
    public function edit(
        int $id,
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        UserRepository $userRepository) : JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$id
            );
        }

        $updatedUser = $serializer->deserialize($request->getContent(), type: User::class, format: 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

        $updatedUser->setUpdatedAt(new DateTimeImmutable());

        $errors = $validator->validate($updatedUser);

        $errorReadable = [];
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }
        if (count($errors) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($updatedUser);
        $em->flush();

        return $this->json(['success' => 'User modified successfully.'], 200);
    }

    #[Route('/user/{id}', name: 'delete', methods: "DELETE")]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($userRepository->find($id));
        $em->flush();
        
        return $this->json(['success' => 'User deleted successfully.'], JsonResponse::HTTP_OK);
    }
}
