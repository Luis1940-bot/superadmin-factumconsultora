import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import { mostrarPrompt } from '../../js/modules/ui/prompt.js';

let idReporteActual = null;

const selectReporte = document.getElementById('selectReporte');
const btnRecargar = document.getElementById('btnRecargar');
const btnSalir = document.getElementById('btnSalir');

// 🔄 Cargar lista de reportes
async function cargarReportes() {
  try {
    const res = await fetch('reportes.php');
    const json = await res.json();
    if (!json.success) {
      mostrarMensaje('❌ Error al cargar reportes', 'error');
      return;
    }

    json.data.forEach((r) => {
      const opt = document.createElement('option');
      opt.value = r.idLTYreporte;
      opt.textContent = `#${r.idLTYreporte} - ${r.nombre} (${r.cliente})`;
      selectReporte.appendChild(opt);
    });
  } catch (err) {
    console.error(err);
    mostrarMensaje('❌ Error de red al cargar reportes', 'error');
  }
}

// 📋 Cargar controles del reporte seleccionado
async function cargarControles(ide) {
  try {
    const res1 = await fetch('data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ide }), // ✅ parámetro corregido
    });

    const json = await res1.json();

    const tbody = document.querySelector('#dataTable tbody');
    tbody.innerHTML = '';

    if (json.success && json.data.length > 0) {
      json.data.forEach((row) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${row.idLTYcontrol}</td>
          <td>${row.control}</td>
          <td>${row.nombre || ''}</td>
          <td>${row.detalle}</td>
          <td>${row.orden}</td>
          <td>
            <button class="mover-btn" data-id="${row.idLTYcontrol}" data-orden="${row.orden}">
              ⬆️ Mover
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });

      // 🎯 Eventos para los botones de mover
      document.querySelectorAll('.mover-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          const { id } = btn.dataset;
          const actualOrden = btn.dataset.orden;

          const nuevoOrden = await mostrarPrompt(
            `Control #${id} (orden actual: ${actualOrden})\n¿A qué posición lo querés mover?`,
            'Aceptar',
          );

          const ordenNumerico = Number(nuevoOrden);
          if (!nuevoOrden || Number.isNaN(ordenNumerico)) return;

          const res = await fetch('mover.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              idLTYcontrol: id,
              nuevoOrden: ordenNumerico,
            }),
          });

          const json1 = await res.json();
          if (json1.success) {
            mostrarMensaje('✅ Posición actualizada', 'ok');
            if (idReporteActual) cargarControles(idReporteActual);
          } else {
            mostrarMensaje('❌ Error al mover el control', 'error');
          }
        });
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='6'>Sin resultados.</td></tr>";
    }
  } catch (error) {
    console.error(error);
    mostrarMensaje('❌ Error al cargar controles', 'error');
  }
}

// 🎯 Cambio de selección
selectReporte.addEventListener('change', () => {
  const selectedId = selectReporte.value;
  if (!selectedId) {
    mostrarMensaje('⚠️ Seleccioná un reporte válido', 'warning');
    return;
  }

  idReporteActual = selectedId;
  cargarControles(selectedId);
});

// 🔁 Recargar controles del reporte actual
btnRecargar.addEventListener('click', () => {
  if (idReporteActual) cargarControles(idReporteActual);
});

// 🚪 Salir
btnSalir.addEventListener('click', () => {
  if (window.close) window.close();
  else mostrarMensaje('Cerrá esta pestaña manualmente.', 'info');
});

// 🔄 Inicial
cargarReportes();
