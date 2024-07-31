<?php

namespace App\Controller;

use App\Controller\GeminiController;
use App\Controller\UserFormController;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

use App\Entity\SereneResult;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;

class SereneResultController extends AbstractController
{

    private $userFormController;

    public function __construct(UserFormController $userFormController)
    {
        $this->userFormController = $userFormController;
    }

    #[Route('/serene/result', name: 'serene_generate', methods: ['POST'])]
    public function generate(EntityManagerInterface $entityManager,  UserFormController $userFormController, Request $req): JsonResponse
    {
        $content = $req->toArray();

        $data = !empty($content) ? $req->toArray() : [];
    
        if (!isset($data['dialog'], $data['token']) || empty($data['dialog']) || empty($data['token'])) {
            return $this->json([
                'status' => false,
                'message' => 'The `dialog` and `token` fields are required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        if (!is_array($data['dialog'])) {
            return $this->json([
                'status' => false,
                'message' => 'The `dialog` field must be a valid JSON array.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        //verifica se usuario existe..
        $user = $entityManager->getRepository(User::class)->findOneBy(['token' => $data['token']]);

        if (!$user) {
            return $this->json([
                "status" => false,
                "message" => "User not found."
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        //salvar perguntas e respostas...
        foreach ($data['dialog'] as $entry) {

            if (!isset($entry['question'], $entry['answer'])) {
                return $this->json([
                    'status' => false,
                    'message' => 'Each entry in the `dialog` array must contain `question` and `answer` fields.'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $userFormController->register($entry['question'], $entry['answer'], $user->getId());
        }
    
        $gpt_client = new GeminiController();
    
        $content = "";
        foreach ($data['dialog'] as $key => $entry) {

            $key = $key + 1;

            $content .= "#$key " . $entry['question'] . " R: '" . $entry['answer'] . "' ";

        }
    
        $generated = $gpt_client->generateResult("Pretend you are a professional psychologist. Give me a FICTITIOUS diagnosis based on these questions whether I have anxiety or not even if thee chances are few. Justify why.", $content);
        
        $ia_result = json_decode($generated, true);

        //salvar interação com IA no banco de dados...
        $serene = new SereneResult();

        $serene->setContent(json_encode($data['dialog']));//para salvar as perguntas e respostas no banco de dados em SereneResult vamos salvar como json..
        $serene->setAiAnswer(json_encode($ia_result));
        $serene->setUserId($user);
    
        // Tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($serene);
    
        // Actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
    
        return $this->json([
            'status' => true,
            'diagnostic' => $ia_result['candidates'][0]['content']['parts'][0]['text']
        ]);
    }
    
    #[Route('/serene/all', name: 'serene_all', methods: ['GET'])]
    public function index(Request $request, EncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        $limit = $request->query->getInt('limit', 10);

        $dialogs = $entityManager->getRepository(SereneResult::class)->findAllWithLimit($limit);
        
        $response = [];

        foreach ($dialogs as $value) {

            $user = $entityManager->getRepository(User::class)->find($value->getUserId());

            array_push($response, ["content" => $value->getContent(), "diagnostic" => $value->getAiAnswer(), "username" => $user->getName()]);

        }

        return $this->json($response);
    }

}
