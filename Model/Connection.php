<?php
class Connection {
    private $db;

    public function __construct($database) {
        $this->db = $database->connect();
    }

    /**
     * Vérifier les identifiants de connexion
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function checkCredentials($email, $password) {
        $stmt = mysqli_prepare($this->db, 'SELECT id, name, password FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }

        return false;
    }

    /**
     * Récupérer l'ID de l'utilisateur
     * @param string $email
     * @return int|null
     */
    public function getId($email) {
        $stmt = mysqli_prepare($this->db, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        return $user ? $user['id'] : null;
    }

    /**
     * Récupérer la photo de profil de l'utilisateur
     * @param string $email
     * @return string|null
     */
    public function getPhoto($email) {
        $stmt = mysqli_prepare($this->db, "SELECT photo FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        return $user ? $user['photo'] : null;
    }

    /**
     * Créer un nouvel utilisateur
     * @param string $nom
     * @param string $prenom
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function createUser($name, $email, $password) {
        // Vérifier si l'email existe déjà
        if ($this->emailExists($email)) {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($this->db,
            "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashedPassword);

        return mysqli_stmt_execute($stmt);
    }

    /**
     * Vérifier si un email existe déjà
     * @param string $email
     * @return bool
     */
    private function emailExists($email) {
        $stmt = mysqli_prepare($this->db, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return mysqli_num_rows($result) > 0;
    }

    /**
     * Récupérer les informations complètes d'un utilisateur
     * @param int $id
     * @return array|null
     */
    public function getUserById($id) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);
    }

    /**
     * Mettre à jour les informations d'un utilisateur
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data) {
        $fields = [];
        $values = [];
        $types = '';

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'email', 'photo'])) {
                $fields[] = "$key = ?";
                $values[] = $value;
                $types .= 's';
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $types .= 'i';

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        return mysqli_stmt_execute($stmt);
    }
}