<?php

require_once 'mod-inscription.php';
require_once 'modules\connexion\view-connexion.php';

class InscriptionController {
    public function handle() {
        $model = new InscriptionModel();
        $view = new ConnexionView();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $pseudo = $_POST['pseudo'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $_SESSION['active_tab'] = 'signup';

            if (empty($nom)) {
                $_SESSION['error_message'] = 'Le nom est obligatoire !';
                $view->render();
                return;
            }elseif (empty($prenom)) {
                $_SESSION['error_message'] = 'Le prénom est obligatoire !';
                $view->render();
                return;
            }elseif (empty($email)) {
                $_SESSION['error_message'] = 'L\'email est obligatoire !';
                $view->render();
                return;
            }elseif (empty($password)) {
                $_SESSION['error_message'] = 'Le mot de passe est obligatoire !';
                $view->render();
                return;
            }elseif (empty($confirm_password)) {
                $_SESSION['error_message'] = 'Confirmez votre mot de passe !';
                $view->render();
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = 'L\'adresse e-mail est invalide.';
                $view->render();
                return;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error_message'] = 'Les mots de passe ne correspondent pas.';
                $view->render();
                return;
            }

            if (strlen($password) < 5) {
                $_SESSION['error_message'] = 'Le mot de passe doit contenir au moins 5 caractères.';
                $view->render();
                return;
            }

            if ($model->checkUsernameExists($pseudo)) {
                $_SESSION['error_message'] = 'Ce pseudo est déjà pris, veuillez en choisir un autre.';
                $view->render();
                return;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $id = $model->registerUser($nom,$prenom,$pseudo, $email, $passwordHash);
//            if ($model->registerRole($id,$role)) {
                $_SESSION['success_message'] = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
                $view->render();
                header('Refresh:1; url=index.php');
//            } else {
//                $_SESSION['error_message'] = 'Une erreur est survenue lors de l\'inscription.';
//                $view->render();
//            }
            exit;
        } else {
            $view->render();
        }
    }
}

?>