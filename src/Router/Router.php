<?php
namespace App\Router;

use App\App\App;

class Router
{
    static private $currentRoute;
    static private $routes;
    static private $nameRoute;

    // Récupérer la route actuelle de la page
    static private function getCurrentRoute() : void
    {
        // Extraire la partie de l'url qui indique la route à laquelle on tente d'accéder
        $queryString = $_SERVER['REDIRECT_QUERY_STRING'];
        $arguments = explode('&', $queryString);
        $route = substr($arguments[0],6);

        self::$currentRoute = $route;
        unset($_GET['route']);
    }

    // Récupérer la liste des routes configurées dans /config/routes.json
    static private function getConfiguredRoutes() : void
    {
        $configFile = file_get_contents(__DIR__ . '/../../config/routes.json');
        $json = json_decode($configFile, true);
        $routes = $json['routes'];

        self::$routes = $routes;
    }

    // Vérifier qu'une route corresponde à l'url actuelle

    static private function compareRoutes(array $route) : bool
    {
        self::$nameRoute = $route['name'];

        // Détecter si la route comporte des paramètre dynamiques
        if(strpos($route['url'], '{') !== false){
            // construire une RegEx: convertir la route en expression à comparer avec l'URL
            $expression = preg_replace("/{[\w-]+}/", "([\w-]+)", $route['url']);

            // si l'URL actuelle match avec la Regec générée
            if(preg_match('#^' . $expression . '$#', self::$currentRoute, $params)){
                array_shift($params);
                $method = [$route['class'], $route['method']]; // Un type collable
                call_user_func_array($method, $params); //Executer un callable
                return true;
            }
            return false;

            // Si la route ne comporte pas de paramètre dynamique
        }elseif ($route['url'] === self::$currentRoute){
            $method = [$route['class'], $route['method']]; // Un type collable
            call_user_func($method); //Executer un callable

            return true;
        }

        return false;
    }




    // Executer la méthode qui correspond à la route actuelle
    static public function parseRoute() : void
    {
        self::getCurrentRoute();
        self::getConfiguredRoutes();

        foreach (self::$routes as $route){
            $compareRoute = self::compareRoutes($route);
            if($compareRoute){
                break;
            }
        }

        if(!$compareRoute){
            header("location:". App::URL . "/error/404");
        }
    }


    /*
    *Construire un lien vers une autre route
     * @param string $route_name
     * @param array $params
   */
    static public function buildPath(string $route_name, array $params = [])
    {
//        Nom des routes existante
        $route_names_list = array_column(self::$routes, 'name');
//        Index de la route cible
        $route_index = array_search($route_name, $route_names_list);



        if($route_index === false){
            throw new \Exception('La route ' . $route_name . ' n\' existe pas');
        }

//        URL de la route cible
        $route_cibe = self::$routes[$route_index]['url'];


        $route_parts = explode('/', self::$currentRoute);
        $route_cibe_parts = explode('/', $route_cibe);



        while(isset($route_parts[0])
            && isset($route_parts[0])
            && $route_parts[0] === $route_cibe_parts[0]
        ){
            array_shift($route_parts);
            array_shift($route_cibe_parts);
        }

        array_pop($route_parts);

//        Construction des retours arriere;
        $retours = array_fill(0, count($route_parts), '..');
        $retours = implode('/', $retours);
        $retours = empty($retours) ? '' : $retours . '/';
        $lien = implode('/', $route_cibe_parts);
        $lien = $retours . $lien;

        while(preg_match('/{([\w-]+)}/', $lien, $args)){
            App::Debug($args);
            $cle = $params[$args[1]];
            if(!isset($cle))
            {
                throw new \Exception('La clé ' . $args[1] . ' a été oubliée');
            }

            $lien = str_replace($args[0], $cle, $lien);
        }

        return $lien;
    }

    static public function getNameRoute() : string
    {
        return self::$nameRoute;
    }
}