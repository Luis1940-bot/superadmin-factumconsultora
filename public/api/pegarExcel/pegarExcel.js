import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

function md5(str) {
  return window.CryptoJS.MD5(str).toString();
}

async function generarCodigoAlfabetico(rep, ord) {
  let reporte = rep;
  const orden = parseInt(ord, 10);
  if (!reporte) return 'error00000';

  reporte = reporte.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  reporte = reporte.replace(/[^\p{L}\p{N}\s-]/gu, '');

  const palabras = reporte.split(/[\s-]+/);
  let codigoBase = palabras
    .map((p) => p.slice(0, 2))
    .join('')
    .toLowerCase();
  codigoBase = codigoBase.substring(0, 6);

  const ordenStr = orden.toString().padStart(4, '0');
  let hash = await md5(reporte + orden);
  hash = hash.substring(0, 5);
  return (codigoBase + ordenStr + hash).substring(0, 15);
}

async function procesarDatos(ultimoOrdenJS, nombreReporteJS) {
  const tabla = document.querySelector('#dataTable tbody');
  tabla.innerHTML = '';

  const campos = document.getElementById('campoInput').value.split('\n');
  const detalles = document.getElementById('detalleInput').value.split('\n');
  const tiposDato = document.getElementById('tipoDatoInput').value.split('\n');
  const tpObservas = document
    .getElementById('tpObservaInput')
    .value.split('\n');

  const totalRegistros = Math.max(
    campos.length,
    detalles.length,
    tiposDato.length,
    tpObservas.length,
  );

  const codigosGenerados = await Promise.all(
    Array.from({ length: totalRegistros }, async (_, i) => {
      const ordenActual = parseInt(ultimoOrdenJS, 10) + i + 1;
      const ordenStr = ordenActual.toString().padStart(4, '0');
      const codigo = await generarCodigoAlfabetico(nombreReporteJS, ordenStr);
      return { codigo, ordenNum: ordenActual - 1 };
    }),
  );

  for (let i = 0; i < totalRegistros; i++) {
    const campo = campos[i]?.trim() || '-';

    if (campo && campo !== '-') {
      const fila = tabla.insertRow();
      fila.insertCell(0).textContent = i + 1;
      fila.insertCell(1).textContent = campo;
      fila.insertCell(2).textContent = detalles[i]?.trim() || '-';
      fila.insertCell(3).textContent = tiposDato[i]?.trim() || '-';
      fila.insertCell(4).textContent = tpObservas[i]?.trim() || '-';
      fila.insertCell(5).textContent = codigosGenerados[i].ordenNum;
      fila.insertCell(6).textContent = codigosGenerados[i].codigo;
    }
  }
}

async function guardarDatosEnBaseDeDatos() {
  const tabla = document.querySelector('#dataTable tbody');
  const filas = tabla.querySelectorAll('tr');
  const idLTYreporte = document.getElementById('idLTYreporte').value;
  const bdCliente = document.getElementById('cliente-id')?.dataset.id;
  // const match = numeroidLTYcliente.match(/^mc(\d+)00+$/);
  const idLTYcliente = parseInt(bdCliente.slice(2, 3), 10);
  if (!filas.length) {
    mostrarMensaje('No hay datos para guardar.', 'warning');
    return false;
  }

  const ultimaFila = document.querySelector(
    '#tablaExistente tbody tr:last-child',
  );
  const ultimoID = ultimaFila?.querySelector('td')?.textContent.trim();

  if (!ultimoID) {
    mostrarMensaje('No hay registros existentes.', 'info');
    return false;
  }

  const datosParaGuardar = [...filas].map((fila) => {
    const celdas = fila.querySelectorAll('td');
    return {
      control: celdas[6].textContent.trim(),
      nombre: celdas[1].textContent.trim(),
      detalle: celdas[2].textContent.trim(),
      tipodato: celdas[3].textContent.trim(),
      tpdeobserva: celdas[4].textContent.trim(),
      orden: parseInt(celdas[5].textContent.trim(), 10),
    };
  });

  const payload = {
    ruta: '/addListaCampos',
    datos: datosParaGuardar,
    ultimoID,
    idLTYcliente,
    idLTYreporte,
    bdCliente,
  };
  try {
    const { baseUrl } = await getConfig();
    const response = await fetch(`${baseUrl}/api/router.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const resultado = await response.json();

    if (resultado.success) {
      mostrarMensaje('Datos guardados correctamente.', 'ok');
      window.location.reload();
      return true;
    }
    mostrarMensaje('Error al guardar los datos.', 'error');
    return false;
  } catch (err) {
    console.error('❌ Error en la petición:', err);
    mostrarMensaje('Ocurrió un error al conectar con el servidor.', 'error');
    return false;
  }
}

function limpiarDatos() {
  window.location.href = 'pegarExcel.php';
}

function actualizarTabla(datos) {
  const tbody = document.querySelector('#tablaExistente tbody');
  tbody.innerHTML = '';

  datos.forEach((dato) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${dato.idLTYcontrol}</td>
      <td>${dato.control}</td>
      <td>${dato.detalle}</td>
      <td>${dato.tipodato}</td>
      <td>${dato.tpdeobserva}</td>
      <td>${dato.orden}</td>`;
    tbody.appendChild(tr);
  });
}

async function buscarReporte() {
  const inputReporte = document.getElementById('idLTYreporte');
  let dbId = document.getElementById('cliente-id')?.dataset.id;
  if (!dbId) {
    dbId = new URLSearchParams(window.location.search).get('id');
  }

  const idLTYreporte = inputReporte.value;

  if (!idLTYreporte || !dbId) {
    mostrarMensaje('Faltan datos necesarios para buscar.', 'error');
    return;
  }

  try {
    const response = await fetch(
      `/api/pegarExcel/buscarReporte.php?idLTYreporte=${idLTYreporte}&id=${dbId}`,
    );
    const data = await response.json();
    if (data?.registros?.length > 0) {
      actualizarTabla(data.registros);
      const ultimoOrden = data.registros[data.registros.length - 1]?.orden;
      document.getElementById('reporteNombre').innerText =
        `${data.registros[0].nombre_reporte}`;
      document.getElementById('ultimoOrden').innerText = ultimoOrden;

      window.ultimoOrdenJS = Math.max(...data.registros.map((d) => d.orden));
      window.nombreReporteJS = data.registros[0].nombre_reporte ?? '';
    } else {
      mostrarMensaje('No se encontraron registros.', 'info');
    }
  } catch (err) {
    console.error('❌ Error al obtener datos:', err);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('procesarBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    procesarDatos(window.ultimoOrdenJS, window.nombreReporteJS);
  });

  document.getElementById('limpiarBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    limpiarDatos();
  });

  document.getElementById('guardarBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    guardarDatosEnBaseDeDatos();
  });

  document.getElementById('reporteForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    buscarReporte();
  });
});
