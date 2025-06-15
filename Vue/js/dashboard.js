// Dashboard JavaScript pour le système de chambre froide
class ColdRoomDashboard {
    constructor() {
        this.proximityThreshold = 20; // Seuil d'alerte en cm
        this.updateInterval = 2000; // Mise à jour toutes les 2 secondes
        this.chart = null;
        this.isAlertActive = false;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.startDataUpdates();
        this.initChart();
        this.loadInitialData();
    }

    setupEventListeners() {
        // Contrôles du buzzer
        document.getElementById('buzzer-on')?.addEventListener('click', () => {
            this.controlBuzzer('on');
        });

        document.getElementById('buzzer-off')?.addEventListener('click', () => {
            this.controlBuzzer('off');
        });

        // Modal de fermeture
        document.querySelector('.close')?.addEventListener('click', () => {
            this.closeModal();
        });

        document.getElementById('acknowledge-alert')?.addEventListener('click', () => {
            this.acknowledgeAlert();
        });

        // Fermeture modal en cliquant à l'extérieur
        window.addEventListener('click', (event) => {
            const modal = document.getElementById('alert-modal');
            if (event.target === modal) {
                this.closeModal();
            }
        });
    }

    async loadInitialData() {
        await this.updateSensorData();
        await this.updateAlerts();
    }

    startDataUpdates() {
        // Mise à jour périodique des données
        setInterval(() => {
            this.updateSensorData();
            this.updateAlerts();
        }, this.updateInterval);

        // Simulation de données du capteur de proximité (remplacer par vraies données)
        this.simulateProximityData();
    }

    async updateSensorData() {
        try {
            const response = await fetch('/Projet_communication/api/sensor-data');
            if (response.ok) {
                const data = await response.json();
                this.processSensorData(data);
                this.updateChart(data);
                this.updateDataTable(data);
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des données:', error);
        }
    }

    processSensorData(data) {
        const proximityData = data.filter(item => item.sensor_type === 'proximity');

        if (proximityData.length > 0) {
            const latestData = proximityData[0];
            this.updateProximityDisplay(latestData.value);
        }
    }

    updateProximityDisplay(distance) {
        const distanceElement = document.getElementById('distance-value');
        const statusElement = document.getElementById('sensor-status');
        const proximityStatusElement = document.getElementById('proximity-status');
        const gaugeFill = document.getElementById('gauge-fill');

        if (distanceElement) {
            distanceElement.textContent = distance.toFixed(1);
        }

        if (proximityStatusElement) {
            proximityStatusElement.textContent = `${distance.toFixed(1)} cm`;
        }

        // Mise à jour du statut et de la jauge
        let status, statusClass, fillPercentage;

        if (distance < this.proximityThreshold) {
            status = 'ALERTE - Intrusion détectée !';
            statusClass = 'danger';
            fillPercentage = 0;
            this.triggerSecurityAlert(distance);
        } else if (distance < this.proximityThreshold * 1.5) {
            status = 'Attention - Proximité détectée';
            statusClass = 'warning';
            fillPercentage = 30;
        } else {
            status = 'Zone sécurisée';
            statusClass = 'normal';
            fillPercentage = Math.min(90, (distance / 100) * 100);
        }

        if (statusElement) {
            statusElement.textContent = status;
            statusElement.className = `sensor-status ${statusClass}`;
        }

        if (gaugeFill) {
            gaugeFill.style.width = `${fillPercentage}%`;
        }

        // Mise à jour du statut de sécurité global
        const securityStatus = document.getElementById('security-status');
        if (securityStatus) {
            securityStatus.textContent = distance < this.proximityThreshold ? 'ALERTE' : 'Normal';
            securityStatus.style.color = distance < this.proximityThreshold ? '#e74c3c' : '#27ae60';
        }
    }

    async controlBuzzer(action) {
        try {
            const response = await fetch('/Projet_communication/api/buzzer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action })
            });

            const result = await response.json();

            if (response.ok) {
                this.updateBuzzerStatus(action);
                this.showNotification(`Buzzer ${action === 'on' ? 'activé' : 'désactivé'}`, 'success');
            } else {
                this.showNotification(result.Error, 'error');
            }
        } catch (error) {
            console.error('Erreur lors du contrôle du buzzer:', error);
            this.showNotification('Erreur de communication', 'error');
        }
    }

    updateBuzzerStatus(action) {
        const buzzerStatus = document.getElementById('buzzer-status');
        if (buzzerStatus) {
            buzzerStatus.textContent = action === 'on' ? 'Activée' : 'Arrêtée';
            buzzerStatus.style.color = action === 'on' ? '#e74c3c' : '#27ae60';
        }
    }

