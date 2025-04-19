import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

// document.addEventListener('DOMContentLoaded', async () => {
//   // 🔄 Cargar registros duplicados al iniciar
//   try {
//     // const dbId = document.getElementById('cliente-id')?.dataset.id;
//     const res = await fetch(window.location.pathname, {
//       // const res = await fetch(`${window.location.pathname}?id=${dbId}`, {
//       method: 'POST',
//       headers: { 'Content-Type': 'application/json' },
//     });

//     const { success, data } = await res.json();

//     if (!success || !Array.isArray(data) || data.length === 0) {
//       mostrarMensaje('No se encontraron controles repetidos.', 'info');
//       return;
//     }

//     const tbody = document.querySelector('#dataTable tbody');
//     tbody.innerHTML = '';
//     data.forEach(async (row) => {
//       // eslint-disable-next-line no-use-before-define
//       const nuevoCodigo = await generarCodigoAlfabetico(
//         row.nombre_reporte,
//         row.orden,
//       );

//       const tr = document.createElement('tr');

//       tr.innerHTML = `
//         <td>${row.control}</td>
//         <td>${row.idLTYcontrol}</td>
//         <td>${row.idLTYreporte}</td>
//         <td>${row.nombre_reporte}</td>
//         <td>${row.orden}</td>
//         <td>${nuevoCodigo}</td>
//         <td>
//           <button class="copy-btn">📋 Copiar</button>
//           <button class="update-btn"
//             data-idltycontrol="${row.idLTYcontrol}"
//             data-nuevocodigo="${nuevoCodigo}">🔄 Actualizar</button>
//           <button class="update-all-btn"
//             data-idltyreporte="${row.idLTYreporte}">🔄 Todo</button>
//         </td>
//       `;

//       tbody.appendChild(tr);
//     });

//     // Reasignar eventos
//     // eslint-disable-next-line no-use-before-define
//     asignarEventos();
//   } catch (error) {
//     console.error('❌ Error al cargar datos:', error);
//     mostrarMensaje('Error al obtener los registros.', 'error');
//   }

//   // Evento cerrar
//   const cerrarBtn = document.getElementById('cerrarBtn');
//   if (cerrarBtn) {
//     cerrarBtn.addEventListener('click', () => {
//       if (window.close) {
//         window.close();
//       } else {
//         mostrarMensaje('Cerrá esta pestaña manualmente.', 'info');
//       }
//     });
//   }
// });

function asignarEventos() {
  document.querySelectorAll('.update-btn').forEach((button) => {
    button.addEventListener('click', async function fristButton() {
      const idLTYcontrol = this.dataset.idltycontrol;
      const nuevoCodigo = this.dataset.nuevocodigo;

      if (!idLTYcontrol || !nuevoCodigo) {
        mostrarMensaje('Error: Falta información.', 'error');
        return;
      }

      if (
        // eslint-disable-next-line no-alert, no-restricted-globals
        confirm(
          `¿Actualizar el control ${idLTYcontrol} con código ${nuevoCodigo}?`,
        )
      ) {
        // eslint-disable-next-line no-unused-vars
        const { baseUrl, routes } = await getConfig();
        const response = await fetch(`${baseUrl}/api/router.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            ruta: '/updateControl',
            idLTYcontrol,
            nuevoCodigo,
          }),
        });
        const data = await response.json();
        if (data.success) {
          mostrarMensaje(
            `El control ${idLTYcontrol}  se modificó con éxito.`,
            'ok',
          );
        } else {
          mostrarMensaje(
            `No pudo modificarse el control: ${idLTYcontrol} `,
            'error',
          );
        }
      }
    });
  });

  function copiarAlPortapapeles(texto, boton) {
    const btn = boton;
    navigator.clipboard
      .writeText(texto)
      .then(() => {
        btn.textContent = '✔ Copiado';
        setTimeout(() => {
          btn.textContent = '📋 Copiar';
        }, 1500);
      })
      .catch((err) => console.error('Error al copiar:', err));
  }

  async function actualizarTodosPorReporte(idLTYreporte) {
    // eslint-disable-next-line no-alert, no-restricted-globals
    if (
      // eslint-disable-next-line no-restricted-globals, no-alert
      confirm(`¿Actualizar todos los controles del reporte ${idLTYreporte}?`)
    ) {
      // eslint-disable-next-line no-unused-vars
      const { baseUrl, routes } = await getConfig();
      const response = await fetch(`${baseUrl}/api/router.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ruta: '/updateControl',
          idLTYreporte,
        }),
      });
      const data = await response.json();
      if (data.success) {
        mostrarMensaje(
          `Se modificaron todos los registros del reporte ${idLTYreporte}`,
          'ok',
        );
      } else {
        mostrarMensaje(
          `No pudo modificarse el reporte: ${idLTYreporte}`,
          'error',
        );
      }
    }
  }

  document.querySelector('#dataTable tbody').addEventListener('click', (e) => {
    const btn = e.target;

    if (btn.classList.contains('copy-btn')) {
      const fila = btn.closest('tr');
      const texto = fila?.children[5]?.textContent || '';
      copiarAlPortapapeles(texto, btn);
    }

    if (btn.classList.contains('update-btn')) {
      const idLTYcontrol = btn.dataset.idltycontrol;
      const nuevoCodigo = btn.dataset.nuevocodigo;

      if (!idLTYcontrol || !nuevoCodigo) {
        mostrarMensaje('Error: Falta información.', 'error');
        return;
      }

      if (
        // eslint-disable-next-line no-restricted-globals, no-alert
        confirm(
          `¿Actualizar el control ${idLTYcontrol} con código ${nuevoCodigo}?`,
        )
      ) {
        fetch('updateControl.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ idLTYcontrol, nuevoCodigo }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              mostrarMensaje('Actualizado correctamente.', 'ok');
              window.location.reload();
            } else {
              mostrarMensaje(data.message, 'error');
            }
          });
      }
    }

    if (btn.classList.contains('update-all-btn')) {
      const idLTYreporte = btn.dataset.idltyreporte;
      actualizarTodosPorReporte(idLTYreporte);
    }
  });

  document
    .getElementById('searchInput')
    // eslint-disable-next-line no-use-before-define
    ?.addEventListener('input', filtrarPorIdLTYreporte);
}

