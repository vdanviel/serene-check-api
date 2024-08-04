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
use Exception;

class SereneResultController extends AbstractController
{

    #[Route('/serene/result', name: 'serene_generate', methods: ['POST'])]
    public function generateInteraction(EntityManagerInterface $entityManager,  UserFormController $userFormController, Request $req): JsonResponse
    {

        try {
            
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

            foreach ($data['dialog'] as $index => $item) {

                if (!is_array($item)) {
                    return $this->json([
                        'status' => false,
                        'message' => 'Each item in the `dialog` field must be a valid JSON object.'
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            
                if (!isset($item['question']) || !isset($item['answer'])) {
                    return $this->json([
                        'status' => false,
                        'message' => 'Each item in the `dialog` field must contain both `question` and `answer` fields.'
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            
                if (empty($item['question']) || empty($item['answer'])) {
                    return $this->json([
                        'status' => false,
                        'message' => 'Neither `question` nor `answer` fields can be empty.'
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
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

            $tittle_answer = json_decode($tittle, true)['answer'];

            if (isset(json_decode($tittle, true)['error'])) {
                
                return $this->json([
                    $tittle_answer
                ], 400);

            }

            //salvar interação com IA no banco de dados...
            $serene = new SereneResult();

            $serene->setContent(json_encode($data['dialog']));//para salvar as perguntas e respostas no banco de dados em SereneResult vamos salvar como json..
            $serene->setAiAnswer(json_encode($ai_answer));
            $serene->setUserId($user);
            $serene->setDiagnostic($tittle_answer);
            $serene->setResult($result_answer == "1" ? true : false);
        
            // Tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($serene);
        
            // Actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
        
            return $this->json([
                'status' => true,
                'description' => $ai_answer,
                'diagnostic' => $tittle_answer,
                'result' => $result_answer == "1" ? true : false
            ], 200);

        } catch (\Throwable | Exception | \JsonException $th) {
            
            return $this->json([
                'status' => false,
                'error' => [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTrace(),
                    'line' => $th->getLine()
                ]
            ], 200);

        }


    }
    
    #[Route('serene/questions', name: 'serene_generate_questuons', methods: ['GET'])]
    public function generateQuestions(Request $request): JsonResponse
    {

        try {
            
            $len = $request->query->getInt('len',5);

            $list = [
                "Do you often feel nervous or restless?",
                "Do you have difficulty relaxing?",
                "Do you worry excessively about different aspects of your life?",
                "Do you have difficulty controlling your worries?",
                "Do you feel tired or drained of energy easily?",
                "Do you have difficulty concentrating on daily tasks?",
                "Do you often feel irritable or impatient?",
                "Do you have difficulty sleeping or feel dissatisfied with the quality of your sleep?",
                "Do you have panic attacks or episodes of intense fear?",
                "Do you avoid social situations for fear of being judged?",
                "Do you experience muscle pain or constant tension?",
                "Do you have stomach or digestion problems related to anxiety?",
                "Do you often feel overwhelmed?",
                "Do you have recurring negative thoughts?",
                "Do you feel afraid of losing control or 'going crazy'?",
                "Do you have excessive sweating or sweaty hands?",
                "Do you experience shortness of breath or difficulty breathing?",
                "Do you have palpitations or a rapid heartbeat?",
                "Do you feel dizzy or faint?",
                "Do you have a lump in your throat or difficulty swallowing?",
                "Do you feel isolated or disconnected from others?",
                "Do you avoid situations or activities that may cause anxiety?",
                "Do you feel nausea or frequent abdominal discomfort?",
                "Do you feel a constant need for reassurance?",
                "Do you have thoughts that something bad is going to happen?",
                "Do you have difficulty controlling your anxious thoughts?",
                "Do you feel a need for perfection or excessive control?",
                "Do you worry about your health or fear illness?",
                "Do you fear public speaking or being the center of attention?",
                "Do you feel a need to avoid conflict at all costs?",
                "Do you have difficulty making decisions due to worry?",
                "Do you feel a need to check things repeatedly?",
                "Do you fear being alone or feel insecure without company?",
                "Do you feel a constant desire to please others?",
                "Do you have memory problems or difficulty remembering details?",
                "Do you feel a need to plan or organize excessively?",
                "Are you afraid of failing or not being good enough?",
                "Do you worry about what others think of you?",
                "Do you feel a need to be perfect in everything you do?",
                "Are you afraid of making mistakes or being criticized?",
                "Do you feel a constant need to control your environment?",
                "Do you worry about the future or the unknown?",
                "Do you feel a need to avoid responsibility?",
                "Do you have difficulty trusting others?",
                "Do you feel a need to prepare for all eventualities?",
                "Do you feel a need to avoid new or unfamiliar situations?",
                "Do you often feel tired or exhausted for no apparent reason?",
                "Do you have difficulty enjoying activities you used to enjoy?",
                "Do you often feel tense or on edge?",
                "Do you have difficulty controlling your impulses?",
                "Do you feel a need to avoid crowds or crowded places?",
                "Do you worry about losing control in public?",
                "Do you fear driving or using public transportation?",
                "Do you feel a need to escape or avoid stressful situations?",
                "Do you fear rejection or abandonment?",
                "Do you worry about the safety of your loved ones?",
                "Do you feel a need to be approved by everyone?",
                "Do you have difficulty dealing with change or unexpected events?",
                "Do you feel a need to keep everything clean and organized?",
                "Do you worry about your appearance or what others think of your appearance?",
                "Do you fear making important decisions?",
                "Do you feel a need to prepare for the worst?",
                "Do you fear being judged or criticized by others?",
                "Do you feel a need to avoid responsibilities or commitments?",
                "Do you worry about losing your job or yoursource of income?",
                "Do you fear being left out or forgotten?",
                "Do you feel a need to keep the peace at all costs?",
                "Do you worry about being misunderstood or misinterpreted?",
                "Do you fear being deceived or betrayed?",
                "Do you feel a need to make sure everything is under control?",
                "Do you worry about making serious mistakes?",
                "Do you fear being seen as incompetent or incapable?",
                "Do you feel a need to avoid situations where you might fail?",
                "Do you worry about losing something important?",
                "Do you fear not being able to handle difficult situations?",
                "Do you feel a need to avoid conflict or arguments?",
                "Do you worry about being exposed or humiliated?",
                "Do you fear being abandoned by friends or family?",
                "Do you feel a need to avoid situations where you might be rejected?",
                "Do you worry about being forgotten or neglected?",
                "Do you fear not being able to handle responsibilities?",
                "Do you feel a need to make sure everything is perfect?",
                "Do you worry about losing emotional control?",
                "Do you fear being seen as weak or vulnerable?",
                "Do you feel a need to avoid situations where you might be criticized?",
                "Do you worry about not being able to meet expectations?",
                "Do you fear being left out or ignored?",
                "Do you feel a need to maintain the appearance of success?",
                "Do you worry about being misunderstood?",
                "Do you fear being rejected for being different?",
                "Do you feel a need to avoid situations where you might be ridiculed?",
                "Do you worry about not being able to achieve your goals?",
                "Do you fear being seen as inadequate or incapable?",
                "Do you feel a need to make sure everything is under control?",
                "Do you worry about being misjudged?",
                "Do you fear being left behind or forgotten?"
            ];

            $random = array_rand($list, $len);

            $questions = [];
            foreach ($random as $value) {
                
                array_push($questions, $list[$value]);

            }

            return $this->json($questions);

        } catch (\Throwable | Exception | \JsonException $th) {
            return $this->json([
                'status' => false,
                'error' => [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTrace(),
                    'line' => $th->getLine()
                ]
            ], 200);
        }
        

    }

    #[Route('/serene/diags/all', name: 'serene_user_diags', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {

        try {
            
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

        } catch (\Throwable | Exception | \JsonException $th) {
           
            return $this->json([
                'status' => false,
                'error' => [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTrace(),
                    'line' => $th->getLine()
                ]
            ], 200);

        }

    }

    #[Route('/serene/diags/find', name: 'serene_diag', methods: ['GET'])]
    public function find(Request $request, EntityManagerInterface $entityManager): Response
    {

        try {
            
            $id = $request->query->getString('id');

            if ( $id === "") {
                return $this->json([
                    'status' => false,
                    'message' => 'Please send the `id` query.'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $dialog = $entityManager->getRepository(SereneResult::class)->getDialog(intval($id));

            if (empty($dialog)) {
                return $this->json([
                    "status" => false,
                    "message" => "This dialog doesn't exist."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            return $this->json($dialog);

        } catch (\Throwable | Exception | \JsonException $th) {
           
            return $this->json([
                'status' => false,
                'error' => [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTrace(),
                    'line' => $th->getLine()
                ]
            ], 200);

        }

    }
}
