import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

// ✅ Función para calcular MD5 en JavaScript (idéntico a PHP)
function md5(str) {
  return window.CryptoJS.MD5(str).toString();
}

// ✅ Función para generar código alfabético idéntico a PHP
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

async function procesarDatos(ultimoOrdenJS, nombreReporteJS) {
  // console.log("✅ Botón 'Procesar' clickeado");

  const tabla = document
    .getElementById('dataTable')
    .getElementsByTagName('tbody')[0];
  tabla.innerHTML = ''; // Limpiar la tabla antes de insertar nuevos datos

  // Obtener valores pegados desde los textarea
  const campos = document.getElementById('campoInput').value.split('\n');
  const detalles = document.getElementById('detalleInput').value.split('\n');
  const tiposDato = document.getElementById('tipoDatoInput').value.split('\n');
  const tpObservas = document
    .getElementById('tpObservaInput')
    .value.split('\n');

  // Determinar la cantidad de filas a procesar (según el textarea con más datos)
  const totalRegistros = Math.max(
    campos.length,
    detalles.length,
    tiposDato.length,
    tpObservas.length,
  );

  // Crear una lista de promesas para generar códigos en paralelo
  const promesasCodigos = Array.from(
    { length: totalRegistros },
    async (_, i) => {
      const ordenActual = parseInt(ultimoOrdenJS, 10) + i + 1; // Calcular el número correcto
      const ordenNum = ordenActual - 1;
      // ✅ Asegurar que siempre tenga 4 dígitos
      const ordenStr = ordenActual.toString().padStart(4, '0');

      const codigo = await generarCodigoAlfabetico(nombreReporteJS, ordenStr);

      return { codigo, ordenNum };
    },
  );

  // Resolver todas las promesas al mismo tiempo
  const codigosGenerados = await Promise.all(promesasCodigos);

  // Insertar los datos en la tabla
  for (let i = 0; i < totalRegistros; i++) {
    const fila = tabla.insertRow();

    // Obtener valores de cada campo (si existe, si no dejar "-")
    const campo = campos[i] ? campos[i].trim() : '-';
    const detalle = detalles[i] ? detalles[i].trim() : '-';
    const tipoDato = tiposDato[i] ? tiposDato[i].trim() : '-';
    const tpObserva = tpObservas[i] ? tpObservas[i].trim() : '-';

    if (campo && campo !== '-') {
      // Usar los códigos generados en paralelo
      const { codigo, ordenNum } = codigosGenerados[i];

      // Insertar valores en la fila
      fila.insertCell(0).textContent = i + 1;
      fila.insertCell(1).textContent = campo;
      fila.insertCell(2).textContent = detalle;
      fila.insertCell(3).textContent = tipoDato;
      fila.insertCell(4).textContent = tpObserva;
      fila.insertCell(5).textContent = ordenNum;
      fila.insertCell(6).textContent = codigo;
    }
  }
  [...tabla.rows].forEach((row) => {
    const isEmpty = [...row.cells].every((cell) => !cell.textContent.trim());
    if (isEmpty) row.remove();
  });
}

async function guardarDatosEnBaseDeDatos() {
  // Obtener los datos de la tabla
  const tabla = document
    .getElementById('dataTable')
    .getElementsByTagName('tbody')[0];
  const filas = tabla.getElementsByTagName('tr');
  const idLTYreporte = document.getElementById('idLTYreporte').value;
  const idLTYcliente = document.getElementById('idCliente').dataset.id;

  if (filas.length === 0) {
    // eslint-disable-next-line no-alert
    alert('No hay datos para guardar.');
    return;
  }

  // 🔹 Obtener el último ID desde la tabla de registros existentes
  const tablaExistente = document
    .getElementById('tablaExistente')
    .getElementsByTagName('tbody')[0];
  const filasExistentes = tablaExistente.getElementsByTagName('tr');

  if (filasExistentes.length === 0) {
    // eslint-disable-next-line no-alert
    alert('No hay registros existentes.');
    return;
  }

  // 🔹 Tomamos el último ID de la primera columna de la última fila
  const ultimaFila = filasExistentes[filasExistentes.length - 1];
  const ultimoID = ultimaFila.getElementsByTagName('td')[0].textContent.trim();

  // 🔹 Obtener datos de cada fila nueva
  const datosParaGuardar = [];
  for (let i = 0; i < filas.length - 1; i++) {
    const celdas = filas[i].getElementsByTagName('td');
    const filaDatos = {
      control: celdas[6].textContent.trim(), // Campo
      nombre: celdas[1].textContent.trim(), // Nombre
      detalle: celdas[2].textContent.trim(), // Detalle
      tipodato: celdas[3].textContent.trim(), // TipoDato
      tpdeobserva: celdas[4].textContent.trim(), // TpObserva
      orden: parseInt(celdas[5].textContent.trim(), 10), // Orden
    };

    datosParaGuardar.push(filaDatos);
  }

  // 🔹 Agregamos `ultimoID` al JSON para enviarlo al servidor
  const payload = {
    ruta: '/addListaCampos',
    datos: datosParaGuardar,
    ultimoID,
    idLTYcliente,
    idLTYreporte,
  };
  // console.log(payload);
  // Enviar datos a PHP con `fetch`
  try {
    // eslint-disable-next-line no-unused-vars
    const { baseUrl, routes } = await getConfig();

    const response = await fetch(`${baseUrl}/api/router.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });
    const resultado = await response.json();

    if (resultado.success) {
      mostrarMensaje('Datos guardados correctamente.', 'ok');
      window.location.reload(); // Recargar la página después de guardar
    } else {
      mostrarMensaje('Error al guardar los datos.', 'error');
    }
  } catch (error) {
    console.error('Error en la petición:', error);
    mostrarMensaje('Ocurrio un error al conectar con el servidor.', 'error');
  }
}

// 🔹 Agregar evento al botón de guardar
document.addEventListener('DOMContentLoaded', () => {
  const btnGuardar = document.getElementById('guardarBtn');
  if (btnGuardar) {
    btnGuardar.onclick = async (event) => {
      event.preventDefault();
      await guardarDatosEnBaseDeDatos();
    };
  } else {
    console.error("❌ No se encontró el botón 'Guardar'.");
  }
});

// ✅ Función para limpiar los datos
function limpiarDatos() {
  // console.log("✅ Botón 'Limpiar' clickeado");
  window.location.href = 'pegarExcel.php';
}

// ✅ Esperar a que el DOM esté cargado antes de asignar eventos
document.addEventListener('DOMContentLoaded', () => {
  // console.log('🌍 DOM completamente cargado');

  const btnProcesar = document.getElementById('procesarBtn');
  const btnLimpiar = document.getElementById('limpiarBtn');

  if (btnProcesar) {
    btnProcesar.onclick = (event) => {
      event.preventDefault();
      procesarDatos(window.ultimoOrdenJS, window.nombreReporteJS);
    };
  } else {
    console.error("❌ No se encontró el botón 'Procesar'");
  }

  if (btnLimpiar) {
    btnLimpiar.onclick = (event) => {
      event.preventDefault();
      limpiarDatos();
    };
  } else {
    console.error("❌ No se encontró el botón 'Limpiar'");
  }
});
