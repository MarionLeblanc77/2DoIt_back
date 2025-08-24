<?php

namespace App\Controller;

use App\Entity\Section;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\SectionRepository;
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
    public function browse(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();
        return $this->json($tasks, Response::HTTP_OK, [], ["groups" => ["task_read"]]);
    }

    #[Route('/task/section/{id<\d+>}', name: 'add', methods: "POST")]
    public function add(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage, 
        Section $section) :JsonResponse
    {
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();

        $json = $request->getContent();
        
        $task = $serializer->deserialize(data: $json, type: Task::class, format: 'json');
        $task->addSection($section);
        $task->addUser($user);

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

        return $this->json(['success' => 'Task added successfully.', 'task'=>$task], JsonResponse::HTTP_OK, [], ["groups" => ["task_read"]]);
    }

    #[Route('/task/{id<\d+>}', name: 'edit', methods: "PUT")]
    public function edit(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        Task $task,
        TokenStorageInterface $tokenStorage, 
        UserRepository $userRepository) : JsonResponse
    {
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();
        $userId = $user->getId();

        $json = $request->getContent();
        $data = json_decode($json, true);

        $updatedTask = $serializer->deserialize($json, type: Task::class, format: 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $task]);

        $updatedTask->setUpdatedAt(new DateTimeImmutable());

        if (!isset($data['users'])) {
            $users = $task->getUsers();
            foreach ($users as $user) {
                $data['users'][] = $user->getId();
            }
        } else {
            $data['users'][] = $userId;
        }

        if (isset($data['users']) && is_array($data['users'])) {
            foreach ($data['users'] as $userId) {
                $user = $userRepository->find($userId);
                    if ($user) {
                        $updatedTask->addUser($user);
                    } else {
                    return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
                }
            }
            foreach ($task->getUsers() as $user) {
                $userId = $user->getId();
                if (!in_array($userId, $data['users'])) {
                    $updatedTask->removeUser($user);
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

        return $this->json(['success' => 'Task modified successfully.', 'task'=>$task], JsonResponse::HTTP_OK, [], ["groups" => ["task_read"]]);
    }

        #[Route('/task/{id<\d+>}/toggle', name: 'toggle_active', methods: "PUT")]
    public function toggleActive(
        EntityManagerInterface $em,  
        Task $task) : JsonResponse
    {
        $task->setActive(!$task->isActive());

        $em->persist($task);
        $em->flush();

        return $this->json(['success' => 'Active status modified successfully.', 'task'=>$task], JsonResponse::HTTP_OK, [], ["groups" => ["task_toggle_active"]]);
    }

    #[Route('/task/{id<\d+>}', name: 'delete', methods: "DELETE")]
    public function delete(
        Task $task, 
        EntityManagerInterface $em, 
        TokenStorageInterface $tokenStorage, 
        SectionRepository $sectionRepository): JsonResponse
    {
        try{
        if ($task->getUsers()->count() > 1) {
            try{
                $token = $tokenStorage->getToken();
                /** @var User */
                $user = $token->getUser();
                $toRemoveSection = $sectionRepository->findOneByTaskAndUser($task->getId(), $user->getId());
                $user->removeTask($task);
                $toRemoveSection->removeTask($task);
                $task->setActive(false);
                $em->flush();
                return $this->json(['user'=> $user, 'toremove'=> $toRemoveSection], JsonResponse::HTTP_OK, [], ["groups" => ["user_read","section_read"]]);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Error :'.$e], Response::HTTP_NOT_FOUND);
            }
        } else {
            $em->remove($task);
            $em->flush();
            return $this->json(['success' => 'Task deleted successfully.'], JsonResponse::HTTP_OK);
        }} catch (\Exception $e) {
            return $this->json(['error' => 'Error :'.$e], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/task/{task<\d+>}/user/{user<\d+>}', name: 'add_user', methods: "POST")]
    public function addUserToTask(
        Task $task, 
        User $user,
        EntityManagerInterface $em, 
        TokenStorageInterface $tokenStorage, 
        SectionRepository $sectionRepository): JsonResponse
    {

        if ($task->getUsers()->contains($user)) {
            return $this->json(['error' => 'User already has this task.'], Response::HTTP_NOT_FOUND);
        }
        $token = $tokenStorage->getToken();
        /** @var User */
        $connectedUser = $token->getUser();
        $connectedUserName = $connectedUser->getFirstName().' '.$connectedUser->getLastName();
        $userSections = $user->getSections();
        
        $shareSection = $sectionRepository->findOneByTitle('From '.$connectedUserName);
        if (!$userSections->contains($shareSection)) {
            $shareSection = new Section();
            $shareSection->setTitle('From '.$connectedUserName);
            $shareSection->setUser($user);
            $shareSection->addTask($task);
            $em->persist($shareSection);
            $em->flush();
        }

        $task->addSection($shareSection);
        $task->addUser($user);
        $em->persist($task);
        $em->flush();

        return $this->json(['success' => 'User added to task successfully.'], JsonResponse::HTTP_OK);
    }

    #[Route('/task/{task<\d+>}/user/{user<\d+>}', name: 'delete_user', methods: "DELETE")]
    public function deleteUserFromTask(Task $task, User $user, EntityManagerInterface $em, SectionRepository $sectionRepository): JsonResponse
    {
        if (!$task->getUsers()->contains($user)) {
            return $this->json(['error' => 'User not found in task.'], Response::HTTP_NOT_FOUND);
        }
        try {
        $toRemoveSection = $sectionRepository->findOneByTaskAndUser($task->getId(), $user->getId());
        $toRemoveSection->removeTask($task);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Section not found.'.$e], Response::HTTP_NOT_FOUND);
        }

        $task->removeUser($user);
        $em->flush();

        return $this->json(['success' => 'User removed from task successfully.'], JsonResponse::HTTP_OK);
    }
}