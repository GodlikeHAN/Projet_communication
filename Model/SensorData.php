<?php
class SensorData {
    private $sharedDatabase;

    const SENSOR_PROXIMITY_ID = 3; // Capteur de proximité
    const ACTUATOR_BUZZER_ID = 4;

    public function __construct($sharedDatabase) {
        $this->sharedDatabase = $sharedDatabase;
    }

    /**
     * Enregistrer des données (capteur ou actionneur) dans la table sensorData
     * @param int $sensorId
     * @param float $value
     * @return array
     */
    public function recordData($sensorId, $value) {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données partagée'];
        }

        $stmt = $conn->prepare("INSERT INTO sensorData (sensorId, timeRecorded, value) VALUES (?, NOW(), ?)");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("id", $sensorId, $value);

        if ($stmt->execute()) {
            $dataId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Données enregistrées dans la DB partagée', 'data_id' => $dataId];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement: ' . $error];
        }
    }

    /**
     * Récupérer les dernières données d'un capteur/actionneur spécifique
     * @param int $sensorId
     * @param int $limit
     * @return array
     */
    public function getLatestData($sensorId, $limit = 50) {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("SELECT * FROM sensorData WHERE sensorId = ? ORDER BY timeRecorded DESC LIMIT ?");
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("ii", $sensorId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $data;
    }

    /**
     * Récupérer toutes les données de tous les capteurs/actioneurs
     * @param int $limit
     * @return array
     */
    public function getAllSensorsData($limit = 100) {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("SELECT * FROM sensorData  ORDER BY timeRecorded DESC LIMIT ?");
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $data;
    }


    /**
     * Récupérer les données par période pour un capteur/actionneur
     * @param int $sensorId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDataByPeriod($sensorId, $startDate, $endDate) {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("SELECT * FROM sensorData WHERE sensorId = ? AND timeRecorded BETWEEN ? AND ? ORDER BY timeRecorded ASC");
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("iss", $sensorId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $data;
    }

    /**
     * Obtenir des statistiques pour un capteur/actionneur
     * @param int $sensorId
     * @param string $period (hour, day, week, month)
     * @return array
     */
    public function getStats($sensorId, $period = 'day') {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $intervals = [
            'hour' => 'DATE_SUB(NOW(), INTERVAL 1 HOUR)',
            'day' => 'DATE_SUB(NOW(), INTERVAL 1 DAY)',
            'week' => 'DATE_SUB(NOW(), INTERVAL 1 WEEK)',
            'month' => 'DATE_SUB(NOW(), INTERVAL 1 MONTH)'
        ];

        $interval = $intervals[$period] ?? $intervals['day'];

        $sql = "SELECT 
                    COUNT(*) as count,
                    AVG(value) as average,
                    MIN(value) as minimum,
                    MAX(value) as maximum,
                    STDDEV(value) as standard_deviation
                FROM sensorData 
                WHERE sensorId = ? AND timeRecorded >= $interval";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("i", $sensorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $stats = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $stats ?: [];
    }

    /**
     * Obtenir la liste de tous les capteurs actifs
     * * @return array
     */
    public function getActiveSensors() {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $sql = "SELECT 
                    sensorId,
                    COUNT(*) as data_count,
                    MAX(timeRecorded) as last_update,
                    AVG(value) as avg_value
                FROM sensorData 
                WHERE timeRecorded >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                GROUP BY sensorId 
                ORDER BY last_update DESC";

        $result = $conn->query($sql);
        if (!$result) {
            $conn->close();
            return [];
        }

        $sensors = [];
        while ($row = $result->fetch_assoc()) {
            $sensors[] = $row;
        }

        $conn->close();
        return $sensors;
    }

    /**
     * Obtenir des statistiques globales de tous les capteurs et actionneurs
     * @return array
     */
    public function getGlobalStats() {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $sql = "SELECT 
                    COUNT(DISTINCT CASE WHEN sensorId <= 100 THEN sensorId END) as total_sensors,
                    COUNT(DISTINCT CASE WHEN sensorId > 100 THEN sensorId END) as total_actuators,
                    COUNT(*) as total_records,
                    MIN(timeRecorded) as first_record,
                    MAX(timeRecorded) as last_record
                FROM sensorData";

        $result = $conn->query($sql);
        if (!$result) {
            $conn->close();
            return [];
        }

        $stats = $result->fetch_assoc();
        $conn->close();

        return $stats ?: [];
    }

    /**
     * Obtenir les données récentes pour graphiques temps réel
     * @param int $sensorId
     * @param int $minutes Dernières X minutes
     * @return array
     */
    public function getRealtimeData($sensorId, $minutes = 30) {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT 
                id,
                value,
                timeRecorded,
                DATE_FORMAT(timeRecorded, '%H:%i:%s') as time_formatted
            FROM sensorData 
            WHERE sensorId = ? 
            AND timeRecorded >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ORDER BY timeRecorded ASC
        ");

        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("ii", $sensorId, $minutes);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $data;
    }

    /**
     * Obtenir le dernier état d'un actionneur
     * @param int $actuatorId (sensorId > 100)
     * @return array|null
     */
    public function getLastActuatorState($actuatorId) {
        $conn = $this->sharedDatabase->connect();
        if (!$conn) {
            return null;
        }

        $stmt = $conn->prepare("SELECT * FROM sensorData WHERE sensorId = ? ORDER BY timeRecorded DESC LIMIT 1");
        if (!$stmt) {
            $conn->close();
            return null;
        }

        $stmt->bind_param("i", $actuatorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $state = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $state;
    }

}