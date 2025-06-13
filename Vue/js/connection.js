document.querySelector('.submit-button').addEventListener('click', async () => {
  const email    = document.querySelector('input[name="email"]').value;
  const password = document.querySelector('input[name="password"]').value;

  const res = await fetch('/Projet_communication/connection', {
    method : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body   : JSON.stringify({ email, password })
  });

  const data = await res.json();
  if (res.ok) {
    alert(data.Success);         // Connexion réussie
    window.location.href = '/Projet_communication/'; // redirige par ex.
  } else {
    document.querySelector('.error-message').textContent = data.Error;
  }
});
