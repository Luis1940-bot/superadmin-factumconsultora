import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

let plant;
// eslint-disable-next-line no-unused-vars
let cliente;

function extraerDatosCliente() {
  const h1 = document.querySelector('h1');
  const p = document.querySelector('p');

  // Extraer nombre del cliente desde el H1
  const nombreCliente = h1.textContent.replace('🎛️ Panel de ', '').trim();

  // Extraer ID desde el P usando expresión regular
  const match = p.textContent.match(/ID:\s*(\d+)/);
  const idCliente = match ? match[1] : null;

  return { nombreCliente, idCliente };
}
document.addEventListener('DOMContentLoaded', () => {
  const { nombreCliente, idCliente } = extraerDatosCliente();
  // console.log('🏢 Cliente:', nombreCliente);
  // console.log('🆔 ID:', idCliente);
  plant = idCliente;
  cliente = nombreCliente;

  // Podés usarlo para enviar a backend, filtrar datos, etc.
});

function validarEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

function checaRequeridos() {
  const email = document.getElementById('input-email');
  if (!validarEmail(email.value)) {
    mostrarMensaje('Hubo un problema con el email', 'error');
    return false;
  }

  const objeto = {
    email: email.value,
  };
  return { add: true, objeto };
}

async function autorizarCorreo() {
  const envia = checaRequeridos();

  if (envia.add) {
    try {
      // eslint-disable-next-line no-unused-vars
      const { baseUrl, routes } = await getConfig();
      const response = await fetch(`${baseUrl}/api/router.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ruta: '/checarEmail',
          planta: plant,
          email: envia.objeto.email.trim(),
        }),
      });
      const data = await response.json();

      if (data.success) {
        mostrarMensaje('Este email ya está registrado', 'info');
      } else {
        const q = {
          email: envia.objeto.email.trim(),
          plant, // aseguramos que sea número
        };

        const insert = await fetch(`${baseUrl}/api/router.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            q,
            ruta: '/nuevoAuth',
          }),
        });
        const insertado = await insert.json();
        if (insertado.success) {
          mostrarMensaje(
            `El email ${envia.objeto.email.trim()} se registró con éxito.`,
            'ok',
          );
        }
      }
    } catch (error) {
      console.error('Error en traerAuth:', error);
    }
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  const input = document.getElementById('input-email');
  input.value = '';
  const button = document.getElementById('btn-checar');
  button.addEventListener('click', () => {
    autorizarCorreo();
  });
  document.addEventListener('keydown', (e) => {
    if (e.target.matches('.input-email')) {
      if (e.key === ',' || e.key === ':' || e.key === "'" || e.key === '"') {
        e.preventDefault();
      }
    }
  });
});

document.getElementById('btn-cerrar').addEventListener('click', () => {
  const cerrado = window.close();
  if (!cerrado) {
    mostrarMensaje(
      'Esta pestaña no se puede cerrar automáticamente. Cerrala manualmente.',
      'warning',
    );
    // O redirigir:
    // window.location.href = 'https://factumconsultora.com';
  }
});
