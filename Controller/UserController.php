<?php
require_once 'Model/User.php';

class UserController {

    /**
     * Page de connexion
     */
    public function connexion($database) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // S'assurer que nous renvoyons du JSON
            header('Content-Type: application/json');

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode(['Error' => 'Données JSON invalides']);
                return;
            }

            $email = isset($input['email']) ? trim($input['email']) : '';
            $password = isset($input['password']) ? $input['password'] : '';

            // Validation des données
            if (empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(['Error' => 'Email et mot de passe requis']);
                return;
            }

            $userModel = new User($database);
            $result = $userModel->authenticate($email, $password);

            if ($result['success']) {
                session_start();
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_name'] = $result['user']['name'];

                echo json_encode(['Success' => $result['message']]);
            } else {
                http_response_code(401);
                echo json_encode(['Error' => $result['message']]);
            }
        } else {
            // Afficher la page de connexion
            include 'Vue/html/connection.php';
        }
    }

    /**
     * Page d'inscription
     */
    public function inscription($database) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // S'assurer que nous renvoyons du JSON
            header('Content-Type: application/json');

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode(['Error' => 'Données JSON invalides']);
                return;
            }

            $name = isset($input['name']) ? trim($input['name']) : '';
            $email = isset($input['email']) ? trim($input['email']) : '';
            $password = isset($input['password']) ? $input['password'] : '';

            // Validation des données
            if (empty($name) || empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(['Error' => 'Tous les champs sont requis']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['Error' => 'Email invalide']);
                return;
            }

            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['Error' => 'Le mot de passe doit contenir au moins 6 caractères']);
                return;
            }

            $userModel = new User($database);
            $result = $userModel->create($name, $email, $password);

            if ($result['success']) {
                echo json_encode(['Success' => 'Inscription réussie']);
            } else {
                if (strpos($result['message'], 'email') !== false) {
                    http_response_code(409);
                } else {
                    http_response_code(500);
                }
                echo json_encode(['Error' => $result['message']]);
            }
        } else {
            // Afficher la page d'inscription
            include 'Vue/html/inscription.php';
        }
    }

    /**
     * Déconnexion
     */
    public function deconnexion($database) {
        session_start();
        session_destroy();
        header('Location: /Projet_communication/');
        exit();
    }
}