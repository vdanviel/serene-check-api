<?php

namespace App\Controller;

use App\Controller\HFController;
use App\Controller\UserFormController;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\SereneResult;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;

class SereneResultController extends AbstractController
{

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
    
        $hf_client = new HFController();
    
        $content = "";
        foreach ($data['dialog'] as $key => $entry) {

            $key = $key + 1;

            $content .= "#$key " . $entry['question'] . " A: '" . $entry['answer'] . "' ";

        }
        
        $system_sentence = "Pretend you are a professional psychologist and I will pretend I am your patient. Give me based on these questions whether I have anxiety or not even if thee chances are few. Justify why.";

        $generated = $hf_client->generateAIAnswer($system_sentence, $content);
        
        $ai_answer = json_decode($generated, true)['choices'][0]['message']['content'];

        //generating boolean result if he/she has ansiety or not..
        $result = $hf_client->hasAnsiety($ai_answer);

        $result_answer = json_decode($result, true)['choices'][0]['message']['content'];
        
        //generating diagnostic..
        $tittle = $hf_client->generateTitle($ai_answer);

        $tittle_arr = json_decode($tittle, true);

        //salvar interação com IA no banco de dados...
        $serene = new SereneResult();

        $serene->setContent(json_encode($data['dialog']));//para salvar as perguntas e respostas no banco de dados em SereneResult vamos salvar como json..
        $serene->setAiAnswer(json_encode($ai_answer));
        $serene->setUserId($user);
        $serene->setDiagnostic($tittle);
        $serene->setResult($result_answer == "1" ? true : false);
    
        // Tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($serene);
    
        // Actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
    
        return $this->json([
            'description' => $ai_answer,
            'diagnostic' => $tittle_arr['answer'],
            'result' => $result_answer == "1" ? true : false
        ]);

    }
    
    #[Route('/serene/all', name: 'serene_all', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $limit = $request->query->getString('limit', 10);
        $offset = $request->query->getString('offset', 0);

        if ($offset === "" || $limit === "") {
            return $this->json([
                'status' => false,
                'message' => 'Please send the `offset` / `limit` query.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $dialogs = $entityManager->getRepository(SereneResult::class)->listAllUserDialogs(intval($limit), intval($offset));

        return $this->json($dialogs);

    }

}
