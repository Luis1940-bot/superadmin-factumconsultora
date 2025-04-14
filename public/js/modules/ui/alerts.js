/**
 * Muestra un mensaje flotante en pantalla
 *
 * @param {string} mensaje - Texto o HTML a mostrar
 * @param {string} tipo - "ok", "error", "warning", "info"
 * @param {number} duracion - Tiempo en ms (opcional, por defecto 4000)
 */
export function mostrarMensaje(mensaje, tipo = 'info', duracion = 5000) {
  const div = document.createElement('div');

  // Iconos por tipo
  const iconos = {
    ok: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️',
  };

  const icono = iconos[tipo] || '🔔';

  // Permitir HTML pero evitando XSS (solo si el contenido es seguro)
  // div.innerHTML = `<strong>${icono}</strong> ${mensaje}`;
  div.innerHTML = `
    <strong>${icono}</strong> ${mensaje}
    <button class="cerrar-alerta" title="Cerrar">✖</button>
  `;
  // Estilos generales
  div.style.position = 'fixed';
  div.style.top = '20px';
  div.style.left = '50%';
  div.style.transform = 'translateX(-50%)';
  div.style.padding = '12px 24px';
  div.style.borderRadius = '6px';
  div.style.zIndex = '9999';
  div.style.fontFamily = 'monospace';
  div.style.fontSize = '1em';
  div.style.boxShadow = '0 0 12px rgba(0,0,0,0.4)';
  div.style.maxWidth = '90%';
  div.style.textAlign = 'center';
  div.style.display = 'flex';
  div.style.alignItems = 'center';
  div.style.justifyContent = 'space-between';
  div.style.gap = '1rem';

  // 🎨 Colores según tipo
  switch (tipo) {
    case 'ok':
      div.style.backgroundColor = '#28a745';
      div.style.color = '#fff';
      break;
    case 'error':
      div.style.backgroundColor = '#dc3545';
      div.style.color = '#fff';
      break;
    case 'warning':
      div.style.backgroundColor = '#ffc107';
      div.style.color = '#000';
      break;
    default: // info
      div.style.backgroundColor = '#17a2b8';
      div.style.color = '#fff';
  }
  // Estilo botón de cierre
  const btnCerrar = div.querySelector('.cerrar-alerta');
  btnCerrar.style.background = 'transparent';
  btnCerrar.style.border = 'none';
  btnCerrar.style.color = 'inherit';
  btnCerrar.style.cursor = 'pointer';
  btnCerrar.style.fontSize = '1rem';

  btnCerrar.addEventListener('click', () => {
    div.remove();
  });

  document.body.appendChild(div);

  setTimeout(() => {
    div.remove();
  }, duracion);
}

// EJEMPLO DE USO
// import { mostrarMensaje } from './modules/ui/alerts.js';

// mostrarMensaje('Bienvenido al sistema', 'ok');

// mostrarMensaje('Hubo un error con los datos', 'error');

// mostrarMensaje('⚙️ Guardando cambios...', 'info', 6000);

// mostrarMensaje('<em>Advertencia:</em> no olvides guardar', 'warning');
