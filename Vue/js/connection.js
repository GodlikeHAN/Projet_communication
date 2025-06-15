// Gestion du formulaire de connexion simple
document.addEventListener('DOMContentLoaded', function() {
  const simpleLoginForm = document.getElementById('simpleLoginForm');
  const loginFormElement = document.getElementById('loginFormElement');
  const signupFormElement = document.getElementById('signupFormElement');

  // Formulaire de connexion simple (original)
  if (simpleLoginForm) {
    simpleLoginForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const email = document.querySelector('input[name="email"]').value;
      const password = document.querySelector('input[name="password"]').value;
      const errorElement = document.getElementById('simpleLoginError');
      const successElement = document.getElementById('simpleLoginSuccess');

      try {
        const res = await fetch('/Projet_communication/connection', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });

        const data = await res.json();

        if (res.ok) {
          successElement.textContent = data.Success;
          errorElement.textContent = '';
          // Redirection après succès
          setTimeout(() => {
            window.location.href = '/Projet_communication/';
          }, 1500);
        } else {
          errorElement.textContent = data.Error;
          successElement.textContent = '';
        }
      } catch (error) {
        errorElement.textContent = 'Erreur de connexion au serveur' ;
        successElement.textContent = '';
      }
    });
  }
  // Formulaire d'inscription
  if (signupFormElement) {
    signupFormElement.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(signupFormElement);
      const nom = formData.get('nom');
      const prenom = formData.get('prenom');
      const email = formData.get('email');
      const password = formData.get('password');
      const confirm_password = formData.get('confirm_password');
      const errorElement = document.getElementById('signupError');
      const successElement = document.getElementById('signupSuccess');

      // Validation côté client
      if (password !== confirm_password) {
        errorElement.textContent = 'Les mots de passe ne correspondent pas';
        successElement.textContent = '';
        return;
      }

      try {
        const res = await fetch('/Projet_communication/inscription', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ nom, prenom, email, password, confirm_password })
        });

        const data = await res.json();

        if (res.ok) {
          successElement.textContent = data.Success;
          errorElement.textContent = '';
          // Réinitialiser le formulaire
          signupFormElement.reset();
          // Optionnel: basculer vers l'onglet connexion
          setTimeout(() => {
            showForm('loginForm');
          }, 2000);
        } else {
          errorElement.textContent = data.Error;
          successElement.textContent = '';
        }
      } catch (error) {
        errorElement.textContent = 'Erreur de connexion au serveur';
        successElement.textContent = '';
      }
    });
  }
});