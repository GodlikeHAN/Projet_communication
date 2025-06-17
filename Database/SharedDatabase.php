<?php
class SharedDatabase {
    /**
     * Connexion à la base de données commune partagée entre toutes les équipes
     * @return bool|mysqli
     */
    public function connect(){
        // Configuration pour la base de données commune
        $host = getenv("SHARED_DB_HOST") ?: 'bddprojetcommun.mysql.database.azure.com';
        $user = getenv("SHARED_DB_USER") ?: 'adminprojet';
        $pass = getenv("SHARED_DB_PASSWORD") ?: '9UVxldpsUF&4';
        $db = getenv("SHARED_DB_NAME") ?: 'projetcommun';
        $port = 3306;

        $connection = mysqli_connect($host, $user, $pass, $db, $port);

        if (!$connection) {
            error_log("Erreur connexion DB partagée: " . mysqli_connect_error());
            return false;
        }

        // Définir le charset pour éviter les problèmes d'encodage
        mysqli_set_charset($connection, 'utf8mb4');

        return $connection;
    }
}