<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{

    public static function loadEnvFile(string $path) : array
    {

        $rows_file = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $lines = [];
        foreach ($rows_file as $row) {

            if (strpos($row,'#') === 0) {
                continue;
            }else{
                array_push($lines, $row);
            }

        }

        $variables = [];
        foreach ($lines as $var) {
            
            $key_atr = explode('=', $var);

            $variables[$key_atr[0]] = $key_atr[1];

        }

        return $variables;

    }

}
