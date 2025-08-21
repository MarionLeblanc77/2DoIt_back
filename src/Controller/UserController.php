<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_user_')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'register', methods: "POST")]
    public function register(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        $json = $request->getContent();

        $user = $serializer->deserialize($json, User::class, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        
        if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
            return $this->json(['errors' => 'A user with this email already exists.'], JsonResponse::HTTP_CONFLICT);
        }
        $errorReadable = [];

        $password = $user->getPassword();
        if (strlen($password) < 12) {
            $errorReadable[]= 'The password must contains 12 caracters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errorReadable[]= 'The password must contains an uppercase caracter.';
        }

        if (!preg_match('/[\W]/', $password)) {
            $errorReadable[]= 'The password must contains a special caracter.';
        }
        
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);

        $errors = $validator->validate($user);

        foreach ($errors as $currentError) {
            $errorReadable[] = $currentError->getMessage();
        }
        if (count($errorReadable) > 0) {
            return $this->json(['errors' => $errorReadable], status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['success' => 'New account created'], 200);
    }

    #[Route('/login', name: 'login', methods: "POST")]
    public function login( Request $request, UserProviderInterface $userProvider, JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data['password'];

        if (!$email || !$password) {
            return $this->json(['errors' => 'Both email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User */
            $user = $userProvider->loadUserByIdentifier($email);
        } catch (UserNotFoundException $e) {
            return $this->json(['errors' => 'Email and password do not match'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['errors' => 'Email and password do not match'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {

            error_log('JWT_PASSPHRASE set: ' . (isset($_ENV['JWT_PASSPHRASE']) ? 'YES' : 'NO'));
            error_log('JWT_SECRET_KEY: ' . ($_ENV['JWT_SECRET_KEY'] ?? 'NOT SET'));

            $privateKeyPath = $this->getParameter('kernel.project_dir') . '/config/jwt/private.pem';

            // Debug: Check PHP extensions and OpenSSL
            error_log('OpenSSL loaded: ' . (extension_loaded('openssl') ? 'YES' : 'NO'));
            error_log('PHP version: ' . phpversion());
            


            // Test if we can read the private key content
            $privateKeyContent = file_get_contents($privateKeyPath);
            error_log('Private key content length: ' . strlen($privateKeyContent));
            error_log('Private key starts with: ' . substr($privateKeyContent, 0, 50));
                
            // Test OpenSSL directly
            $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath), $_ENV['JWT_PASSPHRASE'] ?? '');
            if (!$privateKey) {
                error_log('OpenSSL Error: ' . openssl_error_string());
                return $this->json(['errors' => 'OpenSSL key error'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
            error_log('OpenSSL key loaded successfully');

            $token = $jwtManager->create($user);
            
            error_log('Token created: ' . ($token ? 'YES' : 'NO'));
            error_log('Token length: ' . strlen($token ?? ''));
            
            if (empty($token)) {
                error_log('JWT token is empty!');
                return $this->json(['errors' => 'Failed to create authentication token'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        
        } catch (\Exception $e) {
            error_log('JWT Error: ' . $e->getMessage());
            error_log('JWT Class: ' . get_class($e));
            return $this->json(['errors' => 'Authentication system error: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }    
        
        return $this->json(['user' => $user, 'token' => $token], 200, context: ["groups" => ["user_read"]]);
    }

    #[Route('/users', name: 'browse', methods: "GET")]
    public function browse(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json($users, Response::HTTP_OK, [], ["groups" => ["user_read"]]);
    }

    #[Route('/user', name: 'edit', methods: "PUT")]
    public function edit(
        EntityManagerInterface $em, 
        Request $request, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage) : JsonResponse
    {
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();  
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

    #[Route('/user', name: 'delete', methods: "DELETE")]
    public function delete(EntityManagerInterface $em, TokenStorageInterface $tokenStorage): JsonResponse
    {     
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();          
        $em->remove($user);
        $em->flush();
        
        return $this->json(['success' => 'User deleted successfully.'], 200);
    }

    #[Route('/user/contacts/', name: 'contacts_browse', methods: "GET")]
    public function browseUserContact(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        /** @var User */
        $user = $token->getUser();
        $contacts = $user->getUsers();
        return $this->json($contacts, Response::HTTP_OK, [], ["groups" => ["user_contacts"]]);
    }

    #[Route('/user/{email}/contact', name: 'contacts_add', methods: "POST")]
    public function addContact(
        string $email,
        UserRepository $userRepository, 
        EntityManagerInterface $em, 
        TokenStorageInterface $tokenStorage): JsonResponse
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['errors' => 'Invalid email format'], Response::HTTP_BAD_REQUEST);
        }
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['errors' => 'Contact not found'], 404);
        }
        $token = $tokenStorage->getToken();
        /** @var User */
        $activeUser = $token->getUser();

        if ($activeUser->getUsers()->contains($user)) {
            return $this->json(['errors' => 'Contact already added'], Response::HTTP_CONFLICT);
        }
        $activeUser->addUser($user);
        $em->persist($activeUser);
        $em->flush();

        return $this->json(['success' => "User's contact added successfully.", 'userContacts'=> $activeUser->getUsers()], Response::HTTP_OK, [], ["groups" => ["user_contacts"]]);
    }

    #[Route('/user/{id<\d+>}/contacts', name: 'contacts_delete', methods: "DELETE")]
    public function deleteContact(
        User $user, 
        EntityManagerInterface $em, 
        TokenStorageInterface $tokenStorage): JsonResponse
    {
        if (!$user) {
            return $this->json(['errors' => 'User not found'], 404);
        }
        $token = $tokenStorage->getToken();
        /** @var User */
        $activeUser = $token->getUser();
        $activeUser->removeUser($user);
        $em->flush();

        return $this->json(['success' => "User's contact deleted successfully.", 'usersContact'=> $activeUser->getUsers()], Response::HTTP_OK, [], ["groups" => ["user_contacts"]]);
    }


}
