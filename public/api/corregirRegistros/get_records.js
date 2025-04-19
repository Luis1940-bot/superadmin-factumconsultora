import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

document.addEventListener('DOMContentLoaded', async () => {
  const tabla = document.querySelector('#recordsTable tbody');
  const updateBtn = document.getElementById('updateButton');

  async function cargarRegistros() {
    const mensaje = document.getElementById('mensajeRegistros');
    mensaje.textContent = '';
    mensaje.className = '';

    try {
      const dbId = document.getElementById('cliente-id')?.dataset.id;
      const url = `./get_records_api.php?id=${encodeURIComponent(dbId)}&_=${Date.now()}`;
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Cache-Control': 'no-cache',
          Pragma: 'no-cache',
        },
      });
      const data = await response.json();

      tabla.innerHTML = '';
      if (data.success && Array.isArray(data.data) && data.data.length > 0) {
        data.data.forEach((registro) => {
          const fila = document.createElement('tr');
          fila.innerHTML = `
            <td>${registro.nuxpedido}</td>
            <td>${registro.fecha}</td>
            <td>${registro.hora}</td>
            <td>${registro.idusuario}</td>
            <td>${registro.idLTYreporte}</td>
            <td>${registro.idClienteReporte}</td>
            <td>${registro.idClienteRegistro}</td>
          `;
          tabla.appendChild(fila);
        });
      } else {
        mensaje.textContent = '⚠️ No hay registros pendientes para actualizar.';
        mensaje.classList.add('error');
      }
    } catch (err) {
      console.error(err);
      mostrarMensaje('Error al cargar los registros.', 'error');
      mensaje.textContent = '❌ Error al cargar los registros.';
      mensaje.classList.add('error');
    }
  }

  updateBtn.addEventListener('click', async () => {
    // eslint-disable-next-line no-alert, no-restricted-globals
    const confirmar = confirm('¿Estás seguro de actualizar los registros?');
    if (!confirmar) return;

    const { baseUrl } = await getConfig();
    const response = await fetch(`${baseUrl}/api/router.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ ruta: '/update_records' }),
    });

    const data = await response.json();
    if (data.success) {
      mostrarMensaje(`✅ Registros actualizados`, 'ok');
      await cargarRegistros();
    } else {
      mostrarMensaje(`❌ No se pudo actualizar`, 'error');
    }
  });

  document
    .getElementById('recargarBtn')
    ?.addEventListener('click', await cargarRegistros);
  document
    .getElementById('cerrarBtn')
    ?.addEventListener('click', () => window.close());

  await cargarRegistros(); // Inicial
});
