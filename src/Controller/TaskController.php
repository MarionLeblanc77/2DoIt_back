<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
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

#[Route('/api', name: 'api_task_')]
class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'browse', methods: "GET")]
    public function browse(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        return $this->json($tasks, Response::HTTP_OK);
    }

    #[Route('/task', name: 'add', methods: "POST")]
    public function add(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator) :JsonResponse 
    {
        $json = $request->getContent();
        $task = $serializer->deserialize(data: $json, type: Task::class, format: 'json');
        
        $task->setCreatedAt(new DateTimeImmutable());
        
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

        return $this->json(['success' => 'Item added successfully.'], 200);
    }

    #[Route('/task/{id}', name: 'edit', methods: "PUT")]
    public function edit(
        int $id,
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        TaskRepository $taskRepository) : JsonResponse
    {
        $task = $taskRepository->find($id);

        $updatedTask = $serializer->deserialize($request->getContent(), type: Task::class, format: 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $task]);

        $updatedTask->setUpdatedAt(new DateTimeImmutable());

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

    #[Route('/task/{id}', name: 'delete', methods: "DELETE")]
    public function delete(int $id, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($taskRepository->find($id));
        $em->flush();
        
        return $this->json(['success' => 'Task deleted successfully.'], JsonResponse::HTTP_OK);
    }
}