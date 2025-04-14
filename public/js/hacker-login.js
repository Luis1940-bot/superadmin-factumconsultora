const input = document.getElementById('inputField');
const log = document.getElementById('log');

input.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    const entered = input.value.trim();

    fetch('login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: entered }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          log.innerHTML = '✅ Acceso concedido. Redirigiendo...';
          setTimeout(() => {
            window.location.href = 'select-client.php';
          }, 1500);
        } else {
          log.innerHTML = '⛔ Token inválido. Intentá nuevamente.';
          input.value = '';
        }
      });
  }
});
