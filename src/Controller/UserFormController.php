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

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function register(string $question, string $answer, int $user): JsonResponse
    {

        //verifica se usuario já existe..
        $user = $this->entityManager->getRepository(User::class)->find($user);

        $user_form = new UserForm();
        $user_form->setQuestion($question);
        $user_form->setAnswer($answer);
        $user_form->setUserId($user);

        // tell Doctrine you want to (eventually) save the user_form (no queries yet)
        $this->entityManager->persist($user_form);

        // actually executes the queries (i.e. the INSERT query)
        $this->entityManager->flush();

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
