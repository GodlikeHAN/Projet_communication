<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système Chambre Froide</title>
    <link href="Vue/CSS/connection.css" rel="stylesheet">
    <script src="Vue/JS/connection.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="auth-container">


    <div class="auth-form-container">
        <!-- Bouton retour à l'accueil -->
        <a href="/Projet_communication/" class="back-home-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Accueil</span>
        </a>

        <div class="form-connexion">
            <div class="form-header">
                <i class="fas fa-sign-in-alt"></i>
                <p>Accédez au système de surveillance</p>
            </div>

            <form class="form" id="connection-form">
                <div class="input-group">
                    <input name="email" type="email" class="input-user-first" placeholder="Adresse email" required />
                </div>

                <div class="input-group">
                    <input name="password" type="password" class="input-user" placeholder="Mot de passe" required />
                </div>

                <button type="submit" class="button-connection">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>

                <div class="error-message" id="error-message"></div>
                <div class="success-message" id="success-message"></div>
            </form>

            <div class="form-footer">
                <p>Pas encore de compte ?
                    <a href="/Projet_communication/inscription" class="link" style="color: white">S'inscrire</a>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>