import { mostrarMensaje } from '../../js/modules/ui/alerts.js';

document.getElementById('btnRecargar')?.addEventListener('click', () => {
  window.location.reload();
});

document.getElementById('btnCerrar')?.addEventListener('click', () => {
  window.close();
});

// Filtro por nombre
document.getElementById('filtroRutinas')?.addEventListener('input', (e) => {
  const texto = e.target.value.toLowerCase();
  const filas = document.querySelectorAll('table tbody tr');

  filas.forEach((filaOriginal) => {
    const fila = filaOriginal; // alias para evitar modificar el parámetro directamente
    const primeraCelda = fila.querySelector('td');
    if (!primeraCelda) return;

    const nombre = primeraCelda.textContent.toLowerCase();
    const visible = nombre.includes(texto);
    fila.style.display = visible ? '' : 'none';
  });
});

// Mostrar parámetros (modal)
function mostrarModalParametros(nombre, data) {
  const { params, conteo } = data;
  const modal = document.getElementById('modalParametros');
  const titulo = document.getElementById('paramModalTitulo');
  const cuerpo = document.getElementById('paramModalCuerpo');

  titulo.textContent = `Parámetros de ${nombre}`;
  if (!params || params.length === 0) {
    cuerpo.innerHTML = '<p>⚠️ Sin parámetros definidos.</p>';
  } else {
    cuerpo.innerHTML = `
      <p><strong>🧾 Parámetros:</strong> ${conteo.IN} IN, ${conteo.OUT} OUT, ${conteo.INOUT} INOUT</p>
      <table>
        <thead>
          <tr><th>Nombre</th><th>Tipo</th><th>Modo</th><th>Posición</th></tr>
        </thead>
        <tbody>
          ${params
            .map(
              (p, i) => `
            <tr>
              <td>${p.PARAMETER_NAME || `param${i}`}</td>
              <td>${p.DTD_IDENTIFIER}</td>
              <td>${p.PARAMETER_MODE}</td>
              <td>${p.ORDINAL_POSITION}</td>
            </tr>
          `,
            )
            .join('')}
        </tbody>
      </table>
    `;
  }

  modal.classList.add('show');
}

document
  .getElementById('cerrarModalParametros')
  ?.addEventListener('click', () => {
    document.getElementById('modalParametros').classList.remove('show');
  });

document.querySelectorAll('.btn-parametros').forEach((btn) => {
  btn.addEventListener('click', () => {
    const { id } = btn.dataset;
    fetch(`ver_parametros.php?id=${encodeURIComponent(id)}`)
      .then((res) => res.json())
      .then((data) => mostrarModalParametros(id, data))
      .catch(() => mostrarMensaje('❌ Error al obtener parámetros', 'error'));
  });
});

// Ejecutar rutina (modal)
function mostrarFormularioTest(nombre, tipo, parametros = []) {
  const modal = document.getElementById('modalTest');
  const titulo = document.getElementById('testModalTitulo');
  const cuerpo = document.getElementById('testModalCuerpo');
  const salida = document.getElementById('testResultado');

  titulo.textContent = `🧪 Ejecutar ${tipo} ${nombre}`;
  salida.innerHTML = '';

  let formHtml = `<form id="formEjecutar">`;

  parametros.forEach((p, i) => {
    const paramName = p.PARAMETER_NAME || `param${i}`;
    const type = (p.DATA_TYPE || '').toLowerCase();
    let inputType = 'text';

    if (type === 'date') inputType = 'date';
    else if (type.includes('int')) inputType = 'number';
    else if (type === 'datetime') inputType = 'datetime-local';

    formHtml += `
      <label>${paramName} (${p.DTD_IDENTIFIER}):</label>
      <input type="${inputType}" name="params[]" placeholder="${paramName}" />
    `;
  });
  formHtml += `
  <input type="hidden" name="nombre" value="${nombre}">
  <input type="hidden" name="tipo" value="${tipo}">
  <div class="form-botones">
    <button type="submit">Ejecutar aquí</button>
    <button type="button" id="btnVerEnNuevaPestana">🪟 Ver en nueva pestaña</button>
  </div>
</form>`;

  cuerpo.innerHTML = formHtml;
  modal.classList.add('show');

  document.getElementById('formEjecutar').addEventListener('submit', (e) => {
    e.preventDefault();
    const data = new FormData(e.target);

    fetch('ejecutar_rutina.php', {
      method: 'POST',
      body: data,
    })
      .then((res) => res.text())
      .then((html) => {
        salida.innerHTML = html;
      })
      .catch(() => {
        salida.innerHTML = '❌ Error al ejecutar';
      });
  });

  document
    .getElementById('btnVerEnNuevaPestana')
    ?.addEventListener('click', () => {
      const form = document.getElementById('formEjecutar');
      const data = new FormData(form);
      const nombre2 = data.get('nombre');
      const tipo2 = data.get('tipo');
      const params = data.getAll('params[]');

      // const queryParams = new URLSearchParams({ nombre2, tipo2 });
      const queryParams = new URLSearchParams({
        nombre: nombre2,
        tipo: tipo2,
      });

      params.forEach((p) => queryParams.append('params[]', p));

      const url = `resultado_rutina.php?${queryParams.toString()}`;
      window.open(url, '_blank');
    });
}

document.querySelectorAll('.btn-test').forEach((btn) => {
  btn.addEventListener('click', () => {
    const { id } = btn.dataset;
    const { tipo } = btn.dataset;

    fetch(`ver_parametros.php?id=${encodeURIComponent(id)}`)
      .then((res) => res.json())
      .then((data) => mostrarFormularioTest(id, tipo, data.params))
      .catch(() => mostrarMensaje('❌ Error al preparar ejecución', 'error'));
  });
});

document.getElementById('cerrarModalTest')?.addEventListener('click', () => {
  document.getElementById('modalTest').classList.remove('show');
});
