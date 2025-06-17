<?php
    class SharedDatabase {
            public function connect() {
                $host = 'bddprojetcommun.mysql.database.azure.com';
                $user = 'adminprojet';
                $password = '9UVxldpsUF&4';
                $dbname = 'projetcommun';
                $port = 3306;

                $conn = new mysqli($host, $user, $password, $dbname, $port);
                return $conn->connect_error ? null : $conn;
            }
        }
