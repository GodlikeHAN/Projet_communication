<header class="center-header">
    <div class="logo-container">
        <a href="index.php" style="text-decoration: none">
            <img src="images/logo.png" alt="Petway Logo" class="logo">
        </a>
    </div>
    <nav class="menu">
        <a href="index.php">Accueil</a>
        <a href="index.php?module=faq">FAQ</a>
        <a href="index.php?module=contact">Contactez-nous</a>
        <a href="index.php?module=cgu">CGU</a>
        <?php
        if (isset($_SESSION['user'])) {
            echo '<div class="profile-dropdown">';
            echo '<div class="profile-info">';
            echo '<img src="uploads/' .($_SESSION['url_photo']). '" alt="Photo de profilpetsitter"  onerror="this.src=\'uploads/default-avatar.png\'" class="profile-pic">';
            echo '<span class="username">' . htmlspecialchars($_SESSION['user']) . '</span>';
            echo '</div>';
            echo '<div class="dropdown-content">';
            echo '<a href="index.php?module=infos">Informations Personnelles</a>';
            echo '<a href="index.php?module=profilpetsitter">Profil</a>';
            if ($_SESSION['role'] === 'Proprio') {
                echo '<a href="index.php?module=formulaireanimal">Vos animaux</a>';
            }
            echo '<a href="index.php?module=messagerie">Messagerie</a>';
            echo '<a href="index.php?module=favoris">Mes favoris</a>';
            echo '<a href="index.php?module=deconnexion" style="background-color: red">Déconnexion</a>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<button onclick="openPopup()" class="bouton-rose">Connexion</button>';
        }
        ?>
    </nav>
</header>