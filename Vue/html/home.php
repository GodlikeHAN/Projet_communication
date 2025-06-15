<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chambre Froide - Conservation d'Œuvres d'Art</title>
    <link href="Vue/CSS/home.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<!-- Header -->
<header class="header">
    <nav class="navbar">
        <div class="nav-brand">
            <i class="fas fa-snowflake"></i>
            <span>ColdRoom Monitor</span>
        </div>
        <div class="nav-links">
            <a href="/Projet_communication/connection" class="nav-link">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
            <a href="/Projet_communication/inscription" class="nav-link nav-cta">
                <i class="fas fa-user-plus"></i> S'inscrire
            </a>
        </div>
    </nav>
</header>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-background">
        <div class="animated-elements">
            <i class="fas fa-snowflake snow-1"></i>
            <i class="fas fa-snowflake snow-2"></i>
            <i class="fas fa-snowflake snow-3"></i>
            <i class="fas fa-snowflake snow-4"></i>
            <i class="fas fa-snowflake snow-5"></i>
        </div>
    </div>

    <div class="hero-content">
        <div class="hero-text">
            <h1>Système de Surveillance<br><span class="highlight">Chambre Froide</span></h1>
            <p class="hero-subtitle">Protection et conservation des œuvres d'art grâce à une surveillance intelligente par capteurs de proximité et alertes sonores</p>

            <div class="hero-stats">
                <div class="stat-item">
                    <i class="fas fa-shield-alt"></i>
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Surveillance</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-thermometer-half"></i>
                    <span class="stat-number">-5°C</span>
                    <span class="stat-label">Température optimale</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-palette"></i>
                    <span class="stat-number">100+</span>
                    <span class="stat-label">Œuvres protégées</span>
                </div>
            </div>

            <div class="hero-actions">
                <a href="/Projet_communication/connection" class="btn-primary">
                    <i class="fas fa-play"></i> Accéder au système
                </a>
                <a href="#features" class="btn-secondary">
                    <i class="fas fa-info-circle"></i> En savoir plus
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="cold-room-container">
                <div class="cold-room">
                    <div class="room-interior">
                        <div class="artwork-display">
                            <i class="fas fa-palette artwork-1"></i>
                            <i class="fas fa-image artwork-2"></i>
                            <i class="fas fa-paint-brush artwork-3"></i>
                        </div>
                        <div class="sensor-indicator">
                            <div class="sensor-pulse"></div>
                            <span>Capteur proximité</span>
                        </div>
                    </div>
                    <div class="temperature-display">
                        <i class="fas fa-thermometer-half"></i>
                        <span>-2.5°C</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features">
    <div class="container">
        <div class="section-header">
            <h2>Fonctionnalités du Système</h2>
            <p>Une solution complète pour la protection des œuvres d'art</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
                <h3>Capteur de Proximité</h3>
                <p>Détection précise des mouvements et intrusions avec alertes automatiques en temps réel</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Portée jusqu'à 100cm</li>
                    <li><i class="fas fa-check"></i> Précision au centimètre</li>
                    <li><i class="fas fa-check"></i> Seuils configurables</li>
                </ul>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-volume-up"></i>
                </div>
                <h3>Alerte Sonore</h3>
                <p>Système de buzzer intelligent avec déclenchement automatique et contrôle manuel</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Activation automatique</li>
                    <li><i class="fas fa-check"></i> Contrôle à distance</li>
                    <li><i class="fas fa-check"></i> Niveaux d'alerte</li>
                </ul>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Monitoring Temps Réel</h3>
                <p>Interface de surveillance avancée avec graphiques et historiques des données</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Dashboard interactif</li>
                    <li><i class="fas fa-check"></i> Graphiques dynamiques</li>
                    <li><i class="fas fa-check"></i> Exportation des données</li>
                </ul>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-snowflake"></i>
                </div>
                <h3>Contrôle Environnemental</h3>
                <p>Surveillance des conditions de conservation optimales pour les œuvres d'art</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Température constante</li>
                    <li><i class="fas fa-check"></i> Humidité contrôlée</li>
                    <li><i class="fas fa-check"></i> Alertes préventives</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section class="technology">
    <div class="container">
        <div class="section-header">
            <h2>Technologies Utilisées</h2>
            <p>Un projet ISEP combinant électronique et informatique</p>
        </div>

        <div class="tech-grid">
            <div class="tech-category">
                <h3><i class="fas fa-microchip"></i> Électronique</h3>
                <div class="tech-items">
                    <div class="tech-item">
                        <i class="fas fa-broadcast-tower"></i>
                        <span>Capteur Ultrasonique</span>
                    </div>
                    <div class="tech-item">
                        <i class="fas fa-volume-up"></i>
                        <span>Buzzer Piézoélectrique</span>
                    </div>
                    <div class="tech-item">
                        <i class="fas fa-microchip"></i>
                        <span>Microcontrôleur TIVA</span>
                    </div>
                    <div class="tech-item">
                        <i class="fas fa-usb"></i>
                        <span>Communication Série</span>
                    </div>
                </div>
            </div>

            <div class="tech-category">
                <h3><i class="fas fa-code"></i> Informatique</h3>
                <div class="tech-items">
                    <div class="tech-item">
                        <i class="fab fa-php"></i>
                        <span>PHP Backend</span>
                    </div>
                    <div class="tech-item">
                        <i class="fas fa-database"></i>
                        <span>MySQL Database</span>
                    </div>
                    <div class="tech-item">
                        <i class="fab fa-js"></i>
                        <span>JavaScript Frontend</span>
                    </div>
                    <div class="tech-item">
                        <i class="fab fa-html5"></i>
                        <span>HTML5 & CSS3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team">
    <div class="container">
        <div class="section-header">
            <h2>Notre Équipe</h2>
            <p>Étudiants ISEP - Projet Numérique 2025</p>
        </div>

        <div class="team-info">
            <div class="project-details">
                <h3>Projet Commun - Chambre Froide</h3>
                <p>Dans le cadre du projet commun ISEP, notre équipe a développé un système de surveillance pour chambre froide destinée à la conservation d'œuvres d'art dans les musées.</p>

                <div class="project-specs">
                    <div class="spec-item">
                        <strong>Cas d'usage :</strong> Conservation d'œuvres d'art
                    </div>
                    <div class="spec-item">
                        <strong>Capteur :</strong> Proximité ultrasonique
                    </div>
                    <div class="spec-item">
                        <strong>Actionneur :</strong> Buzzer d'alerte
                    </div>
                    <div class="spec-item">
                        <strong>Durée :</strong> 2 semaines intensives
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-brand">
                    <i class="fas fa-snowflake"></i>
                    <span>ColdRoom Monitor</span>
                </div>
                <p>Système de surveillance intelligent pour la conservation d'œuvres d'art.</p>
            </div>

            <div class="footer-section">
                <h4>Projet</h4>
                <ul>
                    <li>ISEP 2025</li>
                    <li>Projet Numérique</li>
                    <li>Équipe Capteur Proximité</li>
                    <li>Open Source</li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Technologies</h4>
                <ul>
                    <li>PHP & MySQL</li>
                    <li>JavaScript</li>
                    <li>Microcontrôleur TIVA</li>
                    <li>Capteurs IoT</li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Contact</h4>
                <ul>
                    <li><i class="fas fa-envelope"></i> contact@isep.fr</li>
                    <li><i class="fas fa-phone"></i> +33 1 49 54 52 00</li>
                    <li><i class="fas fa-map-marker-alt"></i> ISEP Paris</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 ISEP - Projet Numérique. Tous droits réservés.</p>
        </div>
    </div>
</footer>
</body>
</html>