<?php
include_once 'Model/Connection.php';
class UserController {

    /**
     * Page de connexion
     */
    public function connexion($database) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if ($input) {
                $email = $input['email'] ?? '';
                $password = $input['password'] ?? '';

                // Validation des données
                if (empty($email) || empty($password)) {
                    http_response_code(400);
                    echo json_encode(['Error' => 'Email et mot de passe requis']);
                    return;
                }

                // Vérification en base de données
                $conn = $database->connect();
                if (!$conn) {
                    http_response_code(500);
                    echo json_encode(['Error' => 'Erreur de connexion à la base de données']);
                    return;
                }

                $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($user = $result->fetch_assoc()) {
                    if (password_verify($password, $user['password'])) {
                        session_start();
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];

                        echo json_encode(['Success' => 'Connexion réussie']);
                    } else {
                        http_response_code(401);
                        echo json_encode(['Error' => 'Mot de passe incorrect']);
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(['Error' => 'Utilisateur non trouvé']);
                }

                $stmt->close();
                $conn->close();
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
            $input = json_decode(file_get_contents('php://input'), true);

            if ($input) {
                $name = $input['name'] ?? '';
                $email = $input['email'] ?? '';
                $password = $input['password'] ?? '';

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

                $conn = $database->connect();
                if (!$conn) {
                    http_response_code(500);
                    echo json_encode(['Error' => 'Erreur de connexion à la base de données']);
                    return;
                }

                // Vérifier si l'email existe déjà
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    http_response_code(409);
                    echo json_encode(['Error' => 'Cet email est déjà utilisé']);
                    $stmt->close();
                    $conn->close();
                    return;
                }
                $stmt->close();

                // Créer l'utilisateur
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $hashedPassword);

                if ($stmt->execute()) {
                    echo json_encode(['Success' => 'Inscription réussie']);
                } else {
                    http_response_code(500);
                    echo json_encode(['Error' => 'Erreur lors de l\'inscription']);
                }

                $stmt->close();
                $conn->close();
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