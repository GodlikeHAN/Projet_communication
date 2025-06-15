// Gestion de la page d'accueil
document.addEventListener('DOMContentLoaded', function() {
    // Charger les statistiques
    loadUserStats();

    // Charger les messages récents
    loadRecentMessages();

    // Charger les contacts récents
    loadRecentContacts();
});

// Gestion du menu utilisateur
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Fermer le menu utilisateur si on clique ailleurs
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-dropdown');
    const dropdown = document.getElementById('userDropdown');

    if (!userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Actions rapides
function openNewMessage() {
    const modal = document.getElementById('newMessageModal');
    modal.classList.add('show');
    loadContactsForMessage();
}

function openAddContact() {
    const modal = document.getElementById('addContactModal');
    modal.classList.add('show');
}

function viewConversations() {
    window.location.href = '/Projet_communication/messages';
}

function openSettings() {
    window.location.href = '/Projet_communication/profile';
}

// Gestion des modals
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
}

// Fermer les modals en cliquant sur l'overlay
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal') && event.target.classList.contains('show')) {
        event.target.classList.remove('show');
    }
});

// Gestion du formulaire de nouveau message
document.getElementById('newMessageForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const messageData = {
        recipient: formData.get('recipient'),
        subject: formData.get('subject'),
        message: formData.get('message')
    };

    try {
        const response = await fetch('/Projet_communication/api/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(messageData)
        });

        if (response.ok) {
            const result = await response.json();
            showNotification('Message envoyé avec succès !', 'success');
            closeModal('newMessageModal');
            this.reset();
            loadRecentMessages(); // Recharger les messages
        } else {
            const error = await response.json();
            showNotification(error.message || 'Erreur lors de l\'envoi du message', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
});

// Gestion du formulaire d'ajout de contact
document.getElementById('addContactForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const contactData = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone')
    };

    try {
        const response = await fetch('/Projet_communication/api/contacts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(contactData)
        });

        if (response.ok) {
            const result = await response.json();
            showNotification('Contact ajouté avec succès !', 'success');
            closeModal('addContactModal');
            this.reset();
            loadRecentContacts(); // Recharger les contacts
        } else {
            const error = await response.json();
            showNotification(error.message || 'Erreur lors de l\'ajout du contact', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
});

