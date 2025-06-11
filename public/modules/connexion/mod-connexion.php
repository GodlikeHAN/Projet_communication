<?php
//if (!defined('MY_APP')) {
//    exit('Accès non autorisé');
//}

require_once '../connexionBD/connexionBD.php';

class ConnexionModel extends ConnexionBD {

    public function checkCredentials($pseudo, $password) {
        $stmt = self::$bdd->prepare('SELECT pseudo, passw_hash FROM utilisateur WHERE pseudo = :pseudo');
        $stmt->bindParam(':pseudo', $pseudo);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['passw_hash'])) {
//            if ($this->checkCSRFToken()){
            return true;
//            }
        }

        return false;
    }

    public function getRole($name) {
        try {
            $stmt = self::$bdd->prepare(
                "SELECT d.nom 
            FROM utilisateur u 
            JOIN a_le_droit a ON u.id_utilisateur = a.id_utilisateur 
            JOIN droit d ON a.id_droit = d.id_droit 
            WHERE u.pseudo = :name"
            );
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result === false) {
                // Aucun résultat trouvé
                return null;
            }
            return $result['nom'];
        } catch (PDOException $e) {
            echo "Erreur SQL : " . $e->getMessage();
            return null;
        }
    }

    public function getId($pseudo)
    {
        $stmt = self::$bdd->prepare("SELECT id_utilisateur FROM Utilisateur WHERE pseudo=:nom");
        $stmt->bindParam(':nom',$pseudo);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id_utilisateur'];
    }

    public function getPhoto($pseudo) {
        $stmt = self::$bdd->prepare("SELECT photo FROM Utilisateur WHERE pseudo=:nom");
        $stmt->bindParam(':nom',$pseudo);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['photo'];
    }
}
?>