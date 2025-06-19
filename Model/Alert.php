<?php
class Alert {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    /**
     * Créer une nouvelle alerte
     * @param string $alertType
     * @param string $message
     * @param string $severity
     * @param string $location
     * @return array
     */
    public function create($alertType, $message, $severity = 'medium', $location = 'Chambre froide principale') {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        $stmt = $conn->prepare("INSERT INTO alerts (alert_type, message, severity, location) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("ssss", $alertType, $message, $severity, $location);

        if ($stmt->execute()) {
            $alertId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Alerte créée avec succès', 'alert_id' => $alertId];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors de la création: ' . $error];
        }
    }

    /**
     * Récupérer les alertes récentes
     * @param int $limit
     * @param bool $onlyUnresolved
     * @return array
     */
    public function getRecent($limit = 20, $onlyUnresolved = false) {
        $conn = $this->database->connect();
        if (!$conn) {
            return [];
        }

        $sql = "SELECT * FROM alerts";
        if ($onlyUnresolved) {
            $sql .= " WHERE resolved = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $alerts;
    }

    /**
     * Marquer une alerte comme résolue
     * @param int $alertId
     * @return array
     */
    public function resolve($alertId) {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        $stmt = $conn->prepare("UPDATE alerts SET resolved = 1, resolved_at = NOW() WHERE id = ?");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("i", $alertId);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Alerte marquée comme résolue'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors de la résolution: ' . $error];
        }
    }

    /**
     * Récupérer les alertes par type
     * @param string $alertType
     * @param int $limit
     * @return array
     */
    public function getByType($alertType, $limit = 50) {
        $conn = $this->database->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("SELECT * FROM alerts WHERE alert_type = ? ORDER BY created_at DESC LIMIT ?");
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("si", $alertType, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $alerts;
    }

    /**
     * Récupérer les alertes par niveau de sévérité
     * @param string $severity
     * @param int $limit
     * @return array
     */
    public function getBySeverity($severity, $limit = 50) {
        $conn = $this->database->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("SELECT * FROM alerts WHERE severity = ? ORDER BY created_at DESC LIMIT ?");
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("si", $severity, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $alerts;
    }

    /**
     * Compter les alertes non résolues
     * @return int
     */
    public function getUnresolvedCount() {
        $conn = $this->database->connect();
        if (!$conn) {
            return 0;
        }

        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM alerts WHERE resolved = 0");
        if (!$stmt) {
            $conn->close();
            return 0;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        $conn->close();

        return $row['count'] ?? 0;
    }

    /**
     * Supprimer les anciennes alertes résolues
     * @param int $daysToKeep
     * @return array
     */
    public function cleanOldAlerts($daysToKeep = 7) {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        $stmt = $conn->prepare("DELETE FROM alerts WHERE resolved = 1 AND resolved_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("i", $daysToKeep);

        if ($stmt->execute()) {
            $deletedRows = $stmt->affected_rows;
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => "Nettoyage terminé: $deletedRows alertes supprimées"];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors du nettoyage: ' . $error];
        }
    }
}