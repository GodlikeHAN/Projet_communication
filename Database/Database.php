<?php
    class Database {
        /**
         * Lien avec la base de données (MySQL) (Utilisation des données dans le fichier .env Config perso)
         * @return bool|mysqli
         */
        public function connect(){
            $host =  getenv("MYSQL_HOST") == false ? 'localhost' : getenv("MYSQL_HOST") ;
            $user = getenv("MYSQL_USER") == false ? 'root' : getenv("MYSQL_USER");
            $pass = getenv("MYSQL_PASSWORD") == false ? '' : getenv("MYSQL_PASSWORD");;
            $db = getenv("MYSQL_DATABASE") == false ? 'project_communication' : getenv("MYSQL_DATABASE");;
            $port = 3306;   
            $connection = mysqli_connect($host, $user, $pass, $db,$port);
            return $connection;
        }
    }