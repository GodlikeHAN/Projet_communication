// JavaScript pour la page d'inscription
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('inscription-form');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Récupération des données du formulaire
        const formData = {
            name: document.querySelector('input[name="name"]').value.trim(),
            email: document.querySelector('input[name="email"]').value.trim(),
            password: document.querySelector('input[name="password"]').value,
            confirmPassword: document.querySelector('input[name="confirm-password"]').value
        };

        // Validation côté client
        if (!validateForm(formData)) {
            return;
        }

        // Envoi des données
        try {
            showLoading(true);

            const response = await fetch('/Projet_communication/inscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: formData.name,
                    email: formData.email,
                    password: formData.password
                })
            });

            const result = await response.json();

            if (response.ok) {
                showSuccess(result.Success);
                form.reset();

                // Redirection après 2 secondes
                setTimeout(() => {
                    window.location.href = '/Projet_communication/connection';
                }, 2000);
            } else {
                showError(result.Error);
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError('Erreur de connexion au serveur');
        } finally {
            showLoading(false);
        }
    });

    function validateForm(data) {
        // Vérification des champs vides
        if (!data.name || !data.email || !data.password || !data.confirmPassword) {
            showError('Tous les champs sont obligatoires');
            return false;
        }

        // Validation du nom
        if (data.name.length < 2) {
            showError('Le nom doit contenir au moins 2 caractères');
            return false;
        }

        // Validation de l'email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            showError('Veuillez entrer une adresse email valide');
            return false;
        }

        // Validation du mot de passe
        if (data.password.length < 6) {
            showError('Le mot de passe doit contenir au moins 6 caractères');
            return false;
        }

        // Vérification de la confirmation du mot de passe
        if (data.password !== data.confirmPassword) {
            showError('Les mots de passe ne correspondent pas');
            return false;
        }

        // Validation de la complexité du mot de passe
        if (!isPasswordStrong(data.password)) {
            showError('Le mot de passe doit contenir au moins une lettre et un chiffre');
            return false;
        }

        return true;
    }

    function isPasswordStrong(password) {
        const hasLetter = /[a-zA-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        return hasLetter && hasNumber;
    }

    function showError(message) {
        hideMessages();
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
    }

    function showSuccess(message) {
        hideMessages();
        successMessage.textContent = message;
        successMessage.classList.add('show');
    }

    function hideMessages() {
        errorMessage.classList.remove('show');
        successMessage.classList.remove('show');
    }

    function showLoading(show) {
        const submitButton = document.querySelector('.submit-button');
        if (show) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription en cours...';
        } else {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-user-plus"></i> S\'inscrire';
        }
    }

    // Validation en temps réel
    const inputs = document.querySelectorAll('.input-field');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });

        input.addEventListener('input', function() {
            // Nettoyer les messages d'erreur pendant la saisie
            if (errorMessage.classList.contains('show')) {
                hideMessages();
            }
        });
    });

    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.getAttribute('name');

        switch (fieldName) {
            case 'name':
                if (value.length < 2) {
                    setFieldError(field, 'Nom trop court');
                } else {
                    setFieldValid(field);
                }
                break;

            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    setFieldError(field, 'Email invalide');
                } else {
                    setFieldValid(field);
                }
                break;

            case 'password':
                if (value.length < 6) {
                    setFieldError(field, 'Mot de passe trop court');
                } else if (!isPasswordStrong(value)) {
                    setFieldError(field, 'Mot de passe faible');
                } else {
                    setFieldValid(field);
                }
                break;

            case 'confirm-password':
                const password = document.querySelector('input[name="password"]').value;
                if (value !== password) {
                    setFieldError(field, 'Mots de passe différents');
                } else {
                    setFieldValid(field);
                }
                break;
        }
    }

    function setFieldError(field, message) {
        field.style.borderColor = '#e74c3c';
        field.title = message;
    }

    function setFieldValid(field) {
        field.style.borderColor = '#27ae60';
        field.title = '';
    }
});