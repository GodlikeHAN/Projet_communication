<?php

require_once('Controller/UserController.php');
require_once('Controller/HomeController.php');
require_once('Controller/SensorController.php');
require_once('Database/Database.php');

class Router
{
    protected $routes = [];

    /**
     * Enregistrer dans le tableau le controller + la méthode dédiée à la route
     * @param string $route
     * @param string $controller
     * @param string $action
     */
    public function addRoute($route, $controller, $action) {
        $uri = str_replace("?", "", $route);
        $uri = str_replace("/Projet_communication", "", $uri);
        $this->routes[$uri] = ['controller' => $controller, 'action' => $action];
    }

    /**
     * Appel au controller à partir de l'uri
     * @param string $uri
     * @throws \Exception
     */
    public function dispatch($uri) {
        $uri = str_replace("?", "", $uri);
        $uri = str_replace("/Projet_communication", "", $uri);

        if (array_key_exists($uri, $this->routes)) {
            $controllerName = $this->routes[$uri]['controller'];
            $actionName = $this->routes[$uri]['action'];

            // Vérifier si le controller existe
            if (!class_exists($controllerName)) {
                throw new \Exception("Controller non trouvé: $controllerName");
            }

            $controller = new $controllerName();

            // Vérifier si l'action existe dans le controller
            if (!method_exists($controller, $actionName)) {
                throw new \Exception("Action non trouvée: $actionName dans le controller $controllerName");
            }

            // Appeler l'action avec la base de données
            $controller->$actionName(new Database());
        } else {
            // Route par défaut - redirection vers l'accueil
            $controller = new HomeController();
            $controller->home(new Database());
        }
    }
}