<?php

namespace App\Controller;

use App\Controller\FileController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GPTController extends AbstractController
{
    //https://platform.openai.com/docs/api-reference/chat/create
    public function generateResult(string $system_content, string $user_content): string
    {

        $env_path = str_replace('\src\Controller', '', __DIR__);
        $env_path = str_replace('\\', '/', $env_path) . '/.env';

        $env_data = FileController::loadEnvFile($env_path);

        $curl = curl_init();

        curl_setopt_array($curl, [
             CURLOPT_URL => $env_data['GPT_API_URL'],
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_CUSTOMREQUEST => "POST",
             CURLOPT_POST => true,
             CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $env_data['GPT_API_KEY']
             ],
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_POSTFIELDS => 
             '
                {
                    "model": "gpt-3.5-turbo",
                    "messages": [
                    {
                        "role": "system",
                        "content": " ' . $system_content . ' "
                    },
                    {
                        "role": "user",
                        "content": " ' . $user_content .  ' "
                    }
                    ]
                }
             '

        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
