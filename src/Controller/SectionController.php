<?php

namespace App\Controller;

use App\Entity\Section;
use App\Entity\User;
use App\Repository\SectionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_section')]
class SectionController extends AbstractController
{
    #[Route('/sections', name: 'browse', methods: "GET")]
    public function browse(SectionRepository $sectionRepository): Response
    {
        $sections = $sectionRepository->findAll();
        return $this->json($sections, Response::HTTP_OK, [], ["groups" => ["section_read"]]);
    }

    #[Route('/section/{id<\d+>}', name: 'read', methods: "GET")]
    public function findOne(Section $section): Response
    {
        return $this->json($section, Response::HTTP_OK, [], ["groups" => ["section_read"]]);
    }

    #[Route('/usersections', name: 'user_browse', methods: "GET")]
    public function browseUser(TokenStorageInterface $tokenStorage, SectionRepository $sectionRepository): Response
    {
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();
        // TODO: Check which one is faster in time and number of queries to database
        $sections = $sectionRepository->findBy(['user' => $user]);
        // $sections = $sectionRepository->findByUser($user->getId());
        return $this->json($sections, Response::HTTP_OK, [], ["groups" => ["user_section_read"]]);
    }

    #[Route('/section', name: 'add', methods: "POST")]
    public function add(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        TokenStorageInterface $tokenStorage, 
        ValidatorInterface $validator) :JsonResponse 
    {
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();

        $json = $request->getContent();
        $section = $serializer->deserialize(data: $json, type: Section::class, format: 'json');
        $section->setUser($user);
                
        $errorReadable = [];
        $errors = $validator->validate($section);
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }

        if (count($errorReadable) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($section);
        $em->flush();

        return $this->json(['success' => 'Section added successfully.', 'section' => $section], JsonResponse::HTTP_OK, [], ["groups" => ["section_read"]]);
    }

    #[Route('/section/{id<\d+>}', name: 'edit', methods: "PUT")]
    public function edit(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        Section $section) : JsonResponse
    {
        $updatedSection = $serializer->deserialize($request->getContent(), type: Section::class, format: 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $section]);

        $updatedSection->setUpdatedAt(new DateTimeImmutable());

        $errors = $validator->validate($updatedSection);

        $errorReadable = [];
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }
        if (count($errors) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($updatedSection);
        $em->flush();

        return $this->json(['success' => 'Section modified successfully.', 'section' => $section], JsonResponse::HTTP_OK, [], ["groups" => ["section_read"]]);
    }

    #[Route('/section/{id<\d+>}', name: 'delete', methods: "DELETE")]
    public function delete(
        Section $section, 
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage): JsonResponse
    {
        foreach ($section->getTasks() as $task) {
            if ($task->getUsers()->count() > 1) {
                try{
                $token = $tokenStorage->getToken();
                /** @var User */
                $user = $token->getUser();
                $user->removeTask($task);
                $task->setActive(false);
                $em->flush();
                } catch (\Exception $e) {
                    return $this->json(['error' => 'Error :'.$e], Response::HTTP_NOT_FOUND);
                }
            } else {
                $em->remove($task);
                $em->flush();
            }
        }
        $em->remove($section);
        $em->flush();
        
        return $this->json(['success' => 'Section deleted successfully.'], JsonResponse::HTTP_OK);
    }
}
