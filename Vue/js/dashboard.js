// Dashboard JavaScript pour le système de chambre froide avec tous les capteurs
class ColdRoomDashboard {
    constructor() {
        this.distanceApi = '/Projet_communication/api/distance-data';
        this.temperatureApi = '/Projet_communication/api/temperature-data';
        this.lightApi = '/Projet_communication/api/light-data';
        this.humidityApi = '/Projet_communication/api/humidity-data';
        this.proximityThreshold = 20; // Seuil d'alerte en cm
        this.updateInterval = 2000; // Mise à jour toutes les 2 secondes
        this.maxAgeSec = 10000000000000000000000000000;
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

        document.getElementById('buzzer-auto')?.addEventListener('click', () => {
            this.controlBuzzer('auto');
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
        await this.updateTemperatureData();
        await this.updateLightData();
        await this.updateHumidityData();
        await this.updateAlerts();

        try {
            const r = await fetch('/Projet_communication/api/buzzer-status');
            if (r.ok) {
                const { action } = await r.json();
                this.updateBuzzerStatus(action);
            }
        } catch (e) { console.error('buzzer-status err', e); }
    }

    startDataUpdates() {
        // Mise à jour périodique des données
        setInterval(() => {
            this.updateSensorData();
            this.updateTemperatureData();
            this.updateLightData();
            this.updateHumidityData();
            this.updateAlerts();
        }, this.updateInterval);
    }

    async updateSensorData() {
        try {
            const r = await fetch(this.distanceApi);
            if (!r.ok) throw new Error('HTTP ' + r.status);

            const data = await r.json();

            if (data.length === 0) {
                this.updateProximityDisplay(null);
                this.updateDataTable([]);
                return;
            }
            const latest = data[0];
            const localIso = latest.timestamp.replace(' ', 'T');
            const ageSec   = (Date.now() - Date.parse(localIso)) / 1000;
            const distance= (ageSec <= this.maxAgeSec) ? parseFloat(latest.value): null;
            this.updateProximityDisplay(distance);

            const chartData = data.slice(0, 20).reverse();
            const labels = chartData.map(x => this.formatTime(x.timestamp));
            const values = chartData.map(x => parseFloat(x.value));

            if (this.chart) {
                this.chart.data.labels = labels;
                this.chart.data.datasets[0].data = values;
                this.chart.update();
            }

            this.updateDataTable(
                data.slice(0, 10).map(x => ({
                    timestamp: x.timestamp,
                    sensor_type: 'proximity',
                    value: x.value,
                    location: 'Chambre froide'
                }))
            );
        } catch (e) {
            console.error('Distance fetch error:', e);
        }
    }

    async updateTemperatureData() {
        try {
            const r = await fetch(this.temperatureApi);
            if (!r.ok) throw new Error('HTTP ' + r.status);

            const data = await r.json();

            if (data.length === 0) {
                this.updateTemperatureDisplay(null);
                return;
            }

            const latest = data[0];
            const localIso = latest.timestamp.replace(' ', 'T');
            const ageSec = (Date.now() - Date.parse(localIso)) / 1000;
            const temperature = (ageSec <= this.maxAgeSec) ? parseFloat(latest.value) : null;

            this.updateTemperatureDisplay(temperature);

        } catch (e) {
            console.error('Temperature fetch error:', e);
            this.updateTemperatureDisplay(null);
        }
    }

    async updateLightData() {
        try {
            const r = await fetch(this.lightApi);
            if (!r.ok) throw new Error('HTTP ' + r.status);

            const data = await r.json();

            if (data.length === 0) {
                this.updateLightDisplay(null);
                return;
            }

            const latest = data[0];
            const localIso = latest.timestamp.replace(' ', 'T');
            const ageSec = (Date.now() - Date.parse(localIso)) / 1000;
            const lightLevel = (ageSec <= this.maxAgeSec) ? parseFloat(latest.value) : null;

            this.updateLightDisplay(lightLevel);

        } catch (e) {
            console.error('Light fetch error:', e);
            this.updateLightDisplay(null);
        }
    }

    async updateHumidityData() {
        try {
            const r = await fetch(this.humidityApi);
            if (!r.ok) throw new Error('HTTP ' + r.status);

            const data = await r.json();

            if (data.length === 0) {
                this.updateHumidityDisplay(null);
                return;
            }

            const latest = data[0];
            const localIso = latest.timestamp.replace(' ', 'T');
            const ageSec = (Date.now() - Date.parse(localIso)) / 1000;
            const humidity = (ageSec <= this.maxAgeSec) ? parseFloat(latest.value) : null;

            this.updateHumidityDisplay(humidity);

        } catch (e) {
            console.error('Humidity fetch error:', e);
            this.updateHumidityDisplay(null);
        }
    }

    updateTemperatureDisplay(temperature) {
        const tempElement = document.getElementById('temperature-status');

        if (temperature === null || typeof temperature === 'undefined') {
            if (tempElement) {
                tempElement.textContent = '--°C';
                tempElement.style.color = '#bdc3c7';
            }
            return;
        }

        if (tempElement) {
            tempElement.textContent = `${temperature.toFixed(1)}°C`;

            // Changement de couleur selon la température (pour une chambre froide)
            if (temperature < -10 || temperature > 5) {
                tempElement.style.color = '#e74c3c'; // Rouge si température anormale
            } else if (temperature < -5 && temperature > 0) {
                tempElement.style.color = '#f39c12'; // Orange si température limite
            } else {
                tempElement.style.color = '#27ae60'; // Vert si température normale
            }
        }
    }

    updateLightDisplay(lightLevel) {
        const lightElement = document.getElementById('light-status');
        const lightValueElement = document.getElementById('light-value');
        const lightStatusElement = document.getElementById('light-sensor-status');
        const lightGaugeFill = document.getElementById('light-gauge-fill');

        if (lightLevel === null || typeof lightLevel === 'undefined') {
            if (lightElement) {
                lightElement.textContent = '-- lux';
                lightElement.style.color = '#bdc3c7';
            }
            if (lightValueElement) lightValueElement.textContent = '--';
            if (lightStatusElement) {
                lightStatusElement.textContent = 'Capteur hors ligne';
                lightStatusElement.className = 'sensor-status light-offline';
            }
            if (lightGaugeFill) lightGaugeFill.style.width = '0%';
            return;
        }

        // Mise à jour des valeurs
        if (lightElement) {
            lightElement.textContent = `${Math.round(lightLevel)} lux`;
        }
        if (lightValueElement) {
            lightValueElement.textContent = Math.round(lightLevel);
        }

        // Détermination du statut et de la couleur selon le niveau de lumière
        let status = '';
        let statusClass = '';
        let fillPercent = 0;
        let color = '';

        if (lightLevel < 10) {
            status = 'Très sombre';
            statusClass = 'dark';
            fillPercent = 95;
            color = '#2c3e50';
        } else if (lightLevel < 100) {
            status = 'Faible éclairage';
            statusClass = 'dim';
            fillPercent = 70;
            color = '#f39c12';
        } else if (lightLevel < 500) {
            status = 'Éclairage normal';
            statusClass = 'normal';
            fillPercent = 40;
            color = '#27ae60';
        } else if (lightLevel < 1000) {
            status = 'Bien éclairé';
            statusClass = 'bright';
            fillPercent = 20;
            color = '#f1c40f';
        } else {
            status = 'Très lumineux';
            statusClass = 'very-bright';
            fillPercent = 5;
            color = '#ffffff';
        }

        // Mise à jour de l'affichage
        if (lightStatusElement) {
            lightStatusElement.textContent = status;
            lightStatusElement.className = `sensor-status ${statusClass}`;
        }
        if (lightGaugeFill) {
            lightGaugeFill.style.width = `${fillPercent}%`;
        }
        if (lightElement) {
            lightElement.style.color = color;
        }
    }

    updateHumidityDisplay(humidity) {
        const humidityElement = document.getElementById('humidity-status');
        const humidityValueElement = document.getElementById('humidity-value');
        const humidityStatusElement = document.getElementById('humidity-sensor-status');
        const humidityGaugeFill = document.getElementById('humidity-gauge-fill');

        if (humidity === null || typeof humidity === 'undefined') {
            if (humidityElement) {
                humidityElement.textContent = '--%';
                humidityElement.style.color = '#bdc3c7';
            }
            if (humidityValueElement) humidityValueElement.textContent = '--';
            if (humidityStatusElement) {
                humidityStatusElement.textContent = 'Capteur hors ligne';
                humidityStatusElement.className = 'sensor-status humidity-offline';
            }
            if (humidityGaugeFill) humidityGaugeFill.style.width = '100%';
            return;
        }

        // Limiter les valeurs entre 0 et 100%
        humidity = Math.max(0, Math.min(100, humidity));

        // Mise à jour des valeurs
        if (humidityElement) {
            humidityElement.textContent = `${Math.round(humidity)}%`;
        }
        if (humidityValueElement) {
            humidityValueElement.textContent = Math.round(humidity);
        }

        // Détermination du statut et de la couleur selon le niveau d'humidité
        let status = '';
        let statusClass = '';
        let fillPercent = 0;
        let color = '';

        if (humidity < 20) {
            status = 'Très sec';
            statusClass = 'very-dry';
            fillPercent = 100 - (humidity / 20) * 25; // 75-100%
            color = '#e67e22';
        } else if (humidity < 40) {
            status = 'Sec';
            statusClass = 'dry';
            fillPercent = 100 - (humidity / 40) * 50; // 50-75%
            color = '#f39c12';
        } else if (humidity <= 60) {
            status = 'Optimal';
            statusClass = 'optimal';
            fillPercent = 100 - (humidity / 60) * 75; // 25-50%
            color = '#27ae60';
        } else if (humidity <= 80) {
            status = 'Humide';
            statusClass = 'humid';
            fillPercent = 100 - (humidity / 80) * 90; // 10-25%
            color = '#3498db';
        } else {
            status = 'Très humide';
            statusClass = 'very-humid';
            fillPercent = 100 - (humidity / 100) * 100; // 0-10%
            color = '#9b59b6';
        }

        // Mise à jour de l'affichage
        if (humidityStatusElement) {
            humidityStatusElement.textContent = status;
            humidityStatusElement.className = `sensor-status ${statusClass}`;
        }
        if (humidityGaugeFill) {
            humidityGaugeFill.style.width = `${fillPercent}%`;
        }
        if (humidityElement) {
            humidityElement.style.color = color;
        }

        // Animation spéciale pour humidité élevée
        const statusItem = humidityElement?.closest('.status-item');
        if (humidity > 70) {
            statusItem?.classList.add('humidity-high');
        } else {
            statusItem?.classList.remove('humidity-high');
        }
    }

    updateProximityDisplay(distance) {
        // -- Récupération des éléments du DOM --
        const distanceElement   = document.getElementById('distance-value');
        const statusElement     = document.getElementById('sensor-status');
        const proxElement       = document.getElementById('proximity-status');
        const gaugeFill         = document.getElementById('gauge-fill');
        const securityStatus    = document.getElementById('security-status');

        /* ---------- Cas 1 : pas de valeur (capteur déconnecté) ---------- */
        if (distance === null || typeof distance === 'undefined') {
            if (distanceElement) distanceElement.textContent = '--';
            if (proxElement)     proxElement.textContent     = '--';
            if (statusElement) {
                statusElement.textContent = 'Capteur hors ligne';
                statusElement.className   = 'sensor-status offline';   // pensez à définir ce style en CSS
            }
            if (gaugeFill) gaugeFill.style.width = '0%';
            if (securityStatus) {
                securityStatus.textContent = 'Indéfini';
                securityStatus.style.color = '#bdc3c7';
            }
            return;   // on ne continue pas plus loin
        }

        /* ---------- Cas 2 : on a bien une distance ---------- */
        // 1) Affichage brut
        if (distanceElement) distanceElement.textContent = distance.toFixed(1);
        if (proxElement)     proxElement.textContent   = `${distance.toFixed(1)} cm`;

        // 2) Calcul du statut et de la jauge
        let status       = 'Zone sécurisée';
        let statusClass  = 'normal';
        let fillPercent  = Math.min(90, (distance / 100) * 100);

        if (distance < this.proximityThreshold) {
            status      = 'ALERTE - Intrusion détectée !';
            statusClass = 'danger';
            fillPercent = 0;
            this.triggerSecurityAlert(distance);
        } else if (distance < this.proximityThreshold * 1.5) {
            status      = 'Attention - Proximité détectée';
            statusClass = 'warning';
            fillPercent = 30;
        }

        if (statusElement) {
            statusElement.textContent = status;
            statusElement.className   = `sensor-status ${statusClass}`;
        }
        if (gaugeFill) gaugeFill.style.width = `${fillPercent}%`;

        // 3) Statut global
        if (securityStatus) {
            securityStatus.textContent = (distance < this.proximityThreshold) ? 'ALERTE' : 'Normal';
            securityStatus.style.color = (distance < this.proximityThreshold) ? '#e74c3c' : '#27ae60';
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
        if (!buzzerStatus) return;

        switch (action) {
            case 'on':
                buzzerStatus.textContent = 'Activée';
                buzzerStatus.style.color = '#e74c3c';
                break;
            case 'off':
                buzzerStatus.textContent = 'Arrêtée';
                buzzerStatus.style.color = '#27ae60';
                break;
            case 'auto':
                buzzerStatus.textContent = 'Auto';
                buzzerStatus.style.color = '#3498db';
                break;
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
            'humidity': 'Humidité',
            'light': 'Luminosité'
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

    formatDateTime(ts) {
        return new Date(ts).toLocaleString('fr-FR', {
            day:    '2-digit',
            month:  '2-digit',
            year:   'numeric',
            hour:   '2-digit',
            minute: '2-digit',
            timeZone: 'Europe/Paris'
        });
    }

    formatTime(ts) {
        return new Date(ts).toLocaleTimeString('fr-FR', {
            hour:   '2-digit',
            minute: '2-digit',
            timeZone: 'Europe/Paris'
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