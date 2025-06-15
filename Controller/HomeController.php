<?php
class HomeController {

    /**
     * Page d'accueil
     */
    public function home($database) {
        session_start();
        if (isset($_SESSION['user_id'])) {
            // Utilisateur connecté, rediriger vers le dashboard
            header('Location: /Projet_communication/dashboard');
            exit();
        } else {
            // Utilisateur non connecté, afficher la page d'accueil
            include 'Vue/html/home.php';
        }
    }
}