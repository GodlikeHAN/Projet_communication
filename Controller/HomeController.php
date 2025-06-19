<?php
class HomeController {

    /**
     * Page d'accueil
     */
    public function home($database) {
        session_start();
        if (isset($_SESSION['user_id'])) {
            header('Location: /Projet_communication/dashboard');
            exit();
        } else {
            include 'Vue/html/home.php';
        }
    }
}