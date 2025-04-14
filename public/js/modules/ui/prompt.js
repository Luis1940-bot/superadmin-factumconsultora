/**
 * Muestra un input modal personalizado y devuelve el valor ingresado.
 *
 * @param {string} titulo - Título del prompt
 * @param {string} textoBoton - Texto del botón (opcional)
 * @param {string} valorPorDefecto - Valor inicial del input (opcional)
 * @returns {Promise<string|null>} Resolución del valor ingresado o null si canceló
 */
export function mostrarPrompt(
  titulo = 'Ingrese un valor',
  textoBoton = 'Aceptar',
  valorPorDefecto = '',
) {
  return new Promise((resolve) => {
    // Si no existe el modal, lo creamos dinámicamente (solo una vez)
    let modal = document.getElementById('customPrompt');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'customPrompt';
      modal.innerHTML = `
        <div class="prompt-modal">
          <h3 id="promptTitulo">Prompt</h3>
          <input type="text" id="promptInput" />
          <div class="prompt-buttons">
            <button id="promptOk">Aceptar</button>
            <button id="promptCancelar">Cancelar</button>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
    }

    const input = modal.querySelector('#promptInput');
    const tituloEl = modal.querySelector('#promptTitulo');
    const btnOk = modal.querySelector('#promptOk');
    const btnCancel = modal.querySelector('#promptCancelar');

    // Establecer valores
    tituloEl.textContent = titulo;
    btnOk.textContent = textoBoton;
    input.value = valorPorDefecto;

    // Mostrar el modal
    modal.classList.add('show');

    // Limpieza de eventos previos
    btnOk.onclick = () => {
      modal.classList.remove('show');
      resolve(input.value.trim());
    };

    btnCancel.onclick = () => {
      modal.classList.remove('show');
      resolve(null);
    };

    input.onkeydown = (e) => {
      if (e.key === 'Enter') btnOk.click();
      if (e.key === 'Escape') btnCancel.click();
    };

    input.focus();
  });
}
