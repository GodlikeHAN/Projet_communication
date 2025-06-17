<?php
class User {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    /**
     * Créer un nouvel utilisateur
     * @param string $name
     * @param string $email
     * @param string $password
     * @return array
     */
    public function create($name, $email, $password) {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        // Vérifier si l'email existe déjà
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }

        // Créer l'utilisateur
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");

        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Utilisateur créé avec succès', 'user_id' => $userId];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors de la création: ' . $error];
        }
    }

    /**
     * Vérifier si un email existe déjà
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        $conn = $this->database->connect();
        if (!$conn) {
            return false;
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            $conn->close();
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        $stmt->close();
        $conn->close();

        return $exists;
    }

    /**
     * Authentifier un utilisateur
     * @param string $email
     * @param string $password
     * @return array
     */
    public function authenticate($email, $password) {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $stmt->close();
                $conn->close();
                return [
                    'success' => true,
                    'message' => 'Authentification réussie',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name']
                    ]
                ];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Mot de passe incorrect'];
            }
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
    }

    /**
     * Récupérer un utilisateur par son ID
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $conn = $this->database->connect();
        if (!$conn) {
            return null;
        }

        $stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        if (!$stmt) {
            $conn->close();
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $user;
    }

    /**
     * Mettre à jour les informations d'un utilisateur
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        $fields = [];
        $values = [];
        $types = '';

        // Construire la requête dynamiquement
        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $values[] = $data['name'];
            $types .= 's';
        }

        if (isset($data['email'])) {
            // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $data['email'], $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }
            $stmt->close();

            $fields[] = 'email = ?';
            $values[] = $data['email'];
            $types .= 's';
        }

        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= 's';
        }

        if (empty($fields)) {
            $conn->close();
            return ['success' => false, 'message' => 'Aucune donnée à mettre à jour'];
        }

        $values[] = $id;
        $types .= 'i';

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour: ' . $error];
        }
    }

    /**
     * Supprimer un utilisateur
     * @param int $id
     * @return array
     */
    public function delete($id) {
        $conn = $this->database->connect();
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Erreur de préparation de la requête'];
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $error];
        }
    }

    /**
     * Récupérer tous les utilisateurs
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll($limit = 50, $offset = 0) {
        $conn = $this->database->connect();
        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        $stmt->close();
        $conn->close();

        return $users;
    }
}