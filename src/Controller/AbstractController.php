<?php

namespace App\Controller;

use App\App\App;
use App\Message\Message;


abstract class AbstractController
{

    static public function autorisation(array $autorisation_requis) : bool
    {
        if(!isset($_SESSION['user'])){
            return false;
        }

//      Récupère les autorisation de l'utilisateur
        $autorisation_user = $_SESSION['user']['autorisation'];

        $test_autorisation = array_intersect($autorisation_requis, $autorisation_user);

        if(empty($test_autorisation)){
            return false;
        }
        return true;
    }

    // Rendu de template twig
    static protected function twig(string $template, array $arguments = [])
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates/');
        $twig = new \Twig_Environment($loader, []);

        $app = [
            'SESSION_USER' => $_SESSION['user'] ?? [],
            'MESSAGES_ERREUR' => Message::show(),
            '_FRONT' => App::URL,
            'SESSION' => $_SESSION
        ];

        $arguments = array_merge($arguments, $app);

        $twig->display($template, $arguments);
    }

    // Rendu de template PHP

    static protected function templatePhp(string $template, array $arguments = [])
    {
        //Chemin vers le fichier
        $file = __DIR__ . '/../../templates/' . $template;

        extract($arguments);

        include $file;

    }

    static protected function json(array $json){
        header('content-type: application/json');
        echo json_encode($json);
    }
}