    async updateAlerts() {
        try {
            const response = await fetch('/Projet_communication/api/alerts');
            if (response.ok) {
                const alerts = await response.json();
                this.displayAlerts(alerts);
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des alertes:', error);
        }
    }

    displayAlerts(alerts) {
        const container = document.getElementById('alerts-container');
        if (!container) return;

        if (alerts.length === 0) {
            container.innerHTML = '<div class="no-alerts">Aucune alerte récente</div>';
            return;
        }

        container.innerHTML = alerts.map(alert => `
            <div class="alert-item ${alert.resolved ? 'resolved' : ''}">
                <div class="alert-time">${this.formatDateTime(alert.created_at)}</div>
                <div class="alert-message">${alert.message}</div>
            </div>
        `).join('');
    }

    triggerSecurityAlert(distance) {
        if (this.isAlertActive) return;

        this.isAlertActive = true;

        // Déclencher automatiquement le buzzer
        this.controlBuzzer('on');

        // Afficher la modal d'alerte
        this.showAlertModal(`Intrusion détectée dans la chambre froide ! Distance: ${distance.toFixed(1)}cm`);

        // Enregistrer l'alerte
        this.recordSecurityAlert(distance);

        // Auto-désactiver l'alerte après 30 secondes
        setTimeout(() => {
            this.isAlertActive = false;
        }, 30000);
    }

    async recordSecurityAlert(distance) {
        try {
            await fetch('/Projet_communication/api/proximity-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    distance: distance,
                    location: 'Chambre froide principale'
                })
            });
        } catch (error) {
            console.error('Erreur lors de l\'enregistrement de l\'alerte:', error);
        }
    }

    showAlertModal(message) {
        const modal = document.getElementById('alert-modal');
        const messageElement = document.getElementById('alert-message');

        if (modal && messageElement) {
            messageElement.textContent = message;
            modal.style.display = 'block';
        }
    }

    closeModal() {
        const modal = document.getElementById('alert-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    acknowledgeAlert() {
        this.closeModal();
        this.controlBuzzer('off');
        this.isAlertActive = false;
    }

    updateDataTable(data) {
        const tbody = document.querySelector('#sensor-data-table tbody');
        if (!tbody) return;

        tbody.innerHTML = data.slice(0, 10).map(item => `
            <tr>
                <td>${this.formatDateTime(item.timestamp)}</td>
                <td>${this.getSensorTypeLabel(item.sensor_type)}</td>
                <td>${item.value} ${item.unit || ''}</td>
                <td>${item.location}</td>
            </tr>
        `).join('');
    }

    getSensorTypeLabel(type) {
        const labels = {
            'proximity': 'Proximité',
            'temperature': 'Température',
            'humidity': 'Humidité'
        };
        return labels[type] || type;
    }

    initChart() {
        const ctx = document.getElementById('proximity-chart');
        if (!ctx) return;

        // Si Chart.js est disponible
        if (typeof Chart !== 'undefined') {
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Distance (cm)',
                        data: [],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Distance (cm)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Temps'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
        }
    }

    // Mise à jour périodique des données
    updateChart(data) {
        if (!this.chart) return;

        const proximityData = data.filter(item => item.sensor_type === 'proximity').slice(0, 20);

        const labels = proximityData.map(item => this.formatTime(item.timestamp)).reverse();
        const values = proximityData.map(item => parseFloat(item.value)).reverse();

        this.chart.data.labels = labels;
        this.chart.data.datasets[0].data = values;
        this.chart.update();
    }

    // Chargement de Chart.js si nécessaire
    async loadChartJS() {
        if (typeof Chart === 'undefined') {
            return new Promise((resolve) => {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js';
                script.onload = () => resolve();
                document.head.appendChild(script);
            });
        }
    }

    simulateProximityData() {
        // Simulation de données pour les tests (à remplacer par de vraies données)
        let lastDistance = 45;

        setInterval(() => {
            // Variation aléatoire de distance
            const variation = (Math.random() - 0.5) * 10;
            lastDistance = Math.max(5, Math.min(100, lastDistance + variation));

            // Parfois simulation d'intrusion
            if (Math.random() < 0.05) { // 5% de chance
                lastDistance = Math.random() * 15; // Distance d'intrusion
            }

            this.updateProximityDisplay(lastDistance);
        }, 3000);
    }

    showNotification(message, type = 'info') {
        // Créer une notification temporaire
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1001;
            opacity: 0;
            transition: opacity 0.3s;
        `;

        switch (type) {
            case 'success':
                notification.style.background = '#27ae60';
                break;
            case 'error':
                notification.style.background = '#e74c3c';
                break;
            default:
                notification.style.background = '#3498db';
        }

        document.body.appendChild(notification);

        // Animation d'apparition
        setTimeout(() => {
            notification.style.opacity = '1';
        }, 100);

        // Suppression automatique après 3 secondes
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    formatDateTime(timestamp) {
        return new Date(timestamp).toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatTime(timestamp) {
        return new Date(timestamp).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    new ColdRoomDashboard();
});

// Gestion des erreurs globales
window.addEventListener('error', (event) => {
    console.error('Erreur JavaScript:', event.error);
});