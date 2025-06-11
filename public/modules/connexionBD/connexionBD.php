<?php

Class ConnexionBD {
    public static $bdd;
    public function __construct() {
        $this->connexion();
    }
    public static function connexion() {
        try {
            self::$bdd = new PDO('mysql:host=localhost;dbname=projetcommun', 'horeb', 'horeb');
            self::$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    function checkCSRFToken(){
        if(isset($_POST['csrf_token'])) {
            $user_token = $_POST['csrf_token'];
            if($user_token === $_SESSION['csrf_token']) {
                return true;
            } else {
                echo "Erreur : Jeton CSRF non valide.";
                return false;
            }

        }else if(isset($_GET['csrf_token'])) {
            $user_token = $_GET['csrf_token'];
            if($user_token === $_SESSION['csrf_token']) {
                return true;
            } else {
                echo "Erreur : Jeton CSRF non valide.";
                return false;
            }

        } else {
            echo "Erreur : Jeton CSRF manquant dans la requête.";
            return false;
        }
    }

    static function generateCSRFToken() {
        return bin2hex(random_bytes(32));
    }
}

?>