function filtrarPorIdLTYreporte() {
  const filtro = document
    .getElementById('searchInput')
    .value.trim()
    .toUpperCase();
  const filas = document.querySelectorAll('#dataTable tbody tr');

  filas.forEach((f) => {
    const celda = f.cells[0];
    if (celda) {
      const txt = celda.textContent || celda.innerText;
      const visible = txt.toUpperCase().includes(filtro);
      const filaHTML = f; // ✅ usar variable intermedia
      filaHTML.style.display = visible ? '' : 'none';
    }
  });
}

// Simula hash como PHP md5 (si no tenés lib externa)
function md5(str) {
  return window.CryptoJS.MD5(str).toString();
}
async function generarCodigoAlfabetico(rep, ord) {
  let reporte = rep;
  const orden = parseInt(ord, 10);
  if (!reporte) return 'error00000';

  // Normalizar a UTF-8 y eliminar acentos
  reporte = reporte.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  // Eliminar caracteres especiales, mantener solo letras, números y espacios
  reporte = reporte.replace(/[^\p{L}\p{N}\s-]/gu, '');

  // Obtener las primeras 2 letras de cada palabra
  const palabras = reporte.split(/[\s-]+/); // Separar por espacios y guiones
  let codigoBase = palabras
    .map((p) => p.slice(0, 2))
    .join('')
    .toLowerCase();

  // Limitar a 6 caracteres
  codigoBase = codigoBase.substring(0, 6);

  // Asegurar que el orden sea de 4 dígitos
  const ordenStr = orden.toString().padStart(4, '0');

  // Generar un hash MD5 del reporte y orden
  let hash = await md5(reporte + orden);
  hash = hash.substring(0, 5); // Obtener los primeros 5 caracteres del hash

  // Formar el código final de 15 caracteres
  return (codigoBase + ordenStr + hash).substring(0, 15);
}

async function cargarRegistros() {
  const dbId = document.getElementById('cliente-id')?.dataset.id;

  if (!dbId) {
    mostrarMensaje('No se pudo determinar la base de datos', 'error');
    return;
  }

  try {
    const res = await fetch(window.location.pathname, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: dbId }), // MANDAMOS EL ID DE BASE
    });

    const raw = await res.text();

    let json;
    try {
      json = JSON.parse(raw);
    } catch (err) {
      console.error('No se pudo parsear JSON:', raw);
      mostrarMensaje('Error inesperado al obtener los registros.', 'error');
      return;
    }

    const { success, data } = json;

    if (!success || !Array.isArray(data) || data.length === 0) {
      mostrarMensaje('No se encontraron controles repetidos.', 'info');
      return;
    }
    const tbody = document.querySelector('#dataTable tbody');
    tbody.innerHTML = '';

    const filas = await Promise.all(
      data.map(async (row) => {
        const nuevoCodigo = await generarCodigoAlfabetico(
          row.nombre_reporte,
          row.orden,
        );

        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>${row.control}</td>
      <td>${row.idLTYcontrol}</td>
      <td>${row.idLTYreporte}</td>
      <td>${row.nombre_reporte}</td>
      <td>${row.orden}</td>
      <td>${nuevoCodigo}</td>
      <td>
        <button class="copy-btn">📋 Copiar</button>
        <button class="update-btn"
          data-idltycontrol="${row.idLTYcontrol}"
          data-nuevocodigo="${nuevoCodigo}">🔄 Actualizar</button>
        <button class="update-all-btn"
          data-idltyreporte="${row.idLTYreporte}">🔄 Todo</button>
      </td>
    `;
        return tr;
      }),
    );

    filas.forEach((tr) => tbody.appendChild(tr));

    asignarEventos();
  } catch (error) {
    console.error('❌ Error al cargar datos:', error);
    mostrarMensaje('Error al obtener los registros.', 'error');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const cargarBtn = document.getElementById('btnCargar');
  if (cargarBtn) {
    cargarBtn.addEventListener('click', cargarRegistros);
  }

  const cerrarBtn = document.getElementById('cerrarBtn');
  if (cerrarBtn) {
    cerrarBtn.addEventListener('click', () => {
      if (window.close) {
        window.close();
      } else {
        mostrarMensaje('Cerrá esta pestaña manualmente.', 'info');
      }
    });
  }
});
