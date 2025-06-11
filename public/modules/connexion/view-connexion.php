<?php
class ConnexionView {
    public function render() {
        // Initialisation de la variable d'erreur
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
        unset($_SESSION['error_message']);
        $activeTab = isset($_SESSION['active_tab']) ? $_SESSION['active_tab'] : 'login';
        unset($_SESSION['active_tab']);
        $sucess_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

        ?>
        <!-- Overlay and Popup -->
        <div class="overlay <?= !empty($error_message) ? 'active' : ''; ?>" id="authPopup">
            <div class="popup">
                <button class="close" onclick="closePopup()" style="color: black">&times;</button>
                <?php if (!empty($error_message)) :?>
                    <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <div class="activetab" style="display:none"><?= htmlspecialchars($activeTab); ?></div>
                <div class="tabs">
                    <div class="tab <?= $activeTab === 'login' ? 'active' : ''; ?>" onclick="showForm('loginForm')">Connexion</div>
                    <div class="tab <?= $activeTab === 'signup' ? 'active' : ''; ?>" onclick="showForm('signupForm')">Inscription</div>
                </div>

                <!-- Login Form -->
                <div class="form-container <?= $activeTab === 'login' ? 'active' : ''; ?>" id="loginForm">
                    <h2>Connexion</h2>
                    <form class="form-width" action="index.php?module=connexion" method="post">
                        <input type="hidden" name="form_type" value="login">
                        <input type="text" name="pseudo" placeholder="Pseudo">
                        <input type="password" name="password" placeholder="Mot de passe">
                        <button type="submit">Se connecter</button><br>
                        <a href="index.php?module=motdepasseoublie" class="forgot-password">Mot de passe oublié ?</a>
                    </form>
                </div>

                <!-- Signup Form -->
                <div class="form-container <?= $activeTab === 'signup' ? 'active' : ''; ?>" id="signupForm">
                    <h2>Inscription</h2>
                    <form class="form-width" action="index.php?module=inscription" method="post">
                        <input type="hidden" name="form_type" value="signup">
                        <input type="text" name="nom" placeholder="Nom">
                        <input type="text" name="prenom" placeholder="Prenom">
                        <input type="text" name="pseudo" placeholder="Pseudo">
                        <input type="email" name="email" placeholder="Email">
                        <input type="text" name="ville" placeholder="Ville">
                        <input type="password" name="password" placeholder="Mot de passe">
                        <input type="password" name="confirm_password" placeholder="Confirmer mot de passe">
                        <select name="role" id="role">
                            <option value="proprio">Propriétaire d'animal</option>
                            <option value="petsitter">Petsitter</option>
                        </select>
                        <input type="file" name="photo" placeholder="Photo">
                        <button type="submit">S'inscrire</button>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <?php displayMessage($sucess_message)?>

        <?php
    }

}
?>