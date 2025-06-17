<?php
require_once 'Model/User.php';
require_once 'Model/Alert.php';
require_once 'Model/SensorData.php';
require_once 'Database/SharedDatabase.php';

class SensorController
{

    /**
     * Page principale de gestion des capteurs de la chambre froide
     */
    public function dashboard($database)
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Projet_communication/connection');
            exit();
        }

        include 'Vue/html/dashboard.php';
    }

    /**
     * API pour récupérer les données des capteurs depuis la DB partagée
     */
    public function getSensorData($database)
    {
        header('Content-Type: application/json');

        $sharedDB = new SharedDatabase();
        $sensorDataModel = new SensorData($sharedDB);

        $data = $sensorDataModel->getLatestData(SensorData::SENSOR_PROXIMITY_ID, 50);

        // Formater les données pour compatibilité avec le frontend
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'id' => $row['id'],
                'sensor_type' => 'proximity',
                'value' => $row['value'],
                'unit' => 'cm',
                'timestamp' => $row['timeRecorded'],
                'location' => 'Chambre froide principale'
            ];
        }

        echo json_encode($formattedData);
    }

    /**
     * API pour enregistrer les données du capteur de proximité dans la DB partagée
     */
    public function recordProximityData($database)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['Error' => 'Méthode non autorisée']);
            return;
        }

        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['Error' => 'Données invalides']);
            return;
        }

        $distance = $input['distance'] ?? null;

        if ($distance === null) {
            http_response_code(400);
            echo json_encode(['Error' => 'Valeur de distance requise']);
            return;
        }

        // Enregistrer dans la base de données partagée
        $sharedDB = new SharedDatabase();
        $sensorDataModel = new SensorData($sharedDB);
        $result = $sensorDataModel->recordData(SensorData::SENSOR_PROXIMITY_ID, $distance);

        if ($result['success']) {
            // Vérifier si une alerte doit être déclenchée (stockée en local)
            $this->checkProximityAlert($distance, $database);
            echo json_encode(['Success' => $result['message']]);
        } else {
            http_response_code(500);
            echo json_encode(['Error' => $result['message']]);
        }
    }

    /**
     * API pour contrôler le buzzer (stocké dans la DB partagée)
     */
    public function controlBuzzer($database)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['Error' => 'Méthode non autorisée']);
            return;
        }

        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null;

        if (!in_array($action, ['on', 'off'])) {
            http_response_code(400);
            echo json_encode(['Error' => 'Action invalide (on/off)']);
            return;
        }

        // Convertir l'action en valeur numérique
        $value = ($action === 'on') ? 1.0 : 0.0;

        // Enregistrer l'action du buzzer dans la DB partagée
        $sharedDB = new SharedDatabase();
        $actuatorDataModel = new ActuatorData($sharedDB);
        $result = $actuatorDataModel->recordAction(ActuatorData::ACTUATOR_BUZZER_ID, $value);

        if ($result['success']) {
            // Ici vous pourriez envoyer la commande au buzzer physique
            // via port série ou autre méthode de communication
            echo json_encode(['Success' => "Buzzer $action"]);
        } else {
            http_response_code(500);
            echo json_encode(['Error' => $result['message']]);
        }
    }

    /**
     * Vérifier si une alerte de proximité doit être déclenchée
     */
    private function checkProximityAlert($distance, $database)
    {
        $alertThreshold = 20; // Seuil d'alerte en cm

        if ($distance < $alertThreshold) {
            // Créer une alerte dans la DB locale
            $alertModel = new Alert($database);
            $message = "Intrusion détectée dans la chambre froide - Distance: {$distance}cm";
            $alertModel->create('proximity_intrusion', $message, 'high', 'Chambre froide principale');

            // Déclencher automatiquement le buzzer
            $this->triggerBuzzer($database);
        }
    }

    /**
     * Déclencher automatiquement le buzzer
     */
    private function triggerBuzzer($database)
    {
        $sharedDB = new SharedDatabase();
        $actuatorDataModel = new ActuatorData($sharedDB);
        $actuatorDataModel->recordAction(ActuatorData::ACTUATOR_BUZZER_ID, 1.0); // 1.0 = ON
    }

    /**
     * API pour récupérer les alertes (DB locale)
     */
    public function getAlerts($database)
    {
        header('Content-Type: application/json');

        $alertModel = new Alert($database);
        $alerts = $alertModel->getRecent(20);

        echo json_encode($alerts);
    }

    /**
     * API pour récupérer les statistiques des capteurs depuis la DB partagée
     */
    public function getSensorStats($database)
    {
        header('Content-Type: application/json');

        $sharedDB = new SharedDatabase();
        $sensorDataModel = new SensorData($sharedDB);

        $stats = [
            'proximity_hour' => $sensorDataModel->getStats(SensorData::SENSOR_PROXIMITY_ID, 'hour'),
            'proximity_day' => $sensorDataModel->getStats(SensorData::SENSOR_PROXIMITY_ID, 'day'),
            'proximity_week' => $sensorDataModel->getStats(SensorData::SENSOR_PROXIMITY_ID, 'week'),
            'proximity_month' => $sensorDataModel->getStats(SensorData::SENSOR_PROXIMITY_ID, 'month'),
            'global_stats' => $sensorDataModel->getGlobalStats(),
            'active_sensors' => $sensorDataModel->getActiveSensors()
        ];

        echo json_encode($stats);
    }

    /**
     * API pour récupérer l'historique des actionneurs depuis la DB partagée
     */
    public function getActuatorHistory($database)
    {
        header('Content-Type: application/json');

        $sharedDB = new SharedDatabase();
        $actuatorDataModel = new ActuatorData($sharedDB);

        // Récupérer l'historique de votre buzzer
        $history = $actuatorDataModel->getActionHistory(ActuatorData::ACTUATOR_BUZZER_ID, 50);

        // Formater les données pour compatibilité avec le frontend
        $formattedHistory = [];
        foreach ($history as $row) {
            $formattedHistory[] = [
                'id' => $row['id'],
                'actuator_type' => 'buzzer',
                'action' => $row['value'] > 0 ? 'on' : 'off',
                'value' => $row['value'],
                'timestamp' => $row['timeRecorded'],
                'location' => 'Chambre froide principale',
                'automatic' => false // Cette info n'est plus stockée dans la DB partagée
            ];
        }

        echo json_encode($formattedHistory);
    }

    /**
     * API pour récupérer les statistiques des actionneurs depuis la DB partagée
     */
    public function getActuatorStats($database)
    {
        header('Content-Type: application/json');

        $sharedDB = new SharedDatabase();
        $actuatorDataModel = new ActuatorData($sharedDB);

        $stats = [
            'buzzer_hour' => $actuatorDataModel->getUsageStats(ActuatorData::ACTUATOR_BUZZER_ID, 'hour'),
            'buzzer_day' => $actuatorDataModel->getUsageStats(ActuatorData::ACTUATOR_BUZZER_ID, 'day'),
            'buzzer_week' => $actuatorDataModel->getUsageStats(ActuatorData::ACTUATOR_BUZZER_ID, 'week'),
            'buzzer_month' => $actuatorDataModel->getUsageStats(ActuatorData::ACTUATOR_BUZZER_ID, 'month'),
            'global_actuator_stats' => $actuatorDataModel->getGlobalStats(),
            'active_actuators' => $actuatorDataModel->getActiveActuators()
        ];

        echo json_encode($stats);
    }

    /**
     * API pour récupérer les données temps réel pour les graphiques
     */
    public function getRealtimeData($database)
    {
        header('Content-Type: application/json');

        $minutes = $_GET['minutes'] ?? 30; // Dernières X minutes
        $type = $_GET['type'] ?? 'sensor'; // 'sensor' ou 'actuator'

        $sharedDB = new SharedDatabase();

        if ($type === 'actuator') {
            $actuatorDataModel = new ActuatorData($sharedDB);
            $data = $actuatorDataModel->getRealtimeData(ActuatorData::ACTUATOR_BUZZER_ID, $minutes);
        } else {
            $sensorDataModel = new SensorData($sharedDB);
            $data = $sensorDataModel->getRealtimeData(SensorData::SENSOR_PROXIMITY_ID, $minutes);
        }

        echo json_encode($data);
    }

    /**
     * API pour récupérer les données de tous les capteurs et actionneurs (vue globale)
     */
    public function getAllSystemData($database)
    {
        header('Content-Type: application/json');

        $sharedDB = new SharedDatabase();
        $sensorDataModel = new SensorData($sharedDB);

        $systemData = [
            'sensors' => $sensorDataModel->getAllSensorsData(50),
            'actuators' => $actuatorDataModel->getAllActuatorsData(50),
            'sensor_stats' => $sensorDataModel->getGlobalStats(),
            'actuator_stats' => $actuatorDataModel->getGlobalStats()
        ];

        // Enrichir les données avec des informations sur les types
        $systemData['sensors'] = array_map(function ($row) {
            $sensorInfo = $this->getSensorInfo($row['sensorId']);
            return array_merge($row, $sensorInfo);
        }, $systemData['sensors']);

        $systemData['actuators'] = array_map(function ($row) {
            $actuatorInfo = $this->getActuatorInfo($row['actuatorId']);
            return array_merge($row, $actuatorInfo);
        }, $systemData['actuators']);

        echo json_encode($systemData);
    }

    /**
     * Obtenir les informations d'un capteur selon son ID
     */
    private function getSensorInfo($sensorId)
    {
        $sensorMap = [
            1 => ['type' => 'proximity', 'unit' => 'cm', 'team' => 'Équipe Proximité', 'location' => 'Chambre froide principale'],
            2 => ['type' => 'temperature', 'unit' => '°C', 'team' => 'Équipe Température', 'location' => 'Chambre froide A'],
            3 => ['type' => 'humidity', 'unit' => '%', 'team' => 'Équipe Humidité', 'location' => 'Chambre froide B'],
            4 => ['type' => 'pressure', 'unit' => 'hPa', 'team' => 'Équipe Pression', 'location' => 'Chambre froide C'],
            5 => ['type' => 'light', 'unit' => 'lux', 'team' => 'Équipe Luminosité', 'location' => 'Chambre froide D']
        ];

        return $sensorMap[$sensorId] ?? [
            'type' => 'unknown',
            'unit' => '',
            'team' => 'Équipe Inconnue',
            'location' => 'Non spécifié'
        ];
    }

    /**
     * Obtenir les informations d'un actionneur selon son ID
     */
    private function getActuatorInfo($actuatorId)
    {
        $actuatorMap = [
            1 => ['type' => 'buzzer', 'unit' => 'on/off', 'team' => 'Équipe Proximité', 'location' => 'Chambre froide principale'],
            2 => ['type' => 'heater', 'unit' => 'on/off', 'team' => 'Équipe Température', 'location' => 'Chambre froide A'],
            3 => ['type' => 'humidifier', 'unit' => 'on/off', 'team' => 'Équipe Humidité', 'location' => 'Chambre froide B'],
            4 => ['type' => 'ventilator', 'unit' => 'on/off', 'team' => 'Équipe Pression', 'location' => 'Chambre froide C'],
            5 => ['type' => 'light', 'unit' => 'on/off', 'team' => 'Équipe Luminosité', 'location' => 'Chambre froide D']
        ];

        return $actuatorMap[$actuatorId] ?? [
            'type' => 'unknown',
            'unit' => '',
            'team' => 'Équipe Inconnue',
            'location' => 'Non spécifié'
        ];
    }

    /**
     * API pour marquer une alerte comme résolue (DB locale)
     */
    public function resolveAlert($database)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['Error' => 'Méthode non autorisée']);
            return;
        }

        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $alertId = $input['alert_id'] ?? null;

        if (!$alertId) {
            http_response_code(400);
            echo json_encode(['Error' => 'ID d\'alerte requis']);
            return;
        }

        $alertModel = new Alert($database);
        $result = $alertModel->resolve($alertId);

        if ($result['success']) {
            echo json_encode(['Success' => $result['message']]);
        } else {
            http_response_code(500);
            echo json_encode(['Error' => $result['message']]);
        }
    }
    /**
     * API pour récupérer les données de tous les capteurs (vue globale)
     */
    public function getAllSensorsData($database)
    {
        header('Content-Type: application/json');

        $sharedDB = new SharedDatabase();
        $sensorDataModel = new SensorData($sharedDB);

        $data = $sensorDataModel->getAllSensorsData(100);

        // Enrichir les données avec des informations sur les types de capteurs
        $enrichedData = [];
        foreach ($data as $row) {
            $sensorInfo = $this->getSensorInfo($row['sensorId']);
            $enrichedData[] = array_merge($row, $sensorInfo);
        }

        echo json_encode($enrichedData);
    }

}
