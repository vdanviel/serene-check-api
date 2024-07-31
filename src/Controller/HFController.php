<?php

namespace App\Controller;

use App\Controller\FileController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HFController extends AbstractController
{

    private static $envoriment;

    public function __construct(){

        $env_path = str_replace('\src\Controller', '', __DIR__);
        $env_path = str_replace('\\', '/', $env_path) . '/.env';
    
        $env_data = FileController::loadEnvFile($env_path);

        self::$envoriment = $env_data;

    }

    //https://huggingface.co/mistralai/Mistral-7B-Instruct-v0.3
    public function generateResult(string $system_content, string $user_content): string
    {

        $curl = curl_init();

        
        $payload = [
            "model" => "mistralai/Mistral-7B-Instruct-v0.3",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $system_content . " | " . $user_content
                ]
            ],
            "max_tokens" => 500,
            "stream" => false
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => self::$envoriment['HF_CHAT_MODEL_ENDPOINT'], 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer " . self::$envoriment['HF_API_KEY']
            ],
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($curl);
    
        if ($error = curl_error($curl)) {
            curl_close($curl);
            return json_encode(['error' => $error]);
        }

        curl_close($curl);
    
        // Decode and log the raw response for debugging
        $response_data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_encode(['error' => 'JSON decode error: ' . json_last_error_msg(), 'response' => $response]);
        }
    
        return json_encode($response_data);
    }

    //https://huggingface.co/deepset/roberta-base-squad2
    public function checkResult(string $text): string
    {

        $curl = curl_init();

        $payload = [
            "inputs" => [
                "question" => "Do I have ansiety or not?",
                "context" => $text 
            ]
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => self::$envoriment['HF_QUESTION_ANSWERING_MODEL_ENDPOINT'], 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer " . self::$envoriment['HF_API_KEY']
            ],
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($curl);
    
        if ($error = curl_error($curl)) {
            curl_close($curl);
            return json_encode(['error' => $error]);
        }

        curl_close($curl);
    
        // Decode and log the raw response for debugging
        $response_data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_encode(['error' => 'JSON decode error: ' . json_last_error_msg(), 'response' => $response]);
        }
    
        return json_encode($response_data);
    }
    
}
