<?php
require_once './Router.php';
require_once './Database/Database.php';
require_once './Controller/UserController.php';
require_once './Controller/HomeController.php';
require_once './Controller/SensorController.php';

$uri = $_SERVER['REQUEST_URI']; // Récupération de l'uri (la route)
$router = new Router();

// Routes principales
$router->addRoute('/',                          HomeController::class,   'home');
$router->addRoute('/connection',                UserController::class,   'connexion');
$router->addRoute('/inscription',               UserController::class,   'inscription');
$router->addRoute('/deconnexion',               UserController::class,   'deconnexion');

// Routes du dashboard et capteurs
$router->addRoute('/dashboard',                 SensorController::class, 'dashboard');

// API Routes pour les données des capteurs
$router->addRoute('/api/sensor-data',           SensorController::class, 'getSensorData');
$router->addRoute('/api/proximity-data',        SensorController::class, 'recordProximityData');
$router->addRoute('/api/buzzer',                SensorController::class, 'controlBuzzer');
$router->addRoute('/api/alerts',                SensorController::class, 'getAlerts');
$router->addRoute('/upload-shared-distance',    SensorController::class, 'uploadToSharedDatabase');
$router->addRoute('/api/distance-data',         SensorController::class, 'getDistanceData');


if ($uri !== null) {
    try {
        $router->dispatch($uri); // Appel à la méthode du controller dédié
    } catch (Exception $e) {
        // En cas d'erreur, afficher une page d'erreur ou rediriger
        http_response_code(500);
        echo "Erreur: " . $e->getMessage();
    }
}