<?php

namespace App\Controller;

////php -S 127.0.0.1:8000 -t public - para iniciar inicia pela pasta de produção: "public"

//https://symfony.com/doc/current/components/http_foundation.html#request
use Symfony\Component\HttpFoundation\Request;

//https://symfony.com/doc/current/components/http_foundation.html#response
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//https://symfony.com/doc/current/components/http_foundation.html#creating-a-json-response
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\UserForm;
use App\Entity\User;

//https://symfony.com/doc/current/routing.html#creating-routes
use Symfony\Component\Routing\Attribute\Route;

//https://symfony.com/doc/current/doctrine.html#fetching-objects-from-the-database
use Doctrine\ORM\EntityManagerInterface;

class UserFormController extends AbstractController
{
    #[Route('/form/register', name: 'user_form_register' , methods: ['POST'])]
    public function register(EntityManagerInterface $entityManager, Request $req): JsonResponse
    {

        $content = $req->getContent();

        $data = !empty($content) ? $req->toArray() : [];

        if (!isset($data['question'], $data['answer'], $data['user_id']) || empty($data['question']) || empty($data['answer']) || empty($data['user_id'])) {
            return $this->json([
                'status' => false,
                'message' => 'The `question`, `answer`, `user_id` fields are required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        //verifica se usuario já existe..
        $user = $entityManager->getRepository(User::class)->find(intval($data['user_id']));

        if (!$user) {
            return $this->json([
                "status" => false,
                "message" => "User not found."
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user_form = new UserForm();
        $user_form->setQuestion($data['question']);
        $user_form->setAnswer($data['answer']);
        $user_form->setUserId($user);

        // tell Doctrine you want to (eventually) save the user_form (no queries yet)
        $entityManager->persist($user_form);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->json([
            'status' => true,
            'message' => 'Form was saved!',
        ]);
    }

    #[Route('/form/all', name: 'users_answers' , methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserFormController.php',
        ]);
    }
}
