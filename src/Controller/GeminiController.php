<?php

namespace App\Controller;

use App\Controller\FileController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GeminiController extends AbstractController
{
    //https://platform.openai.com/docs/api-reference/chat/create
    public function generateResult(string $system_content, string $user_content): string
    {

        $env_path = str_replace('\src\Controller', '', __DIR__);
        $env_path = str_replace('\\', '/', $env_path) . '/.env';
    
        $env_data = FileController::loadEnvFile($env_path);

        $curl = curl_init();
    
        $payload = [
            "contents" => [
                "parts" => [
                    "text" => $system_content . " | " . $user_content
                ]
            ]
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $env_data['GEMINI_API_KEY'], 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
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
