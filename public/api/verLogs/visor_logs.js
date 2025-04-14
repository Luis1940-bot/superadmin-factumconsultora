import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

document.addEventListener('DOMContentLoaded', () => {
  const mensaje = document.getElementById('mensajeRegistros');
  const tbody = document.getElementById('logTableBody');
  const recargarBtn = document.getElementById('recargarBtn');
  const cerrarBtn = document.getElementById('cerrarBtn');
  const MAX_LINEAS = 50;

  async function cargarLogs() {
    try {
      const { baseUrl } = await getConfig();
      const response = await fetch(`${baseUrl}/api/router.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ruta: '/leerLogsExternos',
        }),
      });

      const text = await response.text();

      tbody.innerHTML = '';
      mensaje.textContent = '';
      const lineasTotales = text.trim().split('\n').filter(Boolean);
      const ultimas = lineasTotales.slice(-MAX_LINEAS);

      if (ultimas.length === 0) {
        mensaje.textContent = '⚠️ El archivo de log está vacío.';
        mensaje.className = 'warning';
        return;
      }

      ultimas.forEach((linea, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${lineasTotales.length - ultimas.length + index + 1}</td>
          <td class="${linea.toLowerCase().includes('error') ? 'error' : ''}">
            ${linea}
          </td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error('❌ Error al cargar logs:', err);
      mensaje.textContent = '❌ Error al cargar el archivo de logs.';
      mensaje.className = 'error';
    }
  }

  recargarBtn.addEventListener('click', () => {
    cargarLogs();
  });

  cerrarBtn.addEventListener('click', () => {
    try {
      window.close();
    } catch {
      mostrarMensaje('Cerrá esta pestaña manualmente.', 'info');
    }
  });

  // 🚀 Carga inicial
  cargarLogs();
});
