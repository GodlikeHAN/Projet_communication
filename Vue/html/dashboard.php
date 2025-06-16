<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surveillance Chambre Froide - Musée</title>
    <link href="Vue/css/dashboard.css" rel="stylesheet">
    <script src="Vue/js/dashboard.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <h1><i class="fas fa-snowflake"></i> Chambre Froide - Conservation d'Œuvres d'Art</h1>
            <div class="user-info">
                <span>Bienvenue, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="/Projet_communication/deconnexion" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </header>

    <!-- Status Bar -->
    <div class="status-bar">
        <div class="status-item">
            <i class="fas fa-shield-alt"></i>
            <span class="status-label">Sécurité</span>
            <span class="status-value" id="security-status">Normal</span>
        </div>
        <div class="status-item">
            <i class="fas fa-thermometer-half"></i>
            <span class="status-label">Température</span>
            <span class="status-value" id="temperature-status">-2.5°C</span>
        </div>
        <div class="status-item">
            <i class="fas fa-eye"></i>
            <span class="status-label">Proximité</span>
            <span class="status-value" id="proximity-status">45 cm</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Sensor Controls -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-broadcast-tower"></i> Capteur de Proximité</h2>
            </div>
            <div class="card-content">
                <div class="sensor-display">
                    <div class="sensor-value">
                        <span id="distance-value">--</span>
                        <span class="unit">cm</span>
                    </div>
                    <div class="sensor-status" id="sensor-status">En attente...</div>
                </div>
                <div class="sensor-visual">
                    <div class="proximity-gauge">
                        <div class="gauge-bg"></div>
                        <div class="gauge-fill" id="gauge-fill"></div>
                        <div class="gauge-text">Zone de sécurité</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buzzer Controls -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-volume-up"></i> Contrôle du Buzzer</h2>
            </div>
            <div class="card-content">
                <div class="buzzer-controls">
                    <button id="buzzer-on" class="btn btn-danger">
                        <i class="fas fa-play"></i> Activer Alarme
                    </button>
                    <button id="buzzer-off" class="btn btn-success">
                        <i class="fas fa-stop"></i> Arrêter Alarme
                    </button>
                </div>
                <div class="buzzer-status">
                    <span id="buzzer-status">Arrêtée</span>
                </div>
            </div>
        </div>

        <!-- Alerts Panel -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Alertes Récentes</h2>
            </div>
            <div class="card-content">
                <div id="alerts-container">
                    <div class="no-alerts">Aucune alerte récente</div>
                </div>
            </div>
        </div>

        <!-- Data History -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Historique des Données</h2>
            </div>
            <div class="card-content">
                <div class="data-table">
                    <table id="sensor-data-table">
                        <thead>
                        <tr>
                            <th>Heure</th>
                            <th>Type</th>
                            <th>Valeur</th>
                            <th>Localisation</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- Données chargées dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Artworks Monitoring -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-palette"></i> Œuvres Surveillées</h2>
            </div>
            <div class="card-content">
                <div class="artworks-grid">
                    <div class="artwork-item">
                        <h3>La Joconde (Reproduction)</h3>
                        <p>Zone de sécurité: 30 cm</p>
                        <span class="status-badge safe">Sécurisée</span>
                    </div>
                    <div class="artwork-item">
                        <h3>Les Tournesols (Reproduction)</h3>
                        <p>Zone de sécurité: 25 cm</p>
                        <span class="status-badge safe">Sécurisée</span>
                    </div>
                    <div class="artwork-item">
                        <h3>La Nuit étoilée (Reproduction)</h3>
                        <p>Zone de sécurité: 35 cm</p>
                        <span class="status-badge safe">Sécurisée</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>Système de surveillance de chambre froide - ISEP 2025</p>
        <p>Équipe: Capteur de Proximité & Buzzer</p>
    </footer>
</div>

<!-- Alert Modal -->
<div id="alert-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Alerte de Sécurité</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p id="alert-message"></p>
        </div>
        <div class="modal-footer">
            <button id="acknowledge-alert" class="btn btn-primary">Acquitter</button>
        </div>
    </div>
</div>
</body>
</html>