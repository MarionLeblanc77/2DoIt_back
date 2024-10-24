<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_category')]
class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'browse', methods: "GET")]
    public function browse(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->json($categories, Response::HTTP_OK);
    }

    #[Route('/category', name: 'add', methods: "POST")]
    public function add(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator) :JsonResponse 
    {
        $json = $request->getContent();
        $category = $serializer->deserialize(data: $json, type: Category::class, format: 'json');
        
        $category->setCreatedAt(new DateTimeImmutable());
        
        $errorReadable = [];
        $errors = $validator->validate($category);
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }

        if (count($errorReadable) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($category);
        $em->flush();

        return $this->json(['success' => 'Category added successfully.'], 200);
    }

    #[Route('/category/{id}', name: 'edit', methods: "PUT")]
    public function edit(
        int $id,
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository) : JsonResponse
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }

        $updatedCategory = $serializer->deserialize($request->getContent(), type: Category::class, format: 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $category]);

        $updatedCategory->setUpdatedAt(new DateTimeImmutable());

        $errors = $validator->validate($updatedCategory);

        $errorReadable = [];
        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }
        if (count($errors) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($updatedCategory);
        $em->flush();

        return $this->json(['success' => 'Category modified successfully.'], 200);
    }

    #[Route('/category/{id}', name: 'delete', methods: "DELETE")]
    public function delete(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($categoryRepository->find($id));
        $em->flush();
        
        return $this->json(['success' => 'Category deleted successfully.'], JsonResponse::HTTP_OK);
    }
}