// Charger les statistiques utilisateur
async function loadUserStats() {
    try {
        const response = await fetch('/Projet_communication/api/stats');
        if (response.ok) {
            const stats = await response.json();
            updateStatsDisplay(stats);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des statistiques:', error);
    }
}

// Mettre à jour l'affichage des statistiques
function updateStatsDisplay(stats) {
    const statNumbers = document.querySelectorAll('.stat-number');
    if (stats.unreadMessages !== undefined) statNumbers[0].textContent = stats.unreadMessages;
    if (stats.totalContacts !== undefined) statNumbers[1].textContent = stats.totalContacts;
    if (stats.totalConversations !== undefined) statNumbers[2].textContent = stats.totalConversations;
}

// Charger les messages récents
async function loadRecentMessages() {
    try {
        const response = await fetch('/Projet_communication/api/messages/recent');
        if (response.ok) {
            const messages = await response.json();
            displayRecentMessages(messages);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des messages:', error);
    }
}

// Afficher les messages récents
function displayRecentMessages(messages) {
    const messagesList = document.querySelector('.recent-messages .messages-list');

    if (messages.length === 0) {
        messagesList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>Aucun message récent</p>
                <p class="empty-subtitle">Vos conversations apparaîtront ici</p>
            </div>
        `;
        return;
    }

    messagesList.innerHTML = messages.map(message => `
        <div class="message-item" onclick="openMessage(${message.id})">
            <div class="message-avatar">
                ${message.sender_photo ?
        `<img src="${message.sender_photo}" alt="${message.sender_name}">` :
        `<div class="avatar-placeholder">${message.sender_name.charAt(0).toUpperCase()}</div>`
    }
            </div>
            <div class="message-content">
                <div class="message-header">
                    <span class="sender-name">${message.sender_name}</span>
                    <span class="message-time">${formatTime(message.created_at)}</span>
                </div>
                <div class="message-subject">${message.subject}</div>
                <div class="message-preview">${message.content.substring(0, 100)}...</div>
            </div>
            ${message.unread ? '<div class="unread-indicator"></div>' : ''}
        </div>
    `).join('');
}

// Charger les contacts récents
async function loadRecentContacts() {
    try {
        const response = await fetch('/Projet_communication/api/contacts/recent');
        if (response.ok) {
            const contacts = await response.json();
            displayRecentContacts(contacts);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des contacts:', error);
    }
}

// Afficher les contacts récents
function displayRecentContacts(contacts) {
    const contactsList = document.querySelector('.recent-contacts .contacts-list');

    if (contacts.length === 0) {
        contactsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p>Aucun contact ajouté</p>
                <p class="empty-subtitle">Commencez par ajouter des contacts</p>
                <button class="action-btn" onclick="openAddContact()">Ajouter un contact</button>
            </div>
        `;
        return;
    }

    contactsList.innerHTML = contacts.map(contact => `
        <div class="contact-item" onclick="openContactProfile(${contact.id})">
            <div class="contact-avatar">
                ${contact.photo ?
        `<img src="${contact.photo}" alt="${contact.name}">` :
        `<div class="avatar-placeholder">${contact.name.charAt(0).toUpperCase()}</div>`
    }
            </div>
            <div class="contact-info">
                <div class="contact-name">${contact.name}</div>
                <div class="contact-email">${contact.email}</div>
                ${contact.phone ? `<div class="contact-phone">${contact.phone}</div>` : ''}
            </div>
            <div class="contact-actions">
                <button class="contact-action-btn" onclick="event.stopPropagation(); sendMessageToContact(${contact.id})" title="Envoyer un message">
                    💬
                </button>
                <button class="contact-action-btn" onclick="event.stopPropagation(); editContact(${contact.id})" title="Modifier">
                    ✏️
                </button>
            </div>
        </div>
    `).join('');
}

// Charger les contacts pour le formulaire de nouveau message
async function loadContactsForMessage() {
    try {
        const response = await fetch('/Projet_communication/api/contacts');
        if (response.ok) {
            const contacts = await response.json();
            const select = document.getElementById('recipient');

            select.innerHTML = '<option value="">Sélectionner un contact</option>' +
                contacts.map(contact =>
                    `<option value="${contact.id}">${contact.name} (${contact.email})</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Erreur lors du chargement des contacts:', error);
    }
}

// Fonctions utilitaires
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);

    if (diffInHours < 1) {
        return 'À l\'instant';
    } else if (diffInHours < 24) {
        return `Il y a ${Math.floor(diffInHours)}h`;
    } else if (diffInHours < 48) {
        return 'Hier';
    } else {
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'short'
        });
    }
}

// Actions sur les messages et contacts
function openMessage(messageId) {
    window.location.href = `/Projet_communication/messages/${messageId}`;
}

function openContactProfile(contactId) {
    window.location.href = `/Projet_communication/contacts/${contactId}`;
}

function sendMessageToContact(contactId) {
    openNewMessage();
    // Pré-sélectionner le contact dans le formulaire
    setTimeout(() => {
        const select = document.getElementById('recipient');
        select.value = contactId;
    }, 100);
}

function editContact(contactId) {
    window.location.href = `/Projet_communication/contacts/${contactId}/edit`;
}

// Système de notifications
function showNotification(message, type = 'info') {
    // Créer l'élément de notification s'il n'existe pas
    let notificationContainer = document.getElementById('notificationContainer');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notificationContainer';
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
    }

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="closeNotification(this)">&times;</button>
        </div>
    `;

    notificationContainer.appendChild(notification);

    // Animation d'entrée
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
        closeNotification(notification.querySelector('.notification-close'));
    }, 5000);
}

function closeNotification(closeBtn) {
    const notification = closeBtn.closest('.notification');
    notification.classList.add('hide');

    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Gestion du responsive - menu mobile
function toggleMobileMenu() {
    const nav = document.querySelector('.main-nav');
    nav.classList.toggle('mobile-open');
}

// Raccourcis clavier
document.addEventListener('keydown', function(event) {
    // Ctrl/Cmd + M pour nouveau message
    if ((event.ctrlKey || event.metaKey) && event.key === 'm') {
        event.preventDefault();
        openNewMessage();
    }

    // Ctrl/Cmd + N pour nouveau contact
    if ((event.ctrlKey || event.metaKey) && event.key === 'n') {
        event.preventDefault();
        openAddContact();
    }

    // Échap pour fermer les modals
    if (event.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            modal.classList.remove('show');
        });
    }
});

// Mise à jour en temps réel (optionnel - WebSocket ou polling)
function startRealTimeUpdates() {
    // Mise à jour des statistiques toutes les 30 secondes
    setInterval(loadUserStats, 30000);

    // Mise à jour des messages toutes les 10 secondes
    setInterval(loadRecentMessages, 10000);
}

// Démarrer les mises à jour en temps réel
// startRealTimeUpdates();
    // Gestion des modales
    function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
}

    function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

    // Simulation du test de capteur
    function startTest() {
    closeModal('testModal');

    // Animation de test
    const testButtons = document.querySelectorAll('.buzzer-test, .sensor-action-btn.primary');
    testButtons.forEach(btn => {
    btn.style.background = '#FF3860';
    btn.textContent = '🔄 Test en cours...';
    btn.disabled = true;
});

    // Simulation du buzzer
    setTimeout(() => {
    showNotification('🔊 Test buzzer lancé', 'info');
}, 500);

    setTimeout(() => {
    showNotification('📡 Vérification capteurs...', 'info');
}, 2000);

    setTimeout(() => {
    showNotification('✅ Test terminé avec succès', 'success');
    testButtons.forEach(btn => {
    btn.style.background = '';
    btn.textContent = '🧪 Tester';
    btn.disabled = false;
});
}, 4000);
}

    // Système de notifications
    function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()">&times;</button>
            `;

    // Ajouter au DOM
    if (!document.querySelector('.notifications-container')) {
    const container = document.createElement('div');
    container.className = 'notifications-container';
    document.body.appendChild(container);
}

    document.querySelector('.notifications-container').appendChild(notification);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
    if (notification.parentElement) {
    notification.remove();
}
}, 5000);
}

    // Mise à jour des données en temps réel (simulation)
    function updateSensorData() {
    // Mise à jour de la température
    const tempValue = document.querySelector('.temperature-sensor .sensor-reading');
    if (tempValue) {
    const newTemp = (18 + Math.random() * 0.5).toFixed(1);
    tempValue.innerHTML = `${newTemp}<span class="sensor-unit">°C</span>`;
}

    // Mise à jour de l'humidité
    const humidityValue = document.querySelector('.sensor-card:last-child .sensor-reading');
    if (humidityValue) {
    const newHumidity = Math.floor(44 + Math.random() * 3);
    humidityValue.innerHTML = `${newHumidity}<span class="sensor-unit">%</span>`;
}

    // Mise à jour des timestamps
    const connectionStatuses = document.querySelectorAll('.connection-status');
    connectionStatuses.forEach(status => {
    if (status.textContent.includes('il y a')) {
    const seconds = Math.floor(Math.random() * 60) + 1;
    status.textContent = `Données mises à jour il y a ${seconds}s`;
}
});
}

    // Événements
    document.addEventListener('DOMContentLoaded', function() {
    // Test des boutons
    document.querySelectorAll('.buzzer-test').forEach(btn => {
        btn.addEventListener('click', () => openModal('testModal'));
    });

    // Mise à jour périodique des données
    setInterval(updateSensorData, 30000); // Toutes les 30 secondes

    // Animation des indicateurs
    setInterval(() => {
    document.querySelectorAll('.radar-pulse').forEach(pulse => {
    pulse.style.animation = 'none';
    setTimeout(() => {
    pulse.style.animation = 'radar-pulse 2s infinite';
}, 10);
});
}, 5000);

    console.log('🎨 ArtGuard - Système de capteurs initialisé');
});
