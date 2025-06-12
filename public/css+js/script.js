function openPopup() {
    // Si la popup n'a pas encore été chargée
    if (!document.getElementById('authPopup')) {
        // Charger la popup depuis le module connexion
        fetch('index.php?module=connexion')
            .then(response => response.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;

                const popup = tempDiv.querySelector('#authPopup');
                if (popup) {
                    document.body.appendChild(popup);
                    popup.classList.add('active');
                }

            })
            .catch(err => console.error('Erreur lors du chargement de la popup:', err));
    } else {
        // Si la popup est déjà chargée, l'afficher
        const popup = document.getElementById('authPopup');
        popup.classList.add('active');
    }
}


function closePopup() {
    document.getElementById('authPopup').classList.remove('active');

}

function showForm(formId) {
    const forms = document.querySelectorAll('.form-container');
    const tabs = document.querySelectorAll('.tab');

    forms.forEach(form => {
        form.classList.remove('active');
    });

    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    document.getElementById(formId).classList.add('active');
    document.querySelector(`.tab[onclick="showForm('${formId}')"]`).classList.add('active');
}

const errorMessage = document.querySelector('.error-message');
if (!errorMessage || errorMessage.textContent.trim() === '') {
    document.getElementById('authPopup').classList.remove('active');
}

function showPopup() {
    alert("Merci pour votre question !");
}

function confirmDeleteProfile() {
    if (confirm("Êtes-vous sûr de vouloir supprimer votre profil ? Cette action est irréversible.")) {
        window.location.href = "?module=deleteprofil";
    }
}
