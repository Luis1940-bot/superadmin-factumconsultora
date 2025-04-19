import { mostrarMensaje } from '../../js/modules/ui/alerts.js';

document.addEventListener('DOMContentLoaded', () => {
  const recargarBtn = document.getElementById('recargarBtn');
  const cerrarBtn = document.getElementById('cerrarBtn');

  if (recargarBtn) {
    recargarBtn.addEventListener('click', () => {
      // recarga forzada con parámetro cache para evitar versiones en caché

      const url = new URL(window.location.href);
      url.searchParams.set('refresh', Date.now());
      window.location.href = url.toString();
    });
  }

  if (cerrarBtn) {
    cerrarBtn.addEventListener('click', () => {
      if (window.close) {
        window.close();
      } else {
        mostrarMensaje('Cerrá esta pestaña manualmente', 'info');
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.json-wrapper').forEach((wrapper) => {
    const toggle = wrapper.querySelector('.json-toggle');
    const container = wrapper.querySelector('.json-container');

    toggle.addEventListener('click', () => {
      if (container.style.display === 'none') {
        try {
          const raw = container.dataset.json;
          container.textContent = raw;
        } catch (e) {
          container.textContent = '❌ JSON inválido';
        }

        container.style.display = 'block';
        toggle.textContent = '➖ newJSON';
      } else {
        container.style.display = 'none';
        toggle.textContent = '➕ newJSON';
      }
    });
  });
});

document.getElementById('registroSearch').addEventListener('input', () => {
  const filtro = document.getElementById('registroSearch').value.toLowerCase();
  const filas = document.querySelectorAll('table tbody tr');

  filas.forEach((f) => {
    const fila = f;
    const columnas = fila.querySelectorAll('td');

    // const fecha = columnas[1]?.textContent.toLowerCase();
    const pedido = columnas[2]?.textContent.toLowerCase();
    // const cliente = columnas[9]?.textContent.toLowerCase();

    const coincide = pedido.includes(filtro);

    fila.style.display = coincide ? '' : 'none';
  });
});
