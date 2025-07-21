<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_task_')]
class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'browse', methods: "GET")]
    public function browse(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        return $this->json($tasks, Response::HTTP_OK, [], ["groups" => ["task_read"]]);
    }

    #[Route('/usertasks', name: 'user_browse', methods: "GET")]
    public function browseUser(TokenStorageInterface $tokenStorage): Response
    {
        $token = $tokenStorage->getToken();
        $user = $token->getUser();
        $tasks = $user->getTasks();
        return $this->json($tasks, 200);
    }

    #[Route('/task', name: 'add', methods: "POST")]
    public function add(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        UserRepository $userRepository) :JsonResponse

    {
        $json = $request->getContent();
        $data = json_decode($json, true);
        
        $task = $serializer->deserialize(data: $json, type: Task::class, format: 'json');
                
        if (isset($data['owners']) && is_array($data['owners'])) {
            foreach ($data['owners'] as $userId) {
                $user = $userRepository->find($userId);
                if ($user) {
                    $task->addUser($user);     
                    $user->addTask($task);
                    $em->persist($user);                  
                } else {
                    return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
                }
            }
        }        
        
        $errorReadable = [];
        $errors = $validator->validate($task);
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }

        if (count($errorReadable) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($task);
        $em->flush();

        return $this->json(['success' => 'Task added successfully.'], 200);
    }

    #[Route('/task/{id<\d+>}', name: 'edit', methods: "PUT")]
    public function edit(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        Task $task,
        UserRepository $userRepository) : JsonResponse
    {
        $json = $request->getContent();
        $data = json_decode($json, true);

        $updatedTask = $serializer->deserialize($request->getContent(), type: Task::class, format: 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $task]);

        $updatedTask->setUpdatedAt(new DateTimeImmutable());

        if (isset($data['users']) && is_array($data['users'])) {
            foreach ($data['users'] as $userId) {
                $user = $userRepository->find($userId);
                    if ($user) {
                        $updatedTask->addUser($user);
                        $em->persist($user);
                    } else {
                    return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
                }
            }
            foreach ($task->getUsers() as $user) {
                $userId = $user->getId();
                if (!in_array($userId, $data['users'])) {
                    $updatedTask->removeUser($user);
                    $em->persist($user);
                }
            }
        } 

        $errors = $validator->validate($updatedTask);

        $errorReadable = [];
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }
        if (count($errors) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($updatedTask);
        $em->flush();

        return $this->json(['success' => 'Task modified successfully.'], 200);
    }

    #[Route('/task/{id<\d+>}', name: 'delete', methods: "DELETE")]
    public function delete(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($task);
        $em->flush();
        
        return $this->json(['success' => 'Task deleted successfully.'], JsonResponse::HTTP_OK);
    }
}