<?php
class SensorController {

    /**
     * Page principale de gestion des capteurs de la chambre froide
     */
    public function dashboard($database) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Projet_communication/connection');
            exit();
        }

        include 'Vue/html/dashboard.php';
    }

    /**
     * API pour récupérer les données des capteurs
     */
    public function getSensorData($database) {
        header('Content-Type: application/json');

        $conn = $database->connect();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['Error' => 'Erreur de connexion à la base de données']);
            return;
        }

        // Récupérer les dernières données des capteurs
        $query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 50";
        $result = $conn->query($query);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $conn->close();
        echo json_encode($data);
    }

    /**
     * API pour enregistrer les données du capteur de proximité
     */
    // public function recordProximityData($database) {
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         http_response_code(405);
    //         echo json_encode(['Error' => 'Méthode non autorisée']);
    //         return;
    //     }

    //     $input = json_decode(file_get_contents('php://input'), true);

    //     if (!$input) {
    //         http_response_code(400);
    //         echo json_encode(['Error' => 'Données invalides']);
    //         return;
    //     }

    //     $sensorType = 'proximity';
    //     $value = $input['distance'] ?? null;
    //     $location = $input['location'] ?? 'Chambre froide principale';

    //     if ($value === null) {
    //         http_response_code(400);
    //         echo json_encode(['Error' => 'Valeur de distance requise']);
    //         return;
    //     }

    //     $conn = $database->connect();
    //     if (!$conn) {
    //         http_response_code(500);
    //         echo json_encode(['Error' => 'Erreur de connexion à la base de données']);
    //         return;
    //     }

    //     $stmt = $conn->prepare("INSERT INTO sensor_data (sensor_type, value, location, team_id) VALUES (?, ?, ?, ?)");
    //     $teamId = 1; // ID de votre équipe
    //     $stmt->bind_param("sdsi", $sensorType, $value, $location, $teamId);

    //     if ($stmt->execute()) {
    //         // Vérifier si une alerte doit être déclenchée
    //         $this->checkProximityAlert($value, $database);
    //         echo json_encode(['Success' => 'Données enregistrées']);
    //     } else {
    //         http_response_code(500);
    //         echo json_encode(['Error' => 'Erreur lors de l\'enregistrement']);
    //     }

    //     $stmt->close();
    //     $conn->close();
    // }

    /**
     * API : récupérer les dernières valeurs de distance (capteur ultrason, sensorId = 3)
     * Retourne un JSON des 50 mesures les plus récentes.
     */
    public function getDistanceData() {
        header('Content-Type: application/json');

        // Connexion à la BDD partagée (Azure)
        require_once 'Database/SharedDatabase.php';
        $sharedDb = new SharedDatabase();
        $conn = $sharedDb->connect();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['Error' => 'Connexion à la base partagée impossible']);
            return;
        }

        // Requête : dernières 50 lignes pour sensorId = 3
        $sql  = "SELECT timeRecorded AS timestamp, value 
                FROM sensorData 
                WHERE sensorId = 3
                    AND  timeRecorded > UTC_TIMESTAMP() - INTERVAL 30 SECOND
                ORDER BY timeRecorded DESC 
                LIMIT 50";
        $result = $conn->query($sql);
        // format JSON
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;      // chaque ligne : {timestamp: "...", value: 23.4}
        }
        $conn->close();
        echo json_encode($data);
    }

    /**
     * API pour contrôler le buzzer
     */
    public function controlBuzzer($database) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['Error' => 'Méthode non autorisée']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null; // 'on' ou 'off'

        if (!in_array($action, ['on', 'off'])) {
            http_response_code(400);
            echo json_encode(['Error' => 'Action invalide (on/off)']);
            return;
        }

        $conn = $database->connect();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['Error' => 'Erreur de connexion à la base de données']);
            return;
        }

        // Enregistrer l'action du buzzer
        $stmt = $conn->prepare("INSERT INTO actuator_actions (actuator_type, action, location, team_id) VALUES (?, ?, ?, ?)");
        $actuatorType = 'buzzer';
        $location = 'Chambre froide principale';
        $teamId = 1;
        $stmt->bind_param("sssi", $actuatorType, $action, $location, $teamId);

        if ($stmt->execute()) {
            // Ici vous pourriez envoyer la commande au buzzer physique
            // via port série ou autre méthode de communication
            echo json_encode(['Success' => "Buzzer $action"]);
        } else {
            http_response_code(500);
            echo json_encode(['Error' => 'Erreur lors du contrôle du buzzer']);
        }

        $stmt->close();
        $conn->close();
    }

    /**
     * Vérifier si une alerte de proximité doit être déclenchée
     */
    private function checkProximityAlert($distance, $database) {
        $alertThreshold = 10; // Seuil d'alerte en cm

        if ($distance < $alertThreshold) {
            // Déclencher une alerte
            $conn = $database->connect();
            if ($conn) {
                $stmt = $conn->prepare("INSERT INTO alerts (alert_type, message, severity, location) VALUES (?, ?, ?, ?)");
                $alertType = 'proximity_intrusion';
                $message = "Intrusion détectée dans la chambre froide - Distance: {$distance}cm";
                $severity = 'high';
                $location = 'Chambre froide principale';

                $stmt->bind_param("ssss", $alertType, $message, $severity, $location);
                $stmt->execute();
                $stmt->close();

                // Déclencher automatiquement le buzzer
                $this->triggerBuzzer($database);
            }
        }
    }

    /**
     * Déclencher automatiquement le buzzer
     */
    private function triggerBuzzer($database) {
        $conn = $database->connect();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO actuator_actions (actuator_type, action, location, team_id, automatic) VALUES (?, ?, ?, ?, ?)");
            $actuatorType = 'buzzer';
            $action = 'on';
            $location = 'Chambre froide principale';
            $teamId = 1;
            $automatic = 1;

            $stmt->bind_param("sssii", $actuatorType, $action, $location, $teamId, $automatic);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * API pour récupérer les alertes
     */
    public function getAlerts($database) {
        header('Content-Type: application/json');

        $conn = $database->connect();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['Error' => 'Erreur de connexion à la base de données']);
            return;
        }

        $query = "SELECT * FROM alerts ORDER BY created_at DESC LIMIT 20";
        $result = $conn->query($query);

        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        $conn->close();
        echo json_encode($alerts);
    }

    
}