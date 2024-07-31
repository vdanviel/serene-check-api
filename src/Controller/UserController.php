<?php

namespace App\Controller;

//https://symfony.com/doc/current/controller.html
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//https://symfony.com/doc/current/components/http_foundation.html#creating-a-json-response
use Symfony\Component\HttpFoundation\JsonResponse;

//https://symfony.com/doc/current/components/http_foundation.html#request
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use App\Entity\SereneResult;

//https://symfony.com/doc/current/doctrine.html#fetching-objects-from-the-database
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/register', name: 'save_user', methods: ['POST'])]
    public function register(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {

        $content = $request->getContent();

        $data = !empty($content) ? $request->toArray() : [];

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json([
                'status' => false,
                'message' => 'The name field is required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setToken(substr(md5(strtotime('now')), 0, 14));

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->json([
            'status' => true,
            'identifier' => $user->getToken(),
            'message' => 'User registered successfully!'
        ]);

    }

    #[Route('/user/info', name:'get_user_info', methods: ['GET'])]
    public function find(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {

        $token = $request->query->getString('token');
        $offset = $request->query->getString('offset', 0);
        $limit = $request->query->getString('limit', 10);

        if ($token === "" || $offset === "" || $limit === "") {
            return $this->json([
                'status' => false,
                'message' => 'Please send the `token` / `offset` / `limit` query.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['token' => $token]);

        if (!$user) {
            return $this->json([
                'status' => false,
                'message' => 'User not found.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $dialogs = $entityManager->getRepository(SereneResult::class)->listUserDialogs($user, $limit, $offset);

        $user_data = [
            "user" => [
                "name" => $user->getName(),
                "token" => $user->getToken(),
                "created_at" => $user->getCreatedAt()
            ],
            "interactions" => $dialogs
        ];

        return $this->json($user_data, JsonResponse::HTTP_OK);

    }

}
