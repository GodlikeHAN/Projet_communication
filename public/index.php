<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset= "UTF-8"/>
    <title>Projet Commun</title>
    <link rel="stylesheet" href="css+js/style.css">
    <script src="css+js/script.js"></script>
</head>
<body>
<?php
session_start();
include_once "header.php";

if (isset($_GET['module'])) {
    $module = $_GET['module'];
    switch ($module) {
        case 'inscription':
            include_once "modules/inscription/cont-inscription.php";
            $controller = new InscriptionController();
            $controller->handle();
            break;
        case 'connexion':
            include_once "modules/connexion/cont-connexion.php";
            $controller = new ConnexionController();
            $controller->handle();
            break;
        case 'deconnexion':
            include_once "modules/deconnexion/cont-deconnexion.php";
            $controller = new DeconnexionController();
            $controller->handle();
            break;
    }
}
function displayMessage($message) {
    if (!empty($message)) {
        echo "<div class=\"bloc_table_white\">$message</div>";
    }
}
?>

</body>
</html>