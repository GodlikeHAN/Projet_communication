<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Projet Communication</title>
    <link href="Vue/css/connection.css" rel="stylesheet">
    <script src="Vue/js/connection.js" defer></script>
</head>

<body>
<!-- Simple connection form (your original design) -->
<div class="form-connexion">
    <p class="Title-connexion">Connectez-vous</p>
    <form class="form" id="simpleLoginForm">
        <input name="email" type="email" class="input-user-first" placeholder="Entrez votre email" required />
        <input name="password" type="password" class="input-user" placeholder="Mot de passe" required />
        <button type="submit" class="button-connection submit-button">Se connecter</button>
        <p class="error-message" id="simpleLoginError"></p>
        <p class="success-message" id="simpleLoginSuccess"></p>
    </form>
</div>

</body>

</html>