<?php
require_once './Router.php';
require_once './Database/Database.php';
require_once './Controller/UserController.php';


$uri = $_SERVER['REQUEST_URI']; //Recupération de l'uri (la route)
$router = new Router();

//$router->addRoute('/',            HomeController::class, 'home');
$router->addRoute('/connection',   UserController::class, 'connexion');



if ($uri !== null) {
    $router->dispatch($uri); //Appel a la méthode du controller dedié
}