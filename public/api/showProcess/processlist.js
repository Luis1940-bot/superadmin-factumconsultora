import { mostrarMensaje } from '../../js/modules/ui/alerts.js';

document.getElementById('btnRecargar')?.addEventListener('click', () => {
  window.location.href = 'processlist.php';
});

document.getElementById('btnSleep')?.addEventListener('click', () => {
  window.location.href = 'processlist.php?sleep=1';
});

document.getElementById('btnCerrar')?.addEventListener('click', () => {
  window.close();
});

document.querySelectorAll('.btn-kill').forEach((btn) => {
  btn.addEventListener('click', () => {
    const { id } = btn.dataset;
    // eslint-disable-next-line no-alert, no-restricted-globals
    const confirmar = confirm(`¿Seguro que quieres KILL el proceso #${id}?`);
    if (confirmar) {
      fetch(`kill.php?id=${id}`)
        .then(() => window.location.reload())
        .catch(() => mostrarMensaje('❌ No se pudo ejecutar KILL.', 'error'));
    }
  });
});

document.getElementById('btnKillMasivo')?.addEventListener('click', () => {
  // eslint-disable-next-line no-alert, no-restricted-globals
  const confirmar = confirm(
    '¿Deseás matar todos los procesos inactivos (Sleep > 60s)?',
  );
  if (!confirmar) return;

  fetch('kill.php?all=1')
    .then(() => window.location.reload())
    .catch(() =>
      mostrarMensaje('❌ No se pudo ejecutar KILL masivo.', 'error'),
    );
});
