function openPopup() {
    // Si la popup n'a pas encore été chargée
    if (!document.getElementById('authPopup')) {
        // Charger la popup depuis le module connexion
        fetch('index.php?module=connexion')
            .then(response => response.text())
            .then(html => {
                // Ajouter le HTML de la popup dans le corps de la page
                document.body.insertAdjacentHTML('beforeend', html);

                // Ajouter la classe active pour afficher la popup
                const popup = document.getElementById('authPopup');
                popup.classList.add('active');

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