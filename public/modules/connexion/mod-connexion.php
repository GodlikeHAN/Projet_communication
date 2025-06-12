<?php
require_once 'modules/connexionBD/connexionBD.php';
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