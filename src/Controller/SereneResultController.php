<?php

namespace App\Controller;

use App\Controller\GPTController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\SereneResult;
use Doctrine\ORM\EntityManagerInterface;

class SereneResultController extends AbstractController
{
    #[Route('/serene/result', name: 'app_serene_result')]
    public function register(EntityManagerInterface $entityManager, Request $req): JsonResponse
    {

        

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SereneResultController.php',
        ]);

    }


}
