<?php

namespace App\Controller;

//https://symfony.com/doc/current/controller.html
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//https://symfony.com/doc/current/components/http_foundation.html#creating-a-json-response
use Symfony\Component\HttpFoundation\JsonResponse;

//https://symfony.com/doc/current/components/http_foundation.html#request
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;

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

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->json([
            'status' => true,
            'message' => 'User registered successfully!'
        ]);

    }

}
