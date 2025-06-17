// JavaScript pour la page de connexion
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('connection-form');
  const errorMessage = document.getElementById('error-message');
  const successMessage = document.getElementById('success-message');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Récupération des données du formulaire
    const formData = {
      email: document.querySelector('input[name="email"]').value.trim(),
      password: document.querySelector('input[name="password"]').value
    };

    // Validation côté client
    if (!validateForm(formData)) {
      return;
    }

    // Envoi des données
    try {
      showLoading(true);

      const response = await fetch('/Projet_communication/connection', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      // Vérifier si la réponse est du JSON valide
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Réponse non-JSON reçue:', text);
        throw new Error('Le serveur a renvoyé une réponse non-JSON');
      }

      const result = await response.json();

      if (response.ok) {
        showSuccess(result.Success);

        // Redirection vers le dashboard après connexion réussie
        setTimeout(() => {
          window.location.href = '/Projet_communication/dashboard';
        }, 1000);
      } else {
        showError(result.Error || 'Erreur lors de la connexion');
      }
    } catch (error) {
      console.error('Erreur complète:', error);
      if (error.message.includes('JSON')) {
        showError('Erreur de communication avec le serveur. Vérifiez votre configuration.');
      } else {
        showError('Erreur de connexion au serveur');
      }
    } finally {
      showLoading(false);
    }
  });

  function validateForm(data) {
    // Vérification des champs vides
    if (!data.email || !data.password) {
      showError('Tous les champs sont obligatoires');
      return false;
    }

    // Validation de l'email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(data.email)) {
      showError('Veuillez entrer une adresse email valide');
      return false;
    }

    return true;
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
    const submitButton = document.querySelector('.button-connection');
    if (show) {
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';
    } else {
      submitButton.disabled = false;
      submitButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
    }
  }

  // Validation en temps réel
  const inputs = document.querySelectorAll('.input-user-first, .input-user');
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
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
          setFieldError(field, 'Email invalide');
        } else {
          setFieldValid(field);
        }
        break;

      case 'password':
        if (value.length < 1) {
          setFieldError(field, 'Mot de passe requis');
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

  // Gestion des touches du clavier
  document.addEventListener('keydown', function(e) {
    // Échapper pour revenir à l'accueil
    if (e.key === 'Escape') {
      window.location.href = '/Projet_communication/';
    }
  });
});