<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Système Chambre Froide</title>
    <link href="Vue/CSS/connection.css" rel="stylesheet">
    <script src="Vue/JS/inscription.js" defer></script>
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

        <div class="form-inscription">
            <div class="form-header">
                <i class="fas fa-user-plus"></i>
                <h1>Créer un compte</h1>
                <p>Système de surveillance de chambre froide</p>
            </div>

            <form class="form" id="inscription-form">
                <div class="input-group">
                    <input name="name" type="text" class="input-user" placeholder="Nom complet" required />
                </div>

                <div class="input-group">
                    <input name="email" type="email" class="input-user" placeholder="Adresse email" required />
                </div>

                <div class="input-group">
                    <input name="password" type="password" class="input-user" placeholder="Mot de passe" required />
                </div>

                <div class="input-group">
                    <input name="confirm-password" type="password" class="input-user" placeholder="Confirmer le mot de passe" required />
                </div>

                <button type="submit" class="button-connection">
                    <i class="fas fa-user-plus"></i>
                    S'inscrire
                </button>

                <div class="error-message" id="error-message"></div>
                <div class="success-message" id="success-message"></div>
            </form>

            <div class="form-footer">
                <p>Déjà un compte ?
                    <a href="/Projet_communication/connection" class="link" style="text-decoration: none;color: white">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>